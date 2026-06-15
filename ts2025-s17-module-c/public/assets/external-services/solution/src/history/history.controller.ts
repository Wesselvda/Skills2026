import { Controller, Get } from '@nestjs/common';
import { User } from 'generated/prisma';
import { Auth } from '../auth/decorators/auth.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { HistoryService } from './history.service';

@Controller('histories')
export class HistoryController {
    constructor(private readonly historyService: HistoryService) {}

    @Get('/')
    @Auth()
    async getHistories(@CurrentUser() currentUser: Pick<User, 'id'>) {
        const histories = await this.historyService.getHistories(
            currentUser.id,
        );

        return {
            data: histories,
        };
    }
}
