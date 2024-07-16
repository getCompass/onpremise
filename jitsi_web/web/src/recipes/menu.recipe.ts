import { menuAnatomy } from "@ark-ui/react";
import { defineSlotRecipe } from "@pandacss/dev";

export const menu = defineSlotRecipe({
	className: "menu",
	slots: menuAnatomy.keys(),
	base: {
		positioner: {
			// shadow: "0px 2px 15px 0px rgba(0, 0, 0, 0.05)",
			rounded: "10px",
		},
		content: {
			background: "white",
			rounded: "10px",
			py: "6px",
			outline: "none",
			overflow: "hidden",
		},
		item: {},
		trigger: {
			"-webkit-appearance": "none",
		},
	},
	variants: {
		type: {
			desktop_small: {
				content: {
					width: "276px",
					background: "white",
					rounded: "12px",
					py: "6px",
					border: "1px solid #f0f0f0",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					pl: "11px",
					pr: "7px",
					pt: "8px",
					pb: "7px",
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
			desktop: {
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
			mobile: {
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
			lang_desktop: {
				content: {
					width: "150px",
					background: "white",
					rounded: "8px",
					py: "4px",
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
						},
					},
				},
			},
			lang_mobile: {
				content: {
					width: "150px",
					background: "white",
					rounded: "8px",
					py: "4px",
					border: "1px solid #f0f0f0",
					outline: "none",
					overflow: "hidden",
				},
				item: {
					py: "6px",
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
						},
					},
				},
			},
		},
	},
});
