import { Module } from '@nestjs/common';
import { PrismaModule } from 'src/prisma/prisma.module';
import { BicycleService } from '../bicycle/bicycle.service';
import { ExternalServiceService } from '../external-service/external-service.service';
import { HistoryService } from '../history/history.service';
import { RentService } from '../rent/rent.service';
import { UserController } from './user.controller';
import { UserService } from './user.service';

@Module({
    imports: [PrismaModule],
    controllers: [UserController],
    providers: [UserService, BicycleService, HistoryService, RentService, ExternalServiceService],
    exports: [UserService]
})
export class UserModule {}
