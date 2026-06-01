import React, { useContext } from "react";
import AppContext from "./AppContext";
import { Navigate, Outlet } from "react-router";

const RequireAuth = ({ children }) => {
  const { user } = useContext(AppContext);

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (user.loading === true) {
    return <h1 className="loading">Loading...</h1>;
  }

  return children ? children : <Outlet />;
};

export default RequireAuth;
