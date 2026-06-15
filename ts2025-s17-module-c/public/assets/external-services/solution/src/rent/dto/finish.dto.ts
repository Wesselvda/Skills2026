import { ApiProperty } from '@nestjs/swagger';
import { Type } from 'class-transformer';
import { ArrayMinSize, IsArray, IsOptional, Max, Min } from 'class-validator';

export class FinishRentBicycleDto {
    @ApiProperty({
        type: 'number',
        example: 4,
        required: false,
    })
    @IsOptional()
    @Min(1)
    @Max(5)
    rating?: string;

    @ApiProperty({
        type: 'array',
        example: [],
        minimum: 2,
    })
    @IsArray()
    @ArrayMinSize(2)
    @Type(() => String)
    photos: string[];
}
