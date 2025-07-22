import { Portal } from "@ark-ui/react";
import { useCallback, useState } from "react";
import { Menu, MenuArrow, MenuArrowTip, MenuContent, MenuPositioner, MenuTrigger } from "../menu.tsx";
import { css } from "../../../styled-system/css";
import useDeviceDetect from "../../lib/deviceDetect.ts";

type DownloadMenuProps = {
	triggerEl: JSX.Element;
	menuItems: JSX.Element;
};

const OnpremiseInstallDownloadMenuDesktop = ({ triggerEl, menuItems }: DownloadMenuProps) => {
	const [ isStoreMenuOpen, setStoreMenuOpen ] = useState(false);
	const deviceDetect = useDeviceDetect();

	const onCloseHandler = useCallback(() => {
		setStoreMenuOpen(false);
	}, []);

	return (
		<Menu
			isOpen = {isStoreMenuOpen}
			onSelect = {() => null}
			onClose = {onCloseHandler}
			closeOnSelect = {!deviceDetect.isDesktopWindows}
			onFocusOutside = {() => {
				setStoreMenuOpen(false);
			}}
			onInteractOutside = {() => {
				setStoreMenuOpen(false);
			}}
			onPointerDownOutside = {() => {
				setStoreMenuOpen(false);
			}}
			positioning = {{ placement: "bottom", offset: { mainAxis: -2 } }}
			type = "install_desktop"
		>
			<MenuTrigger asChild>{triggerEl}</MenuTrigger>
			<Portal>
				<MenuPositioner w = "367px">
					<MenuContent>
						<MenuArrow
							className = {css({
								"--arrow-size": "9px",
								"--arrow-offset": "-6px !important",
							})}
						>
							<MenuArrowTip
								className = {css({
									"--arrow-background": "white",
								})}
							/>
						</MenuArrow>
						{menuItems}
					</MenuContent>
				</MenuPositioner>
			</Portal>
		</Menu>
	);
};

export default OnpremiseInstallDownloadMenuDesktop;
