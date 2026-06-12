import {
    BadRequestException,
    ConflictException,
    Injectable,
    NotFoundException,
} from '@nestjs/common';
import { isUUID } from 'class-validator';
import { ApplicationStatus, User } from 'generated/prisma';
import { PrismaService } from 'src/prisma/prisma.service';

@Injectable()
export class WorkService {
    constructor(private prisma: PrismaService) {}

    async getWorks() {
        const works = await this.prisma.user.findMany({
            where: {
                categories: {
                    some: {
                        bicycles: {
                            some: {},
                        },
                    },
                },
            },
            select: {
                id: true,
                name: true,
                bookings: {
                    where: {
                        userRating: {
                            not: null,
                        },
                    },
                    select: {
                        userRating: true,
                    },
                },
            },
        });

        return works.map((work) => ({
            id: work.id,
            name: work.name,
            rating: work.bookings.reduce(
                (curr, item) => Number(curr + item.userRating!),
                0,
            ),
        }));
    }

    /**
     * Submit a job application.
     */
    async sendRequest(workId: User['id'], userId: User['id']) {
        if (!isUUID(workId)) throw new NotFoundException();

        const oldApplication = await this.prisma.application.findFirst({
            where: {
                status: {
                    in: [ApplicationStatus.APPROVED, ApplicationStatus.PENDING],
                },
                userId,
            },
        });

        if (oldApplication)
            throw new ConflictException(
                'The application has already been sent',
            );

        const result = await this.prisma.application.groupBy({
            by: ['categoryId'],
            where: {
                status: ApplicationStatus.APPROVED,
                category: {
                    userId: workId,
                },
            },
            _count: {
                categoryId: true,
            },
            orderBy: {
                _count: {
                    categoryId: 'asc',
                },
            },
            take: 1,
        });

        let categoryId: string | null = null;

        const category = await this.prisma.category.findFirst({
            where: {
                userId: workId,
            },
        });

        if (!category) throw new BadRequestException();

        categoryId = category.id;

        return this.prisma.application.create({
            data: {
                categoryId,
                userId,
            },
        });
    }
}
