import {
    Body,
    Controller,
    Post,
    UsePipes,
    ValidationPipe,
} from '@nestjs/common';
import { ApiBody, ApiResponse } from '@nestjs/swagger';
import { User } from 'generated/prisma';
import { Auth } from '../auth/decorators/auth.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { TransactionDto } from './dto/transaction.dto';
import { PayService } from './pay.service';

@Controller('users/me/transactions')
export class PayController {
    constructor(private readonly payService: PayService) {}

    @ApiBody({
        type: TransactionDto,
    })
    @ApiResponse({
        status: 201,
        schema: {
            properties: {
                data: {
                    properties: {
                        balance: {
                            type: 'number',
                            example: 10000,
                        },
                    },
                },
            },
        },
    })
    @ApiResponse({
        status: 400,
        schema: {
            properties: {
                message: {
                    type: 'array',
                    example: ['amount must not be greater than 100000'],
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
        status: 409,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: `The balance cannot be less than zero`,
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
    @Post('/')
    @Auth()
    @UsePipes(new ValidationPipe())
    async replenishment(
        @Body() dto: TransactionDto,
        @CurrentUser() currentUser: Pick<User, 'id'>,
    ) {
        const data = await this.payService.transaction(dto, currentUser.id);

        return {
            data,
        };
    }
}
