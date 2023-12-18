import {pinInputAnatomy} from "@ark-ui/react"
import {defineSlotRecipe} from "@pandacss/dev"

export const pinInput = defineSlotRecipe({
	className: "pinInput",
	slots: pinInputAnatomy.keys(),
	base: {
		control: {
			display: "flex",
			gap: "4px",
		},
	},
})
