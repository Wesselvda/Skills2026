import {
    Body,
    Controller,
    Get,
    HttpCode,
    Param,
    Post,
    UsePipes,
    ValidationPipe,
} from '@nestjs/common';
import { ApiBody, ApiResponse } from '@nestjs/swagger';
import { BicycleStatus, User } from 'generated/prisma';
import { Auth } from '../auth/decorators/auth.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { RentService } from '../rent/rent.service';
import { BicycleService } from './bicycle.service';
import { RateBookingDto } from './dto/rate-booking.dto';
import { RepairBicycleDto } from './dto/repair.dto';

@Controller('bicycles')
export class BicycleController {
    constructor(
        private readonly bicycleService: BicycleService,
        private readonly rentService: RentService,
    ) {}

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    items: {
                        properties: {
                            id: {
                                type: 'string',
                                example: '',
                            },
                            slug: {
                                type: 'string',
                                example: '',
                            },
                            locationX: {
                                type: 'string',
                                example: '101.56',
                            },
                            locationY: {
                                type: 'string',
                                example: '56.11',
                            },
                            status: {
                                enum: Object.values({
                                    ...BicycleStatus,
                                    BUSY: 'BUSY',
                                }),
                            },
                        },
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
    @Get()
    @Auth()
    async getBicycles() {
        const bicycles = await this.bicycleService.getBicycles();

        return {
            data: bicycles,
        };
    }

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    properties: {
                        name: {
                            type: 'string',
                            example: '',
                        },
                        description: {
                            type: 'string',
                            example: '',
                        },
                        percentageOfWear: {
                            type: 'number',
                            example: 56,
                            minimum: 0,
                            maximum: 100,
                        },
                        pathToImage: {
                            type: 'string',
                            example: '',
                        },
                        rating: {
                            type: 'number',
                            example: '4.6',
                            minimum: 1,
                            maximum: 5,
                        },
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
    @Get('/:bicycleId')
    @Auth()
    async getBicycle(@Param('bicycleId') bicycleId) {
        const bicycle = await this.bicycleService.getBicycle(bicycleId);

        return {
            data: bicycle,
        };
    }

    @ApiBody({
        type: RepairBicycleDto,
    })
    @ApiResponse({
        status: 204,
    })
    @ApiResponse({
        status: 400,
        schema: {
            properties: {
                message: {
                    type: 'array',
                    example: [
                        'type must be one of the following values: wash, repair, tires, chain',
                    ],
                },
                error: {
                    type: 'string',
                    example: 'Bad Request',
                },
                statusCode: {
                    type: 'number',
                    example: 400,
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
    @ApiResponse({
        status: 409,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: `You don't have enough funds`,
                },
                error: {
                    type: 'string',
                    example: 'Conflict',
                },
                statusCode: {
                    type: 'number',
                    example: 409,
                },
            },
        },
    })
    @Post('/:bicycleId/repair')
    @HttpCode(204)
    @Auth()
    @UsePipes(new ValidationPipe())
    async repairBicycle(
        @CurrentUser() currentUser: Pick<User, 'id'>,
        @Param('bicycleId') bicycleId,
        @Body() dto: RepairBicycleDto,
    ) {
        await this.bicycleService.repairBicycle(dto, currentUser.id, bicycleId);

        return;
    }

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    items: {
                        properties: {
                            id: {
                                type: 'string',
                                example: '',
                            },
                            percentageOfWear: {
                                type: 'number',
                                example: 56,
                                minimum: 0,
                                maximum: 100,
                            },
                            photos: {
                                type: 'array',
                                example: [],
                            },
                        },
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
    @Get('/:bicycleId/rentals')
    @Auth()
    async getBicycleBookings(
        @CurrentUser() currentUser: Pick<User, 'id'>,
        @Param('bicycleId') bicycleId,
    ) {
        const bookings = await this.bicycleService.getBicycleBookings(
            bicycleId,
            currentUser.id,
        );

        return {
            data: bookings,
        };
    }

    @ApiBody({
        type: RateBookingDto,
    })
    @ApiResponse({
        status: 204,
    })
    @ApiResponse({
        status: 400,
        schema: {
            properties: {
                message: {
                    type: 'array',
                    example: ['some error'],
                },
                error: {
                    type: 'string',
                    example: 'Bad Request',
                },
                statusCode: {
                    type: 'number',
                    example: 400,
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
        status: 403,
        schema: {
            oneOf: [
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'The rent has already been assessed',
                        },
                        error: {
                            type: 'string',
                            example: 'Forbidden',
                        },
                        statusCode: {
                            type: 'number',
                            example: 400,
                        },
                    },
                },
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: `The bicycle owner doesn't have enough money`,
                        },
                        error: {
                            type: 'string',
                            example: 'Forbidden',
                        },
                        statusCode: {
                            type: 'number',
                            example: 400,
                        },
                    },
                },
            ],
        },
    })
    @ApiResponse({
        status: 404,
        schema: {
            oneOf: [
                {
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'Rental not found',
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
            ],
        },
    })
    @Post(`/:bicycleId/rentals/:rentalId/rate`)
    @HttpCode(204)
    @Auth()
    @UsePipes(new ValidationPipe())
    async rateBooking(
        @Body() dto: RateBookingDto,
        @CurrentUser() currentUser: Pick<User, 'id'>,
        @Param('bicycleId') bicycleId,
        @Param('rentalId') bookingId,
    ) {
        await this.bicycleService.rateBooking(dto, bookingId, currentUser.id);

        return;
    }
}
