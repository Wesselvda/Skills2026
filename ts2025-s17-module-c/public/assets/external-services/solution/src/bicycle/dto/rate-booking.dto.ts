import { ApiProperty } from '@nestjs/swagger';
import { IsNotEmpty, Max, Min } from 'class-validator';

export class RateBookingDto {
    @ApiProperty({
        type: 'number',
        example: 4,
        minimum: 1,
        maximum: 5,
    })
    @IsNotEmpty()
    @Min(1)
    @Max(5)
    rating: string;
}
