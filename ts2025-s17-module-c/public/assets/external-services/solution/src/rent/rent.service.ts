import {
    BadRequestException,
    ConflictException,
    Injectable,
    NotFoundException,
    UnauthorizedException,
    UnprocessableEntityException,
} from '@nestjs/common';
import { plainToInstance } from 'class-transformer';
import { isUUID, validate } from 'class-validator';
import {
    Bicycle,
    BicycleStatus,
    Category,
    HistoryType,
    Tariff,
    TariffType,
    User,
} from 'generated/prisma';
import { randomUUID } from 'node:crypto';
import { createWriteStream } from 'node:fs';
import { join } from 'node:path';
import { PrismaService } from 'src/prisma/prisma.service';
import { ExternalServiceService } from '../external-service/external-service.service';
import { FinishRentBicycleDto } from './dto/finish.dto';
import { RentBicycleDto } from './dto/rent.dto';

@Injectable()
export class RentService {
    private readonly INSURANCE_COST = 1000;
    private readonly WEAR_PER_MIN = 0.1;

    constructor(
        private prisma: PrismaService,
        private externalServiceService: ExternalServiceService,
    ) {}

    /**
     * Return only one active booking per user.
     */
    async getCurrentRent(userId: User['id']) {
        const booking = await this.prisma.booking.findFirst({
            where: {
                userId,
                endedAt: null,
            },
            select: {
                id: true,
                pricePerMin: true,
                startedAt: true,
                bicycle: {
                    select: {
                        name: true,
                    },
                },
            },
        });

        return booking;
    }

    async rent(dto: RentBicycleDto, userId: User['id']) {
        if (!isUUID(dto.bicycleId) || !isUUID(dto.tariffId))
            throw new UnprocessableEntityException();

        const user = await this.prisma.user.findFirst({
            where: {
                id: userId,
            },
        });

        if (!user) throw new UnauthorizedException();

        const oldBooking = await this.getCurrentRent(userId);

        if (oldBooking)
            throw new ConflictException(`You're already renting a bike`);

        const bicycle = await this.prisma.bicycle.findFirst({
            where: {
                id: dto.bicycleId,
            },
        });

        if (!bicycle) throw new NotFoundException('Bicycle not found');

        if (bicycle.percentageOfWear >= 50)
            throw new ConflictException('The bike is broken');

        const oldBicycleBooking = await this.prisma.booking.findFirst({
            where: {
                bicycleId: bicycle.id,
                endedAt: null,
            },
        });

        if (oldBicycleBooking)
            throw new ConflictException('The bike is already taken');

        if (bicycle.status !== BicycleStatus.AVAILABLE)
            throw new ConflictException('The bike is not available');

        if (bicycle.percentageOfWear >= 50)
            throw new ConflictException('The bike is broken');

        if (dto.promoCode)
            await this.validatePromoCode(dto.promoCode, bicycle.categoryId);

        const tariff = await this.prisma.tariff.findFirst({
            where: {
                id: dto.tariffId,
            },
        });

        if (!tariff) throw new NotFoundException('Tariff not found');

        if (user.balance < this.INSURANCE_COST)
            throw new ConflictException(
                'There are not enough funds for insurance',
            );

        let pricePerMin = await this.calculateRentPrice(tariff);

        await this.prisma.$transaction([
            this.prisma.user.update({
                where: {
                    id: userId,
                },
                data: {
                    balance: user.balance - this.INSURANCE_COST,
                },
            }),
            this.prisma.booking.create({
                data: {
                    startedAt: new Date(),
                    pricePerMin,
                    bicycleId: bicycle.id,
                    tariffId: tariff.id,
                    userId,
                },
            }),
        ]);

        return;
    }

    private async validatePromoCode(code: string, categoryId: Category['id']) {
        const promoCode = await this.prisma.promoCode.findFirst({
            where: {
                code,
                category: {
                    id: categoryId,
                },
            },
        });

        if (!promoCode) throw new NotFoundException('Promo code not found');

        const isExpiredPromoCodeTime =
            +new Date(promoCode.expiresAt) - +new Date();

        if (isExpiredPromoCodeTime <= 0)
            throw new ConflictException('The promo code is not valid');

        return true;
    }

