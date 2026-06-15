import "./assets/style/style.css";
import { BrowserRouter, Route, Routes } from "react-router";
import Layout from "./Layouts/Layout";
import Home from "./Pages/Home";
import Login from "./Pages/Login";
import Alerts from "./Pages/Alerts";
import Actions from "./Pages/Actions";
import Logs from "./Pages/Logs";
import Forbidden from "./Pages/Forbidden";
import AppContext from "./Helpers/AppContext";
import ProtectedRoute from "./Helpers/ProtectedRoute";

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
						<Route
							path="alerts"
							element={
								<ProtectedRoute roles={["operator", "admin"]}>
									<Alerts />
								</ProtectedRoute>
							}
						/>
						<Route path="turbines/:turbineId/actions" element={<Actions />} />
						<Route
							path="turbines/:turbineId/logs"
							element={
								<ProtectedRoute roles={["operator", "admin"]}>
									<Logs />
								</ProtectedRoute>
							}
						/>
						<Route path="login" element={<Login />} />
						<Route path="forbidden" element={<Forbidden />} />
					</Route>
				</Routes>
			</BrowserRouter>
		</AppContext>
    );
}

export default App;
