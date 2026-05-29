import { useContext } from "react";
import { Link, Outlet } from "react-router";
import AppContext from "../Helpers/AppContext";
import { logout } from "../Helpers/ApiHelper";

const Layout = ({ children }) => {
    const { user } = useContext(AppContext);

    const creditBalance = user?.user?.creditBalance ?? user?.user?.credits ?? 0;
    const userName = user?.user?.name || "User";

    return (
        <>
            <header className="main-header">
                <div className="header-wrapper">
                    <Link to="/" className="logo-link">
                        SkillShare Academy
                    </Link>
                    <nav className="main-nav">
                        <Link to="/">Dashboard</Link>
                        <Link to="/courses">Courses</Link>
                        <Link to="/mentors">Mentors</Link>
                    </nav>
                    <div className="user-meta">{creditBalance} credits</div>
                    <div className="user-greeting">
                        Welcome, {user?.user?.name || "User"}!
                    </div>
                    <button
                        type="button"
                        className="logout-button"
                        onClick={logout}
                    >
                        Logout
                    </button>
                </div>
            </header>
            <main className="main-content">
                {children ? children : <Outlet />}
            </main>
        </>
    );
};

export default Layout;
