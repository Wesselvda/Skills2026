const BASE_URL = "http://localhost:8080";

function apiCall(url, options) {
    const token = localStorage.getItem("token");
    const headers = {
        ...(options?.headers || {}),
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
    };

    return fetch(BASE_URL + url, {
        ...options,
        headers,
    });
}

export { BASE_URL, apiCall };
