import ConferenceEnded84Svg from "../../img/desktop/ConferenceEnded84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import DownloadMenu from "../../components/desktop/DownloadMenu.tsx";
import {useCallback, useState} from "react";
import { getDeeplinkUrlScheme } from "../../private/custom.ts";
import { openDeepLink } from "../../lib/functions.ts";

const PageContentDesktopConferenceEnded = () => {
	const langStringDesktopConferenceEndedContentTitle = useLangString("desktop.conference_ended_content.title");
	const langStringDesktopConferenceEndedContentDesc = useLangString("desktop.conference_ended_content.desc");
	const langStringDesktopConferenceEndedContentTryCompassButton = useLangString(
		"desktop.conference_ended_content.try_compass_button"
	);

	const [clickCount, setClickCount] = useState(0);

	const onTryCompassClickHandler = useCallback(() => {
		openDeepLink(false, getDeeplinkUrlScheme());
	}, []);

	return (
		<VStack minW="942px" mt="122px" px="55px" py="32px" rounded="20px" bgColor="255255255.03" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={ConferenceEnded84Svg} />
			</Center>
			<Text style="inter_40_48_700" letterSpacing="-0.3px" mt="12px" textAlign="center">
				{langStringDesktopConferenceEndedContentTitle}
			</Text>
			<Text style="inter_20_28_400" mt="16px" textAlign="center">
				{langStringDesktopConferenceEndedContentDesc.split("\n").map((line, index) => (
					<div key={index}>{line}</div>
				))}
			</Text>
			<DownloadMenu
				size="default"
				triggerEl={
					<Button
						minW="370px"
						size="px32py16"
						textSize="inter_20_24_500"
						mt="32px"
						rounded="12px"
						onClick={() => {
							onTryCompassClickHandler();
							setClickCount(clickCount + 1);
						}}
					>
						{langStringDesktopConferenceEndedContentTryCompassButton}
					</Button>
				}
				clickCount={clickCount}
			/>
		</VStack>
	);
};

export default PageContentDesktopConferenceEnded;
