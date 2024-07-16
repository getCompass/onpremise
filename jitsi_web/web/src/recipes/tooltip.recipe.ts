import { tooltipAnatomy } from "@ark-ui/react";
import { defineSlotRecipe } from "@pandacss/dev";

export const tooltip = defineSlotRecipe({
	className: "tooltip",
	slots: tooltipAnatomy.keys(),
	base: {
		content: {
			borderRadius: "5px",
			color: "f8f8f8",
			fontFamily: "roboto_regular",
			fontWeight: "normal",
			textAlign: "center",
		},
	},
	variants: {
		style: {
			desktop: {
				content: {
					px: "8px",
					py: "6px",
					fontSize: "13px",
					lineHeight: "18px",
				},
			},
			warning_desktop: {
				content: {
					px: "12px",
					py: "8px",
					fontSize: "14px",
					lineHeight: "20px",
				},
			},
			success_desktop: {
				content: {
					px: "8px",
					py: "6px",
					fontSize: "13px",
					lineHeight: "18px",
				},
			},
		},
		type: {
			default_desktop: {
				arrow: {
					opacity: "90%",
				},
				content: {
					background: "rgba(0, 0, 0, 0.9)",
				},
			},
			warning_desktop: {
				arrow: {
					opacity: "100%",
				},
				content: {
					background: "ff8a00",
				},
			},
			success_desktop: {
				arrow: {
					opacity: "100%",
				},
				content: {
					background: "13ae5c",
					color: "f8f8f8",
				},
			},
		},
	},
});
