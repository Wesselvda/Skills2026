import {
    Body,
    Controller,
    Get,
    HttpCode,
    Post,
    Query,
    Req,
    Res,
    UsePipes,
    ValidationPipe,
} from '@nestjs/common';
import { ApiBody, ApiQuery, ApiResponse } from '@nestjs/swagger';
import crypto from 'crypto';
import { ExtractJwt } from 'passport-jwt';
import { AuthService } from './auth.service';
import { Auth } from './decorators/auth.decorator';
import { SignInDto } from './dto/sign-in.dto';
import { SignUpDto } from './dto/sign-up.dto';

@Controller('auth')
export class AuthController {
    constructor(private readonly authService: AuthService) {}

    @ApiBody({
        type: SignInDto,
    })
    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    properties: {
                        accessToken: {
                            type: 'string',
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
            properties: {
                message: {
                    type: 'string',
                    example: 'Invalid email or password',
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
    })
    @ApiResponse({
        status: 400,
        schema: {
            properties: {
                message: {
                    type: 'array',
                    example: [
                        'password must be longer than or equal to 8 characters',
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
    @UsePipes(new ValidationPipe())
    @Post('/sign-in')
    @HttpCode(200)
    async signIn(
        @Body() dto: SignInDto,
        @Res({
            passthrough: true,
        })
        res: Response,
    ) {
        const { tokens } = await this.authService.signIn(dto);

        this.authService.addRefreshTokenToCookie(res, tokens.refreshToken);

        return {
            data: {
                accessToken: tokens.accessToken,
            },
        };
    }

    @ApiBody({
        type: SignUpDto,
    })
    @ApiResponse({
        status: 201,
        schema: {
            properties: {
                data: {
                    properties: {
                        accessToken: {
                            type: 'string',
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
            properties: {
                message: {
                    type: 'string',
                    example: 'Invalid email',
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
    })
    @ApiResponse({
        status: 400,
        schema: {
            properties: {
                message: {
                    type: 'array',
                    example: [
                        'password must be longer than or equal to 8 characters',
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
    @UsePipes(new ValidationPipe())
    @Post('/sign-up')
    @HttpCode(201)
    async signUp(
        @Body() dto: SignUpDto,
        @Res({
            passthrough: true,
        })
        res: Response,
    ) {
        const { tokens } = await this.authService.signUp(dto);

        this.authService.addRefreshTokenToCookie(res, tokens.refreshToken);

        return {
            data: {
                accessToken: tokens.accessToken,
            },
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
                    enum: ['Invalid token', 'Missing token'],
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
    })
    @Post('/sign-out')
    @HttpCode(204)
    @Auth()
    async signOut(
        @Req() req: Request,
        @Res({
            passthrough: true,
        })
        res: Response,
    ) {
        const accessToken = ExtractJwt.fromAuthHeaderAsBearerToken()(req);

        await this.authService.signOut(res, accessToken!);

        return;
    }

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    properties: {
                        link: {
                            type: 'string',
                            example:
                                'http://localhost:7000/authorize?response_type=code&client_id=cid-9143bf89&scope=openid&code_challenge=6oE295xCVhKuO_P44Qxp_yWAhLa2VHhUX_rysVlk1t4&code_challenge_method=S256',
                        },
                    },
                },
            },
        },
    })
    @Get('/oauth')
    async getOAuthLink(
        @Query('redirectUri') redirectUri: string,
        @Req()
        req: Request & {
            session: Map<string, string>;
        },
    ) {
        const verifier = crypto.randomBytes(32).toString('hex');

        // nonce for OAuth
        req.session.set('verifier', verifier);

        const link = this.authService.getOAuthLink(verifier, redirectUri);

        return {
            data: {
                link,
            },
        };
    }

    @ApiQuery({
        name: 'code',
        type: 'string',
        example: 'code-331564ec-388e-4b74-b2b7-da90cb536719',
    })
    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                data: {
                    properties: {
                        accessToken: {
                            type: 'string',
                            example: '',
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
        status: 422,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: 'Unprocessable Entity',
                },
                statusCode: {
                    type: 'number',
                    example: 422,
                },
            },
        },
    })
    @ApiResponse({
        status: 503,
        schema: {
            properties: {
                message: {
                    type: 'string',
                    example: 'Service Unavailable',
                },
                statusCode: {
                    type: 'number',
                    example: 503,
                },
            },
        },
    })
    @Post('/oauth')
    async loginOAuth(
        @Query('code') code: string,
        @Req()
        req: Request & {
            session: {
                verifier?: string;
            };
        },
    ) {
        const { tokens } = await this.authService.loginByOAuth(req, code);

        return {
            data: {
                accessToken: tokens.accessToken,
            },
        };
    }
}
