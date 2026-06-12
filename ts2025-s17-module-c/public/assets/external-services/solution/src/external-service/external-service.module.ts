import { Module } from '@nestjs/common';
import { ExternalServiceController } from './external-service.controller';
import { ExternalServiceService } from './external-service.service';

@Module({
    controllers: [ExternalServiceController],
    providers: [ExternalServiceService],
})
export class ExternalServiceModule {}
