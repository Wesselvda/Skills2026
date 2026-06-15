function pad(value) {
    return String(value).padStart(2, "0");
}

function formatDateTime(value) {
    if (!value) {
        return "";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return [
        `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}`,
        `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`,
    ].join(" ");
}

export { formatDateTime };
