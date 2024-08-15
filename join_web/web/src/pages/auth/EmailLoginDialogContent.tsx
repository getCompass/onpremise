import { Box, styled, VStack } from "../../../styled-system/jsx";
import {
	activeDialogIdState,
	authInputState,
	authState,
	captchaProviderState,
	captchaPublicKeyState,
	confirmCodeState,
	isLoginCaptchaRenderedState,
	joinLinkState,
	needShowForgotPasswordButtonState,
	passwordInputState,
	prepareJoinLinkErrorState,
} from "../../api/_stores.ts";
import { useAtomValue, useSetAtom } from "jotai";
import useIsMobile from "../../lib/useIsMobile.ts";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { KeyIcon80 } from "../../components/KeyIcon80.tsx";
import PasswordInput from "../../components/PasswordInput.tsx";
import { Button } from "../../components/button.tsx";
import { useNavigateDialog } from "../../components/hooks.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import { plural } from "../../lib/plural.ts";
import dayjs from "dayjs";
import {
	ALREADY_MEMBER_ERROR_CODE,
	APIAuthInfoDataTypeRegisterLoginResetPasswordByMail,
	AUTH_MAIL_SCENARIO_SHORT,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	LIMIT_ERROR_CODE,
} from "../../api/_types.ts";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import {
	useApiAuthMailCancel,
	useApiAuthMailConfirmFullAuthPassword,
	useApiAuthMailConfirmShortAuthPassword,
} from "../../api/auth/mail.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import { useAtom } from "jotai/index";
import { useApiSecurityMailTryResetPassword } from "../../api/security/mail.ts";
import { doCaptchaReady, doCaptchaRender, doCaptchaReset } from "../../lib/functions.ts";

type EmailLoginDialogContentProps = {
	showCaptchaState: ShowGrecaptchaState;
	setShowCaptchaState: (value: ShowGrecaptchaState) => void;
	grecaptchaResponse: string;
	setGrecaptchaResponse: (value: string) => void;
};

