import React from "react"
import ReactDOM from "react-dom/client"
import App from "./App.tsx"
import "./index.css"
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat"

dayjs.extend(customParseFormat)

ReactDOM.createRoot(document.getElementById("root")!).render(
	<React.StrictMode>
		<App/>
	</React.StrictMode>,
)
