import { ApiProperty } from '@nestjs/swagger';
import { IsEnum, IsNotEmpty } from 'class-validator';

export enum BicycleRepairType {
    WASH = 'wash',
    REPAIR = 'repair',
    TIRES = 'tires',
    CHAIN = 'chain',
}

export class RepairBicycleDto {
    @ApiProperty({
        enum: BicycleRepairType,
    })
    @IsNotEmpty()
    @IsEnum(BicycleRepairType)
    type: string;
}
