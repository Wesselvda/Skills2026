import { ApiProperty } from '@nestjs/swagger';
import { IsEnum, IsNotEmpty, IsNumber, Max, Min } from 'class-validator';

export enum TransactionTypeDto {
    TOP_UP = 'top-up',
    WITHDRAW = 'withdraw',
}

export class TransactionDto {
    @ApiProperty({
        enum: TransactionTypeDto,
    })
    @IsNotEmpty()
    @IsEnum(TransactionTypeDto)
    type: string;

    @ApiProperty({
        type: 'number',
        example: 5000,
        minimum: 1,
        maximum: 100000,
    })
    @IsNumber()
    @Min(1)
    @Max(100000)
    amount: number;
}
