import JoinPrivateConferenceIcon84 from "../../img/desktop/JoinPrivateConferenceIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import { useCallback, useEffect, useState } from "react";
import { useAtomValue } from "jotai";
import { conferenceDataState } from "../../api/_stores.ts";
import DownloadMenu from "../../components/desktop/DownloadMenu.tsx";
import { getDeeplink, openDeepLink } from "../../lib/functions.ts";

const PageContentDesktopJoinPrivateConference = () => {
	const langStringDesktopJoinPrivateConferenceContentTitle = useLangString(
		"desktop.join_private_conference_content.title"
	);
	const langStringDesktopJoinPrivateConferenceContentDesc = useLangString(
		"desktop.join_private_conference_content.desc"
	);
	const langStringDesktopJoinPrivateConferenceContentJoinViaCompassButton = useLangString(
		"desktop.join_private_conference_content.join_via_compass_button"
	);
	const langStringDesktopJoinPrivateConferenceContentTryAgainButton = useLangString(
		"desktop.join_private_conference_content.try_again_button"
	);

	const conferenceData = useAtomValue(conferenceDataState);

	const [clickCount, setClickCount] = useState(0);

	// если у пользователя установлено приложение Compass – поверх страницы сразу появляется системное браузерное окно с предложением открыть ссылку в приложении.
	useEffect(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(false, getDeeplink(conferenceData.link));
	}, []);

	const onOpenCompassClickHandler = useCallback(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(false, getDeeplink(conferenceData.link));
	}, [conferenceData?.link]);

	const onTryAgainClickHandler = useCallback(async () => {
		window.location.reload();
	}, []);

	return (
		<VStack minW="942px" mt="119px" px="32px" py="32px" rounded="20px" bgColor="255255255.03" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={JoinPrivateConferenceIcon84} />
			</Center>
			<Text style="inter_40_48_700" letterSpacing="-0.3px" mt="12px" textAlign="center">
				{langStringDesktopJoinPrivateConferenceContentTitle}
			</Text>
			<Text style="inter_20_28_400" mt="16px" textAlign="center">
				{langStringDesktopJoinPrivateConferenceContentDesc.split("\n").map((line, index) => (
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
							onOpenCompassClickHandler();
							setClickCount(clickCount + 1);
						}}
					>
						{langStringDesktopJoinPrivateConferenceContentJoinViaCompassButton}
					</Button>
				}
				clickCount={clickCount}
			/>
			<Button textSize="inter_16_22_400" mt="20px" color="2574a9" onClick={() => onTryAgainClickHandler()}>
				{langStringDesktopJoinPrivateConferenceContentTryAgainButton}
			</Button>
		</VStack>
	);
};

export default PageContentDesktopJoinPrivateConference;
