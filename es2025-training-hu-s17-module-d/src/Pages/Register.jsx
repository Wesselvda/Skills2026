import { useContext, useEffect, useState } from "react";
import { Link, useNavigate } from "react-router";
import { register } from "../Helpers/ApiHelper";
import AppContext from "../Helpers/AppContext";

const Register = () => {
  const [error, setError] = useState();

  const [formData, setFormData] = useState({
    fullname: "",
    email: "",
    password: "",
    confirmpassword: "",
  });

  const navigate = useNavigate();

  const {user} = useContext(AppContext);

  if (user) {
    navigate("/");
  }

  const [passwordStrengthMessage, setPasswordStrengthMessage] = useState();

  useEffect(() => {
    const password = formData.password;

    if (password.length === 0) {
      setPasswordStrengthMessage("");
    } else if (password.length < 8) {
      setPasswordStrengthMessage("Password Strength: Very Weak");
    } else {
      setPasswordStrengthMessage("");
    }
  }, [formData.password]);

  function submit(e) {
    e.preventDefault();

    register({
      email: formData.email,
      password: formData.password,
      name: formData.fullname,
    }, (err, data) => {
      if (err) {
        console.error(err);
        setError(err.message);
      } else {
        navigate("/login?registered=true");
      }
    });

    console.log(formData);
  }

  return (
    <>
      <h1>CREATE ACCOUNT</h1>
      {error && <p className="error">{error}</p>}
      <div className="input-wrapper">
        <label htmlFor="fullname">FULL NAME</label>
        <input
          type="text"
          id="fullname"
          name="fullname"
          value={formData.fullname}
          placeholder="Enter your full name"
          onChange={(e) =>
            setFormData((formData) => {
              return { ...formData, fullname: e.target.value };
            })
          }
        />
      </div>
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
          {passwordStrengthMessage && (
            <p className="error">{passwordStrengthMessage}</p>
          )}
        </div>
        <div className="input-wrapper">
          <label htmlFor="confirmpassword">CONFIRM PASSWORD</label>
          <input
            type="password"
            id="confirmpassword"
            name="confirmpassword"
            value={formData.confirmpassword}
            placeholder="Confirm your password"
            onChange={(e) =>
              setFormData((formData) => {
                return { ...formData, confirmpassword: e.target.value };
              })
            }
          />
          {formData.password &&
            formData.confirmpassword &&
            formData.password !== formData.confirmpassword && (
              <p className="error">Passwords don't match</p>
            )}
        </div>
        <button
          type="submit"
          disabled={
            !(
              formData.fullname &&
              formData.email &&
              formData.password &&
              formData.confirmpassword &&
              formData.password === formData.confirmpassword
            )
          }
        >
          CREATE ACCOUNT
        </button>
        <p>
          Already have an account? <Link to={"/login"}>SIGN IN HERE</Link>
        </p>
      </form>
    </>
  );
};

export default Register;
