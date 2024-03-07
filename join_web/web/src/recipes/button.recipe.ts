import {defineRecipe} from "@pandacss/dev"

export const button = defineRecipe({
	className: "button",
	base: {
		rounded: "8px",
		fontSize: "17px",
		lineHeight: "26px",
		outline: "none",
		userSelect: "none",
		fontFamily: "lato_semibold",
		display: "flex",
		alignItems: "center",
		justifyContent: "center",
		WebkitTapHighlightColor: "transparent",
		_enabled: {
			cursor: "pointer",
		},
		_disabled: {
			cursor: "default",
		},
	},
	defaultVariants: {
		size: "full",
		textSize: "xl",
		color: "007aff",
	},
	variants: {
		textSize: {
			"lato_13_18_400": {
				fontSize: "13px",
				lineHeight: "18px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			"lato_14_20_400": {
				fontSize: "14px",
				lineHeight: "20px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			"lato_15_23_600": {
				fontSize: "15px",
				lineHeight: "23px",
				fontFamily: "lato_semibold",
				fontWeight: "normal",
			},
			"lato_16_22_400": {
				fontSize: "16px",
				lineHeight: "22px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			"lato_17_26_600": {
				fontSize: "17px",
				lineHeight: "26px",
				fontFamily: "lato_semibold",
				fontWeight: "normal",
			},
			"md": {fontSize: "16px", lineHeight: "22px", fontWeight: "400"},
			"xl": {fontSize: "17px", lineHeight: "26px", fontWeight: "500"},
			"md_desktop": {fontSize: "13px", lineHeight: "18px", fontWeight: "400"},
			"xl_desktop": {fontSize: "15px", lineHeight: "23px", fontWeight: "500"},
		},
		color: {
			"007aff": {
				bgColor: "007aff",
				color: "white",
				_enabled: {
					opacity: "100%",
					_active: {
						bgColor: "007aff.hover",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "007aff.hover"
						}
					},
				},
				_disabled: {
					opacity: "30%",
				},
			},
			"05c46b": {
				bgColor: "05c46b",
				color: "white",
				_enabled: {
					_active: {
						bgColor: "05c46b.hover",
						color: "white",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "05c46b.hover",
							color: "white",
						}
					},
				},
			},
			"2574a9": {
				color: "2574a9",
				_active: {
					color: "2574a9.hover",
				},
				_enabled: {
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							color: "2574a9.hover"
						}
					},
				},
				_disabled: {
					opacity: "30%",
				},
			},
			"2574a9_opacity70": {
				color: "2574a9",
				opacity: "70%",
				_active: {
					color: "2574a9.hover",
				},
				_enabled: {
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							color: "2574a9.hover"
						}
					},
				},
				_disabled: {
					opacity: "30%",
				},
			},
			"f5f5f5": {
				bgColor: "f5f5f5",
				color: "b4b4b4",
				_enabled: {
					_active: {
						bgColor: "f5f5f5.hover",
						color: "b4b4b4.hover",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "f5f5f5.hover",
							color: "b4b4b4.hover",
						}
					},
				},
			},
			"f8f8f8": {
				color: "f8f8f8",
				opacity: "30%",
				_active: {
					opacity: "80%",
				},
				_enabled: {
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							opacity: "80%",
						}
					},
				},
			},
			"ff6a64": {
				bgColor: "ff6a64",
				color: "white",
				_enabled: {
					_active: {
						bgColor: "ff6a64.hover",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "ff6a64.hover"
						}
					},
				},
			},
			"ffffff_opacity30": {
				bgColor: "rgba(255, 255, 255, 0.05)",
				color: "rgba(255, 255, 255, 0.3)",
				_enabled: {
					_active: {
						color: "rgba(255, 255, 255, 0.8)",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							color: "rgba(255, 255, 255, 0.8)",
						}
					},
				},
			},
		},
		size: {
			px0py0: {px: "0px", py: "0px"},
			pl12pr14py6: {pl: "12px", pr: "14px", py: "6px"},
			px16py6: {px: "16px", py: "6px"},
			px16py9: {px: "16px", py: "9px"},
			pl16pr14py6: {pl: "16px", pr: "14px", py: "6px"},
			pl16pr14py9: {pl: "16px", pr: "14px", py: "9px"},
			px12py6full: {w: "100%", px: "12px", py: "6px"},
			px16py9full: {w: "100%", px: "16px", py: "9px"},
			// ниже deprecated
			full: {w: "100%", px: "16px", py: "9px", minHeight: "44px"},
			full_desktop: {w: "100%", px: "16px", py: "6px", minHeight: "35px"},
			fullPy12: {w: "100%", px: "16px", py: "12px"},
			px21py6: {px: "21px", py: "6px"},
			px0py8: {px: "0px", py: "8px"},
			px8py8: {px: "8px", py: "8px"},
		},
	},
})
