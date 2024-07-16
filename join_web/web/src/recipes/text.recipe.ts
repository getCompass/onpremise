import { defineRecipe } from "@pandacss/dev";

export const text = defineRecipe({
	className: "text",
	base: {},
	defaultVariants: {
		overflow: "default",
		color: "333e49",
	},
	variants: {
		overflow: {
			default: {},
			ellipsis: { whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" },
			wrapEllipsis: { overflow: "hidden", textOverflow: "ellipsis" },
			breakWord: { overflow: "hidden", whiteSpace: "pre-wrap", wordWrap: "break-word" },
		},
		style: {
			lato_13_18_400: {
				fontSize: "13px",
				lineHeight: "18px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			lato_14_17_400: {
				fontSize: "14px",
				lineHeight: "17px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			lato_14_17_700: {
				fontSize: "14px",
				lineHeight: "17px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_14_20_400: {
				fontSize: "14px",
				lineHeight: "20px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			lato_14_20_700: {
				fontSize: "14px",
				lineHeight: "20px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_15_21_700: {
				fontSize: "15px",
				lineHeight: "21px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_16_20_700: {
				fontSize: "16px",
				lineHeight: "20px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_16_22_400: {
				fontSize: "16px",
				lineHeight: "22px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			lato_16_22_700: {
				fontSize: "16px",
				lineHeight: "22px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_20_28_700: {
				fontSize: "20px",
				lineHeight: "28px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_18_24_900: {
				fontSize: "18px",
				lineHeight: "24px",
				fontFamily: "lato_black",
				fontWeight: "normal",
			},
			inter_14_17_400: {
				fontSize: "14px",
				lineHeight: "17px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_14_17_500: {
				fontSize: "14px",
				lineHeight: "17px",
				fontFamily: "inter_medium",
				fontWeight: "normal",
			},
			inter_14_20_400: {
				fontSize: "14px",
				lineHeight: "20px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_14_21_600: {
				fontSize: "14px",
				lineHeight: "20px",
				fontFamily: "inter_semibold",
				fontWeight: "normal",
			},
			inter_15_21_400: {
				fontSize: "15px",
				lineHeight: "21px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_16_19_400: {
				fontSize: "16px",
				lineHeight: "19px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_16_19_500: {
				fontSize: "16px",
				lineHeight: "19px",
				fontFamily: "inter_medium",
				fontWeight: "normal",
			},
			inter_16_22_400: {
				fontSize: "16px",
				lineHeight: "22px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_18_22_400: {
				fontSize: "18px",
				lineHeight: "22px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_18_25_400: {
				fontSize: "18px",
				lineHeight: "25px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_18_27_400: {
				fontSize: "18px",
				lineHeight: "27px",
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
			inter_30_36_700: {
				fontSize: "30px",
				lineHeight: "36px",
				fontFamily: "inter_bold",
				fontWeight: "normal",
			},
			inter_40_48_700: {
				fontSize: "40px",
				lineHeight: "48px",
				fontFamily: "inter_bold",
				fontWeight: "normal",
			},
		},
		ls: {
			"-012": {
				letterSpacing: "-0.12px",
			},
			"-015": {
				letterSpacing: "-0.15px",
			},
			"-02": {
				letterSpacing: "-0.2px",
			},
			"-03": {
				letterSpacing: "-0.3px",
			},
		},
		// ниже deprecated
		fs: {
			"13": {
				fontSize: "13px",
			},
			"14": {
				fontSize: "14px",
			},
			"15": {
				fontSize: "15px",
			},
			"16": {
				fontSize: "16px",
			},
			"17": {
				fontSize: "17px",
			},
			"18": {
				fontSize: "18px",
			},
			"20": {
				fontSize: "20px",
			},
		},
		lh: {
			"16": {
				lineHeight: "16px",
			},
			"18": {
				lineHeight: "18px",
			},
			"20": {
				lineHeight: "20px",
			},
			"22": {
				lineHeight: "22px",
			},
			"24": {
				lineHeight: "24px",
			},
			"27": {
				lineHeight: "27px",
			},
			"28": {
				lineHeight: "28px",
			},
		},
		color: {
			white: {
				color: "white",
			},
			black: {
				color: "black",
			},
			"007aff": {
				color: "007aff",
			},
			"009fe6": {
				color: "009fe6",
			},
			"333e49": {
				color: "333e49",
			},
			"677380": {
				color: "677380",
			},
			b4b4b4: {
				color: "b4b4b4",
			},
			f8f8f8: {
				color: "f8f8f8",
			},
			"248248248.03": {
				color: "248248248.03",
			},
			"248248248.05": {
				color: "rgba(248, 248, 248, 0.5)"
			},
			ff6a64: {
				color: "ff6a64",
			},
		},
		font: {
			regular: {
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			bold: {
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			bold900: {
				fontFamily: "lato_black",
				fontWeight: "normal",
			},
		},
	},
});
