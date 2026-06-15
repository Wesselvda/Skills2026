import { Module } from '@nestjs/common';
import { PrismaModule } from 'src/prisma/prisma.module';
import { ExternalServiceService } from '../external-service/external-service.service';
import { RentService } from '../rent/rent.service';
import { BicycleController } from './bicycle.controller';
import { BicycleService } from './bicycle.service';

@Module({
    imports: [PrismaModule],
    controllers: [BicycleController],
    providers: [BicycleService, RentService, ExternalServiceService],
})
export class BicycleModule {}
