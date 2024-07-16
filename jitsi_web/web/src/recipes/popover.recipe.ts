import { popoverAnatomy } from "@ark-ui/react";
import { defineSlotRecipe } from "@pandacss/dev";

export const popover = defineSlotRecipe({
	className: "popover",
	slots: popoverAnatomy.keys(),
	base: {
		positioner: {
			rounded: "10px",
		},
		content: {
			boxShadow: "0px 2px 24px 0px rgba(0, 0, 0, 0.1)",
			background: "white",
			rounded: "5px",
			pt: "12px",
			pb: "16px",
			px: "16px",
			outline: "none",
			overflow: "hidden",
		},
		trigger: {
			"-webkit-appearance": "none",
		},
	},
	variants: {
		type: {
			desktop: {
				content: {
					maxW: "310px",
				},
			},
			mobile: {},
		},
	},
});
