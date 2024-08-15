import { useLangString } from "../lib/getLangString.ts";
import { useShowToast } from "../lib/Toast.tsx";
import { useNavigateDialog } from "./hooks.ts";
import { useCallback, useEffect, useState } from "react";
import dayjs from "dayjs";
import { useSetAtom } from "jotai";
import { authState, captchaProviderState } from "../api/_stores.ts";
import { ApiError, NetworkError, ServerError } from "../api/_index.ts";
import { LIMIT_ERROR_CODE } from "../api/_types.ts";
import { plural } from "../lib/plural.ts";
import { Button } from "./button.tsx";
import { Text } from "./text.tsx";
import { useApiAuthPhoneNumberRetry } from "../api/auth/phonenumber.ts";
import { doCaptchaReset } from "../lib/functions.ts";
import { useAtomValue } from "jotai/index";

type ShowGrecaptchaState = null | "need_render" | "rendered";

type DynamicTimerPhoneNumberProps = {
	endTimeUnix: number;
	setNextResend: (value: number) => void;
	setInputValues: (value: string[]) => void;
	setIsLoading: (value: boolean) => void;
	setIsError: (value: boolean) => void;
	setCompleted: (value: boolean) => void;
	size: "px8py8" | "px0py8";
	textSize: "lato_16_22_400" | "lato_13_18_400";
	isCompleted: boolean;
	authKey: string;
	activeDialogId: string;
	showCaptchaState: ShowGrecaptchaState;
	setShowCaptchaState: (value: ShowGrecaptchaState) => void;
	grecaptchaResponse: string;
	setGrecaptchaResponse: (value: string) => void;
	captchaWidgetId: string;
};

export const DynamicTimerPhoneNumber = ({
	endTimeUnix,
	setNextResend,
	setInputValues,
	setIsLoading,
	setIsError,
	setCompleted,
	size,
	textSize,
	isCompleted,
	authKey,
	activeDialogId,
	showCaptchaState,
	setShowCaptchaState,
	grecaptchaResponse,
	setGrecaptchaResponse,
	captchaWidgetId,
}: DynamicTimerPhoneNumberProps) => {
	const langStringConfirmCodePhoneNumberDialogResendButton = useLangString(
		"confirm_code_phone_number_dialog.resend_button"
	);
	const langStringConfirmCodePhoneNumberDialogResendAfter = useLangString(
		"confirm_code_phone_number_dialog.resend_after"
	);
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsConfirmCodeLimitError = useLangString("errors.confirm_code_limit_error");
	const langStringErrorsIncorrectCaptcha = useLangString("errors.incorrect_captcha");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const showToast = useShowToast(activeDialogId);
	const { navigateToDialog } = useNavigateDialog();

	const [timeLeft, setTimeLeft] = useState(endTimeUnix - dayjs().unix());
	const apiAuthPhoneNumberRetry = useApiAuthPhoneNumberRetry();
	const setAuth = useSetAtom(authState);

	const minutes = Math.floor(timeLeft / 60);
	const seconds = timeLeft % 60;

	const captchaProvider = useAtomValue(captchaProviderState);

	useEffect(() => {
		setTimeLeft(endTimeUnix - dayjs().unix());
		const timer = setInterval(() => {
			setTimeLeft((prevTime) => prevTime - 1);
		}, 1000);

		return () => clearInterval(timer);
	}, [endTimeUnix]);

	const onRetryClickHandler = useCallback(async () => {
		try {
			// очищаем инпут и показываем прелоадер
			setIsLoading(true);
			setIsError(false);
			setCompleted(false);
			setInputValues(Array(6).fill(""));

			const response = await apiAuthPhoneNumberRetry.mutateAsync({
				auth_key: authKey,
				grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
			});
			setGrecaptchaResponse(""); // сбрасываем
			setAuth(response);
			setIsLoading(false);
		} catch (error) {
			setIsLoading(false);
			setGrecaptchaResponse(""); // сбрасываем
			// @ts-ignore
			if (showCaptchaState === "rendered" && grecaptcha.enterprise.reset !== undefined) {
				// @ts-ignore
				grecaptcha.enterprise.reset();
			}

			if (error instanceof NetworkError) {
				showToast(langStringErrorsNetworkError, "warning");
				return;
			}

			if (error instanceof ServerError) {
				showToast(langStringErrorsServerError, "warning");
				return;
			}

			if (error instanceof ApiError) {
				if (error.error_code === 1708200) {
					if (showCaptchaState === null) {
						setShowCaptchaState("need_render");
					}
					return;
				}

				if (error.error_code === 1708201) {
					showToast(langStringErrorsIncorrectCaptcha, "warning");
					if (showCaptchaState === "rendered") {
						doCaptchaReset(captchaProvider, captchaWidgetId);
					}
					return;
				}

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
	}, [apiAuthPhoneNumberRetry]);

	// если время истекло, отображаем кнопку повторить
	if (timeLeft <= 0) {
		return (
			<Button
				size={size}
				textSize={textSize}
				color="2574a9"
				disabled={isCompleted}
				onClick={() => onRetryClickHandler()}
			>
				{langStringConfirmCodePhoneNumberDialogResendButton}
			</Button>
		);
	}

	return (
		<Text py="8px" px={size === "px0py8" ? "0px" : "8px"} color="b4b4b4" style={textSize}>
			{langStringConfirmCodePhoneNumberDialogResendAfter}
			{minutes}:{String(seconds).padStart(2, "0")}
		</Text>
	);
};
