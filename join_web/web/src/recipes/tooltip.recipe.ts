import {tooltipAnatomy} from "@ark-ui/react"
import {defineSlotRecipe} from "@pandacss/dev"

export const tooltip = defineSlotRecipe({
	className: "tooltip",
	slots: tooltipAnatomy.keys(),
	base: {
		content: {
			borderRadius: "5px",
			color: "f8f8f8",
			fontWeight: 400,
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
			mobile: {
				content: {
					px: "12px",
					py: "6px",
					fontSize: "14px",
					lineHeight: "20px",
				},
			},
		},
		type: {
			default_desktop: {
				arrow: {
					opacity: "90%"
				},
				content: {
					background: "rgba(0, 0, 0, 0.9)",
				},
			},
			default_mobile: {
				arrow: {
					opacity: "100%"
				},
				content: {
					background: "rgba(0, 0, 0, 1)",
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
			warning_mobile: {
				arrow: {
					opacity: "100%",
				},
				content: {
					background: "ff8a00",
				},
			},
		},
	},
})
