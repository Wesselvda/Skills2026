import {
    Body,
    Controller,
    Get,
    HttpCode,
    Post,
    Req,
    UsePipes,
    ValidationPipe,
} from '@nestjs/common';
import { ApiBody, ApiResponse } from '@nestjs/swagger';
import { ApplicationStatus, HistoryType, User } from 'generated/prisma';
import { Auth } from '../auth/decorators/auth.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { BicycleService } from '../bicycle/bicycle.service';
import { FinishRentBicycleDto } from '../rent/dto/finish.dto';
import { RentBicycleDto } from '../rent/dto/rent.dto';
import { RentService } from '../rent/rent.service';
import { UserService } from './user.service';

@Controller('users')
export class UserController {
    constructor(
        private readonly userService: UserService,
        private readonly bicycleService: BicycleService,
        private readonly rentService: RentService,
    ) {}

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
                        email: {
                            type: 'string',
                            example: '',
                        },
                        phone: {
                            type: 'string',
                            example: '',
                        },
                        balance: {
                            type: 'number',
                            example: '',
                        },
                    },
                },
            },
        },
    })
    @ApiResponse({
        status: 401,
        schema: {
            oneOf: [
                {
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'Invalid token',
                        },
                        error: {
                            type: 'string',
                            example: 'Unauthorized',
                        },
                        statusCode: {
                            type: 'number',
                            example: 401,
                        },
                    },
                },
            ],
        },
    })
    @Get('/me')
    @Auth()
    async getUser(@CurrentUser() currentUser: Pick<User, 'id'>) {
        const user = await this.userService.getUser(
            {
                id: currentUser.id,
            },
            {
                name: true,
                email: true,
                phone: true,
                balance: true,
            },
        );

        return {
            data: user,
        };
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
                            name: {
                                type: 'string',
                                example: '',
                            },
                            percentageOfWear: {
                                type: 'number',
                                example: 5,
                            },
                            isOwner: {
                                type: 'boolean',
                                example: true,
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
    @Get('/me/bicycles')
    @Auth()
    async getMyBicycles(@CurrentUser() currentUser: Pick<User, 'id'>) {
        const bicycles = await this.bicycleService.getMyBicycles(
            currentUser.id,
        );

        return {
            data: bicycles,
        };
    }

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                balance: {
                    type: 'number',
                    example: 0,
                },
                payments: {
                    items: {
                        properties: {
                            type: {
                                enum: Object.values(HistoryType),
                                example: HistoryType.MODERATION_REWARD,
                            },
                            value: {
                                type: 'number',
                                example: 0,
                            },
                            createdAt: {
                                type: 'date',
                                example: '',
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
            oneOf: [
                {
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'Invalid token',
                        },
                        error: {
                            type: 'string',
                            example: 'Unauthorized',
                        },
                        statusCode: {
                            type: 'number',
                            example: 401,
                        },
                    },
                },
            ],
        },
    })
    @Get('/me/transactions')
    @Auth()
    async getMyPayments(@CurrentUser() currentUser: Pick<User, 'id'>) {
        const data = await this.userService.getMyPayments(currentUser.id);

        return {
            data,
        };
    }

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    properties: {
                        id: {
                            type: 'string',
                            example: '',
                        },
                        pricePerMin: {
                            type: 'number',
                            example: 100,
                        },
                        startedAt: {
                            type: 'date',
                            example: '',
                        },
                        bicycle: {
                            properties: {
                                name: {
                                    type: 'string',
                                    example: '',
                                },
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
            oneOf: [
                {
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'Invalid token',
                        },
                        error: {
                            type: 'string',
                            example: 'Unauthorized',
                        },
                        statusCode: {
                            type: 'number',
                            example: 401,
                        },
                    },
                },
            ],
        },
    })
    @Get('/me/rental')
    @Auth()
    async getCurrentRent(@CurrentUser() currentUser: Pick<User, 'id'>) {
        const currentRent = await this.rentService.getCurrentRent(
            currentUser.id,
        );

        return {
            data: currentRent,
        };
    }

    @ApiBody({
        type: RentBicycleDto,
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
                    example: ['tariffId should not be empty'],
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
            oneOf: [
                {
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'Invalid token',
                        },
                        error: {
                            type: 'string',
                            example: 'Unauthorized',
                        },
                        statusCode: {
                            type: 'number',
                            example: 401,
                        },
                    },
                },
            ],
        },
    })
    @ApiResponse({
        status: 404,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: 'Promo code not found',
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
            oneOf: [
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: `You're already renting a bike`,
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: `The bike is already taken`,
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: `The bike is not available`,
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: `The bike is broken`,
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: `The promo code is not valid`,
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: `There are not enough funds for insurance`,
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
            ],
        },
    })
    @Post('/me/rental')
    @HttpCode(204)
    @Auth()
    @UsePipes(new ValidationPipe())
    async rent(
        @Body() dto: RentBicycleDto,
        @CurrentUser() currentUser: Pick<User, 'id'>,
    ) {
        await this.rentService.rent(dto, currentUser.id);

        return;
    }

    @ApiBody({
        type: FinishRentBicycleDto,
    })
    @ApiResponse({
        status: 204,
    })
    @ApiResponse({
        status: 400,
        schema: {
            properties: {
                message: {
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
        status: 403,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: `Don't have an active bike rental`,
                },
                error: {
                    type: 'string',
                    example: 'Conflict',
                },
                statusCode: {
                    type: 'number',
                    example: 403,
                },
            },
        },
    })
    @ApiResponse({
        status: 401,
        schema: {
            oneOf: [
                {
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'Invalid token',
                        },
                        error: {
                            type: 'string',
                            example: 'Unauthorized',
                        },
                        statusCode: {
                            type: 'number',
                            example: 401,
                        },
                    },
                },
            ],
        },
    })
    @Post('/me/rental/complete')
    @HttpCode(204)
    @Auth()
    async finish(@Req() req, @CurrentUser() currentUser: Pick<User, 'id'>) {
        const { dto, photos } = await this.rentService.uploadPhotos(req);

        await this.rentService.finish(dto, photos, currentUser.id);

        return;
    }

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    properties: {
                        id: {
                            type: 'string',
                            example: '',
                        },
                        name: {
                            type: 'string',
                            example: '',
                        },
                        rating: {
                            type: 'number',
                            example: '4.6',
                        },
                        status: {
                            enum: Object.values(ApplicationStatus),
                        },
                    },
                },
            },
        },
    })
    @ApiResponse({
        status: 401,
        schema: {
            oneOf: [
                {
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
                {
                    properties: {
                        message: {
                            type: 'string',
                            example: 'Invalid token',
                        },
                        error: {
                            type: 'string',
                            example: 'Unauthorized',
                        },
                        statusCode: {
                            type: 'number',
                            example: 401,
                        },
                    },
                },
            ],
        },
    })
    @Get('/me/work')
    @Auth()
    async getWork(@CurrentUser() currentUser: Pick<User, 'id'>) {
        const work = await this.userService.getWork(currentUser.id);

        return {
            data: work || {},
        };
    }
}
