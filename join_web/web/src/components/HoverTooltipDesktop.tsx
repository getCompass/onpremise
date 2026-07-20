import { Tooltip, TooltipArrow, TooltipContent, TooltipProvider, TooltipTrigger } from "./tooltip.tsx";
import { Portal } from "@ark-ui/react";
import { PropsWithChildren, useMemo } from "react";

type HoverTooltipDesktopProps = PropsWithChildren<{
	tooltipText: string;
	tooltipType?: string;
}>;
const HoverTooltipDesktop = ({ children, tooltipText, tooltipType }: HoverTooltipDesktopProps) => {

	const renderedTooltipArrow = useMemo(() => {
		// if (tooltipType === "warning") {
		// 	return (
		// 		<TooltipArrow width="8px" height="5px" asChild>
		// 			<svg width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
		// 				<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00" />
		// 			</svg>
		// 		</TooltipArrow>
		// 	);
		// }

		return <TooltipArrow width="8px" height="5px" />;
	}, [ tooltipType ]);

	return (
		<TooltipProvider>
			<Tooltip
				style="desktop"
				type="default_desktop"
				delayDuration={0}
			>
				<TooltipTrigger asChild>
					{children}
				</TooltipTrigger>
				<Portal>
					<TooltipContent
						padding="4px 8px 6px 8px"
						fontSize="13px"
						lineHeight="18px"
						sideOffset={4}
						avoidCollisions={false}
						style={{
							maxWidth: "300px",
							width: "var(--radix-tooltip-trigger-width)",
						}}
					>
						{renderedTooltipArrow}
						{tooltipText}
					</TooltipContent>
				</Portal>
			</Tooltip>
		</TooltipProvider>
	);
}

export default HoverTooltipDesktop;
