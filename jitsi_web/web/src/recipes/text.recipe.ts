import { defineRecipe } from "@pandacss/dev";

export const text = defineRecipe({
	className: "text",
	base: {},
	defaultVariants: {
		overflow: "default",
		style: "inter_20_28_400",
		color: "2d343c",
	},
	variants: {
		overflow: {
			default: {},
			ellipsis: { whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" },
			breakWord: { overflow: "hidden", whiteSpace: "pre-wrap", wordWrap: "break-word" },
			noWrap: { whiteSpace: "nowrap" },
		},
		style: {
			lato_13_13_700: {
				fontSize: "13px",
				lineHeight: "13px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_14_17_700: {
				fontSize: "14px",
				lineHeight: "17px",
				fontFamily: "lato_bold",
				fontWeight: "normal",
			},
			lato_14_20_500: {
				fontSize: "14px",
				lineHeight: "20px",
				fontFamily: "lato_medium",
				fontWeight: "normal",
			},
			lato_15_20_400: {
				fontSize: "15px",
				lineHeight: "20px",
				fontFamily: "lato_regular",
				fontWeight: "normal",
			},
			lato_15_20_700: {
				fontSize: "15px",
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
			lato_18_24_500: {
				fontSize: "18px",
				lineHeight: "24px",
				fontFamily: "lato_medium",
				fontWeight: "normal",
			},
			inter_12_14_400: {
				fontSize: "12px",
				lineHeight: "14px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_13_19_400: {
				fontSize: "13px",
				lineHeight: "19px",
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
			inter_20_28_400: {
				fontSize: "20px",
				lineHeight: "28px",
				fontFamily: "inter_regular",
				fontWeight: "normal",
			},
			inter_24_34_700: {
				fontSize: "24px",
				lineHeight: "34px",
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
		color: {
			"2d343c": {
				color: "2d343c",
			},
			"333e49": {
				color: "333e49",
			},
			"677380": {
				color: "677380",
			},
		},
	},
});
