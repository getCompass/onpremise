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
		},
		control: {
			userSelect: "none",
			outline: "none",
			WebkitTapHighlightColor: "transparent",
		},
		trigger: {
			cursor: "pointer",
			userSelect: "none",
			outline: "none",
			WebkitTapHighlightColor: "transparent",
		},
		positioner: {
			zIndex: "9999",
		},
		content: {
			userSelect: "none",
			outline: "none",
			WebkitTapHighlightColor: "transparent",
			gap: "0px",
			bgColor: "white",
			border: "1px solid #f5f5f5",
			roundedTop: "8px",
			display: "flex",
			flexDirection: "column",
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
			gap: "13px",
			width: "100%",
			alignItems: "center",
			cursor: "pointer",
			display: "flex",
			justifyContent: "flex-start",
			outline: "none",
			_hover: {
				bgColor: "c.f8f8f8",
			},
		},
		itemText: {
			fontFamily: "lato_regular",
			fontSize: "17px",
			lineHeight: "22px",
			color: "333e49",
		},
	},
})
