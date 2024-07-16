import {selectAnatomy} from "@ark-ui/react"
import {defineSlotRecipe} from "@pandacss/dev"

export const select = defineSlotRecipe({
	className: "select",
	slots: selectAnatomy.keys(),
	base: {
		root: {
			display: "flex",
			flexDirection: "column",
			width: "100%",
			gap: "6px",
		},
		trigger: {
			px: "16px",
			py: "12px",
			alignItems: "center",
			display: "inline-flex",
			justifyContent: "space-between",
			outline: 0,
			appearance: "none",
			transitionDuration: "normal",
			transitionProperty: "background, box-shadow, border-color",
			transitionTimingFunction: "default",
			width: "100%",
			rounded: "12px",
		},
		value: {
			fontFamily: "roboto_bold",
			fontSize: "20px",
			lineHeight: "24px",
			letterSpacing: "-0.2",
		},
		positioner: {
			zIndex: "1",
		},
		content: {
			py: "6px",
			bgColor: "white",
			border: "1px solid #f5f5f5",
			rounded: "8px",
			display: "flex",
			flexDirection: "column",
			outline: 0,
			appearance: "none",
			_hidden: {
				display: "none",
			},
			_open: {
				animation: "fadeIn 0.25s ease-out",
			},
			_closed: {
				animation: "fadeOut 0.2s ease-out",
			},
		},
		item: {
			px: "16px",
			py: "6px",
			width: "100%",
			alignItems: "center",
			cursor: "pointer",
			display: "flex",
			justifyContent: "space-between",
			outline: "none",
			_hover: {
				bgColor: "f8f8f8",
			},
		},
		itemText: {
			fontFamily: "roboto_bold",
			fontSize: "20px",
			lineHeight: "23px",
		},
	},
})
