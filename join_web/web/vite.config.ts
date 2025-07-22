import {defineConfig} from "vite";
import react from "@vitejs/plugin-react";
// @ts-ignore
import {getPublicPathApi, IS_NEED_INDEX_WEB} from "./src/private/custom.ts";

// https://vitejs.dev/config/
export default defineConfig({
	base: getPublicPathApi() || "/",
	plugins: [
		react(),
		{
			name: "conditional-noindex-nofollow",
			transformIndexHtml(html) {
				// если не нужно индексировать (IS_NEED_INDEX_WEB === false),
				// то добавляем мета-тег
				if (!IS_NEED_INDEX_WEB) {
					// вставляем мета-тег сразу после <head ...>
					return html.replace(
						/(<head[^>]*>)/,
						`$1
    <meta name="robots" content="noindex, nofollow">`
					);
				}
				// если IS_NEED_INDEX === true, ничего не меняем
				return html;
			},
		},
	],
});
