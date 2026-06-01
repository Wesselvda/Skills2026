import React, { useContext, useEffect, useState } from "react";
import { Link, useNavigate } from "react-router";
import { login } from "../Helpers/ApiHelper";
import AppContext from "../Helpers/AppContext";

const Login = () => {
  const [formData, setFormData] = useState({
    email: "",
    password: "",
  });

  const [error, setError] = useState();
  const navigate = useNavigate();

  const {user, setUser} = useContext(AppContext);

  useEffect(() => {
    if (user) {
      navigate("/");
    }
  }, [user, navigate]);

  function submit(e) {
    e.preventDefault();

    login(formData, (err, data) => {
      if (err) {
        console.error(err);
        setError(err.message);
      } else {
        console.log(data);
        setUser(data.user);
        navigate("/");
      }
    });

    console.log(formData);
  }

  return (
    <>
      {location.search.includes("registered=true") && (
        <div className="alert alert-success">
          <p>Registered successfully!</p>
        </div>
      )}
      <h1>WELCOME BACK</h1>
      {error && <p className="error">{error}</p>}
      <form onSubmit={submit}>
        <div className="input-wrapper">
          <label htmlFor="email">EMAIL ADDRESS</label>
          <input
            type="email"
            id="email"
            name="email"
            value={formData.email}
            placeholder="Enter your email"
            onChange={(e) =>
              setFormData((formData) => {
                return { ...formData, email: e.target.value };
              })
            }
          />
        </div>
        <div className="input-wrapper">
          <label htmlFor="password">PASSWORD</label>
          <input
            type="password"
            id="password"
            name="password"
            value={formData.password}
            placeholder="Enter your password"
            onChange={(e) =>
              setFormData((formData) => {
                return { ...formData, password: e.target.value };
              })
            }
          />
        </div>
        <button type="submit" disabled={!formData.email || !formData.password}>
          LOGIN
        </button>
        <p>
          Registration is free! <Link to={"/register"}>CREATE AN ACCOUNT</Link>
        </p>
      </form>
    </>
  );
};

export default Login;
