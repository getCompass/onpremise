import { defineRecipe } from "@pandacss/dev";

export const button = defineRecipe({
	className: "button",
	base: {
		rounded: "6px",
		outline: "none",
		userSelect: "none",
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
		size: "default",
		textSize: "inter_20_28_400",
		color: "665ebe",
	},
	variants: {
		textSize: {
			lato_13_18_700: {
				fontSize: "13px",
				lineHeight: "18px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			inter_13_18_500: {
				fontSize: "13px",
				lineHeight: "18px",
				fontFamily: "inter_medium",
				fontWeight: "normal",
			},
			inter_16_22_400: {
				fontSize: "16px",
				lineHeight: "22px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_18_27_600: {
				fontSize: "18px",
				lineHeight: "27px",
				fontFamily: "inter_semibold",
				fontWeight: "normal",
			},
			inter_20_24_500: {
				fontSize: "20px",
				lineHeight: "24px",
				fontFamily: "inter_medium",
				fontWeight: "normal",
			},
			inter_20_28_400: {
				fontSize: "20px",
				lineHeight: "28px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
		},
		size: {
			default: {},
			px12py6: { px: "12px", py: "6px" },
			px12py7: { px: "12px", py: "7px" },
			px24py12full: { px: "24px", py: "12px", width: "100%" },
			px32py16: { px: "32px", py: "16px" },
		},
		color: {
			"2574a9": {
				color: "2574a9",
				_enabled: {
					opacity: "100%",
					transition: "background-color .2s linear",
					_active: {
						color: "2574a9.hover",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							color: "2574a9.hover",
						},
					},
				},
				_disabled: {
					opacity: "30%",
				},
			},
			"665ebe": {
				bgColor: "665ebe",
				color: "white",
				_enabled: {
					opacity: "100%",
					transition: "background-color .2s linear",
					_active: {
						bgColor: "665ebe.hover",
					},
					["@media (hover: hover) and (pointer: fine)"]: {
						"&:hover": {
							bgColor: "665ebe.hover",
						},
					},
				},
				_disabled: {
					opacity: "30%",
				},
			},
		},
	},
});
