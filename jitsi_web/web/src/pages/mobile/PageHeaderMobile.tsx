import { useLangString } from "../../lib/getLangString.ts";
import { HStack } from "../../../styled-system/jsx";
import { Icon } from "../../components/Icon.tsx";
import CompassLogo32Svg from "../../img/mobile/CompassLogo32.svg";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import DownloadMenu from "../../components/mobile/DownloadMenu.tsx";
import { CLICK_COUNT_AFTER_SHOW_DOWNLOAD_POPOVER } from "../../api/_types.ts";
import LangMenuSelector from "../../components/mobile/LangMenuSelector.tsx";

const PageHeaderMobile = () => {
	const langStringMobileLogoTitle = useLangString("mobile.logo.title");
	const langStringPageMobileDownloadCompassButton = useLangString("mobile.download_compass.button");

	return (
		<HStack w="100%" pb="12px" justifyContent="space-between" pt="12px">
			<HStack gap="10px" onClick={() => (window.location.href = "https://getcompass.ru/")}>
				<Icon width="32px" height="32px" avatar={CompassLogo32Svg} />
				<Text textTransform="uppercase" style="lato_14_17_700" color="333e49" mt="-2px">
					{langStringMobileLogoTitle}
				</Text>
			</HStack>
			<HStack gap="16px">
				<DownloadMenu
					triggerEl={
						<Button size="px12py6" textSize="inter_13_18_500">
							{langStringPageMobileDownloadCompassButton}
						</Button>
					}
					marginTop="0px"
					width={undefined}
					isNeedDeviceAutoDetect={true}
					clickCount={CLICK_COUNT_AFTER_SHOW_DOWNLOAD_POPOVER}
				/>
				<LangMenuSelector />
			</HStack>
		</HStack>
	);
};

export default PageHeaderMobile;
