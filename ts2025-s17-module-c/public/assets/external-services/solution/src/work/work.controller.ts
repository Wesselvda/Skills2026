import { Controller, Get, Param, Post } from '@nestjs/common';
import { ApiResponse } from '@nestjs/swagger';
import { User } from 'generated/prisma';
import { Auth } from '../auth/decorators/auth.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { WorkService } from './work.service';

@Controller('works')
export class WorkController {
    constructor(private readonly workService: WorkService) {}

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
                            rating: {
                                type: 'number',
                                example: '4.6',
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
                            example: 'Invalid tokens',
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
    @Get()
    @Auth()
    async getWorks() {
        const works = await this.workService.getWorks();

        return {
            data: works,
        };
    }

    @ApiResponse({
        status: 204,
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
                    example: 'The application has already been sent',
                },
                error: {
                    type: 'string',
                    example: 'Conflict',
                },
                statusCode: {
                    type: 'number',
                    example: 401,
                },
            },
        },
    })
    @Post('/:userId')
    @Auth()
    async sendRequest(
        @CurrentUser() currentUser: Pick<User, 'id'>,
        @Param('userId') workId: User['id'],
    ) {
        await this.workService.sendRequest(workId, currentUser.id);

        return;
    }
}
