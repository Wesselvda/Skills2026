import React, { useContext } from "react";
import { Navigate, useLocation } from "react-router";
import AppContext from "./AppContext";

const ProtectedRoute = ({ children, roles = [] }) => {
    const { token, user } = useContext(AppContext);
    const location = useLocation();

    if (!token) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    if (roles.length > 0 && !roles.includes(user?.role)) {
        return <Navigate to="/forbidden" replace />;
    }

    return children;
};

export default ProtectedRoute;
