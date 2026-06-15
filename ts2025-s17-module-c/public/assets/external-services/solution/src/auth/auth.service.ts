import {
    BadRequestException,
    Injectable,
    ServiceUnavailableException,
    UnauthorizedException,
    UnprocessableEntityException,
} from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { verify } from 'argon2';
import axios from 'axios';
import crypto from 'crypto';
import { PrismaService } from 'src/prisma/prisma.service';
import { v4 } from 'uuid';
import { UserService } from '../user/user.service';
import { SignInDto } from './dto/sign-in.dto';
import { SignUpDto } from './dto/sign-up.dto';

@Injectable()
export class AuthService {
    REFRESH_TOKEN_NAME = 'refreshToken';
    REFRESH_TOKEN_EXPIRES_DAY = 6;
    ACCESS_TOKEN_EXPIRES_DAY = 2;

    constructor(
        private readonly jwtService: JwtService,
        private readonly userService: UserService,
        private readonly prismaService: PrismaService,
    ) {}

    /**
     * Issue jwt access/refresh tokens
     */
    private issueTokens(email: string) {
        const payload = {
            email,
        };

        const accessToken = this.jwtService.sign(payload, {
            expiresIn: `${this.ACCESS_TOKEN_EXPIRES_DAY}d`,
            jwtid: v4(),
        });

        const refreshToken = this.jwtService.sign(payload, {
            expiresIn: `${this.REFRESH_TOKEN_EXPIRES_DAY}d`,
            jwtid: v4(),
        });

        return {
            accessToken,
            refreshToken,
        };
    }

    addRefreshTokenToCookie(res, refreshToken: string) {
        const expiresIn = new Date();
        expiresIn.setDate(expiresIn.getDate() + this.REFRESH_TOKEN_EXPIRES_DAY);

        res.cookie(this.REFRESH_TOKEN_NAME, refreshToken, {
            expires: expiresIn,
            sameSite: 'strict',
            path: '/',
        });
    }

    removeRefreshTokenFromCookie(res) {
        res.cookie(this.REFRESH_TOKEN_NAME, '', {
            expires: new Date(0),
            sameSite: 'strict',
            path: '/',
        });
    }

    async signIn(dto: SignInDto) {
        const { id, email } = await this.validateUser(dto);

        const tokens = this.issueTokens(email);

        // store hashed token for ability to revoke sessions
        await this.prismaService.userToken.create({
            data: {
                value: tokens.accessToken,
                hash: crypto
                    .createHash('sha256')
                    .update(tokens.accessToken)
                    .digest('hex'),
                userId: id,
            },
        });

        return {
            tokens,
        };
    }

    async signUp(dto: SignUpDto) {
        const oldUser = await this.userService.getUser(
            {
                email: dto.email,
            },
            {
                id: true,
            },
        );

        if (oldUser) throw new UnauthorizedException('Invalid email');

        const user = await this.userService.createUser(dto);

        const tokens = this.issueTokens(user.email);

        await this.prismaService.userToken.create({
            data: {
                value: tokens.accessToken,
                hash: crypto
                    .createHash('sha256')
                    .update(tokens.accessToken)
                    .digest('hex'),
                userId: user.id,
            },
        });

        return {
            tokens,
        };
    }

    async signOut(res: Response, accessToken: string) {
        this.removeRefreshTokenFromCookie(res);

        await this.prismaService.userToken.delete({
            where: {
                hash: crypto
                    .createHash('sha256')
                    .update(accessToken)
                    .digest('hex'),
            },
        });
    }

    /**
     * OAuth login flow. Validates codes and obtains user profile.
     */
    async loginByOAuth(
        req: Request & {
            session: {
                verifier?: string;
            };
        },
        code: string,
    ) {
        if (!code) throw new UnprocessableEntityException();

        const oauthUrl = process.env.OAUTH_URL?.includes("localhost:7000") ? "http://oauth:7000" : process.env.OAUTH_URL;

        try {
            const verifier = req.session.verifier;

            const response = await axios.post(
                `${oauthUrl}/token`,
                {
                    grant_type: 'authorization_code',
                    code,
                    client_id: process.env.CLIENT_ID,
                    client_secret: process.env.CLIENT_SECRET,
                    //code_verifier: verifier,
                },
            );

            const userInfoResponse = await axios.get(
                `${oauthUrl}/userinfo`,
                {
                    headers: {
                        Authorization: `Bearer ${response.data.access_token}`,
                    },
                },
            );

            const user = await this.userService.findOrCreateUser({
                name: userInfoResponse.data.name,
                email: userInfoResponse.data.email,
                phone: userInfoResponse.data.phone,
                password: `123`,
            });

            const tokens = this.issueTokens(user.email);

            return {
                tokens,
            };
        } catch (error) {
            console.log(error);

            if (error.code === 'ECONNREFUSED') {
                throw new ServiceUnavailableException();
            }

            throw new BadRequestException();
        }
    }

    /**
     * Generate link for OAuth.
     */
    getOAuthLink(verifier: string, redirectUri: string) {
        const clientId = process.env.CLIENT_ID;

        const codeChallenge = crypto
            .createHash('sha256')
            .update(verifier)
            .digest('base64')
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '');

        return `${process.env.OAUTH_URL}/authorize?response_type=code&client_id=${clientId}&redirect_uri=${redirectUri}&scope=openid`; //&code_challenge=${codeChallenge}&code_challenge_method=S256`;
    }

    private async validateUser(dto: SignInDto) {
        const user = await this.userService.getUser(
            {
                email: dto.email,
            },
            {
                id: true,
                email: true,
                password: true,
            },
        );

        if (!user) throw new UnauthorizedException('Invalid email or password');

        const isValidPassword = await verify(user.password, dto.password);

        if (!isValidPassword)
            throw new UnauthorizedException('Invalid email or password');

        return user;
    }
}
