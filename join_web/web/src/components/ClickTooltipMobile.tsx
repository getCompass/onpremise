import { Tooltip, TooltipArrow, TooltipContent, TooltipProvider, TooltipTrigger } from "./tooltip.tsx";
import { Portal } from "@ark-ui/react";
import { PropsWithChildren, useMemo, useState } from "react";
import { VStack } from "../../styled-system/jsx";

type ClickTooltipMobileProps = PropsWithChildren<{
	tooltipText: string;
	tooltipType?: string;
}>;
const ClickTooltipMobile = ({ children, tooltipText, tooltipType }: ClickTooltipMobileProps) => {

	const [ isToolTipVisible, setIsToolTipVisible ] = useState(false);

	const renderedTooltipArrow = useMemo(() => {
		return <TooltipArrow width="8px" height="5px" />;
	}, [ tooltipType ]);

	return (
		<TooltipProvider>
			<Tooltip
				open={isToolTipVisible}
				onOpenChange={() => null}
				style="mobile"
				type="default_mobile"
				disableHoverableContent
			>
				<VStack w="100%" mt="8px" onClick={() => setIsToolTipVisible((prev) => !prev)}>
					<TooltipTrigger
						style={{
							width: "100%",
							height: "0px",
							opacity: "0%",
						}}
					/>
					{children}
				</VStack>
				<Portal>
					<TooltipContent
						onClick={() => setIsToolTipVisible(false)}
						onEscapeKeyDown={() => setIsToolTipVisible(false)}
						onPointerDownOutside={() => setIsToolTipVisible(false)}
						padding="6px 12px"
						fontSize="14px"
						lineHeight="20px"
						sideOffset={4}
						avoidCollisions={false}
						style={{
							maxWidth: "256px",
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

export default ClickTooltipMobile;
