import {menuAnatomy} from "@ark-ui/react"
import {defineSlotRecipe} from "@pandacss/dev"

export const menu = defineSlotRecipe({
	className: "menu",
	slots: menuAnatomy.keys(),
	base: {
		positioner: {
			width: "var(--positioner-width)",
		},
		item: {},
		trigger: {
			"-webkit-appearance": "none",
		},
	},
	variants: {
		type: {
			medium: {
				content: {
					w: "100%",
					background: "white",
					rounded: "8px",
					py: "4px",
					shadow: "0px 2px 15px 0px rgba(0, 0, 0, 0.05)",
					border: "1px solid #f0f0f0",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					pt: "9px",
					pb: "10px",
					px: "16px",
					bgColor: "white",
					cursor: "pointer",
					userSelect: "none",
					outline: "none",
					_active: {
						bgColor: "f8f8f8",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "f8f8f8",
						}
					},
				},
			},
			small_desktop: {
				content: {
					w: "100%",
					background: "white",
					rounded: "8px",
					py: "4px",
					shadow: "0px 2px 15px 0px rgba(0, 0, 0, 0.05)",
					border: "1px solid #f0f0f0",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					py: "8px",
					px: "12px",
					bgColor: "white",
					cursor: "pointer",
					userSelect: "none",
					outline: "none",
					_active: {
						bgColor: "f8f8f8",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "f8f8f8",
						}
					},
				},
			},
			medium_desktop: {
				content: {
					w: "100%",
					background: "white",
					rounded: "8px",
					py: "8px",
					shadow: "0px 2px 15px 0px rgba(0, 0, 0, 0.05)",
					border: "1px solid #f0f0f0",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					py: "8px",
					px: "16px",
					bgColor: "white",
					cursor: "pointer",
					userSelect: "none",
					outline: "none",
					_active: {
						bgColor: "f8f8f8",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "f8f8f8",
						}
					},
				},
			},
			screenBottom: {
				content: {
					w: "100%",
					background: "white",
					roundedTop: "15px",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					pl: "16px",
					bgColor: "white",
					cursor: "pointer",
					userSelect: "none",
					outline: "none",
					_active: {
						bgColor: "f8f8f8",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "f8f8f8",
						}
					},
				},
			},
			install_desktop: {
				content: {
					width: "367px",
					background: "white",
					rounded: "12px",
					py: "11px",
					border: "1px solid #f0f0f0",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					py: "9px",
					px: "19px",
					bgColor: "white",
					cursor: "pointer",
					userSelect: "none",
					outline: "none",
					_active: {
						bgColor: "f8f8f8",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "f8f8f8",
						},
					},
				},
			},
			install_mobile: {
				content: {
					minW: "318px",
					background: "white",
					rounded: "12px",
					py: "11px",
					border: "1px solid #f0f0f0",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					pt: "11px",
					pb: "10px",
					px: "20px",
					bgColor: "white",
					cursor: "pointer",
					userSelect: "none",
					outline: "none",
					_active: {
						bgColor: "f8f8f8",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "f8f8f8",
						},
					},
				},
			},
		},
	},
	defaultVariants: {
		type: "medium",
	}
})
