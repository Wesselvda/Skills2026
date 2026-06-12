import { ConfigService } from '@nestjs/config';
import { JwtModuleOptions } from '@nestjs/jwt';

/**
 * Centralized jwt configuration for DI usages.
 */
export const getJwtConfig = async (
    configService: ConfigService,
): Promise<JwtModuleOptions> => ({
    secret: configService.get('JWT_SECRET_KEY'),
});
