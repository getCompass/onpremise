import ConferenceEnded84Svg from "../../img/mobile/ConferenceEnded84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import DownloadMenu from "../../components/mobile/DownloadMenu.tsx";
import { useCallback, useState } from "react";
import { getDeeplinkUrlScheme } from "../../private/custom.ts";
import { openDeepLink } from "../../lib/functions.ts";

const PageContentMobileKickedFromConference = () => {
	const langStringMobileKickedFromConferenceContentTitle = useLangString("mobile.kicked_from_conference_content.title");
	const langStringMobileKickedFromConferenceContentDesc = useLangString("mobile.kicked_from_conference_content.desc");
	const langStringMobileKickedFromConferenceContentTryCompassButton = useLangString(
		"mobile.kicked_from_conference_content.try_compass_button"
	);

	const [ clickCount, setClickCount ] = useState(0);

	const onTryCompassClickHandler = useCallback(() => {
		openDeepLink(true, getDeeplinkUrlScheme());
	}, []);

	return (
		<VStack w = "100%" mt = "98px" pt = "32px" pb = "24px" px = "24px" rounded = "16px" bgColor = "255255255.04"
				gap = "0px">
			<Center w = "100px" h = "100px">
				<Icon width = "84px" height = "84px" avatar = {ConferenceEnded84Svg} />
			</Center>
			<Text style = "inter_24_34_700" mt = "16px" textAlign = "center" color = "333e49">
				{langStringMobileKickedFromConferenceContentTitle}
			</Text>
			<Text style = "inter_18_25_400" mt = "8px" textAlign = "center" color = "333e49">
				{langStringMobileKickedFromConferenceContentDesc}
			</Text>
			<DownloadMenu
				marginTop = "24px"
				width = "100%"
				triggerEl = {
					<Button
						size = "px24py12full"
						textSize = "inter_18_27_600"
						rounded = "12px"
						onClick = {() => {
							onTryCompassClickHandler();
							setClickCount(clickCount + 1);
						}}
					>
						{langStringMobileKickedFromConferenceContentTryCompassButton}
					</Button>
				}
				isNeedDeviceAutoDetect = {false}
				clickCount = {clickCount}
			/>
		</VStack>
	);
};

export default PageContentMobileKickedFromConference;
