import { Controller, Get } from '@nestjs/common';
import { ApiResponse } from '@nestjs/swagger';
import { ExternalServiceService } from './external-service.service';

@Controller('external-services')
export class ExternalServiceController {
    constructor(
        private readonly externalServiceService: ExternalServiceService,
    ) {}

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                color_scale_of_corks: {
                    enum: ['green', 'yellow', 'red', 'black'],
                },
                number_scale: {
                    type: 'number',
                    example: 50,
                    minimum: 1,
                    maximum: 100,
                },
            },
        },
    })
    @Get('/weather')
    getCurrentWeather() {
        const currentWeather = this.externalServiceService.getCurrentWeather();

        return {
            data: currentWeather,
        };
    }

    @ApiResponse({
        status: 200,
        schema: {
            properties: {
                weather: {
                    enum: ['clear', 'fog', 'rain', 'snow'],
                },
                temperature: {
                    type: 'number',
                    example: 25,
                },
            },
        },
    })
    @Get('/city')
    getCurrentTraffic() {
        const currentTraffic = this.externalServiceService.getCurrentTraffic();

        return {
            data: currentTraffic,
        };
    }
}