const EmailLoginDialogContentDesktop = ({
	showCaptchaState,
	setShowCaptchaState,
	grecaptchaResponse,
	setGrecaptchaResponse,
}: EmailLoginDialogContentProps) => {
	const langStringErrorsConfirmCodeIncorrectCodeOneLeft = useLangString(
		"errors.confirm_code_incorrect_code_one_left"
	);
	const langStringErrorsConfirmCodeIncorrectCodeTwoLefts = useLangString(
		"errors.confirm_code_incorrect_code_two_lefts"
	);
	const langStringErrorsConfirmCodeIncorrectCodeFiveLefts = useLangString(
		"errors.confirm_code_incorrect_code_five_lefts"
	);
	const langStringErrorsConfirmCodeIncorrectCodeOneAttempt = useLangString(
		"errors.confirm_code_incorrect_code_one_attempt"
	);
	const langStringErrorsConfirmCodeIncorrectCodeTwoAttempts = useLangString(
		"errors.confirm_code_incorrect_code_two_attempts"
	);
	const langStringErrorsConfirmCodeIncorrectCodeFiveAttempts = useLangString(
		"errors.confirm_code_incorrect_code_five_attempts"
	);
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const langStringEmailLoginDialogTitle = useLangString("email_login_dialog.title");
	const langStringEmailLoginDialogDesc = useLangString("email_login_dialog.desc");
	const langStringEmailLoginDialogPasswordInputPlaceholder = useLangString(
		"email_login_dialog.password_input_placeholder"
	);
	const langStringEmailLoginDialogLoginButton = useLangString("email_login_dialog.login_button");
	const langStringEmailLoginDialogAuthBlocked = useLangString("email_login_dialog.auth_blocked");
	const langStringErrorsAuthIncorrectPasswordError = useLangString("errors.auth_incorrect_password_error");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsIncorrectCaptcha = useLangString("errors.incorrect_captcha");
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const activeDialogId = useAtomValue(activeDialogIdState);
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);
	const captchaProvider = useAtomValue(captchaProviderState);
	const setNeedShowForgotPasswordButton = useSetAtom(needShowForgotPasswordButtonState);
	const auth = useAtomValue(authState);
	const [password, setPassword] = useAtom(passwordInputState);
	const setIsLoginCaptchaRendered = useSetAtom(isLoginCaptchaRenderedState);
	const { navigateToDialog } = useNavigateDialog();
	const showToast = useShowToast(activeDialogId);
	const apiAuthMailConfirmShortAuthPassword = useApiAuthMailConfirmShortAuthPassword();
	const apiAuthMailConfirmFullAuthPassword = useApiAuthMailConfirmFullAuthPassword();

	const passwordInputRef = useRef<HTMLInputElement>(null);
	const [isAuthBlocked, setIsAuthBlocked] = useState(false);
	const [nextAttempt, setNextAttempt] = useState(0);
	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const [isError, setIsError] = useState(false);
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);
	const [widgetId, setWidgetId] = useState("");

	useEffect(() => {
		setNeedShowForgotPasswordButton(!isAuthBlocked);
	}, [isAuthBlocked]);

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					setWidgetId(doCaptchaRender(node, captchaPublicKey, captchaProvider, setGrecaptchaResponse));
				} catch (error) {}

				setShowCaptchaState("rendered");

				doCaptchaReady(captchaProvider, setIsLoginCaptchaRendered);
			}

			if (node !== null && showCaptchaState === "rendered") {
				doCaptchaReset(captchaProvider, widgetId);
			}
		},
		[showCaptchaState, captchaPublicKey]
	);

	const onLoginClickHandler = useCallback(async () => {
		if (apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading) {
			return;
		}

		if (auth === null) {
			navigateToDialog("auth_email_phone_number");
			return;
		}

		if (password.length < 8) {
			setIsError(true);
			return;
		}

		if ((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).scenario === AUTH_MAIL_SCENARIO_SHORT) {
			try {
				await apiAuthMailConfirmShortAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
					grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
				});

				setGrecaptchaResponse(""); // сбрасываем
			} catch (error) {
				setGrecaptchaResponse(""); // сбрасываем
				// @ts-ignore
				if (showCaptchaState === "rendered" && grecaptcha.enterprise.reset !== undefined) {
					doCaptchaReset(captchaProvider, widgetId);
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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708116) {
						showToast(
							langStringErrorsAuthIncorrectPasswordError.replace(
								"$REMAINING_ATTEMPT_COUNTS",
								`${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneLeft,
									langStringErrorsConfirmCodeIncorrectCodeTwoLefts,
									langStringErrorsConfirmCodeIncorrectCodeFiveLefts
								)} ${error.available_attempts}${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneAttempt,
									langStringErrorsConfirmCodeIncorrectCodeTwoAttempts,
									langStringErrorsConfirmCodeIncorrectCodeFiveAttempts
								)}`
							),
							"warning"
						);
						return;
					}

					if (error.error_code === 1708200) {
						if (showCaptchaState === null) {
							setShowCaptchaState("need_render");
						}
						return;
					}

					if (error.error_code === 1708201) {
						showToast(langStringErrorsIncorrectCaptcha, "warning");
						if (showCaptchaState === "rendered") {
							doCaptchaReset(captchaProvider, widgetId);
						}
						return;
					}

					if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
						setIsAuthBlocked(true);
						setNextAttempt(error.next_attempt);
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		} else {
			try {
				await apiAuthMailConfirmFullAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
					grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
				});

				setGrecaptchaResponse(""); // сбрасываем
			} catch (error) {
				setGrecaptchaResponse(""); // сбрасываем
				// @ts-ignore
				if (showCaptchaState === "rendered" && grecaptcha.enterprise.reset !== undefined) {
					doCaptchaReset(captchaProvider, widgetId);
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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708116) {
						showToast(
							langStringErrorsAuthIncorrectPasswordError.replace(
								"$REMAINING_ATTEMPT_COUNTS",
								`${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneLeft,
									langStringErrorsConfirmCodeIncorrectCodeTwoLefts,
									langStringErrorsConfirmCodeIncorrectCodeFiveLefts
								)} ${error.available_attempts}${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneAttempt,
									langStringErrorsConfirmCodeIncorrectCodeTwoAttempts,
									langStringErrorsConfirmCodeIncorrectCodeFiveAttempts
								)}`
							),
							"warning"
						);
						return;
					}

					if (error.error_code === 1708200) {
						if (showCaptchaState === null) {
							setShowCaptchaState("need_render");
						}
						return;
					}

					if (error.error_code === 1708201) {
						showToast(langStringErrorsIncorrectCaptcha, "warning");
						if (showCaptchaState === "rendered") {
							doCaptchaReset(captchaProvider, widgetId);
						}
						return;
					}

					if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
						setIsAuthBlocked(true);
						setNextAttempt(error.next_attempt);
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		}
	}, [
		password,
		navigateToDialog,
		apiAuthMailConfirmShortAuthPassword,
		apiAuthMailConfirmFullAuthPassword,
		auth,
		passwordInputRef,
		grecaptchaResponse,
	]);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="20px">
				<KeyIcon80 />

				<Text mt="16px" style="lato_18_24_900" ls="-02">
					{langStringEmailLoginDialogTitle}
				</Text>

				<Text mt="6px" textAlign="center" style="lato_14_20_400" ls="-015" maxW="328px" overflow="wrapEllipsis">
					{langStringEmailLoginDialogDesc}
					<styled.span fontFamily="lato_bold">
						«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				{isAuthBlocked ? (
					<Text
						mt="20px"
						px="16px"
						py="10px"
						w="100%"
						bgColor="255106100.01"
						textAlign="center"
						rounded="8px"
						style="lato_13_18_400"
					>
						{langStringEmailLoginDialogAuthBlocked.replace(
							"$MINUTES",
							`${Math.ceil((nextAttempt - dayjs().unix()) / 60)}${plural(
								Math.ceil((nextAttempt - dayjs().unix()) / 60),
								langStringOneMinute,
								langStringTwoMinutes,
								langStringFiveMinutes
							)}`
						)}
					</Text>
				) : (
					<>
						<PasswordInput
							isDisabled={
								apiAuthMailConfirmShortAuthPassword.isLoading ||
								apiAuthMailConfirmFullAuthPassword.isLoading
							}
							mt="20px"
							autoFocus={true}
							password={password}
							setPassword={setPassword}
							inputPlaceholder={langStringEmailLoginDialogPasswordInputPlaceholder}
							isToolTipVisible={isToolTipVisible}
							setIsToolTipVisible={setIsToolTipVisible}
							isNeedShowTooltip={isNeedShowTooltip}
							setIsNeedShowTooltip={setIsNeedShowTooltip}
							isError={isError}
							setIsError={setIsError}
							onEnterClick={onLoginClickHandler}
							inputRef={passwordInputRef}
							isPasswordVisible={isPasswordVisible}
							setIsPasswordVisible={setIsPasswordVisible}
						/>

						<Box
							ref={captchaContainerRef}
							id="path_to_captcha"
							mt="12px"
							style={{
								display: showCaptchaState === "rendered" ? "block" : "none",
							}}
						/>

						<Button
							mt="12px"
							size="px12py6full"
							textSize="lato_15_23_600"
							disabled={password.length < 1}
							onClick={() => onLoginClickHandler()}
						>
							{apiAuthMailConfirmShortAuthPassword.isLoading ||
							apiAuthMailConfirmFullAuthPassword.isLoading ? (
								<Box py="3.5px">
									<Preloader16 />
								</Box>
							) : (
								langStringEmailLoginDialogLoginButton
							)}
						</Button>
					</>
				)}
			</VStack>
		</VStack>
	);
};

