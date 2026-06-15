import fastifyCookie from '@fastify/cookie';
import fastifyHelmet from '@fastify/helmet';
import fastifyMultipart from '@fastify/multipart';
import fastifySecureSession from '@fastify/secure-session';
import { NestFactory } from '@nestjs/core';
import {
    FastifyAdapter,
    NestFastifyApplication,
} from '@nestjs/platform-fastify';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';
import { join } from 'path';
import { ApiModule } from './api.module';

async function bootstrap() {
    const app = await NestFactory.create<NestFastifyApplication>(
        ApiModule,
        new FastifyAdapter({
            logger: true,
        }),
    );

    await app.register(fastifyHelmet);
    await (app as unknown as any).register(fastifyCookie, {
        secret: process.env.COOKIE_SECRET_KEY,
    });

    app.register(fastifyMultipart, {
        limits: {
            fileSize: 100 * 1024 * 1024,
            files: 4,
        },
    });

    app.register(fastifySecureSession, {
        key: Buffer.from('a'.repeat(32)),
        cookie: {
            path: '/',
            httpOnly: true,
            secure: false,
            maxAge: 60 * 60, // 1 hour
        },
    });

    app.enableCors({
        origin: (origin, callback) => {
            callback(null, true);
        },
        methods: '*',
        allowedHeaders: [
            'Accept',
            'Accept-Version',
            'Content-Type',
            'Api-Version',
            'Origin',
            'X-Requested-With',
            'Authorization',
        ],
        credentials: true,
    });

    app.setGlobalPrefix('/api/v1');

    app.useStaticAssets({
        root: join(__dirname, '../..', 'public'),
        prefix: '/public/',
    });

    const config = new DocumentBuilder()
        .setTitle('VelOrda')
        .setDescription('Description')
        .setVersion('1.0')
        .addTag('velorda')
        .build();

    const documentFactory = () => SwaggerModule.createDocument(app, config);
    SwaggerModule.setup('api', app, documentFactory);

    await app.listen(process.env.API_PORT ?? 8000, '0.0.0.0');
}

bootstrap();
