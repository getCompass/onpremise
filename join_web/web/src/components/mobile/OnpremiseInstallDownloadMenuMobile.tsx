import { Portal } from "@ark-ui/react";
import { useState } from "react";
import { Menu, MenuArrow, MenuArrowTip, MenuContent, MenuPositioner, MenuTrigger } from "../menu.tsx";
import { css } from "../../../styled-system/css";

type DownloadMenuProps = {
	triggerEl: JSX.Element;
	menuItems: JSX.Element;
	onSelectHandler: (value: string) => void;
};

const OnpremiseInstallDownloadMenuMobile = ({ triggerEl, menuItems, onSelectHandler }: DownloadMenuProps) => {
	const [isStoreMenuOpen, setStoreMenuOpen] = useState(false);

	return (
		<Menu
			isOpen={isStoreMenuOpen}
			onSelect={({ value }) => onSelectHandler(value)}
			onClose={() => setStoreMenuOpen(false)}
			onFocusOutside={() => setStoreMenuOpen(false)}
			onInteractOutside={() => setStoreMenuOpen(false)}
			onPointerDownOutside={() => setStoreMenuOpen(false)}
			positioning={{ placement: "bottom", offset: { mainAxis: 6 } }}
			type="install_mobile"
		>
			<MenuTrigger asChild>{triggerEl}</MenuTrigger>
			<Portal>
				<MenuPositioner w="290px">
					<MenuContent>
						<MenuArrow
							className={css({
								"--arrow-size": "9px",
								"--arrow-offset": "-6px !important",
							})}
						>
							<MenuArrowTip
								className={css({
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

export default OnpremiseInstallDownloadMenuMobile;