const EmailLoginDialogContentMobile = ({
	showCaptchaState,
	setShowCaptchaState,
	grecaptchaResponse,
	setGrecaptchaResponse,
}: EmailLoginDialogContentProps) => {
	const langStringErrorsConfirmCodeIncorrectCodeOneLeft = useLangString(
		"errors.confirm_code_incorrect_code_one_left"
	);
	const langStringErrorsConfirmCodeIncorrectCodeTwoLefts = useLangString(
		"errors.confirm_code_incorrect_code_two_lefts"
	);
	const langStringErrorsConfirmCodeIncorrectCodeFiveLefts = useLangString(
		"errors.confirm_code_incorrect_code_five_lefts"
	);
	const langStringErrorsConfirmCodeIncorrectCodeOneAttempt = useLangString(
		"errors.confirm_code_incorrect_code_one_attempt"
	);
	const langStringErrorsConfirmCodeIncorrectCodeTwoAttempts = useLangString(
		"errors.confirm_code_incorrect_code_two_attempts"
	);
	const langStringErrorsConfirmCodeIncorrectCodeFiveAttempts = useLangString(
		"errors.confirm_code_incorrect_code_five_attempts"
	);
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const langStringEmailLoginDialogTitle = useLangString("email_login_dialog.title");
	const langStringEmailLoginDialogDesc = useLangString("email_login_dialog.desc");
	const langStringEmailLoginDialogPasswordInputPlaceholder = useLangString(
		"email_login_dialog.password_input_placeholder"
	);
	const langStringEmailLoginDialogLoginButton = useLangString("email_login_dialog.login_button");
	const langStringEmailLoginDialogBackButton = useLangString("email_login_dialog.back_button");
	const langStringEmailLoginDialogForgotPasswordButton = useLangString("email_login_dialog.forgot_password_button");
	const langStringEmailLoginDialogAuthBlocked = useLangString("email_login_dialog.auth_blocked");
	const langStringErrorsAuthIncorrectPasswordError = useLangString("errors.auth_incorrect_password_error");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsIncorrectCaptcha = useLangString("errors.incorrect_captcha");
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const authInput = useAtomValue(authInputState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);
	const [auth, setAuth] = useAtom(authState);
	const setJoinLink = useSetAtom(joinLinkState);
	const [password, setPassword] = useAtom(passwordInputState);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);
	const { navigateToDialog } = useNavigateDialog();
	const apiAuthMailConfirmShortAuthPassword = useApiAuthMailConfirmShortAuthPassword();
	const apiAuthMailConfirmFullAuthPassword = useApiAuthMailConfirmFullAuthPassword();
	const apiAuthMailCancel = useApiAuthMailCancel();
	const apiSecurityMailTryResetPassword = useApiSecurityMailTryResetPassword();

	const email = useMemo(() => {
		const [authValue, _] = authInput.split("__|__") || ["", 0];

		return authValue;
	}, [authInput]);

	const showToast = useShowToast(activeDialogId);

	const passwordInputRef = useRef<HTMLInputElement>(null);
	const [isAuthBlocked, setIsAuthBlocked] = useState(false);
	const [nextAttempt, setNextAttempt] = useState(0);
	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const [isError, setIsError] = useState(false);
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);
	const [widgetId, setWidgetId] = useState("");
	const captchaProvider = useAtomValue(captchaProviderState);
	const setIsLoginCaptchaRendered = useSetAtom(isLoginCaptchaRenderedState);

	const screenWidth = useMemo(() => document.body.clientWidth, [document.body.clientWidth]);

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					setWidgetId(doCaptchaRender(node, captchaPublicKey, captchaProvider, setGrecaptchaResponse));
				} catch (error) {}

				setShowCaptchaState("rendered");

				doCaptchaReady(captchaProvider, setIsLoginCaptchaRendered);
			}

			if (node !== null && showCaptchaState === "rendered") {
				doCaptchaReset(captchaProvider, widgetId);
			}
		},
		[showCaptchaState, captchaPublicKey]
	);

	const onLoginClickHandler = useCallback(async () => {
		if (apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading) {
			return;
		}

		if (auth === null) {
			navigateToDialog("auth_email_phone_number");
			return;
		}

		if (password.length < 8) {
			setIsError(true);
			return;
		}

		if ((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).scenario === AUTH_MAIL_SCENARIO_SHORT) {
			try {
				await apiAuthMailConfirmShortAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
					grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
				});

				setGrecaptchaResponse(""); // сбрасываем
			} catch (error) {
				setGrecaptchaResponse(""); // сбрасываем
				// @ts-ignore
				if (showCaptchaState === "rendered" && grecaptcha.enterprise.reset !== undefined) {
					doCaptchaReset(captchaProvider, widgetId);
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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708116) {
						showToast(
							langStringErrorsAuthIncorrectPasswordError.replace(
								"$REMAINING_ATTEMPT_COUNTS",
								`${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneLeft,
									langStringErrorsConfirmCodeIncorrectCodeTwoLefts,
									langStringErrorsConfirmCodeIncorrectCodeFiveLefts
								)} ${error.available_attempts}${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneAttempt,
									langStringErrorsConfirmCodeIncorrectCodeTwoAttempts,
									langStringErrorsConfirmCodeIncorrectCodeFiveAttempts
								)}`
							),
							"warning"
						);
						return;
					}

					if (error.error_code === 1708200) {
						if (showCaptchaState === null) {
							setShowCaptchaState("need_render");
						}
						return;
					}

					if (error.error_code === 1708201) {
						showToast(langStringErrorsIncorrectCaptcha, "warning");
						if (showCaptchaState === "rendered") {
							doCaptchaReset(captchaProvider, widgetId);
						}
						return;
					}

					if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
						setIsAuthBlocked(true);
						setNextAttempt(error.next_attempt);
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		} else {
			try {
				await apiAuthMailConfirmFullAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
					grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
				});

				setGrecaptchaResponse(""); // сбрасываем
			} catch (error) {
				setGrecaptchaResponse(""); // сбрасываем
				// @ts-ignore
				if (showCaptchaState === "rendered" && grecaptcha.enterprise.reset !== undefined) {
					doCaptchaReset(captchaProvider, widgetId);
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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708116) {
						showToast(
							langStringErrorsAuthIncorrectPasswordError.replace(
								"$REMAINING_ATTEMPT_COUNTS",
								`${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneLeft,
									langStringErrorsConfirmCodeIncorrectCodeTwoLefts,
									langStringErrorsConfirmCodeIncorrectCodeFiveLefts
								)} ${error.available_attempts}${plural(
									error.available_attempts,
									langStringErrorsConfirmCodeIncorrectCodeOneAttempt,
									langStringErrorsConfirmCodeIncorrectCodeTwoAttempts,
									langStringErrorsConfirmCodeIncorrectCodeFiveAttempts
								)}`
							),
							"warning"
						);
						return;
					}

					if (error.error_code === 1708200) {
						if (showCaptchaState === null) {
							setShowCaptchaState("need_render");
						}
						return;
					}

					if (error.error_code === 1708201) {
						showToast(langStringErrorsIncorrectCaptcha, "warning");
						if (showCaptchaState === "rendered") {
							doCaptchaReset(captchaProvider, widgetId);
						}
						return;
					}

					if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
						setIsAuthBlocked(true);
						setNextAttempt(error.next_attempt);
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		}
	}, [
		password,
		navigateToDialog,
		apiAuthMailConfirmShortAuthPassword,
		apiAuthMailConfirmFullAuthPassword,
		auth,
		passwordInputRef,
		grecaptchaResponse,
	]);

	const onForgotPasswordButtonClick = useCallback(async () => {
		if (apiSecurityMailTryResetPassword.isLoading) {
			return;
		}

		try {
			const response = await apiSecurityMailTryResetPassword.mutateAsync({
				mail: email,
				join_link:
					prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
						? window.location.href
						: undefined,
			});

			setAuth(response.auth_info);
			setJoinLink(response.join_link_info);
			navigateToDialog("auth_email_confirm_code");
		} catch (error) {
			if (error instanceof NetworkError) {
				showToast(langStringErrorsNetworkError, "warning");
				return;
			}

			if (error instanceof ServerError) {
				showToast(langStringErrorsServerError, "warning");
				return;
			}

			if (error instanceof ApiError) {
				if (error.error_code === INCORRECT_LINK_ERROR_CODE || error.error_code === INACTIVE_LINK_ERROR_CODE) {
					setPrepareJoinLinkError({ error_code: error.error_code });
					return;
				}

				if (error.error_code === 1708200) {
					navigateToDialog("auth_forgot_password");
					return;
				}

				// если сказали что уже участник этой компании - то логиним без передачи joinLink
				if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {
					try {
						const response = await apiSecurityMailTryResetPassword.mutateAsync({
							mail: email,
						});

						setAuth(response.auth_info);
						setJoinLink(response.join_link_info);
						navigateToDialog("auth_email_confirm_code");
					} catch (error) {
						if (error instanceof NetworkError) {
							showToast(langStringErrorsNetworkError, "warning");
							return;
						}

						if (error instanceof ServerError) {
							showToast(langStringErrorsServerError, "warning");
							return;
						}

						if (error instanceof ApiError) {
							if (
								error.error_code === INCORRECT_LINK_ERROR_CODE ||
								error.error_code === INACTIVE_LINK_ERROR_CODE
							) {
								setPrepareJoinLinkError({ error_code: error.error_code });
								return;
							}

							if (error.error_code === 1708200) {
								navigateToDialog("auth_forgot_password");
								return;
							}
						}
					}
				}
			}
		}
	}, [email, prepareJoinLinkError, apiSecurityMailTryResetPassword, window.location.href]);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<>
			<VStack w="100%" gap="0px">
				<Box w="100%">
					<Button
						color="2574a9"
						textSize="lato_16_22_400"
						size="px0py0"
						onClick={() => apiAuthMailCancel.mutate({ auth_key: auth.auth_key })}
					>
						{langStringEmailLoginDialogBackButton}
					</Button>
				</Box>
				<VStack gap="0px" mt="-6px">
					<KeyIcon80 />

					<Text mt="16px" style="lato_20_28_700" ls="-03">
						{langStringEmailLoginDialogTitle}
					</Text>

					<Text
						mt="4px"
						textAlign="center"
						style="lato_16_22_400"
						maxW={screenWidth <= 390 ? "326px" : "350px"}
						overflow="wrapEllipsis"
					>
						{langStringEmailLoginDialogDesc}
						<styled.span fontFamily="lato_bold">
							«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
						</styled.span>
					</Text>

					{isAuthBlocked ? (
						<Text
							mt="20px"
							p="12px"
							w="100%"
							bgColor="255106100.01"
							textAlign="center"
							rounded="8px"
							style="lato_16_22_400"
						>
							{langStringEmailLoginDialogAuthBlocked.replace(
								"$MINUTES",
								`${Math.ceil((nextAttempt - dayjs().unix()) / 60)}${plural(
									Math.ceil((nextAttempt - dayjs().unix()) / 60),
									langStringOneMinute,
									langStringTwoMinutes,
									langStringFiveMinutes
								)}`
							)}
						</Text>
					) : (
						<>
							<PasswordInput
								isDisabled={
									apiAuthMailConfirmShortAuthPassword.isLoading ||
									apiAuthMailConfirmFullAuthPassword.isLoading
								}
								mt="24px"
								autoFocus={true}
								password={password}
								setPassword={setPassword}
								inputPlaceholder={langStringEmailLoginDialogPasswordInputPlaceholder}
								isToolTipVisible={isToolTipVisible}
								setIsToolTipVisible={setIsToolTipVisible}
								isNeedShowTooltip={isNeedShowTooltip}
								setIsNeedShowTooltip={setIsNeedShowTooltip}
								isError={isError}
								setIsError={setIsError}
								onEnterClick={onLoginClickHandler}
								inputRef={passwordInputRef}
								isPasswordVisible={isPasswordVisible}
								setIsPasswordVisible={setIsPasswordVisible}
							/>

							<Box
								ref={captchaContainerRef}
								id="path_to_captcha"
								mt="12px"
								style={{
									display: showCaptchaState === "rendered" ? "block" : "none",
								}}
							/>

							<Button
								mt="12px"
								size="px16py9full"
								textSize="lato_17_26_600"
								disabled={password.length < 1}
								onClick={() => onLoginClickHandler()}
							>
								{apiAuthMailConfirmShortAuthPassword.isLoading ||
								apiAuthMailConfirmFullAuthPassword.isLoading ? (
									<Box py="5px">
										<Preloader16 />
									</Box>
								) : (
									langStringEmailLoginDialogLoginButton
								)}
							</Button>

							<Button
								mt="16px"
								size="px0py0"
								color="2574a9_opacity70"
								textSize="lato_16_22_400"
								onClick={() => onForgotPasswordButtonClick()}
							>
								{langStringEmailLoginDialogForgotPasswordButton}
							</Button>
						</>
					)}
				</VStack>
			</VStack>
		</>
	);
};

