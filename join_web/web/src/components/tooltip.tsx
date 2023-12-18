import * as RadixTooltip from '@radix-ui/react-tooltip';
import {styled} from "../../styled-system/jsx"
import {tooltip, type TooltipVariantProps} from "../../styled-system/recipes"
import {createStyleContext} from "../lib/create-style-context"

const {withProvider, withContext} = createStyleContext(tooltip)

export type TooltipProps = RadixTooltip.TooltipProps & TooltipVariantProps

const TooltipRoot = withProvider(styled(RadixTooltip.Root))
export const TooltipProvider = withProvider(styled(RadixTooltip.Provider))
export const TooltipArrow = withContext(styled(RadixTooltip.Arrow), "arrow")
export const TooltipContent = withContext(styled(RadixTooltip.Content), "content")
export const TooltipTrigger = withContext(styled(RadixTooltip.Trigger), "trigger")

export const Tooltip = Object.assign(TooltipRoot, {
    Root: TooltipRoot,
    Provider: TooltipProvider,
    Arrow: TooltipArrow,
    Content: TooltipContent,
    Trigger: TooltipTrigger,
})
