import { Injectable, UnauthorizedException } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { PassportStrategy } from '@nestjs/passport';
import { ExtractJwt, Strategy } from 'passport-jwt';
import { UserService } from '../../user/user.service';

@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {
    constructor(
        private readonly configService: ConfigService,
        private readonly userService: UserService,
    ) {
        // @ts-ignore
        super({
            jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
            ignoreExpiration: true,
            secretOrKey: configService.get('JWT_SECRET_KEY'),
            passReqToCallback: true,
        });
    }

    async validate(
        req,
        payload: {
            email: string;
        },
    ) {
        // retrieve minimal user data for token validation
        const user = await this.userService.getUser(
            {
                email: payload.email,
            },
            {
                id: true,
            },
        );

        if (!user) throw new UnauthorizedException('Invalid user');

        const accessToken = ExtractJwt.fromAuthHeaderAsBearerToken()(req);
        if (!accessToken) throw new UnauthorizedException('Missing token');

        const token = await this.userService.getUserToken(user.id, accessToken);
        if (!token) throw new UnauthorizedException('Invalid token');

        return user;
    }
}