type ShowGrecaptchaState = null | "need_render" | "rendered";

const EmailLoginDialogContent = () => {
	const isMobile = useIsMobile();
	const setConfirmCode = useSetAtom(confirmCodeState);

	const [showCaptchaState, setShowCaptchaState] = useState<ShowGrecaptchaState>(null);
	const [grecaptchaResponse, setGrecaptchaResponse] = useState("");

	const inputRef = useRef<HTMLDivElement>(null);
	useEffect(() => {
		if (inputRef.current) {
			inputRef.current.focus();
			setConfirmCode(Array(6).fill(""));
		}
	}, [inputRef]);

	if (isMobile) {
		return (
			<EmailLoginDialogContentMobile
				showCaptchaState={showCaptchaState}
				setShowCaptchaState={setShowCaptchaState}
				grecaptchaResponse={grecaptchaResponse}
				setGrecaptchaResponse={setGrecaptchaResponse}
			/>
		);
	}

	return (
		<EmailLoginDialogContentDesktop
			showCaptchaState={showCaptchaState}
			setShowCaptchaState={setShowCaptchaState}
			grecaptchaResponse={grecaptchaResponse}
			setGrecaptchaResponse={setGrecaptchaResponse}
		/>
	);
};

export default EmailLoginDialogContent;
