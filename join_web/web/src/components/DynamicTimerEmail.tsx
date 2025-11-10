import { useLangString } from "../lib/getLangString.ts";
import { useShowToast } from "../lib/Toast.tsx";
import { useNavigateDialog } from "./hooks.ts";
import { useCallback, useEffect, useState } from "react";
import dayjs from "dayjs";
import { useSetAtom } from "jotai";
import { authLdapState, authState } from "../api/_stores.ts";
import { ApiError, NetworkError, ServerError } from "../api/_index.ts";
import { APIAuthInfoType, APIAuthTypeResetPasswordByMail, LIMIT_ERROR_CODE } from "../api/_types.ts";
import { plural } from "../lib/plural.ts";
import { Button } from "./button.tsx";
import { Text } from "./text.tsx";
import { useApiAuthMailResendFullAuthCode } from "../api/auth/mail.ts";
import { useApiSecurityMailResendResetPasswordCode } from "../api/security/mail.ts";
import { useApiFederationLdapMailResendConfirmCode } from "../api/auth/ldap.ts";
import { useAtom } from "jotai/index";

type DynamicTimerEmailProps = {
	endTimeUnix: number;
	setNextResend: (value: number) => void;
	setConfirmCode: (value: string) => void;
	setIsLoading: (value: boolean) => void;
	setIsError: (value: boolean) => void;
	setCompleted: (value: boolean) => void;
	size: "px8py8" | "px0py8";
	textSize: "lato_16_22_400" | "lato_13_18_400";
	isCompleted: boolean;
	authKey?: string;
	authType?: APIAuthInfoType;
	mailConfirmStoryKey?: string;
	isLdapConfirm: boolean,
	activeDialogId: string;
};

