import { Outlet } from "react-router";

const AuthLayout = ({children}) => {
  return (
    <>
      <main className="auth-wrapper">
        {children}
      </main>
    </>
  );
};

export default AuthLayout;
