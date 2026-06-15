import { Controller, Get, Param } from '@nestjs/common';
import { ApiResponse } from '@nestjs/swagger';
import { Bicycle, Tariff, TariffType } from 'generated/prisma';
import { Auth } from '../auth/decorators/auth.decorator';
import { RentService } from '../rent/rent.service';
import { TariffService } from './tariff.service';

@Controller('bicycles/:bicycleId/tariffs')
export class TariffController {
    constructor(
        private readonly tariffService: TariffService,
        private readonly rentService: RentService,
    ) {}

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    type: 'array',
                    items: {
                        oneOf: [
                            {
                                properties: {
                                    id: {
                                        type: 'string',
                                        example: '',
                                    },
                                    name: {
                                        type: 'string',
                                        example: '',
                                    },
                                    type: {
                                        enum: [TariffType.STATIC],
                                    },
                                    price: {
                                        type: 'number',
                                        example: 500,
                                    },
                                },
                                required: ['id', 'name', 'type', 'price'],
                            },
                            {
                                properties: {
                                    id: {
                                        type: 'string',
                                        example: '',
                                    },
                                    name: {
                                        type: 'string',
                                        example: '',
                                    },
                                    type: {
                                        enum: [TariffType.DYNAMIC],
                                    },
                                    price: {
                                        type: 'number',
                                        example: 500,
                                    },
                                    additionalPrices: {
                                        type: 'object',
                                        properties: {
                                            min: {
                                                type: 'number',
                                                example: 100,
                                            },
                                            max: {
                                                type: 'number',
                                                example: 500,
                                            },
                                        },
                                        required: ['min', 'max'],
                                    },
                                },
                                required: [
                                    'id',
                                    'name',
                                    'type',
                                    'price',
                                    'additionalPrices',
                                ],
                            },
                        ],
                    },
                },
            },
        },
    })
    @ApiResponse({
        status: 401,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: 'Unauthorized',
                },
                statusCode: {
                    type: 'number',
                    example: 401,
                },
            },
        },
    })
    @ApiResponse({
        status: 404,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: 'Bicycle not found',
                },
                error: {
                    type: 'string',
                    example: 'Not Found',
                },
                statusCode: {
                    type: 'number',
                    example: 404,
                },
            },
        },
    })
    @Get()
    @Auth()
    async getBicycleTariffs(@Param('bicycleId') bicycleId: Bicycle['id']) {
        const tariffs = await this.tariffService.getBicycleTariffs(bicycleId);

        return {
            data: tariffs,
        };
    }

    @ApiResponse({
        status: 401,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: 'Unauthorized',
                },
                statusCode: {
                    type: 'number',
                    example: 401,
                },
            },
        },
    })
    @Get('/:tariffId/price')
    @Auth()
    async getCurrentPrice(
        @Param('bicycleId') bicycleId: Bicycle['id'],
        @Param('tariffId') tariffId: Tariff['id'],
    ) {
        const currentPrice = await this.rentService.getCurrentRentPrice(
            bicycleId,
            tariffId,
        );

        return {
            data: {
                pricePerMin: currentPrice,
            },
        };
    }
}
