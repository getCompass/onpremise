import { defineConfig } from "@pandacss/dev";
import { button } from "./src/recipes/button.recipe";
import { input } from "./src/recipes/input.recipe";
import { menu } from "./src/recipes/menu.recipe";
import { popover } from "./src/recipes/popover.recipe";
import { select } from "./src/recipes/select.recipe";
import { text } from "./src/recipes/text.recipe";
import { tooltip } from "./src/recipes/tooltip.recipe";

export default defineConfig({
	// Whether to use css reset
	preflight: true,

	presets: ["@pandacss/dev/presets"],

	// Where to look for your css declarations
	include: ["./src/**/*.{js,jsx,ts,tsx,css}", "./pages/**/*.{js,jsx,ts,tsx,css}"],

	// Files to exclude
	exclude: [],

	// Useful for theme customization
	theme: {
		extend: {
			recipes: {
				button,
				input,
				text,
			},
			slotRecipes: {
				menu,
				popover,
				select,
				tooltip,
			},

			keyframes: {
				"caret-blink": {
					"0%,70%,100%": { opacity: "1" },
					"20%,50%": { opacity: "0" },
				},
			},
			tokens: {
				animations: {
					spin500ms: {
						value: "spin 0.5s linear infinite",
					},
					caretBlink: {
						value: "caret-blink 1.1s ease-out infinite",
					},
				},
				colors: {
					"2574a9": { value: "#2574a9" },
					"2574a9.hover": { value: "#1d5c86" },
					"2d343c": { value: "#2d343c" },
					"333e49": { value: "#333e49" },
					"665ebe": { value: "#665ebe" },
					"665ebe.hover": { value: "#4f46ae" },
					"677380": { value: "#677380" },
					b4b4b4: { value: "#b4b4b4" },
					e6e6e6: { value: "#e6e6e6" },
					f8f8f8: { value: "#f8f8f8" },
					"103115128.01": { value: "rgba(103, 115, 128, 0.1)" },
					"255255255.03": { value: "rgba(255, 255, 255, 0.3)" },
					"255255255.04": { value: "rgba(255, 255, 255, 0.4)" },
					"255255255.08": { value: "rgba(255, 255, 255, 0.8)" },
				},
				fonts: {
					lato_regular: { value: "var(--font-lato-regular), sans-serif" },
					lato_medium: { value: "var(--font-lato-medium), sans-serif" },
					lato_semibold: { value: "var(--font-lato-semibold), sans-serif" },
					lato_bold: { value: "var(--font-lato-bold), sans-serif" },
					lato_black: { value: "var(--font-lato-black), sans-serif" },
					inter_regular: { value: "var(--font-inter-regular), sans-serif" },
					inter_medium: { value: "var(--font-inter-medium), sans-serif" },
					inter_semibold: { value: "var(--font-inter-semibold), sans-serif" },
					inter_bold: { value: "var(--font-inter-bold), sans-serif" },
					inter_black: { value: "var(--font-inter-black), sans-serif" },
				},
			},
		},
	},

	// The output directory for your css system
	outdir: "styled-system",

	jsxFramework: "react",
});
