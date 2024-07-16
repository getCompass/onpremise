import JoinConferenceIcon84Svg from "../../img/mobile/JoinConferenceIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Box, Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import DownloadMenu from "../../components/mobile/DownloadMenu.tsx";
import { useApiJitsiGetConferenceData, useApiJitsiJoinConference } from "../../api/jitsi.ts";
import { useAtom, useSetAtom } from "jotai";
import { conferenceDataErrorCodeState, conferenceDataState, limitNextAttemptState } from "../../api/_stores.ts";
import { useCallback, useState } from "react";
import { ApiError, LimitError, NetworkError, ServerError } from "../../api/_index.ts";
import {
	API_JITSI_CONFERENCE_CODE_ERROR_LIMIT,
	API_JITSI_GET_CONFERENCE_CODE_ERROR_ATTEMPT_JOIN_TO_PRIVATE_CONFERENCE,
} from "../../api/_types.ts";
import { getDeeplink, openDeepLink } from "../../lib/functions.ts";
import { useShowToast } from "../../components/Toast.tsx";

const PageContentMobileJoinPublicConference = () => {
	const langStringNetworkError = useLangString("network_error");
	const langStringServerError = useLangString("server_error");
	const langStringMobileJoinPublicConferenceContentTitle = useLangString(
		"mobile.join_public_conference_content.title"
	);
	const langStringMobileJoinPublicConferenceContentDesc = useLangString("mobile.join_public_conference_content.desc");
	const langStringMobileJoinPublicConferenceContentJoinButton = useLangString(
		"mobile.join_public_conference_content.join_button"
	);
	const langStringMobileJoinPublicConferenceContentOpenCompassButton = useLangString(
		"mobile.join_public_conference_content.open_compass_button"
	);

	const apiJitsiGetConferenceData = useApiJitsiGetConferenceData();
	const apiJitsiJoinConference = useApiJitsiJoinConference();
	const showToast = useShowToast("page_main");

	const [conferenceData, setConferenceData] = useAtom(conferenceDataState);
	const setConferenceDataErrorCode = useSetAtom(conferenceDataErrorCodeState);
	const setLimitNextAttempt = useSetAtom(limitNextAttemptState);

	const [clickCount, setClickCount] = useState(0);

	const onJoinClickHandler = useCallback(async () => {
		try {
			const response = await apiJitsiJoinConference.mutateAsync({
				link: window.location.href,
			});
			window.location.assign(
				`${response.jitsi_conference_link}`
			);
		} catch (error) {
			if (error instanceof ApiError) {
				if (error.error_code === API_JITSI_GET_CONFERENCE_CODE_ERROR_ATTEMPT_JOIN_TO_PRIVATE_CONFERENCE) {
					try {
						apiJitsiGetConferenceData.mutate({ link: window.location.href });
					} catch (errorGetConferenceData) {
						if (errorGetConferenceData instanceof ApiError) {
							setConferenceDataErrorCode(errorGetConferenceData.error_code);
							if (errorGetConferenceData.error_code === API_JITSI_CONFERENCE_CODE_ERROR_LIMIT) {
								setLimitNextAttempt(errorGetConferenceData.next_attempt);
							}
							return;
						}

						if (errorGetConferenceData instanceof LimitError) {
							setConferenceDataErrorCode(API_JITSI_CONFERENCE_CODE_ERROR_LIMIT);
							return;
						}

						setConferenceDataErrorCode(0);
						setLimitNextAttempt(0);
					}
					return;
				}

				if (error.error_code === API_JITSI_CONFERENCE_CODE_ERROR_LIMIT) {
					setLimitNextAttempt(error.next_attempt);
				}

				setConferenceDataErrorCode(error.error_code);
				setConferenceData(null);
				return;
			}

			if (error instanceof NetworkError) {
				showToast(langStringNetworkError, "info");
				return;
			}

			if (error instanceof ServerError) {
				showToast(langStringServerError, "warning");
				return;
			}
		}
	}, [window.location.href]);

	const onOpenCompassClickHandler = useCallback(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(true, getDeeplink(conferenceData.link));
	}, [conferenceData]);

	return (
		<VStack w="100%" mt="98px" pt="32px" pb="24px" rounded="16px" bgColor="255255255.04" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={JoinConferenceIcon84Svg} />
			</Center>
			<Text style="inter_24_34_700" mt="16px" textAlign="center" color="333e49" px="24px">
				{langStringMobileJoinPublicConferenceContentTitle}
			</Text>
			<Text style="inter_18_25_400" mt="8px" textAlign="center" color="333e49" px="20px">
				{langStringMobileJoinPublicConferenceContentDesc}
			</Text>
			<Box w="100%" px="24px">
				<Button
					size="px24py12full"
					textSize="inter_18_27_600"
					mt="24px"
					rounded="12px"
					onClick={() => onJoinClickHandler()}
				>
					{langStringMobileJoinPublicConferenceContentJoinButton}
				</Button>
			</Box>
			<DownloadMenu
				triggerEl={
					<Button
						textSize="inter_16_22_400"
						color="2574a9"
						onClick={() => {
							onOpenCompassClickHandler();
							setClickCount(clickCount + 1);
						}}
					>
						{langStringMobileJoinPublicConferenceContentOpenCompassButton}
					</Button>
				}
				marginTop="20px"
				width={undefined}
				isNeedDeviceAutoDetect={false}
				clickCount={clickCount}
			/>
		</VStack>
	);
};

export default PageContentMobileJoinPublicConference;
