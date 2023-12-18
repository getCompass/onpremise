import {plural} from "./plural.ts";

export function formatTimeToText(seconds: number, strings: { oneHour: string; twoHours: string; fiveHours: string; oneMinute: string; twoMinutes: string; fiveMinutes: string; }): string {

    if (seconds === 0) {
        return `0${strings.fiveMinutes}`;
    }

    // преобразование в часы с округлением вверх при наличии оставшихся секунд
    let hours = Math.floor(seconds / 3600);
    if (seconds % 3600 > 0) {
        hours++;
    }

    // если количество часов больше 0, возвращаем их в нужном формате
    if (hours > 0) {
        return `${hours}${plural(hours, strings.oneHour, strings.twoHours, strings.fiveHours)}`;
    }

    // преобразование оставшихся секунд в минуты с округлением вверх
    let minutes = Math.ceil((seconds % 3600) / 60);
    return `${minutes}${plural(minutes, strings.oneMinute, strings.twoMinutes, strings.fiveMinutes)}`;
}