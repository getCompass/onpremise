import { Box, VStack } from "../../../styled-system/jsx";
import IconLogo from "../../components/IconLogo.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import {
	activeDialogIdState,
	authInputState,
	authState,
	captchaPublicKeyState,
	joinLinkState,
	passwordInputState,
	prepareJoinLinkErrorState,
} from "../../api/_stores.ts";
import { useAtomValue } from "jotai";
import useIsMobile from "../../lib/useIsMobile.ts";
import { useCallback, useMemo, useState } from "react";
import { Text } from "../../components/text.tsx";
import { useNavigateDialog } from "../../components/hooks.ts";
import { useApiSecurityMailTryResetPassword } from "../../api/security/mail.ts";
import { ALREADY_MEMBER_ERROR_CODE, INACTIVE_LINK_ERROR_CODE, INCORRECT_LINK_ERROR_CODE } from "../../api/_types.ts";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { useAtom, useSetAtom } from "jotai/index";
import { useShowToast } from "../../lib/Toast.tsx";
import { Button } from "../../components/button.tsx";
import { useApiAuthMailCancel } from "../../api/auth/mail.ts";

type ForgotPasswordDialogContentProps = {
	showCaptchaState: ShowGrecaptchaState;
	setShowCaptchaState: (value: ShowGrecaptchaState) => void;
};

const ForgotPasswordDialogContentDesktop = ({
	showCaptchaState,
	setShowCaptchaState,
}: ForgotPasswordDialogContentProps) => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsIncorrectCaptcha = useLangString("errors.incorrect_captcha");

	const langStringForgotPasswordDialogTitle = useLangString("forgot_password_dialog.title");
	const langStringForgotPasswordDialogDesc = useLangString("forgot_password_dialog.desc");

	const captchaPublicKey = useAtomValue(captchaPublicKeyState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);
	const authInput = useAtomValue(authInputState);
	const setAuth = useSetAtom(authState);
	const setJoinLink = useSetAtom(joinLinkState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);

	const email = useMemo(() => {
		const [authValue, _] = authInput.split("__|__") || ["", 0];

		return authValue;
	}, [authInput]);

	const apiSecurityMailTryResetPassword = useApiSecurityMailTryResetPassword();
	const { navigateToDialog } = useNavigateDialog();

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					// @ts-ignore
					grecaptcha.enterprise.render(node, {
						sitekey: captchaPublicKey,
						action: "check_captcha",
						callback: async function (grecaptchaResponse: string) {
							try {
								const response = await apiSecurityMailTryResetPassword.mutateAsync({
									mail: email,
									grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
									join_link:
										prepareJoinLinkError === null ||
										prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
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
									if (
										error.error_code === INCORRECT_LINK_ERROR_CODE ||
										error.error_code === INACTIVE_LINK_ERROR_CODE
									) {
										setPrepareJoinLinkError({ error_code: error.error_code });
										return;
									}

									// если сказали что уже участник этой компании - то логиним без передачи joinLink
									if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {
										try {
											const response = await apiSecurityMailTryResetPassword.mutateAsync({
												mail: email,
												grecaptcha_response:
													grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
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

												if (error.error_code === 1708201) {
													showToast(langStringErrorsIncorrectCaptcha, "warning");
													// @ts-ignore
													if (grecaptcha.enterprise.reset !== undefined) {
														// @ts-ignore
														grecaptcha.enterprise.reset();
													}
													return;
												}
											}
										}
									}

									if (error.error_code === 1708201) {
										showToast(langStringErrorsIncorrectCaptcha, "warning");
										// @ts-ignore
										if (grecaptcha.enterprise.reset !== undefined) {
											// @ts-ignore
											grecaptcha.enterprise.reset();
										}
										return;
									}
								}
							}
						},
					});
				} catch (error) {}

				setShowCaptchaState("rendered");
			}

			if (node !== null && showCaptchaState === "rendered") {
				// @ts-ignore
				if (grecaptcha.enterprise.reset !== undefined) {
					// @ts-ignore
					grecaptcha.enterprise.reset();
				}
			}
		},
		[showCaptchaState, captchaPublicKey]
	);

	if (email.length < 1) {

		setPasswordInput("");
		navigateToDialog("auth_email_login");
		return <></>;
	}

	return (
		<VStack w="100%" gap="0px" mt="20px">
			<IconLogo />

			<Text mt="16px" style="lato_18_24_900" ls="-02">
				{langStringForgotPasswordDialogTitle}
			</Text>

			<Text mt="6px" px="4px" textAlign="center" style="lato_14_20_400" ls="-015">
				{langStringForgotPasswordDialogDesc}
			</Text>

			<Box
				ref={captchaContainerRef}
				id="path_to_captcha"
				mt="20px"
				style={{
					display: showCaptchaState === "rendered" ? "block" : "none",
				}}
			/>
		</VStack>
	);
};

