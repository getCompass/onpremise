import { HStack } from "../../../styled-system/jsx";
import CompassLogo32Svg from "../../img/desktop/CompassLogo32.svg";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Button } from "../../components/button.tsx";
import { Icon } from "../../components/Icon.tsx";
import DownloadMenu from "../../components/desktop/DownloadMenu.tsx";
import {CLICK_COUNT_AFTER_SHOW_DOWNLOAD_POPOVER} from "../../api/_types.ts";
import LangMenuSelector from "../../components/desktop/LangMenuSelector.tsx";

const PageHeaderDesktop = () => {
	const langStringDesktopLogoTitle = useLangString("desktop.logo.title");
	const langStringDesktopDownloadCompassButton = useLangString("desktop.download_compass.button");

	return (
		<HStack w="100%" justifyContent="space-between" pt="24px">
			<HStack gap="12px" onClick={() => (window.location.href = "https://getcompass.ru/")} cursor="pointer">
				<Icon width="32px" height="32px" avatar={CompassLogo32Svg} />
				<Text textTransform="uppercase" style="lato_15_21_700">
					{langStringDesktopLogoTitle}
				</Text>
			</HStack>
			<HStack gap="16px">
				<DownloadMenu
					size="small"
					triggerEl={
						<Button size="px12py7" textSize="lato_13_18_700">
							{langStringDesktopDownloadCompassButton}
						</Button>
					}
					clickCount={CLICK_COUNT_AFTER_SHOW_DOWNLOAD_POPOVER}
				/>
				<LangMenuSelector/>
			</HStack>
		</HStack>
	);
};

export default PageHeaderDesktop;