    async finish(
        dto: FinishRentBicycleDto,
        photos: string[],
        userId: User['id'],
    ) {
        const currentBooking = await this.getCurrentRent(userId);

        if (!currentBooking)
            throw new BadRequestException(`Don't have an active bike rental`);

        const user = await this.prisma.user.findFirst({
            where: {
                id: userId,
            },
        });

        if (!user) throw new UnauthorizedException();

        const endedAt = new Date();
        const ms = +endedAt - +currentBooking.startedAt;
        const fullPrice = (ms / 1000 / 60) * currentBooking.pricePerMin;

        let percentageOfWear = Math.floor((ms / 1000 / 60) * 0.1);

        let newBalance = user.balance - fullPrice;

        const booking = await this.prisma.booking.findFirst({
            where: {
                id: currentBooking.id,
            },
            select: {
                bicycle: {
                    select: {
                        id: true,
                        percentageOfWear: true,
                    },
                },
            },
        });

        await this.prisma.$transaction([
            this.prisma.booking.update({
                where: {
                    id: currentBooking.id,
                },
                data: {
                    fullPrice,
                    rating: Number(dto?.rating) || null,
                    endedAt,
                    percentageOfWear,
                    photos: JSON.stringify(photos),
                },
            }),
            this.prisma.bicycle.update({
                where: {
                    id: booking?.bicycle.id,
                },
                data: {
                    percentageOfWear:
                        booking!.bicycle.percentageOfWear + percentageOfWear,
                },
            }),
            this.prisma.user.update({
                where: {
                    id: userId,
                },
                data: {
                    balance: newBalance >= 0 ? newBalance + 1000 : newBalance,
                },
            }),
            this.prisma.balanceHistory.create({
                data: {
                    userId,
                    type: HistoryType.RENTAL,
                    value: fullPrice,
                },
            }),
        ]);

        return;
    }

    async uploadPhotos(req) {
        const parts = req.parts();

        const photos: string[] = [];
        let rating: number | undefined;

        for await (const part of parts) {
            if (part.type === 'file') {
                const filename = `${randomUUID()}-${part.filename}`;
                const filePath = join(process.cwd(), 'public/images', filename);

                await this.saveFileStream(part.file, filePath);

                photos.push(filename);
            } else if (part.type === 'field' && part.fieldname === 'rating') {
                rating = parseInt(part.value, 10);
            }
        }

        const dto = plainToInstance(FinishRentBicycleDto, { rating, photos });
        const errors = await validate(dto);

        if (errors.length > 0) {
            throw new BadRequestException();
        }

        return {
            dto,
            photos,
        };
    }

    private async saveFileStream(
        stream: NodeJS.ReadableStream,
        filePath: string,
    ) {
        return await new Promise<void>((resolve, reject) => {
            const writeStream = createWriteStream(filePath);
            stream.pipe(writeStream);

            writeStream.on('finish', () => resolve());
            writeStream.on('error', reject);
        });
    }

    async getCurrentRentPrice(
        bicycleId: Bicycle['id'],
        tariffId: Tariff['id'],
    ) {
        if (!isUUID(bicycleId) || !isUUID(tariffId))
            throw new NotFoundException();

        const bicycle = await this.prisma.bicycle.findFirst({
            where: {
                id: bicycleId,
            },
        });

        if (!bicycle) throw new NotFoundException();

        const tariff = await this.prisma.tariff.findFirst({
            where: {
                id: tariffId,
            },
        });

        if (!tariff) throw new NotFoundException();

        const pricePerMin = await this.calculateRentPrice(tariff);

        return pricePerMin;
    }

    async calculateRentPrice(tariff: Tariff) {
        let pricePerMin = tariff.basePrice;

        const traffic = this.externalServiceService.getCurrentTraffic();

        const colorMultiplier = this.mapTrafficMultiplier(
            traffic.color_scale_of_corks,
        );

        if (tariff.type === TariffType.DYNAMIC) {
            pricePerMin =
                tariff.basePrice *
                (1 + traffic.number_scale / 100) *
                colorMultiplier;

            pricePerMin = this.clamp(
                pricePerMin,
                tariff.minPrice!,
                tariff.maxPrice!,
            );
        }

        return Number(pricePerMin.toFixed(0));
    }

    private mapTrafficMultiplier(color: string): number {
        const map = {
            green: 0.9,
            yellow: 1,
            red: 1.2,
        };

        return map[color] ?? 1.5;
    }

    private clamp(value: number, min: number, max: number): number {
        return Math.min(Math.max(value, min), max);
    }
}
