import useIsMobile from "../lib/useIsMobile.ts";
import OpenLangMenuButtonDesktop from "./OpenLangMenuButtonDesktop.tsx";
import OpenLangMenuButton from "./OpenLangMenuButton.tsx";
import {HStack} from "../../styled-system/jsx";
import LogoutButtonMobile from "./LogoutButtonMobile.tsx";
import LogoutButtonDesktop from "./LogoutButtonDesktop.tsx";
import {useAtomValue} from "jotai";
import {isShouldShowLogoutButton} from "../api/_stores.ts";


const PageHeaderLeft = () => {
	const isMobile = useIsMobile();
	const shouldShowLogoutButton = useAtomValue(isShouldShowLogoutButton);
	if (isMobile) {
		return (
			<HStack gap="16px" width="100%" justifyContent="flex-end" position="absolute" zIndex="99999" top="16px"
					right="16px">
				<OpenLangMenuButton />
				{shouldShowLogoutButton && <LogoutButtonMobile/>}
			</HStack>
		);
	}
	return (
		<HStack gap="16px" width="100%" justifyContent="flex-end" position="absolute" zIndex="99999" top="16px"
				right="16px">
			<OpenLangMenuButtonDesktop />
			{shouldShowLogoutButton && <LogoutButtonDesktop/>}
		</HStack>
	)
}

export default PageHeaderLeft;