import React, { useState } from "react";
import { apiCall } from "../Helpers/ApiHelper";
import { useNavigate } from "react-router";

const Login = () => {
    const [formData, setFormData] = useState({
        username: "",
        password: "",
    });

    const [errorMessage, setErrorMessage] = useState("");

    const navigate = useNavigate();

    function onSubmit(e) {
        e.preventDefault();

        apiCall("/auth/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
            body: JSON.stringify(formData)
        })
        .then((response) => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error("Invalid credentials");
            }
        })
        .then((data) => {
            localStorage.setItem("token", data.token);
            localStorage.setItem("user", JSON.stringify({
                role: data.role
            }));

            window.location.href = "/";
        })
        .catch((error) => {
            setErrorMessage(error.message);
        });
    }

    return (
        <div className="center-form-wrapper">
            <form onSubmit={onSubmit}>
                <h1>Login</h1>
                <div className="form-group">
                    <label htmlFor="username">Username:</label>
                    <input type="text" id="username" value={formData.username} onChange={(e) => setFormData({ ...formData, username: e.target.value })} name="username" />
                </div>
                <div className="form-group">
                    <label htmlFor="password">Password:</label>
                    <input type="password" id="password" value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })} name="password" />
                </div>
                {errorMessage && <div className="error">{errorMessage}</div>}
                <div className="button-wrapper">
                    <button type="submit">Login</button>
                </div>
            </form>
        </div>
    );
};

export default Login;
