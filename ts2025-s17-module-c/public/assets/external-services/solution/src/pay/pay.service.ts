import {
    ConflictException,
    Injectable,
    UnauthorizedException,
} from '@nestjs/common';
import { HistoryType, User } from 'generated/prisma';
import { PrismaService } from 'src/prisma/prisma.service';
import { TransactionDto, TransactionTypeDto } from './dto/transaction.dto';

@Injectable()
export class PayService {
    constructor(private prisma: PrismaService) {}

    async transaction(dto: TransactionDto, userId: User['id']) {
        const user = await this.prisma.user.findUnique({
            where: {
                id: userId,
            },
        });

        if (!user) throw new UnauthorizedException();

        if (
            dto.type === TransactionTypeDto.WITHDRAW &&
            user.balance < dto.amount
        )
            throw new ConflictException('The balance cannot be less than zero');

        const newBalance =
            dto.type === TransactionTypeDto.TOP_UP
                ? user.balance + Number(dto.amount)
                : user.balance - Number(dto.amount);

        await this.prisma.$transaction([
            this.prisma.user.update({
                where: {
                    id: user.id,
                },
                data: {
                    balance: newBalance,
                },
            }),
            this.prisma.balanceHistory.create({
                data: {
                    userId,
                    type:
                        dto.type === TransactionTypeDto.TOP_UP
                            ? HistoryType.REPLENISHMENT
                            : HistoryType.WITHDRAWAL,
                    value: Number(dto.amount),
                },
            }),
        ]);

        return {
            balance: newBalance,
        };
    }
}
