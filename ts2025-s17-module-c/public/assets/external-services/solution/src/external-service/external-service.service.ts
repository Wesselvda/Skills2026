import { Injectable } from '@nestjs/common';
import { COLOR_MULTIPLIERS, WeatherType } from './external-service.constant';

let currentWeather = {
    weather: WeatherType.CLEAR,
    temperature: 20,
};

const updateCurrentWeather = () => {
    if (Math.random() < 0.3) {
        const enumValues = Object.values(WeatherType) as WeatherType[];
        const randomIndex = Math.floor(Math.random() * enumValues.length);

        currentWeather.weather = enumValues[randomIndex];
    }

    let newTemperature =
        currentWeather.temperature + (Math.floor(Math.random() * 5) - 2);

    newTemperature = Math.max(-15, Math.min(35, newTemperature));

    currentWeather.temperature = newTemperature;
};

setInterval(() => updateCurrentWeather(), 5 * 1000);

let currentTraffic = {
    color_scale_of_corks: 'green',
    number_scale: 5,
};

const updateCurrentTraffic = () => {
    if (Math.random() < 0.3) {
        let newScale =
            currentTraffic.number_scale + (Math.floor(Math.random() * 21) - 10);
        newScale = Math.max(1, Math.min(100, newScale));

        currentTraffic.number_scale = newScale;

        let newColor = COLOR_MULTIPLIERS.find(
            (color) =>
                color.min_scale <= newScale && color.max_scale >= newScale,
        );

        currentTraffic.color_scale_of_corks = newColor!.name;
    }
};

setInterval(() => updateCurrentTraffic(), 5 * 1000);

@Injectable()
export class ExternalServiceService {
    getCurrentWeather() {
        return currentWeather;
    }

    getCurrentTraffic() {
        return currentTraffic;
    }
}
