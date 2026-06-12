import {
    ConflictException,
    ForbiddenException,
    Injectable,
    NotFoundException,
    UnauthorizedException,
    UnprocessableEntityException,
} from '@nestjs/common';
import { isUUID } from 'class-validator';
import {
    ApplicationStatus,
    Bicycle,
    Booking,
    HistoryType,
    User,
} from 'generated/prisma';
import { PrismaService } from 'src/prisma/prisma.service';
import { RateBookingDto } from './dto/rate-booking.dto';
import { BicycleRepairType, RepairBicycleDto } from './dto/repair.dto';

const RepairCosts = {
    [BicycleRepairType.WASH]: 2000,
    [BicycleRepairType.REPAIR]: 10000,
    [BicycleRepairType.TIRES]: 6500,
    [BicycleRepairType.CHAIN]: 15000,
} as const;

const RepairWearRestore = {
    [BicycleRepairType.WASH]: 10,
    [BicycleRepairType.REPAIR]: 25,
    [BicycleRepairType.TIRES]: 35,
    [BicycleRepairType.CHAIN]: 30,
} as const;

@Injectable()
export class BicycleService {
    constructor(private prisma: PrismaService) {}

    async getBicycles() {
        const bicycles = await this.prisma.bicycle.findMany({
            select: {
                id: true,
                slug: true,
                locationX: true,
                locationY: true,
                status: true,
                bookings: {
                    where: {
                        endedAt: null,
                    },
                    select: {
                        id: true,
                    },
                },
            },
        });

        return bicycles.map((bicycle) => ({
            id: bicycle.id,
            slug: bicycle.slug,
            locationX: bicycle.locationX,
            locationY: bicycle.locationY,
            status: bicycle.bookings.length > 0 ? 'BUSY' : bicycle.status,
        }));
    }

    async getBicycle(bicycleId: Bicycle['id']) {
        if (!isUUID(bicycleId)) throw new UnprocessableEntityException();

        const bicycle = await this.prisma.bicycle.findFirst({
            where: {
                id: bicycleId,
            },
            select: {
                name: true,
                slug: true,
                description: true,
                percentageOfWear: true,
                locationX: true,
                locationY: true,
                status: true,
                pathToImage: true,
                bookings: {
                    select: {
                        rating: true,
                    },
                },
            },
        });

        if (!bicycle) throw new NotFoundException('Bicycle not found');

        return {
            name: bicycle.name,
            description: bicycle.description,
            percentageOfWear: bicycle.percentageOfWear,
            pathToImage: bicycle.pathToImage,
            rating: this.calcRating(
                bicycle.bookings.map((booking) => booking.rating ?? 0),
            ),
        };
    }

    calcRating(items: number[]): number {
        const alpha = 5;

        const sum = items.reduce((curr, item) => curr + item, 0);

        const rating = (5 * alpha + sum) / (alpha + items.length);

        return Number(rating.toFixed(1));
    }

    async getMyBicycles(userId: User['id']) {
        const bicycles = await this.prisma.bicycle.findMany({
            where: {
                OR: [
                    {
                        category: {
                            is: {
                                userId,
                            },
                        },
                    },
                    {
                        category: {
                            is: {
                                applications: {
                                    some: {
                                        userId,
                                        status: ApplicationStatus.APPROVED,
                                    },
                                },
                            },
                        },
                    },
                ],
            },
            select: {
                id: true,
                name: true,
                percentageOfWear: true,
                category: {
                    select: {
                        userId: true,
                    },
                },
            },
        });

        return bicycles.map((bicycle) => ({
            id: bicycle.id,
            name: bicycle.name,
            percentageOfWear: bicycle.percentageOfWear,
            isOwner: bicycle.category.userId === userId,
        }));
    }

    async repairBicycle(
        dto: RepairBicycleDto,
        userId: User['id'],
        bicycleId: Bicycle['id'],
    ) {
        const bicycle = await this.prisma.bicycle.findFirst({
            where: {
                id: bicycleId,
                category: {
                    is: {
                        userId,
                    },
                },
            },
        });

        if (!bicycle) throw new NotFoundException('Bicycle not found');

        const user = await this.prisma.user.findFirst({
            where: {
                id: userId,
            },
        });

        if (!user) throw new UnauthorizedException();

        const amount = RepairCosts[dto.type];
        const percent = RepairWearRestore[dto.type];

        if (user.balance < amount)
            throw new ConflictException(`You don't have enough funds`);

        return await this.prisma.$transaction([
            this.prisma.user.update({
                where: {
                    id: userId,
                },
                data: {
                    balance: {
                        decrement: amount,
                    },
                },
            }),
            this.prisma.bicycle.update({
                where: {
                    id: bicycleId,
                },
                data: {
                    percentageOfWear: Math.max(
                        0,
                        bicycle.percentageOfWear - percent,
                    ),
                },
            }),
        ]);
    }

    async getBicycleBookings(bicycleId: Bicycle['id'], userId: User['id']) {
        if (!isUUID(bicycleId)) throw new UnprocessableEntityException();

        const bicycle = await this.prisma.bicycle.findFirst({
            where: {
                id: bicycleId,
            },
            select: {
                id: true,
            },
        });

        if (!bicycle) throw new NotFoundException('Bicycle not found');

        const bookings = await this.prisma.booking.findMany({
            where: {
                bicycle: {
                    is: {
                        id: bicycleId,
                        category: {
                            is: {
                                userId,
                            },
                        },
                    },
                },
                endedAt: {
                    not: null,
                },
                userRating: null,
            },
            select: {
                id: true,
                percentageOfWear: true,
                photos: true,
            },
        });

        return bookings.map((booking) => ({
            ...booking,
            photos: JSON.parse(String(booking?.photos)),
        }));
    }

    async rateBooking(
        dto: RateBookingDto,
        bookingId: Booking['id'],
        userId: User['id'],
    ) {
        if (!isUUID(bookingId)) throw new UnprocessableEntityException();

        const booking = await this.prisma.booking.findFirst({
            where: {
                id: bookingId,
            },
            include: {
                bicycle: {
                    select: {
                        category: {
                            select: {
                                userId: true,
                            },
                        },
                    },
                },
            },
        });

        if (!booking || !booking.fullPrice)
            throw new NotFoundException('Rental not found');

        if (booking.userRating)
            throw new ForbiddenException('The rent has already been assessed');

        // commission is 5% of full price
        const income = booking.fullPrice * 0.05;

        const ownerId = booking.bicycle.category.userId;
        if (!ownerId) throw new UnauthorizedException();

        return await this.prisma.$transaction(async (tx) => {
            // retrieve owner
            const owner = await tx.user.findUnique({
                where: {
                    id: ownerId,
                },
            });

            if (!owner) throw new UnauthorizedException();

            if (owner.balance < income)
                throw new ForbiddenException(
                    `The bicycle owner doesn't have enough money`,
                );

            // debit owner
            await tx.user.update({
                where: {
                    id: ownerId,
                },
                data: {
                    balance: owner.balance - income,
                },
            });

            // current user
            const user = await tx.user.findUnique({
                where: {
                    id: userId,
                },
            });

            if (!user) throw new UnauthorizedException();

            await tx.user.update({
                where: {
                    id: userId,
                },
                data: {
                    balance: user.balance + income,
                },
            });

            // record balance history for auditor
            await tx.balanceHistory.create({
                data: {
                    type: HistoryType.MODERATION_REWARD,
                    value: income,
                    userId,
                },
            });

            // save rating
            return await tx.booking.update({
                where: {
                    id: bookingId,
                },
                data: {
                    userRating: Number(dto.rating),
                },
            });
        });
    }
}
