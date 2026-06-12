import {
    Injectable,
    NotFoundException,
    UnprocessableEntityException,
} from '@nestjs/common';
import { isUUID } from 'class-validator';
import { Bicycle } from 'generated/prisma';
import { PrismaService } from 'src/prisma/prisma.service';

@Injectable()
export class TariffService {
    constructor(private prisma: PrismaService) {}

    async getBicycleTariffs(bicycleId: Bicycle['id']) {
        if (!isUUID(bicycleId)) throw new UnprocessableEntityException();

        const bicycle = await this.prisma.bicycle.findFirst({
            where: {
                id: bicycleId,
            },
        });

        if (!bicycle) throw new NotFoundException('Bicycle not found');

        const tariffs = await this.prisma.tariff.findMany({
            where: {
                categoryId: bicycle.categoryId,
            },
            select: {
                id: true,
                name: true,
                type: true,
                basePrice: true,
                minPrice: true,
                maxPrice: true,
            },
        });

        return tariffs.map((tariff) => ({
            id: tariff.id,
            name: tariff.name,
            type: tariff.type,
            price: tariff.basePrice,
            ...(tariff.minPrice !== null
                ? {
                      additionalPrices: {
                          min: tariff.minPrice,
                          max: tariff.maxPrice,
                      },
                  }
                : {}),
        }));
    }
}
