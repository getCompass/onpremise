import {dialogAnatomy} from "@ark-ui/react"
import {defineSlotRecipe} from "@pandacss/dev"

export const dialog = defineSlotRecipe({
	className: "dialog",
	slots: dialogAnatomy.keys(),
	base: {
		backdrop: {
			inset: "0",
			position: "fixed",
			zIndex: "overlay",
			_open: {
				animation: "backdrop-in",
			},
			_closed: {
				animation: "backdrop-out",
			},
		},
		container: {
			inset: "0",
			position: "fixed",
			zIndex: "modal",
			pointerEvents: "none",
		},
		content: {
			padding: "16px",
			display: "flex",
			alignItems: "center",
			justifyContent: "center",
			bgColor: "white",
			boxShadow: "0 0 0 3px rgba(0, 0, 0, 0.05)",
			outline: "none",
			position: "relative",
			_open: {
				animation: "dialog-in",
			},
			_closed: {
				animation: "dialog-out",
			},
		},
		trigger: {
			"-webkit-appearance": "none",
		},
	},
	defaultVariants: {
		style: "desktop",
		size: "full",
		backdrop: "opacity0",
		position: "center",
	},
	variants: {
		style: {
			desktop: {
				content: {
					borderRadius: "8px",
				},
			},
			mobile: {
				content: {
					borderRadius: "15px",
				},
			},
		},
		backdrop: {
			opacity0: {
				backdrop: {bgColor: "rgba(4, 4, 10, 0)"},
			},
			opacity50: {
				backdrop: {bgColor: "rgba(4, 4, 10, 0.5)"},
			},
			opacity80: {
				backdrop: {bgColor: "rgba(4, 4, 10, 0.8)"},
			},
		},
		size: {
			full: {
				content: {
					width: "100%",
					padding: "16px",
				},
			},
			small: {
				content: {
					width: "360px",
					padding: "16px",
				},
			},
			medium: {
				content: {
					width: "448px",
					padding: "16px",
				},
			},
			asContent: {
				content: {
					minWidth: "360px",
					padding: "16px",
				},
			},
		},
		position: {
			start: {
				container: {
					display: "flex",
					alignItems: "flex-start",
					justifyContent: "center",
					padding: "42px",
				},
			},
			center: {
				container: {
					display: "flex",
					alignItems: "center",
					justifyContent: "center",
					padding: "16px",
				},
			},
		},
	},
})
