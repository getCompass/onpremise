import JoinConferenceIcon84Svg from "../../img/desktop/JoinConferenceIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import DownloadMenu from "../../components/desktop/DownloadMenu.tsx";
import { useApiJitsiGetConferenceData, useApiJitsiJoinConference } from "../../api/jitsi.ts";
import { useCallback, useEffect, useState } from "react";
import { conferenceDataErrorCodeState, conferenceDataState, limitNextAttemptState } from "../../api/_stores.ts";
import { useAtom, useSetAtom } from "jotai";
import {ApiError, LimitError, NetworkError, ServerError} from "../../api/_index.ts";
import {
	API_JITSI_GET_CONFERENCE_CODE_ERROR_ATTEMPT_JOIN_TO_PRIVATE_CONFERENCE,
	API_JITSI_CONFERENCE_CODE_ERROR_LIMIT,
} from "../../api/_types.ts";
import { getDeeplink, openDeepLink } from "../../lib/functions.ts";
import {useShowToast} from "../../components/Toast.tsx";

const PageContentDesktopJoinPublicConference = () => {
	const langStringNetworkError = useLangString("network_error");
	const langStringServerError = useLangString("server_error");
	const langStringDesktopJoinPublicConferenceContentTitle = useLangString(
		"desktop.join_public_conference_content.title"
	);
	const langStringDesktopJoinPublicConferenceContentDesc = useLangString(
		"desktop.join_public_conference_content.desc"
	);
	const langStringDesktopJoinPublicConferenceContentJoinButton = useLangString(
		"desktop.join_public_conference_content.join_button"
	);
	const langStringDesktopJoinPublicConferenceContentOpenCompassButton = useLangString(
		"desktop.join_public_conference_content.open_compass_button"
	);

	const apiJitsiGetConferenceData = useApiJitsiGetConferenceData();
	const apiJitsiJoinConference = useApiJitsiJoinConference();
	const showToast = useShowToast("page_main");

	const [conferenceData, setConferenceData] = useAtom(conferenceDataState);
	const setConferenceDataErrorCode = useSetAtom(conferenceDataErrorCodeState);
	const setLimitNextAttempt = useSetAtom(limitNextAttemptState);

	const [clickCount, setClickCount] = useState(0);

	// если у пользователя установлено приложение Compass – поверх страницы сразу появляется системное браузерное окно с предложением открыть ссылку в приложении.
	useEffect(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(false, getDeeplink(conferenceData.link));
	}, []);

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

		openDeepLink(false, getDeeplink(conferenceData.link));
	}, [conferenceData?.link]);

	return (
		<VStack minW="942px" mt="122px" px="32px" py="32px" rounded="20px" bgColor="255255255.03" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={JoinConferenceIcon84Svg} />
			</Center>
			<Text style="inter_40_48_700" letterSpacing="-0.3px" mt="12px" textAlign="center">
				{langStringDesktopJoinPublicConferenceContentTitle}
			</Text>
			<Text style="inter_20_28_400" mt="16px" textAlign="center">
				{langStringDesktopJoinPublicConferenceContentDesc.split("\n").map((line, index) => (
					<div key={index}>{line}</div>
				))}
			</Text>
			<Button
				minW="370px"
				size="px32py16"
				textSize="inter_20_24_500"
				mt="32px"
				rounded="12px"
				onClick={() => onJoinClickHandler()}
			>
				{langStringDesktopJoinPublicConferenceContentJoinButton}
			</Button>
			<DownloadMenu
				size="default"
				triggerEl={
					<Button
						textSize="inter_16_22_400"
						mt="20px"
						color="2574a9"
						onClick={() => {
							onOpenCompassClickHandler();
							setClickCount(clickCount + 1);
						}}
					>
						{langStringDesktopJoinPublicConferenceContentOpenCompassButton}
					</Button>
				}
				clickCount={clickCount}
			/>
		</VStack>
	);
};

export default PageContentDesktopJoinPublicConference;
