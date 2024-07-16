import { defineRecipe } from "@pandacss/dev";

export const input = defineRecipe({
	className: "input",
	base: {
		appearance: "none",
		position: "relative",
		transitionDuration: "normal",
		transitionProperty: "box-shadow, border-color",
		transitionTimingFunction: "default",
		width: "full",
	},
	variants: {
		input: {
			desktop: {
				bgColor: "255255255.08",
				rounded: "12px",
				color: "2d343c",
				outline: "none",
				fontFamily: "inter_regular",
				fontWeight: "normal",
				fontSize: "18px",
				lineHeight: "27px",
				_placeholder: {
					color: "b4b4b4",
				},
			},
		},
		size: {
			px16py13: { py: "13px", px: "16px" },
		},
	},
});
