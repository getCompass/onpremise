import { Menu, MenuContent, MenuPositioner, MenuTrigger } from "../menu.tsx";
import { VStack } from "../../../styled-system/jsx";
import { Portal } from "@ark-ui/react";
import { useMemo, useState } from "react";
import useDeviceDetect from "../../lib/useDeviceDetect.ts";
import { CLICK_COUNT_AFTER_SHOW_DOWNLOAD_POPOVER, Size } from "../../api/_types.ts";
import WindowsMenuItems from "./download/WindowsMenuItems.tsx";
import LinuxMenuItems from "./download/LinuxMenuItems.tsx";
import MacOsMenuItems from "./download/MacOsMenuItems.tsx";

type DownloadMenuProps = {
	triggerEl: JSX.Element;
	size: Size;
	clickCount: number;
};

const DownloadMenu = ({ triggerEl, size, clickCount }: DownloadMenuProps) => {
	const deviceDetect = useDeviceDetect();

	const [ isStoreMenuOpen, setStoreMenuOpen ] = useState(false);

	const renderedMenuItems = useMemo(() => {
		if (deviceDetect.isDesktopWindows) {
			return <WindowsMenuItems size = {size} />;
		}

		if (deviceDetect.isDesktopLinux) {
			return <LinuxMenuItems size = {size} />;
		}

		if (deviceDetect.isDesktopMacOs || deviceDetect.isSafariDesktop) {
			return <MacOsMenuItems size = {size} />;
		}

		return <WindowsMenuItems size = {size} />;
	}, [ deviceDetect, size ]);

	if (size === "small") {
		return (
			<Menu
				isOpen = {isStoreMenuOpen}
				closeOnSelect = {!deviceDetect.isDesktopWindows}
				onSelect = {() => null}
				onClose = {() => setStoreMenuOpen(false)}
				onFocusOutside = {() => setStoreMenuOpen(false)}
				onInteractOutside = {() => setStoreMenuOpen(false)}
				onPointerDownOutside = {() => setStoreMenuOpen(false)}
				positioning = {{ placement: "bottom", offset: { mainAxis: 4 } }}
				type = "desktop_small"
			>
				<VStack gap = "0px">
					{clickCount >= CLICK_COUNT_AFTER_SHOW_DOWNLOAD_POPOVER ? (
						<MenuTrigger asChild>{triggerEl}</MenuTrigger>
					) : (
						triggerEl
					)}
				</VStack>
				<Portal>
					<MenuPositioner w = "290px">
						<MenuContent>{renderedMenuItems}</MenuContent>
					</MenuPositioner>
				</Portal>
			</Menu>
		);
	}

	return (
		<Menu
			isOpen = {isStoreMenuOpen}
			closeOnSelect = {!deviceDetect.isDesktopWindows}
			onSelect = {() => null}
			onClose = {() => setStoreMenuOpen(false)}
			onFocusOutside = {() => setStoreMenuOpen(false)}
			onInteractOutside = {() => setStoreMenuOpen(false)}
			onPointerDownOutside = {() => setStoreMenuOpen(false)}
			positioning = {{ placement: "bottom", offset: { mainAxis: 8 } }}
			type = "desktop"
		>
			<VStack gap = "0px">{clickCount >= 1 ? <MenuTrigger asChild>{triggerEl}</MenuTrigger> : triggerEl}</VStack>
			<Portal>
				<MenuPositioner w = "290px">
					<MenuContent>{renderedMenuItems}</MenuContent>
				</MenuPositioner>
			</Portal>
		</Menu>
	);
};

export default DownloadMenu;
