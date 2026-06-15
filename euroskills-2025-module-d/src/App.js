import "./assets/style/style.css";
import { BrowserRouter, Route, Routes } from "react-router";
import Layout from "./Layouts/Layout";
import Home from "./Pages/Home";
import Login from "./Pages/Login";
import AppContext from "./Helpers/AppContext";

function App() {
    return (
		<AppContext value={{
			token: localStorage.getItem("token"),
			user: JSON.parse(localStorage.getItem("user")),
		}}>
			<BrowserRouter>
				<Routes>
					<Route path="*" element={<Layout />} >
						<Route index element={<Home />} />
						<Route path="turbines/:turbineId" element={<Home />} />
						<Route path="login" element={<Login />} />
					</Route>
				</Routes>
			</BrowserRouter>
		</AppContext>
    );
}

export default App;
