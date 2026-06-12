import { ApiProperty } from '@nestjs/swagger';
import {
    IsEmail,
    IsNotEmpty,
    IsString,
    MaxLength,
    MinLength,
} from 'class-validator';

export class SignInDto {
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
    @MinLength(8)
    @MaxLength(255)
    password: string;
}
