import { useEffect, useState } from "react";
import { BrowserRouter, Navigate, Route, Routes } from "react-router";
import { getToken, getUser } from "./Helpers/ApiHelper";
import AppContext from "./Helpers/AppContext";
import RequireAuth from "./Helpers/RequireAuth";
import AuthLayout from "./Layouts/AuthLayout";
import Layout from "./Layouts/Layout";
import CourseDetails from "./Pages/CourseDetails";
import Courses from "./Pages/Courses";
import Mentors from "./Pages/Mentors";
import Dashboard from "./Pages/Dashboard";
import Login from "./Pages/Login";
import Register from "./Pages/Register";

function App() {
  const [user, setUser] = useState(getToken() ? {loading: true} : null);

  useEffect(() => {
    getUser((err, data) => {
      if (err) {
        console.error(err);
        setUser(null);
      } else {
        setUser(data);
      }
    });
  }, []);

  return (
    <AppContext.Provider value={{user, setUser}}>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<AuthLayout><Login /></AuthLayout>} />
          <Route path="/register" element={<AuthLayout><Register /></AuthLayout>} />
          <Route path="/" element={<RequireAuth><Layout><Dashboard /></Layout></RequireAuth>} />
          <Route path="/courses" element={<RequireAuth><Layout><Courses /></Layout></RequireAuth>} />
          <Route path="/courses/:courseId" element={<RequireAuth><Layout><CourseDetails /></Layout></RequireAuth>} />
          <Route path="/mentors" element={<RequireAuth><Layout><Mentors /></Layout></RequireAuth>} />
          <Route path="*" element={<RequireAuth><Navigate to={'/'} /></RequireAuth>} />
        </Routes>
      </BrowserRouter>
    </AppContext.Provider>
  );
}

export default App;
