import {PropsWithChildren, useEffect, useMemo} from "react";
import {useAtomValue, useSetAtom} from "jotai";
import {
	authInputState, authState,
	joinLinkState,
	loadingState,
	prepareJoinLinkErrorState,
	profileState
} from "../api/_stores.ts";
import {useApiGlobalDoStart} from "../api/global.ts";
import {useNavigateDialog, useNavigatePage} from "../components/hooks.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import {useApiJoinLinkPrepare} from "../api/joinlink.ts";
import {ApiError} from "../api/_index.ts";
import {
	ALREADY_MEMBER_ERROR_CODE, LIMIT_ERROR_CODE,
	PrepareJoinLinkErrorAlreadyMemberData, PrepareJoinLinkErrorLimitData,
} from "../api/_types.ts";
import dayjs from "dayjs";

export default function GlobalStartProvider({children}: PropsWithChildren) {

	const apiGlobalDoStart = useApiGlobalDoStart();

	const {navigateToPage} = useNavigatePage();
	const {navigateToDialog} = useNavigateDialog();
	const setLoading = useSetAtom(loadingState);
	const setJoinLink = useSetAtom(joinLinkState);
	const authInput = useAtomValue(authInputState);
	const auth = useAtomValue(authState);
	const setPrepareJoinLinkError = useSetAtom(prepareJoinLinkErrorState);
	const {is_authorized, need_fill_profile} = useAtomValue(profileState);

	const isJoinLink = useIsJoinLink();
	const rawJoinLink = useMemo(() => isJoinLink ? window.location.href : "", [window.location.href]);
	const apiJoinLinkPrepare = useApiJoinLinkPrepare(rawJoinLink);

	const authInputValue = useMemo(() => {

		const [authValue, expiresAt] = authInput.split("__|__") || ["", 0]

		if (parseInt(expiresAt) < dayjs().unix()) {
			return "";
		}

		return authValue;
	}, [authInput]);

	useEffect(() => {

		if (isJoinLink) {

			if (apiJoinLinkPrepare.data?.validation_result !== undefined) {
				setJoinLink(apiJoinLinkPrepare.data.validation_result);
			} else {
				setJoinLink(null);
			}

			if (apiJoinLinkPrepare.isError && apiJoinLinkPrepare.error instanceof ApiError) {

				switch (apiJoinLinkPrepare.error.error_code) {

					case ALREADY_MEMBER_ERROR_CODE:

						setPrepareJoinLinkError({
							error_code: apiJoinLinkPrepare.error.error_code,
							data: {
								company_id: apiJoinLinkPrepare.error.company_id,
								inviter_user_id: apiJoinLinkPrepare.error.inviter_user_id,
								inviter_full_name: apiJoinLinkPrepare.error.inviter_full_name,
								is_postmoderation: apiJoinLinkPrepare.error.is_postmoderation,
								role: apiJoinLinkPrepare.error.role,
								was_member_before: apiJoinLinkPrepare.error.was_member_before,
							} as PrepareJoinLinkErrorAlreadyMemberData
						});
						break;

					case LIMIT_ERROR_CODE:

						setPrepareJoinLinkError({
							error_code: apiJoinLinkPrepare.error.error_code,
							data: {
								expires_at: apiJoinLinkPrepare.error.expires_at,
							} as PrepareJoinLinkErrorLimitData
						});
						break;

					default:

						setPrepareJoinLinkError({error_code: apiJoinLinkPrepare.error.error_code});
						break;
				}
			} else {
				setPrepareJoinLinkError(null);
			}
		}

		const isLoading = !apiGlobalDoStart.data || is_authorized === null || (isJoinLink && apiJoinLinkPrepare.isLoading);
		if (!isLoading) {
			setTimeout(() => setLoading(isLoading), 500); // всегда минимум 500ms показываем экран загрузки, даже если быстро загрузились
		} else {
			setLoading(isLoading)
		}

		if (is_authorized) {

			if (need_fill_profile) {

				navigateToPage("auth");
				navigateToDialog("auth_create_profile")
			} else {
				navigateToPage("token");
			}
		} else {

			if (auth !== null && auth.expire_at > dayjs().unix()) {

				navigateToPage("auth");
				navigateToDialog("auth_confirm_code");
			} else {

				if (authInputValue.length > 0) {

					navigateToPage("auth");
					navigateToDialog("auth_phone_number");
				} else {

					if (!isJoinLink) {

						navigateToPage("auth");
						navigateToDialog("auth_phone_number");
					}
				}
			}
		}
	}, [apiGlobalDoStart, apiJoinLinkPrepare, is_authorized]);

	return <>{children}</>;
}
