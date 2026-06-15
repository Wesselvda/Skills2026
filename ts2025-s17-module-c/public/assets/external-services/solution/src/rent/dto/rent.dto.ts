import { ApiProperty } from '@nestjs/swagger';
import { IsNotEmpty, IsOptional, IsString, MaxLength } from 'class-validator';

export class RentBicycleDto {
    @ApiProperty({
        type: 'string',
    })
    @IsString()
    @IsNotEmpty()
    @MaxLength(255)
    bicycleId: string;

    @ApiProperty({
        type: 'string',
    })
    @IsString()
    @IsNotEmpty()
    @MaxLength(255)
    tariffId: string;

    @ApiProperty({
        type: 'string',
        required: false,
    })
    @IsOptional()
    @IsString()
    @MaxLength(255)
    promoCode?: string;
}
