import { Module } from '@nestjs/common';
import { AuthModule } from './auth/auth.module';
import { BicycleModule } from './bicycle/bicycle.module';
import { ExternalServiceModule } from './external-service/external-service.module';
import { HistoryModule } from './history/history.module';
import { PayModule } from './pay/pay.module';
import { PrismaModule } from './prisma/prisma.module';
import { RentModule } from './rent/rent.module';
import { TariffModule } from './tariff/tariff.module';
import { UserModule } from './user/user.module';
import { WorkModule } from './work/work.module';

@Module({
    imports: [
        PrismaModule,
        AuthModule,
        BicycleModule,
        TariffModule,
        HistoryModule,
        UserModule,
        RentModule,
        PayModule,
        ExternalServiceModule,
        WorkModule,
    ],
    providers: [],
})
export class ApiModule {}
