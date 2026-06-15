import { Injectable, NotFoundException } from '@nestjs/common';
import { hash } from 'argon2';
import crypto from 'crypto';
import { Prisma, User } from 'generated/prisma';
import { BicycleService } from 'src/bicycle/bicycle.service';
import { PrismaService } from 'src/prisma/prisma.service';
import { SignUpDto } from '../auth/dto/sign-up.dto';

@Injectable()
export class UserService {
    constructor(
        private prisma: PrismaService,
        private bicycleService: BicycleService,
    ) {}

    getUser(
        whereParameters: Prisma.UserWhereUniqueInput,
        selectFields: Prisma.UserSelect,
    ) {
        return this.prisma.user.findUnique({
            where: whereParameters,
            select: selectFields,
        });
    }

    async createUser(dto: SignUpDto) {
        return this.prisma.user.create({
            data: {
                ...dto,
                password: await hash(dto.password),
            },
        });
    }

    async findOrCreateUser(dto: SignUpDto) {
        return this.prisma.user.upsert({
            where: {
                email: dto.email,
            },
            create: {
                name: dto.name,
                email: dto.email,
                phone: dto.phone,
                password: await hash(dto.password),
            },
            update: {},
        });
    }

    async getMyPayments(userId: User['id']) {
        const userWithHistories = await this.prisma.user.findFirst({
            where: {
                id: userId,
            },
            select: {
                balance: true,
                histories: {
                    select: {
                        type: true,
                        value: true,
                        createdAt: true,
                    },
                },
            },
        });

        if (!userWithHistories) throw new NotFoundException();

        return {
            balance: userWithHistories.balance,
            payments: userWithHistories.histories,
        };
    }

    getUserToken(userId: User['id'], token: string) {
        return this.prisma.userToken.findFirst({
            where: {
                userId,
                hash: crypto.createHash('sha256').update(token).digest('hex'),
            },
        });
    }

    async getWork(userId: User['id']) {
        const work = await this.prisma.application.findFirst({
            where: {
                userId,
            },
            select: {
                category: {
                    select: {
                        user: {
                            select: {
                                id: true,
                                name: true,
                                bookings: {
                                    select: {
                                        userRating: true,
                                    },
                                },
                            },
                        },
                    },
                },
                status: true,
            },
        });

        if (!work) return;

        return {
            id: work.category.user.id,
            name: work.category.user.name,
            rating: this.bicycleService.calcRating(
                work.category.user.bookings.map(
                    (booking) => booking.userRating ?? 0,
                ),
            ),
            status: work.status,
        };
    }
}
