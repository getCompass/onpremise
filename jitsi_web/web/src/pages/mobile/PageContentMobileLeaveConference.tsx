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
import { ApiError, LimitError, NetworkError, ServerError } from "../../api/_index.ts";
import {
	API_JITSI_GET_CONFERENCE_CODE_ERROR_ATTEMPT_JOIN_TO_PRIVATE_CONFERENCE,
	API_JITSI_CONFERENCE_CODE_ERROR_LIMIT,
} from "../../api/_types.ts";
import { useApiJitsiGetConferenceData, useApiJitsiJoinConference } from "../../api/jitsi.ts";
import { useSetAtom } from "jotai";
import { conferenceDataErrorCodeState, conferenceDataState, limitNextAttemptState } from "../../api/_stores.ts";
import { useSearchParams } from "react-router-dom";
import { useShowToast } from "../../components/Toast.tsx";

const PageContentMobileLeaveConference = () => {
	const langStringNetworkError = useLangString("network_error");
	const langStringServerError = useLangString("server_error");
	const langStringMobileConferenceEndedContentTitle = useLangString("mobile.leave_conference_content.title");
	const langStringMobileConferenceEndedContentDesc = useLangString("mobile.leave_conference_content.desc");
	const langStringMobileConferenceEndedContentTryCompassButton = useLangString(
		"mobile.leave_conference_content.try_compass_button"
	);
	const langStringMobileConferenceEndedContentJoinToConferenceAgainButton = useLangString(
		"mobile.leave_conference_content.join_to_conference_again_button"
	);

	const apiJitsiGetConferenceData = useApiJitsiGetConferenceData();
	const apiJitsiJoinConference = useApiJitsiJoinConference();
	const showToast = useShowToast("page_main");

	const setConferenceData = useSetAtom(conferenceDataState);
	const setConferenceDataErrorCode = useSetAtom(conferenceDataErrorCodeState);
	const setLimitNextAttempt = useSetAtom(limitNextAttemptState);

	const [searchParams, setSearchParams] = useSearchParams();

	const [clickCount, setClickCount] = useState(0);

	const onTryCompassClickHandler = useCallback(() => {
		openDeepLink(true, getDeeplinkUrlScheme());
	}, []);

	const onJoinAgainButtonClickHandler = useCallback(async () => {
		try {
			const response = await apiJitsiJoinConference.mutateAsync({
				link: window.location.href,
			});
			window.location.assign(response.jitsi_conference_link);
		} catch (error) {
			if (error instanceof ApiError) {
				if (error.error_code === API_JITSI_GET_CONFERENCE_CODE_ERROR_ATTEMPT_JOIN_TO_PRIVATE_CONFERENCE) {
					try {
						apiJitsiGetConferenceData.mutate({ link: window.location.href });
						searchParams.delete("is_leave");
						setSearchParams(searchParams);
					} catch (errorGetConferenceData) {
						searchParams.delete("is_leave");
						setSearchParams(searchParams);

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
				searchParams.delete("is_leave");
				setSearchParams(searchParams);
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
	}, [window.location.href, searchParams]);

	return (
		<VStack w="100%" mt="98px" pt="32px" pb="24px" px="24px" rounded="16px" bgColor="255255255.04" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={ConferenceEnded84Svg} />
			</Center>
			<Text style="inter_24_34_700" mt="16px" textAlign="center" color="333e49">
				{langStringMobileConferenceEndedContentTitle}
			</Text>
			<Text style="inter_18_25_400" mt="8px" textAlign="center" color="333e49">
				{langStringMobileConferenceEndedContentDesc}
			</Text>
			<DownloadMenu
				marginTop="24px"
				width="100%"
				triggerEl={
					<Button
						size="px24py12full"
						textSize="inter_18_27_600"
						rounded="12px"
						onClick={() => {
							onTryCompassClickHandler();
							setClickCount(clickCount + 1);
						}}
					>
						{langStringMobileConferenceEndedContentTryCompassButton}
					</Button>
				}
				isNeedDeviceAutoDetect={false}
				clickCount={clickCount}
			/>
			<Button textSize="inter_16_22_400" mt="20px" color="2574a9" onClick={onJoinAgainButtonClickHandler}>
				{langStringMobileConferenceEndedContentJoinToConferenceAgainButton}
			</Button>
		</VStack>
	);
};

export default PageContentMobileLeaveConference;
