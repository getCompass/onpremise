import JoinPrivateConferenceIcon84 from "../../img/mobile/JoinPrivateConferenceIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Box, Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import { useAtomValue } from "jotai";
import { conferenceDataState } from "../../api/_stores.ts";
import { useCallback, useState } from "react";
import DownloadMenu from "../../components/mobile/DownloadMenu.tsx";
import { getDeeplink, openDeepLink } from "../../lib/functions.ts";

const PageContentMobileJoinPrivateConference = () => {
	const langStringMobileJoinPrivateConferenceContentTitle = useLangString(
		"mobile.join_private_conference_content.title"
	);
	const langStringMobileJoinPrivateConferenceContentDesc = useLangString(
		"mobile.join_private_conference_content.desc"
	);
	const langStringMobileJoinPrivateConferenceContentJoinViaCompassButton = useLangString(
		"mobile.join_private_conference_content.join_via_compass_button"
	);
	const langStringMobileJoinPrivateConferenceContentTryAgainButton = useLangString(
		"mobile.join_private_conference_content.try_again_button"
	);

	const conferenceData = useAtomValue(conferenceDataState);

	const [clickCount, setClickCount] = useState(0);

	const onOpenCompassClickHandler = useCallback(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(true, getDeeplink(conferenceData.link));
	}, [conferenceData]);

	const onTryAgainClickHandler = useCallback(async () => {
		window.location.reload();
	}, []);

	return (
		<VStack w="100%" mt="74px" pt="32px" pb="24px" rounded="16px" bgColor="255255255.04" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={JoinPrivateConferenceIcon84} />
			</Center>
			<Text style="inter_24_34_700" mt="16px" textAlign="center" color="333e49" px="16px">
				{langStringMobileJoinPrivateConferenceContentTitle}
			</Text>
			<Text style="inter_18_25_400" mt="8px" textAlign="center" color="333e49" px="16px">
				{langStringMobileJoinPrivateConferenceContentDesc}
			</Text>
			<DownloadMenu
				marginTop="24px"
				width="100%"
				triggerEl={
					<Box w="100%" px="24px">
						<Button
							size="px24py12full"
							textSize="inter_18_27_600"
							rounded="12px"
							onClick={() => {
								onOpenCompassClickHandler();
								setClickCount(clickCount + 1);
							}}
						>
							{langStringMobileJoinPrivateConferenceContentJoinViaCompassButton}
						</Button>
					</Box>
				}
				isNeedDeviceAutoDetect={false}
				clickCount={clickCount}
			/>
			<Button textSize="inter_16_22_400" mt="20px" color="2574a9" onClick={() => onTryAgainClickHandler()}>
				{langStringMobileJoinPrivateConferenceContentTryAgainButton}
			</Button>
		</VStack>
	);
};

export default PageContentMobileJoinPrivateConference;
