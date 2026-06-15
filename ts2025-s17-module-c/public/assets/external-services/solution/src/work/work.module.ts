import { Module } from '@nestjs/common';
import { PrismaModule } from 'src/prisma/prisma.module';
import { WorkController } from './work.controller';
import { WorkService } from './work.service';

@Module({
    imports: [PrismaModule],
    controllers: [WorkController],
    providers: [WorkService],
})
export class WorkModule {}
