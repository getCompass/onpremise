import {PropsWithChildren, useEffect, useMemo, useState} from "react";
import {useAtomValue, useSetAtom} from "jotai";
import {
	activeDialogIdState,
	authInputState,
	authSsoState,
	authState,
	isLoadedState,
	isNeedShowCreateProfileDialogAfterLdapRegistrationState,
	isNeedShowCreateProfileDialogAfterSsoRegistrationState,
	joinLinkState,
	loadingState,
	prepareJoinLinkErrorState,
	profileState,
} from "../api/_stores.ts";
import {useApiGlobalDoStart} from "../api/global.ts";
import {useNavigateDialog, useNavigatePage} from "../components/hooks.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import {useApiJoinLinkPrepare} from "../api/joinlink.ts";
import {ApiError, NetworkError, ServerError} from "../api/_index.ts";
import {
	ALREADY_MEMBER_ERROR_CODE,
	APIAuthInfoDataTypeRegisterLoginByPhoneNumber,
	APIAuthInfoDataTypeRegisterLoginResetPasswordByMail,
	APIAuthTypeLoginByMail,
	APIAuthTypeLoginByPhoneNumber,
	APIAuthTypeRegisterByMail,
	APIAuthTypeRegisterByPhoneNumber,
	APIAuthTypeResetPasswordByMail,
	AUTH_MAIL_STAGE_ENTERING_CODE,
	AUTH_MAIL_STAGE_ENTERING_PASSWORD,
	LIMIT_ERROR_CODE,
	PrepareJoinLinkErrorAlreadyMemberData,
	PrepareJoinLinkErrorLimitData,
} from "../api/_types.ts";
import dayjs from "dayjs";
import {useAtom} from "jotai/index";
import {useApiFederationSsoAuthGetStatus, useApiPivotAuthSsoBegin} from "../api/auth/sso.ts";
import {useShowToast} from "../lib/Toast.tsx";
import {useLangString} from "../lib/getLangString.ts";
import {plural} from "../lib/plural.ts";

