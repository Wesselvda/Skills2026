import { Module } from '@nestjs/common';
import { ExternalServiceService } from '../external-service/external-service.service';
import { RentService } from './rent.service';

@Module({
    controllers: [],
    providers: [RentService, ExternalServiceService],
})
export class RentModule {}
