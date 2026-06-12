export enum WeatherType {
    CLEAR = 'clear',
    SNOW = 'snow',
    FOG = 'fog',
    RAIN = 'rain',
}

export const COLOR_MULTIPLIERS = [
    {
        name: 'green',
        min_scale: 1,
        max_scale: 30,
    },
    {
        name: 'yellow',
        min_scale: 31,
        max_scale: 60,
    },
    {
        name: 'red',
        min_scale: 61,
        max_scale: 90,
    },
    {
        name: 'black',
        min_scale: 91,
        max_scale: 100,
    },
];