export const DynamicTimerEmail = ({
	endTimeUnix,
	setNextResend,
	setConfirmCode,
	setIsLoading,
	setIsError,
	setCompleted,
	size,
	textSize,
	isCompleted,
	authKey,
	authType,
	mailConfirmStoryKey,
	isLdapConfirm,
	activeDialogId,
}: DynamicTimerEmailProps) => {
	const langStringConfirmCodeEmailDialogResendButton = useLangString("confirm_code_email_dialog.resend_button");
	const langStringConfirmCodeEmailDialogResendAfter = useLangString("confirm_code_email_dialog.resend_after");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsConfirmCodeLimitError = useLangString("errors.confirm_code_limit_error");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const langStringErrorsConfirmCodeConfirmIsExpiredError = useLangString("errors.confirm_code_confirm_is_expired_error");
	const langStringErrorsConfirmCode2FaIsDisabledError = useLangString("errors.confirm_code_2fa_is_disabled_error");
	const langStringErrorsConfirmCodeIncorrectCodeError = useLangString("errors.confirm_code_incorrect_code_error");

	const showToast = useShowToast(activeDialogId);
	const { navigateToDialog } = useNavigateDialog();

	const [ timeLeft, setTimeLeft ] = useState(endTimeUnix - dayjs().unix());
	const apiAuthMailResendFullAuthCode = useApiAuthMailResendFullAuthCode();
	const apiSecurityMailResendResetPasswordCode = useApiSecurityMailResendResetPasswordCode();
	const apiFederationLdapMailResendConfirmCode = useApiFederationLdapMailResendConfirmCode();
	const setAuth = useSetAtom(authState);
	const [ authLdap, setAuthLdap ] = useAtom(authLdapState);

	const minutes = Math.floor(timeLeft / 60);
	const seconds = timeLeft % 60;

	useEffect(() => {
		setTimeLeft(endTimeUnix - dayjs().unix());
		const timer = setInterval(() => {
			setTimeLeft((prevTime) => prevTime - 1);
		}, 1000);

		return () => clearInterval(timer);
	}, [ endTimeUnix ]);

	const onRetryClickHandler = useCallback(async () => {

		// очищаем инпут и показываем прелоадер
		setIsLoading(true);
		setIsError(false);
		setCompleted(false);
		setConfirmCode("");
		if (isLdapConfirm) {

			try {
				const response = await apiFederationLdapMailResendConfirmCode.mutateAsync({
					mail_confirm_story_key: mailConfirmStoryKey ?? "",
				});
				setAuthLdap(response.ldap_mail_confirm_story_info);
				setIsLoading(false);
			} catch (error) {
				setIsLoading(false);

				if (error instanceof NetworkError) {
					showToast(langStringErrorsNetworkError, "warning");
					return;
				}

				if (error instanceof ServerError) {
					showToast(langStringErrorsServerError, "warning");
					return;
				}

				if (error instanceof ApiError) {
					switch (error.error_code) {
						case LIMIT_ERROR_CODE:
						case 1708010:
							setNextResend(error.next_attempt ?? 1200);
							showToast(
								langStringErrorsConfirmCodeLimitError.replace(
									"$MINUTES",
									`${Math.ceil(((error.expires_at ?? 1200) - dayjs().unix()) / 60)}${plural(
										Math.ceil(((error.expires_at ?? 1200) - dayjs().unix()) / 60),
										langStringOneMinute,
										langStringTwoMinutes,
										langStringFiveMinutes
									)}`
								),
								"warning"
							);
							break;
						case 1708005:
						case 1708006:
						case 1708008:
						case 1708013:
							navigateToDialog("auth_sso_ldap");
							showToast(langStringErrorsConfirmCodeConfirmIsExpiredError, "warning");
							break;
						case 1708007:
							setIsError(true);
							showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
							setTimeout(() => setIsError(false), 2900);
							break;
						case 1708014:
							setNextResend(authLdap?.scenario_data.next_resend_at ?? 60);
							return;
						case 1708016:
							navigateToDialog("auth_sso_ldap");
							showToast(langStringErrorsConfirmCode2FaIsDisabledError, "warning");
							break;
						default:
							setIsError(true);
							showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
					}

					setAuthLdap(null);
					navigateToDialog("auth_sso_ldap");
				}
			}
		} else {
			try {
				if (authType === APIAuthTypeResetPasswordByMail) {
					const response = await apiSecurityMailResendResetPasswordCode.mutateAsync({
						auth_key: authKey ?? "",
					});
					setAuth(response.auth_info);
				} else {
					const response = await apiAuthMailResendFullAuthCode.mutateAsync({
						auth_key: authKey ?? "",
					});
					setAuth(response.auth_info);
				}
				setIsLoading(false);
			} catch (error) {
				setIsLoading(false);

				if (error instanceof NetworkError) {
					showToast(langStringErrorsNetworkError, "warning");
					return;
				}

				if (error instanceof ServerError) {
					showToast(langStringErrorsServerError, "warning");
					return;
				}

				if (error instanceof ApiError) {
					if (error.error_code === LIMIT_ERROR_CODE) {
						setNextResend(error.next_attempt);
						showToast(
							langStringErrorsConfirmCodeLimitError.replace(
								"$MINUTES",
								`${Math.ceil((error.expires_at - dayjs().unix()) / 60)}${plural(
									Math.ceil((error.expires_at - dayjs().unix()) / 60),
									langStringOneMinute,
									langStringTwoMinutes,
									langStringFiveMinutes
								)}`
							),
							"warning"
						);
						return;
					}

					if (error.error_code === 1708399 || error.error_code === 1708114 || error.error_code === 1708199) {
						setNextResend(error.next_attempt);
						showToast(
							langStringErrorsConfirmCodeLimitError.replace(
								"$MINUTES",
								`${Math.ceil((error.next_attempt - dayjs().unix()) / 60)}${plural(
									Math.ceil((error.next_attempt - dayjs().unix()) / 60),
									langStringOneMinute,
									langStringTwoMinutes,
									langStringFiveMinutes
								)}`
							),
							"warning"
						);
						return;
					}

					setAuth(null);
					navigateToDialog("auth_email_phone_number");
				}
			}
		}
	}, [ apiAuthMailResendFullAuthCode ]);

	// если время истекло, отображаем кнопку повторить
	if (timeLeft <= 0) {
		return (
			<Button
				size = {size}
				textSize = {textSize}
				color = "2574a9"
				disabled = {isCompleted}
				onClick = {() => onRetryClickHandler()}
			>
				{langStringConfirmCodeEmailDialogResendButton}
			</Button>
		);
	}

	return (
		<Text py = "8px" px = {size === "px0py8" ? "0px" : "8px"} color = "b4b4b4" style = {textSize}>
			{langStringConfirmCodeEmailDialogResendAfter}
			{minutes}:{String(seconds).padStart(2, "0")}
		</Text>
	);
};
