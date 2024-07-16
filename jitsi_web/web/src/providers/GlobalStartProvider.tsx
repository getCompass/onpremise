import { PropsWithChildren, useEffect, useState } from "react";
import { useApiJitsiGetConferenceData } from "../api/jitsi.ts";
import { ApiError, LimitError } from "../api/_index.ts";
import { useSetAtom } from "jotai";
import { conferenceDataErrorCodeState, limitNextAttemptState } from "../api/_stores.ts";
import useIsMobile from "../lib/useIsMobile.ts";
import PagePreloaderMobile from "../pages/mobile/PagePreloaderMobile.tsx";
import PagePreloaderDesktop from "../pages/desktop/PagePreloaderDesktop.tsx";
import { API_JITSI_CONFERENCE_CODE_ERROR_LIMIT } from "../api/_types.ts";

export default function GlobalStartProvider({ children }: PropsWithChildren) {
	const isMobile = useIsMobile();

	const apiJitsiGetConferenceData = useApiJitsiGetConferenceData();
	const setConferenceDataErrorCode = useSetAtom(conferenceDataErrorCodeState);
	const setLimitNextAttempt = useSetAtom(limitNextAttemptState);

	// это обязательно нужно, иначе появляется кейс моргания экрана
	// т.к сначала рисуется return <>{children}</>
	// и только после вызывается useEffect с apiJitsiGetConferenceData.mutate внутри
	// чтобы избежать моргания - добавляем одно состояние для прелоадера корректного
	const [isInitialLoadingStarted, setIsInitialLoadingStarted] = useState(false);

	useEffect(() => {
		apiJitsiGetConferenceData.mutate({ link: window.location.href });
		setIsInitialLoadingStarted(true);
	}, []);

	useEffect(() => {
		if (window.location.href.includes("requestMediaPermissions")) {
			return;
		}

		if (apiJitsiGetConferenceData.isError && apiJitsiGetConferenceData.error instanceof ApiError) {
			setConferenceDataErrorCode(apiJitsiGetConferenceData.error.error_code);
			if (apiJitsiGetConferenceData.error.error_code === API_JITSI_CONFERENCE_CODE_ERROR_LIMIT) {
				setLimitNextAttempt(apiJitsiGetConferenceData.error.next_attempt);
			}
			return;
		}

		if (apiJitsiGetConferenceData.isError && apiJitsiGetConferenceData.error instanceof LimitError) {
			setConferenceDataErrorCode(API_JITSI_CONFERENCE_CODE_ERROR_LIMIT);
			return;
		}

		setConferenceDataErrorCode(0);
		setLimitNextAttempt(0);
	}, [apiJitsiGetConferenceData.isLoading, apiJitsiGetConferenceData.error, apiJitsiGetConferenceData.data]);

	if (apiJitsiGetConferenceData.isLoading || !isInitialLoadingStarted) {
		if (isMobile) {
			return <PagePreloaderMobile />;
		}

		return <PagePreloaderDesktop />;
	}

	return <>{children}</>;
}
