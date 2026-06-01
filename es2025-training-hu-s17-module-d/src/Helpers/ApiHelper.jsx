const BASE_URL = "http://localhost:5000/api/v1";

export { BASE_URL };

function requestJson(path, options, callback, successStatuses = [200]) {
    fetch(BASE_URL + path, options)
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));

            if (successStatuses.includes(res.status)) {
                callback(null, data, res);
                return;
            }

            const error = new Error(data.message || "Request failed");
            error.status = res.status;
            error.payload = data;
            callback(error, null, res);
        })
        .catch((err) => {
            console.error(err);
            callback(err, null);
        });
}

export function getToken() {
    return localStorage.getItem("token");
}

export function setToken(token) {
    localStorage.setItem("token", token);
}

export function getUser(callback) {
    let token = getToken();

    if (token) {
        requestJson("/users/me", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-API-TOKEN": token
            }
        }, callback, [200]);
    } else {
        callback(new Error("No token found"), null);
    }
}

export function register(data, callback) {
    requestJson("/users/register", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(data)
    }, callback, [201]);
}

export function login(data, callback) {
    requestJson("/users/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(data)
    }, (err, data, res) => {
        if (!err && data?.token) {
            setToken(data.token);
        }
        callback(err, data, res);
    }, [200]);
}

export function logout(e) {
    e.preventDefault();
    fetch(BASE_URL + "/users/logout", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-API-TOKEN": getToken()
        }
    }).then(res => {
        if (res.status === 200) {
            setToken("");
            window.location.href = "/login";
        }
    }).catch(err => {
        console.error(err);
    });
}

export function getCourses(callback) {
    const token = getToken();

    if (!token) {
        callback(new Error("No token found"), null);
        return;
    }

    requestJson(
        "/courses",
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-API-TOKEN": token,
            },
        },
        callback,
        [200],
    );
}

export function getCourse(courseId, callback) {
    const token = getToken();

    if (!token) {
        callback(new Error("No token found"), null);
        return;
    }

    requestJson(
        `/courses/${courseId}`,
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-API-TOKEN": token,
            },
        },
        callback,
        [200],
    );
}

export function enrollInCourse(courseId, callback) {
    const token = getToken();

    if (!token) {
        callback(new Error("No token found"), null);
        return;
    }

    requestJson(
        `/courses/${courseId}/enroll`,
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-API-TOKEN": token,
            },
        },
        callback,
        [200],
    );
}

export function completeChapter(courseId, chapterId, callback) {
    const token = getToken();

    if (!token) {
        callback(new Error("No token found"), null);
        return;
    }

    requestJson(
        `/courses/${courseId}/chapters/${chapterId}/complete`,
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-API-TOKEN": token,
            },
        },
        callback,
        [200],
    );
}

export function getMentorSessions(callback) {
    const token = getToken();

    if (!token) {
        callback(new Error("No token found"), null);
        return;
    }

    requestJson(
        "/mentors/sessions",
        {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-API-TOKEN": token,
            },
        },
        callback,
        [200],
    );
}

export function bookMentorSession(sessionId, callback) {
    const token = getToken();

    if (!token) {
        callback(new Error("No token found"), null);
        return;
    }

    requestJson(
        `/mentors/sessions/${sessionId}/book`,
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-API-TOKEN": token,
            },
        },
        callback,
        [200,201],
    );
}