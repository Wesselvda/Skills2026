import { Module } from '@nestjs/common';
import { PrismaModule } from 'src/prisma/prisma.module';
import { ExternalServiceService } from '../external-service/external-service.service';
import { RentService } from '../rent/rent.service';
import { TariffController } from './tariff.controller';
import { TariffService } from './tariff.service';

@Module({
    imports: [PrismaModule],
    controllers: [TariffController],
    providers: [TariffService, RentService, ExternalServiceService],
})
export class TariffModule {}
