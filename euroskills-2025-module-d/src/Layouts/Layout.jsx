import React, { useContext } from "react";
import { Link, NavLink, Outlet } from "react-router";
import AppContext from "../Helpers/AppContext";

const Layout = () => {
    function logout(e) {
        e.preventDefault();

        localStorage.removeItem("user");
        localStorage.removeItem("token");
        window.location.reload();
    }

    const { user, token } = useContext(AppContext);
    const canOperate = token && ["operator", "admin"].includes(user?.role);

    return (
        <>
            <header className="main-header">
                <div className="inner">
                    <Link to="/" className="logo">
                        Wind Farm
                    </Link>
                    <nav>
                        <NavLink to="/">Map</NavLink>
                        {canOperate && <NavLink to="/alerts">Alerts</NavLink>}
                        { token ? <>
                        
                            <button onClick={logout}>Logout</button>
                        </> : <>
                            <Link className="btn" to="/login">Login</Link>
                        </> }
                    </nav>
                </div>
            </header>
            <main className="main-content">
                <Outlet />
            </main>
        </>
    );
};

export default Layout;
