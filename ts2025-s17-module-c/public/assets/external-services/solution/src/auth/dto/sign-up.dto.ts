import { ApiProperty } from '@nestjs/swagger';
import {
    IsEmail,
    IsNotEmpty,
    IsString,
    MaxLength,
    MinLength,
} from 'class-validator';

export class SignUpDto {
    @ApiProperty({
        type: String,
    })
    @IsString()
    @IsNotEmpty()
    @MaxLength(100)
    name: string;

    @ApiProperty({
        type: String,
    })
    @IsString()
    @IsNotEmpty()
    @IsEmail()
    @MaxLength(255)
    email: string;

    @ApiProperty({
        type: String,
    })
    @IsString()
    @IsNotEmpty()
    @MaxLength(255)
    phone: string;

    @ApiProperty({
        type: String,
    })
    @IsString()
    @IsNotEmpty()
    @MinLength(8)
    @MaxLength(255)
    password: string;
}
