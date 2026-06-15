const BASE_URL = "http://localhost:8080";

function apiCall(url, options) {
    return fetch(BASE_URL + url, options);
}

export { BASE_URL, apiCall };