import { Menu, MenuArrow, MenuArrowTip, MenuContent, MenuPositioner, MenuTrigger } from "../menu.tsx";
import { VStack } from "../../../styled-system/jsx";
import { Portal } from "@ark-ui/react";
import { useMemo } from "react";
import useDeviceDetect from "../../lib/deviceDetect.ts";
import { css } from "../../../styled-system/css";
import WindowsMenuItems from "./download/WindowsMenuItems.tsx";
import LinuxMenuItems from "./download/LinuxMenuItems.tsx";
import MacOsMenuItems from "./download/MacOsMenuItems.tsx";

type DownloadMenuProps = {
	triggerEl: JSX.Element;
	onOpenFunction?: () => void;
	onCloseFunction?: () => void;
};

const DownloadMenu = ({ triggerEl, onOpenFunction, onCloseFunction }: DownloadMenuProps) => {
	const deviceDetect = useDeviceDetect();

	const renderedMenuItems = useMemo(() => {
		if (deviceDetect.isDesktopWindows) {
			return <WindowsMenuItems isNeedAnotherPlatformItem = {true} />;
		}

		if (deviceDetect.isDesktopLinux) {
			return <LinuxMenuItems isNeedAnotherPlatformItem = {true} />;
		}

		if (deviceDetect.isDesktopMacOs || deviceDetect.isSafariDesktop) {
			return <MacOsMenuItems isNeedAnotherPlatformItem = {true} />;
		}

		return <WindowsMenuItems isNeedAnotherPlatformItem = {true} />;
	}, [ deviceDetect ]);

	return (
		<Menu
			onOpen = {onOpenFunction}
			closeOnSelect = {!deviceDetect.isDesktopWindows}
			onClose = {onCloseFunction}
			positioning = {{ placement: "top", offset: { mainAxis: 8 } }}
			type = "install_desktop"
		>
			<VStack gap = "0px">
				<MenuTrigger asChild>{triggerEl}</MenuTrigger>
			</VStack>
			<Portal>
				<MenuPositioner w = "290px">
					<MenuContent>
						<MenuArrow
							className = {css({
								"--arrow-size": "9px",
							})}
						>
							<MenuArrowTip
								className = {css({
									"--arrow-background": "white",
								})}
							/>
						</MenuArrow>
						{renderedMenuItems}
					</MenuContent>
				</MenuPositioner>
			</Portal>
		</Menu>
	);
};

export default DownloadMenu;
