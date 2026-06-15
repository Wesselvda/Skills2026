import { createContext } from "react";

const AppContext = createContext({
    token: localStorage.getItem("token"),
    user: JSON.parse(localStorage.getItem("user")),
});

export default AppContext;