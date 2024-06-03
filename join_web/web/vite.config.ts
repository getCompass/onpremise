import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
// @ts-ignore
import { getPublicPathApi } from "./src/private/custom.ts";

// https://vitejs.dev/config/
export default defineConfig({
	plugins: [react()],
	base: getPublicPathApi() || "/",
});