export default function GlobalStartProvider({children}: PropsWithChildren) {
	const apiGlobalDoStart = useApiGlobalDoStart();

	const { navigateToPage } = useNavigatePage();
	const { activeDialog, navigateToDialog } = useNavigateDialog();
	const setLoading = useSetAtom(loadingState);
	const setJoinLink = useSetAtom(joinLinkState);
	const [isLoaded, setIsLoaded] = useAtom(isLoadedState);
	const authInput = useAtomValue(authInputState);
	const auth = useAtomValue(authState);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);
	const {is_authorized, need_fill_profile} = useAtomValue(profileState);
	const isNeedShowCreateProfileDialogAfterSsoRegistration = useAtomValue(
		isNeedShowCreateProfileDialogAfterSsoRegistrationState
	);
	const isNeedShowCreateProfileDialogAfterLdapRegistration = useAtomValue(
		isNeedShowCreateProfileDialogAfterLdapRegistrationState
	);
	const authSso = useAtomValue(authSsoState);
	const setAuthSso = useSetAtom(authSsoState);
	const apiFederationSsoAuthGetStatus = useApiFederationSsoAuthGetStatus();
	const apiPivotAuthSsoBegin = useApiPivotAuthSsoBegin();

	const isJoinLink = useIsJoinLink();
	const rawJoinLink = useMemo(() => (isJoinLink ? window.location.href : ""), [window.location.href]);
	const apiJoinLinkPrepare = useApiJoinLinkPrepare(rawJoinLink);
	const [toastText, setToastText] = useState("");
	const [toastStatus, setToastStatus] = useState("");

	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsSsoError = useLangString("errors.sso_error");
	const langStringErrorsAuthSsoMethodDisabled = useLangString("errors.auth_method_disabled");
	const langStringErrorsSsoLimitError = useLangString("errors.sso_limit_error");
	const langStringErrorsSsoRegistrationWithoutInvite = useLangString("errors.sso_registration_without_invite");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const [prevIsAuthorized, setPrevIsAuthorized] = useState<boolean | null>(null);

	useEffect(() => {
		// обновляем prevActivePage перед изменением activePage
		const handlePageChange = () => {
			setPrevIsAuthorized(is_authorized);
		};

		// вызываем handlePageChange при изменении activePage
		handlePageChange();
	}, [is_authorized]);

	const authInputValue = useMemo(() => {
		const [authValue, expiresAt] = authInput.split("__|__") || ["", 0];

		if (parseInt(expiresAt) < dayjs().unix()) {
			return "";
		}

		return authValue;
	}, [authInput]);

	/**
	 * Продолжаем попытку аутентификации через SSO, если она имеется
	 */
	async function continueAuthSsoAttemptIfExists() {
		// проверяем наличие активной sso аутентификации
		if (authSso === undefined || authSso === null || authSso.state == "none") {
			return;
		}

		if (apiFederationSsoAuthGetStatus.isLoading || apiPivotAuthSsoBegin.isLoading) {
			return;
		}

		try {
			const responseGetStatus = await apiFederationSsoAuthGetStatus.mutateAsync({
				sso_auth_token: authSso.data.sso_auth_token,
				signature: authSso.data.signature,
			});

			// если статус, что попытка в процессе, то ничего не делаем
			if (responseGetStatus.status === "wait") {
				return;
			}

			// если статус, что попытка протухла или уже завершена, то почистим кэш и дальше ничего не продолжаем
			if (responseGetStatus.status === "expired" || responseGetStatus.status === "completed") {
				setAuthSso(null);
				return;
			}
		} catch (error) {
			// сафари дурак и при переходе по ссылке не дожидаясь выполнения getStatus стопает его, чтобы не уничтожать инфу о попытке - выходим здесь
			if (error instanceof NetworkError) {
				return;
			}

			// уничтожаем информацию о попытке
			setAuthSso(null);

			if (error instanceof ServerError) {
				setToastText(langStringErrorsSsoError);
				setToastStatus("warning");
				return;
			}

			if (error instanceof ApiError) {
				if (error.error_code === 1708118) {
					setToastText(langStringErrorsAuthSsoMethodDisabled);
					setToastStatus("warning");
					return;
				}
				if (error.error_code === 1000) {
					setToastText(langStringErrorsSsoRegistrationWithoutInvite);
					setToastStatus("warning");
					return;
				}

				if (error.error_code === LIMIT_ERROR_CODE) {
					setToastText(
						langStringErrorsSsoLimitError.replace(
							"$MINUTES",
							`${Math.ceil((error.expires_at - dayjs().unix()) / 60)}${plural(
								Math.ceil((error.expires_at - dayjs().unix()) / 60),
								langStringOneMinute,
								langStringTwoMinutes,
								langStringFiveMinutes
							)}`
						)
					);
					setToastStatus("warning");
					return;
				}
			}
		}

		try {
			await apiPivotAuthSsoBegin.mutateAsync({
				sso_auth_token: authSso.data.sso_auth_token,
				signature: authSso.data.signature,
				join_link:
					prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
						? window.location.href
						: undefined,
			});
		} catch (error) {
			// сафари дурак и при переходе по ссылке не дожидаясь выполнения getStatus стопает его, чтобы не уничтожать инфу о попытке - выходим здесь
			if (error instanceof NetworkError) {
				return;
			}

			// уничтожаем информацию о попытке
			setAuthSso(null);

			if (error instanceof ServerError) {
				setToastText(langStringErrorsServerError);
				setToastStatus("warning");
				return;
			}

			if (error instanceof ApiError) {
				if (error.error_code === 1708118) {
					setToastText(langStringErrorsAuthSsoMethodDisabled);
					setToastStatus("warning");
					return;
				}
				if (error.error_code === 1000) {
					setToastText(langStringErrorsSsoRegistrationWithoutInvite);
					setToastStatus("warning");
					return;
				}

				if (error.error_code === LIMIT_ERROR_CODE) {
					setToastText(
						langStringErrorsSsoLimitError.replace(
							"$MINUTES",
							`${Math.ceil((error.expires_at - dayjs().unix()) / 60)}${plural(
								Math.ceil((error.expires_at - dayjs().unix()) / 60),
								langStringOneMinute,
								langStringTwoMinutes,
								langStringFiveMinutes
							)}`
						)
					);
					setToastStatus("warning");
					return;
				}
			}
		}
	}

	useEffect(() => {
		if (isLoaded) {
			return;
		}

		const isLoading =
			apiGlobalDoStart.isFetching ||
			!apiGlobalDoStart.data ||
			is_authorized === null ||
			(isJoinLink && apiJoinLinkPrepare.isLoading) ||
			(authSso !== undefined &&
				authSso !== null &&
				authSso.state !== "none" &&
				(apiFederationSsoAuthGetStatus.isLoading || apiPivotAuthSsoBegin.isLoading));
		if (!isLoading) {
			setTimeout(() => {
				setLoading(isLoading);
				setIsLoaded(true);
			}, 500); // всегда минимум 500ms показываем экран загрузки, даже если быстро загрузились
		} else {
			setLoading(isLoading);
		}
	}, [
		apiGlobalDoStart.isLoading,
		apiGlobalDoStart.data,
		apiJoinLinkPrepare.isLoading,
		is_authorized,
		authSso,
		apiFederationSsoAuthGetStatus.isLoading,
		apiPivotAuthSsoBegin.isLoading,
		isLoaded,
	]);

	useEffect(() => {
		if (toastText === "" || toastStatus === "" || activeDialogId === "") {
			return;
		}

		showToast(toastText, toastStatus);
		setToastText("");
		setToastStatus("");
	}, [toastText, toastStatus, activeDialogId]);

	useEffect(() => {
		continueAuthSsoAttemptIfExists();
	}, [authSso]);

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
								is_waiting_for_postmoderation: apiJoinLinkPrepare.error.is_waiting_for_postmoderation,
								role: apiJoinLinkPrepare.error.role,
								was_member_before: apiJoinLinkPrepare.error.was_member_before,
								join_link_uniq: apiJoinLinkPrepare.error.join_link_uniq
							} as PrepareJoinLinkErrorAlreadyMemberData,
						});
						break;

					case LIMIT_ERROR_CODE:
						setPrepareJoinLinkError({
							error_code: apiJoinLinkPrepare.error.error_code,
							data: {
								expires_at: apiJoinLinkPrepare.error.expires_at,
							} as PrepareJoinLinkErrorLimitData,
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

		if (is_authorized) {
			if (
				need_fill_profile ||
				isNeedShowCreateProfileDialogAfterSsoRegistration ||
				isNeedShowCreateProfileDialogAfterLdapRegistration
			) {
				navigateToPage("auth");
				navigateToDialog("auth_create_profile");
			} else {
				navigateToPage("token");
				navigateToDialog("token_page");
			}
		} else {
			if (
				auth !== null &&
				(auth.type === APIAuthTypeRegisterByPhoneNumber || auth.type === APIAuthTypeLoginByPhoneNumber) &&
				(auth.data as APIAuthInfoDataTypeRegisterLoginByPhoneNumber).expire_at > dayjs().unix()
			) {
				if (isLoaded) {
					return;
				}

				navigateToPage("auth");
				navigateToDialog("auth_phone_number_confirm_code");
			} else if (
				auth !== null &&
				(auth.type === APIAuthTypeRegisterByMail ||
					auth.type === APIAuthTypeLoginByMail ||
					auth.type === APIAuthTypeResetPasswordByMail) &&
				(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).expire_at > dayjs().unix()
			) {
				if (isLoaded) {
					return;
				}

				if (auth.type === APIAuthTypeRegisterByMail) {
					if (
						(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).stage ===
						AUTH_MAIL_STAGE_ENTERING_PASSWORD
					) {
						navigateToPage("auth");
						navigateToDialog("auth_email_register");
					} else if (
						(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).stage ===
						AUTH_MAIL_STAGE_ENTERING_CODE
					) {
						navigateToPage("auth");
						navigateToDialog("auth_email_confirm_code");
					}
				} else if (auth.type === APIAuthTypeLoginByMail) {
					if (
						(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).stage ===
						AUTH_MAIL_STAGE_ENTERING_PASSWORD
					) {
						navigateToPage("auth");
						navigateToDialog("auth_email_login");
					} else if (
						(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).stage ===
						AUTH_MAIL_STAGE_ENTERING_CODE
					) {
						navigateToPage("auth");
						navigateToDialog("auth_email_confirm_code");
					}
				} else if (auth.type === APIAuthTypeResetPasswordByMail) {
					if (
						(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).stage ===
						AUTH_MAIL_STAGE_ENTERING_PASSWORD
					) {
						navigateToPage("auth");
						navigateToDialog("auth_create_new_password");
					} else if (
						(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).stage ===
						AUTH_MAIL_STAGE_ENTERING_CODE
					) {
						navigateToPage("auth");
						navigateToDialog("auth_email_confirm_code");
					}
				}
			} else {
				if (authInputValue.length > 0) {
					// чтобы не выкидывало при перезапросе start на ввод номера/почты
					if (activeDialog !== "auth_sso_ldap") {
						navigateToPage("auth");
						navigateToDialog("auth_email_phone_number");
					}
				} else {
					// чтобы не выкидывало при перезапросе start на ввод номера/почты
					if (!isJoinLink && activeDialog !== "auth_sso_ldap") {
						navigateToPage("auth");
						navigateToDialog("auth_email_phone_number");
					}
				}
			}
		}
	}, [
		apiGlobalDoStart.data,
		apiJoinLinkPrepare.isLoading,
		apiJoinLinkPrepare.error,
		apiJoinLinkPrepare.data,
		is_authorized,
		isLoaded,
		isNeedShowCreateProfileDialogAfterSsoRegistration,
		isNeedShowCreateProfileDialogAfterLdapRegistration,
	]);

	// разлогиниваем если пришло с бека
	useEffect(() => {
		if (prevIsAuthorized !== null && prevIsAuthorized !== is_authorized && is_authorized === false) {
			if (isJoinLink) {
				navigateToPage("welcome");
				navigateToDialog("auth_email_phone_number");
			} else {
				navigateToPage("auth");
				navigateToDialog("auth_email_phone_number");
			}
		}
	}, [prevIsAuthorized, is_authorized]);

	return <>{children}</>;
}