const ForgotPasswordDialogContentMobile = ({
	showCaptchaState,
	setShowCaptchaState,
}: ForgotPasswordDialogContentProps) => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsIncorrectCaptcha = useLangString("errors.incorrect_captcha");

	const langStringForgotPasswordDialogTitle = useLangString("forgot_password_dialog.title");
	const langStringEmailLoginDialogBackButton = useLangString("email_login_dialog.back_button");
	const langStringForgotPasswordDialogDesc = useLangString("forgot_password_dialog.desc");

	const auth = useAtomValue(authState);
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);
	const authInput = useAtomValue(authInputState);
	const setAuth = useSetAtom(authState);
	const setJoinLink = useSetAtom(joinLinkState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);

	const email = useMemo(() => {
		const [authValue, _] = authInput.split("__|__") || ["", 0];

		return authValue;
	}, [authInput]);

	const apiSecurityMailTryResetPassword = useApiSecurityMailTryResetPassword();
	const apiAuthMailCancel = useApiAuthMailCancel();
	const { navigateToDialog } = useNavigateDialog();

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					// @ts-ignore
					grecaptcha.enterprise.render(node, {
						sitekey: captchaPublicKey,
						action: "check_captcha",
						callback: async function (grecaptchaResponse: string) {
							try {
								const response = await apiSecurityMailTryResetPassword.mutateAsync({
									mail: email,
									grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
									join_link:
										prepareJoinLinkError === null ||
										prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
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
									if (
										error.error_code === INCORRECT_LINK_ERROR_CODE ||
										error.error_code === INACTIVE_LINK_ERROR_CODE
									) {
										setPrepareJoinLinkError({ error_code: error.error_code });
										return;
									}

									// если сказали что уже участник этой компании - то логиним без передачи joinLink
									if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {
										try {
											const response = await apiSecurityMailTryResetPassword.mutateAsync({
												mail: email,
												grecaptcha_response:
													grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
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

												if (error.error_code === 1708201) {
													showToast(langStringErrorsIncorrectCaptcha, "warning");
													// @ts-ignore
													if (grecaptcha.enterprise.reset !== undefined) {
														// @ts-ignore
														grecaptcha.enterprise.reset();
													}
													return;
												}
											}
										}
									}

									if (error.error_code === 1708201) {
										showToast(langStringErrorsIncorrectCaptcha, "warning");
										// @ts-ignore
										if (grecaptcha.enterprise.reset !== undefined) {
											// @ts-ignore
											grecaptcha.enterprise.reset();
										}
										return;
									}
								}
							}
						},
					});
				} catch (error) {}

				setShowCaptchaState("rendered");
			}

			if (node !== null && showCaptchaState === "rendered") {
				// @ts-ignore
				if (grecaptcha.enterprise.reset !== undefined) {
					// @ts-ignore
					grecaptcha.enterprise.reset();
				}
			}
		},
		[showCaptchaState, captchaPublicKey]
	);

	if (email.length < 1) {

		setPasswordInput("");
		navigateToDialog("auth_email_login");
		return <></>;
	}

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
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
			<VStack w="100%" gap="0px" mt="-6px">
				<IconLogo />

				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringForgotPasswordDialogTitle}
				</Text>

				<Text mt="4px" px="4px" textAlign="center" style="lato_16_22_400">
					{langStringForgotPasswordDialogDesc}
				</Text>

				<Box
					ref={captchaContainerRef}
					id="path_to_captcha"
					mt="24px"
					style={{
						display: showCaptchaState === "rendered" ? "block" : "none",
					}}
				/>
			</VStack>
		</VStack>
	);
};

type ShowGrecaptchaState = "need_render" | "rendered";

const ForgotPasswordDialogContent = () => {
	const isMobile = useIsMobile();

	const [showCaptchaState, setShowCaptchaState] = useState<ShowGrecaptchaState>("need_render");

	if (isMobile) {
		return (
			<ForgotPasswordDialogContentMobile
				showCaptchaState={showCaptchaState}
				setShowCaptchaState={setShowCaptchaState}
			/>
		);
	}

	return (
		<ForgotPasswordDialogContentDesktop
			showCaptchaState={showCaptchaState}
			setShowCaptchaState={setShowCaptchaState}
		/>
	);
};

export default ForgotPasswordDialogContent;
