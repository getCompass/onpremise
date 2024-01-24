import {defineConfig} from "@pandacss/dev"
import {button} from "./src/recipes/button.recipe";
import {input} from "./src/recipes/input.recipe";
import {text} from "./src/recipes/text.recipe";
import {dialog} from "./src/recipes/dialog.recipe";
import {menu} from "./src/recipes/menu.recipe";
import {pinInput} from "./src/recipes/pinInput.recipe";
import {select} from "./src/recipes/select.recipe";
import {tooltip} from "./src/recipes/tooltip.recipe";

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
				dialog,
				menu,
				pinInput,
				select,
				tooltip,
			},
			tokens: {
				animations: {
					"spin500ms": {
						value: "spin 0.5s linear infinite"
					},
				},
				fonts: {
					"lato_regular": {value: "var(--font-lato-regular), sans-serif"},
					"lato_semibold": {value: "var(--font-lato-semibold), sans-serif"},
					"lato_bold": {value: "var(--font-lato-bold), sans-serif"},
					"lato_black": {value: "var(--font-lato-black), sans-serif"},
				},
				colors: {
					"007aff": {value: "#007aff"},
					"007aff.hover": {value: "#0066d6"},
					"2574a9": {value: "#2574a9"},
					"2574a9.hover": {value: "#1d5c86"},
					"333e49": {value: "#333e49"},
					"393a4d": {value: "#393a4d"},
					"434455": {value: "#434455"},
					"4d4e61": {value: "#4d4e61"},
					"05c46b": {value: "#05c46b"},
					"05c46b.hover": {value: "#049a54"},
					"677380": {value: "#677380"},
					"b4b4b4": {value: "#b4b4b4"},
					"b4b4b4.hover": {value: "#8c8c8c"},
					"f0f0f0": {value: "#f0f0f0"},
					"f5f5f5": {value: "#f5f5f5"},
					"f5f5f5.hover": {value: "#e0e0e0"},
					"f8f8f8": {value: "#f8f8f8"},
					"ff6a64": {value: "#ff6a64"},
					"ff6a64.hover": {value: "#ff453d"},
					"ff8a00": {value: "#ff8a00"},
					"000000.01": {value: "rgba(0, 0, 0, 0.1)"},
					"000000.005": {value: "rgba(0, 0, 0, 0.05)"},
					"000000.005.hover": {value: "rgba(0, 0, 0, 0.15)"},
					"103115128.01": {value: "rgba(103, 115, 128, 0.1)"},
					"255106100.01": {value: "rgba(255, 106, 100, 0.1)"},
				},
			},
		}
	},

	// The output directory for your css system
	outdir: "styled-system",

	jsxFramework: "react",
})