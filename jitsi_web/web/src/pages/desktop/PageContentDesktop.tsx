import PageContentDesktopConferenceEnded from "./PageContentDesktopConferenceEnded.tsx";
import { useAtomValue } from "jotai";
import { conferenceDataErrorCodeState, conferenceDataState } from "../../api/_stores.ts";
import {
	API_JITSI_CONFERENCE_CODE_ERROR_LIMIT,
	API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_ENDED,
	API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_NOT_FOUND,
} from "../../api/_types.ts";
import PageContentDesktopConferenceNotFound from "./PageContentDesktopConferenceNotFound.tsx";
import PageContentDesktopJoinPrivateConference from "./PageContentDesktopJoinPrivateConference.tsx";
import PageContentDesktopJoinPublicConference from "./PageContentDesktopJoinPublicConference.tsx";
import { useSearchParams } from "react-router-dom";
import PageContentDesktopLeaveConference from "./PageContentDesktopLeaveConference.tsx";
import PageContentDesktopConferenceLimit from "./PageContentDesktopConferenceLimit.tsx";
import PageContentDesktopUnsupportedBrowser from "./PageContentDesktopUnsupportedBrowser.tsx";
import { isUnsupportedDesktopBrowser } from "../../lib/functions.ts";
import PageContentDesktopKickedFromConference from "./PageContentDesktopKickedFromConference.tsx";

const PageContentDesktop = () => {
	const conferenceData = useAtomValue(conferenceDataState);
	const conferenceDataErrorCode = useAtomValue(conferenceDataErrorCodeState);

	const [ searchParams ] = useSearchParams();
	const isLeaveConference = Number(searchParams.get("is_leave"));
	const isKickedFromConference = Number(searchParams.get("is_kicked"));

	// если браузер не поддерживается
	if (isUnsupportedDesktopBrowser()) {
		return <PageContentDesktopUnsupportedBrowser />;
	}

	if (conferenceData === null) {
		if (conferenceDataErrorCode === API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_ENDED) {
			return <PageContentDesktopConferenceEnded />;
		}

		if (conferenceDataErrorCode === API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_NOT_FOUND) {
			return <PageContentDesktopConferenceNotFound />;
		}

		if (conferenceDataErrorCode === API_JITSI_CONFERENCE_CODE_ERROR_LIMIT) {
			return <PageContentDesktopConferenceLimit />;
		}

		return <PageContentDesktopConferenceNotFound />;
	}

	if (isLeaveConference === 1) {
		return <PageContentDesktopLeaveConference />;
	}

	if (isKickedFromConference === 1) {
		return <PageContentDesktopKickedFromConference />;
	}

	if (conferenceData.is_private) {
		return <PageContentDesktopJoinPrivateConference />;
	}

	return <PageContentDesktopJoinPublicConference />;
};

export default PageContentDesktop;
