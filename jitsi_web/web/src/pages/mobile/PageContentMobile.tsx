import PageContentMobileJoinPublicConference from "./PageContentMobileJoinPublicConference.tsx";
import { useAtomValue } from "jotai";
import { conferenceDataErrorCodeState, conferenceDataState } from "../../api/_stores.ts";
import {
	API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_ENDED,
	API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_NOT_FOUND, API_JITSI_CONFERENCE_CODE_ERROR_LIMIT,
} from "../../api/_types.ts";
import PageContentMobileConferenceNotFound from "./PageContentMobileConferenceNotFound.tsx";
import PageContentMobileJoinPrivateConference from "./PageContentMobileJoinPrivateConference.tsx";
import PageContentMobileConferenceEnded from "./PageContentMobileConferenceEnded.tsx";
import { useSearchParams } from "react-router-dom";
import PageContentMobileLeaveConference from "./PageContentMobileLeaveConference.tsx";
import PageContentMobileConferenceLimit from "./PageContentMobileConferenceLimit.tsx";
import PageContentMobileUnsupportedBrowser from "./PageContentMobileUnsupportedBrowser.tsx";
import { isUnsupportedMobileBrowser } from "../../lib/functions.ts";
import PageContentMobileKickedFromConference from "./PageContentMobileKickedFromConference.tsx";

const PageContentMobile = () => {
	const conferenceData = useAtomValue(conferenceDataState);
	const conferenceDataErrorCode = useAtomValue(conferenceDataErrorCodeState);

	const [ searchParams ] = useSearchParams();
	const isLeaveConference = Number(searchParams.get("is_leave"));
	const isKickedFromConference = Number(searchParams.get("is_kicked"));

	// если браузер не поддерживается
	if (isUnsupportedMobileBrowser()) {
		return <PageContentMobileUnsupportedBrowser />;
	}

	if (conferenceData === null) {
		if (conferenceDataErrorCode === API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_ENDED) {
			return <PageContentMobileConferenceEnded />;
		}

		if (conferenceDataErrorCode === API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_NOT_FOUND) {
			return <PageContentMobileConferenceNotFound />;
		}

		if (conferenceDataErrorCode === API_JITSI_CONFERENCE_CODE_ERROR_LIMIT) {
			return <PageContentMobileConferenceLimit />;
		}

		return <PageContentMobileConferenceNotFound />;
	}

	if (isLeaveConference === 1) {
		return <PageContentMobileLeaveConference />;
	}

	if (isKickedFromConference === 1) {
		return <PageContentMobileKickedFromConference />;
	}

	if (conferenceData.is_private) {
		return <PageContentMobileJoinPrivateConference />;
	}

	return <PageContentMobileJoinPublicConference />;
};

export default PageContentMobile;
