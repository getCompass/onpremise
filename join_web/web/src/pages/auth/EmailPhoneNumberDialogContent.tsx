import { Box, VStack } from "../../../styled-system/jsx";
import IconLogo from "../../components/IconLogo.tsx";
import { Input } from "../../components/input.tsx";
import { Button } from "../../components/button.tsx";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import {
	activeDialogIdState,
	authInputState,
	authState,
	captchaPublicKeyState,
	confirmCodeState,
	confirmPasswordState,
	isPasswordChangedState,
	isRegistrationState,
	joinLinkState,
	passwordInputState,
	prepareJoinLinkErrorState,
} from "../../api/_stores.ts";
import { useAtom, useAtomValue, useSetAtom } from "jotai";
import useIsMobile from "../../lib/useIsMobile.ts";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { useNavigateDialog, useNavigatePage } from "../../components/hooks.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import dayjs from "dayjs";
import { plural } from "../../lib/plural.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import {
	ALREADY_MEMBER_ERROR_CODE,
	APIAuthTypeRegisterByMail,
	APIAuthTypeRegisterByPhoneNumber,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	isValidEmail,
	LIMIT_ERROR_CODE,
} from "../../api/_types.ts";
import { Tooltip, TooltipArrow, TooltipContent, TooltipProvider, TooltipTrigger } from "../../components/tooltip.tsx";
import { Portal } from "@ark-ui/react";
import useAvailableAuthMethodList from "../../lib/useAvailableAuthMethodList.ts";
import { useApiAuthPhoneNumberBegin } from "../../api/auth/phonenumber.ts";
import { useApiAuthMailBegin } from "../../api/auth/mail.ts";

type EmailPhoneNumberDialogContentProps = {
	onAuthBeginClickHandler: (value: string) => void;
	authInput: string;
	setAuthInput: (value: string) => void;
	isLoading: boolean;
	setIsError: (value: boolean) => void;
	isError: boolean;
	inputRef: any;
	showCaptchaState: ShowGrecaptchaState;
	setShowCaptchaState: (value: ShowGrecaptchaState) => void;
	setGrecaptchaResponse: (value: string) => void;
};

const EmailPhoneNumberDialogContentDesktop = ({
	onAuthBeginClickHandler,
	authInput,
	setAuthInput,
	isLoading,
	setIsError,
	isError,
	inputRef,
	showCaptchaState,
	setShowCaptchaState,
	setGrecaptchaResponse,
}: EmailPhoneNumberDialogContentProps) => {
	const langStringEmailPhoneNumberDialogTitle = useLangString("email_phone_number_dialog.title");
	const langStringEmailPhoneNumberDialogDescEmailPhoneNumber = useLangString(
		"email_phone_number_dialog.desc_email_phone_number"
	);
	const langStringEmailPhoneNumberDialogDescEmail = useLangString("email_phone_number_dialog.desc_email");
	const langStringEmailPhoneNumberDialogDescPhoneNumber = useLangString(
		"email_phone_number_dialog.desc_phone_number"
	);
	const langStringEmailPhoneNumberDialogInputPlaceholderEmail = useLangString(
		"email_phone_number_dialog.input_placeholder_email"
	);
	const langStringEmailPhoneNumberDialogInputPlaceholderPhoneNumber = useLangString(
		"email_phone_number_dialog.input_placeholder_phone_number"
	);
	const langStringEmailPhoneNumberDialogInputPlaceholderEmailPhoneNumber = useLangString(
		"email_phone_number_dialog.input_placeholder_email_phone_number"
	);
	const langStringEmailPhoneNumberDialogConfirmButton = useLangString("email_phone_number_dialog.confirm_button");
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const availableAuthMethodList = useAvailableAuthMethodList();

	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);

	useEffect(() => {
		if (isToolTipVisible) {
			setTimeout(() => setIsToolTipVisible(false), 5000);
		}
	}, [isToolTipVisible]);

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					// @ts-ignore
					grecaptcha.enterprise.render(node, {
						sitekey: captchaPublicKey,
						action: "check_captcha",
						callback: function (grecaptchaResponse: string) {
							setGrecaptchaResponse(grecaptchaResponse);
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

	return (
		<VStack w="100%" gap="20px">
			<VStack px="4px" mt="20px" gap="16px" w="100%">
				<IconLogo />
				<VStack gap="6px" w="100%">
					<Text fs="18" lh="24" font="bold" ls="-02">
						{langStringEmailPhoneNumberDialogTitle}
					</Text>
					<Text fs="14" lh="20" textAlign="center" font="regular">
						{availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()
							? langStringEmailPhoneNumberDialogDescEmailPhoneNumber
							: availableAuthMethodList.isAuthMethodMailEnabled()
							? langStringEmailPhoneNumberDialogDescEmail
							: langStringEmailPhoneNumberDialogDescPhoneNumber}
					</Text>
				</VStack>
			</VStack>
			<VStack w="100%" gap="0px">
				<TooltipProvider>
					<Tooltip open={isToolTipVisible} onOpenChange={() => null} style="desktop" type="warning_desktop">
						<VStack w="100%" gap="0px">
							<TooltipTrigger
								style={{
									width: "100%",
									height: "0px",
									opacity: "0%",
								}}
							/>
							<Input
								ref={inputRef}
								type="search"
								autoComplete="nope"
								value={authInput}
								autoCapitalize="none"
								onChange={(changeEvent) => {
									const value = changeEvent.target.value ?? "";
									if (isNeedShowTooltip) {
										const isTooltipNotSavedSymbolsVisible = /[^а-яА-яёЁa-zA-Z0-9@+\-._ ']/.test(
											value
										);
										if (isTooltipNotSavedSymbolsVisible) {
											setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
											if (isTooltipNotSavedSymbolsVisible) {
												setIsNeedShowTooltip(false);
											}
										}
									}

									setAuthInput(value);
									setIsError(false);
								}}
								maxLength={80}
								placeholder={
									availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()
										? langStringEmailPhoneNumberDialogInputPlaceholderEmailPhoneNumber
										: availableAuthMethodList.isAuthMethodMailEnabled()
										? langStringEmailPhoneNumberDialogInputPlaceholderEmail
										: langStringEmailPhoneNumberDialogInputPlaceholderPhoneNumber
								}
								size="default_desktop"
								onKeyDown={(event: React.KeyboardEvent) => {
									if (event.key === "Enter") {
										onAuthBeginClickHandler(authInput);
									}
								}}
								input={isError ? "error_default" : "default"}
							/>
						</VStack>
						<Portal>
							<TooltipContent
								onClick={() => setIsToolTipVisible(false)}
								onEscapeKeyDown={() => setIsToolTipVisible(false)}
								onPointerDownOutside={() => setIsToolTipVisible(false)}
								sideOffset={4}
								avoidCollisions={false}
								style={{
									maxWidth: "256px",
									width: "var(--radix-tooltip-trigger-width)",
								}}
							>
								<TooltipArrow width="8px" height="5px" asChild>
									<svg
										width="8"
										height="5"
										viewBox="0 0 8 5"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00" />
									</svg>
								</TooltipArrow>
								{langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				<Box
					ref={captchaContainerRef}
					id="path_to_captcha"
					style={{
						display: showCaptchaState === "rendered" ? "block" : "none",
						marginTop: showCaptchaState === "rendered" ? "12px" : "0px",
					}}
				/>
				<Button
					mt="10px"
					size="full_desktop"
					textSize="xl_desktop"
					onClick={() => onAuthBeginClickHandler(authInput)}
					disabled={authInput.replace(/[^а-яА-яёЁa-zA-Z0-9@+\-._']/g, "").trim().length < 1}
				>
					{isLoading ? <Preloader16 /> : langStringEmailPhoneNumberDialogConfirmButton}
				</Button>
			</VStack>
		</VStack>
	);
};

const EmailPhoneNumberDialogContentMobile = ({
	onAuthBeginClickHandler,
	authInput,
	setAuthInput,
	isLoading,
	setIsError,
	isError,
	inputRef,
	showCaptchaState,
	setShowCaptchaState,
	setGrecaptchaResponse,
}: EmailPhoneNumberDialogContentProps) => {
	const langStringEmailPhoneNumberDialogTitle = useLangString("email_phone_number_dialog.title");
	const langStringEmailPhoneNumberDialogDescEmailPhoneNumber = useLangString(
		"email_phone_number_dialog.desc_email_phone_number"
	);
	const langStringEmailPhoneNumberDialogDescEmail = useLangString("email_phone_number_dialog.desc_email");
	const langStringEmailPhoneNumberDialogDescPhoneNumber = useLangString(
		"email_phone_number_dialog.desc_phone_number"
	);
	const langStringEmailPhoneNumberDialogInputPlaceholderEmail = useLangString(
		"email_phone_number_dialog.input_placeholder_email"
	);
	const langStringEmailPhoneNumberDialogInputPlaceholderPhoneNumber = useLangString(
		"email_phone_number_dialog.input_placeholder_phone_number"
	);
	const langStringEmailPhoneNumberDialogInputPlaceholderEmailPhoneNumber = useLangString(
		"email_phone_number_dialog.input_placeholder_email_phone_number"
	);
	const langStringEmailPhoneNumberDialogConfirmButton = useLangString("email_phone_number_dialog.confirm_button");

	const availableAuthMethodList = useAvailableAuthMethodList();

	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);

	useEffect(() => {
		if (isToolTipVisible) {
			setTimeout(() => setIsToolTipVisible(false), 5000);
		}
	}, [isToolTipVisible]);

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					// @ts-ignore
					grecaptcha.enterprise.render(node, {
						sitekey: captchaPublicKey,
						action: "check_captcha",
						callback: function (grecaptchaResponse: string) {
							setGrecaptchaResponse(grecaptchaResponse);
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

	return (
		<VStack w="100%" gap="24px">
			<VStack mt="16px" gap="16px" w="100%">
				<IconLogo />
				<VStack gap="4px" w="100%">
					<Text fs="20" lh="28" font="bold" ls="-03">
						{langStringEmailPhoneNumberDialogTitle}
					</Text>
					<Text fs="16" lh="22" textAlign="center" font="regular">
						{availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()
							? langStringEmailPhoneNumberDialogDescEmailPhoneNumber
							: availableAuthMethodList.isAuthMethodMailEnabled()
							? langStringEmailPhoneNumberDialogDescEmail
							: langStringEmailPhoneNumberDialogDescPhoneNumber}
					</Text>
				</VStack>
			</VStack>
			<VStack w="100%" gap={showCaptchaState === "rendered" ? "16px" : "12px"}>
				<TooltipProvider>
					<Tooltip open={isToolTipVisible} onOpenChange={() => null} style="mobile" type="warning_mobile">
						<VStack w="100%" gap="0px">
							<TooltipTrigger
								style={{
									width: "100%",
									height: "0px",
									opacity: "0%",
								}}
							/>
							<Input
								ref={inputRef}
								type="search"
								autoComplete="nope"
								value={authInput}
								autoCapitalize="none"
								onChange={(changeEvent) => {
									const value = changeEvent.target.value ?? "";
									if (isNeedShowTooltip) {
										const isTooltipNotSavedSymbolsVisible = /[^а-яА-яёЁa-zA-Z0-9@+\-._ ']/.test(
											value
										);
										if (isTooltipNotSavedSymbolsVisible) {
											setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
											if (isTooltipNotSavedSymbolsVisible) {
												setIsNeedShowTooltip(false);
											}
										}
									}

									setAuthInput(value.slice(0, 80));
									setIsError(false);
								}}
								maxLength={80}
								placeholder={
									availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()
										? langStringEmailPhoneNumberDialogInputPlaceholderEmailPhoneNumber
										: availableAuthMethodList.isAuthMethodMailEnabled()
										? langStringEmailPhoneNumberDialogInputPlaceholderEmail
										: langStringEmailPhoneNumberDialogInputPlaceholderPhoneNumber
								}
								onKeyDown={(event: React.KeyboardEvent) => {
									if (event.key === "Enter") {
										onAuthBeginClickHandler(authInput);
									}
								}}
								input={isError ? "error_default" : "default"}
							/>
						</VStack>
						<Portal>
							<TooltipContent
								onClick={() => setIsToolTipVisible(false)}
								onEscapeKeyDown={() => setIsToolTipVisible(false)}
								onPointerDownOutside={() => setIsToolTipVisible(false)}
								sideOffset={4}
								avoidCollisions={false}
								style={{
									maxWidth: "256px",
									width: "var(--radix-tooltip-trigger-width)",
								}}
							>
								<TooltipArrow width="8px" height="5px" asChild>
									<svg
										width="8"
										height="5"
										viewBox="0 0 8 5"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00" />
									</svg>
								</TooltipArrow>
								{langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				<Box
					ref={captchaContainerRef}
					id="path_to_captcha"
					style={{
						display: showCaptchaState === "rendered" ? "block" : "none",
					}}
				/>
				<Button
					onClick={() => onAuthBeginClickHandler(authInput)}
					disabled={authInput.replace(/[^а-яА-яёЁa-zA-Z0-9@+\-._']/g, "").trim().length < 1}
				>
					{isLoading ? <Preloader16 /> : langStringEmailPhoneNumberDialogConfirmButton}
				</Button>
			</VStack>
		</VStack>
	);
};

type ShowGrecaptchaState = null | "need_render" | "rendered";

const EmailPhoneNumberDialogContent = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsEmailLimitError = useLangString("errors.email_limit_error");
	const langStringErrorsPhoneNumberLimitError = useLangString("errors.phone_number_limit_error");
	const langStringErrorsPhoneNumberEmailLimitError = useLangString("errors.phone_number_email_limit_error");
	const langStringErrorsPhoneNumberEmailIncorrectPhoneEmailError = useLangString(
		"errors.phone_number_email_incorrect_phone_email_error"
	);
	const langStringErrorsPhoneNumberIncorrectPhoneError = useLangString("errors.phone_number_incorrect_phone_error");
	const langStringErrorsEmailIncorrectEmailError = useLangString("errors.email_incorrect_email_error");
	const langStringErrorsIncorrectCaptcha = useLangString("errors.incorrect_captcha");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const availableAuthMethodList = useAvailableAuthMethodList();
	const isMobile = useIsMobile();
	const setConfirmCode = useSetAtom(confirmCodeState);

	const [showCaptchaState, setShowCaptchaState] = useState<ShowGrecaptchaState>(null);
	const [grecaptchaResponse, setGrecaptchaResponse] = useState("");
	const { navigateToDialog } = useNavigateDialog();
	const { navigateToPage } = useNavigatePage();
	const apiAuthPhoneNumberBegin = useApiAuthPhoneNumberBegin();
	const apiAuthMailBegin = useApiAuthMailBegin();
	const setAuth = useSetAtom(authState);
	const setJoinLink = useSetAtom(joinLinkState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const [isError, setIsError] = useState(false);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);
	const [authInput, setAuthInput] = useAtom(authInputState);
	const setIsRegistration = useSetAtom(isRegistrationState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setIsPasswordChanged = useSetAtom(isPasswordChangedState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);

	// сбрасываем
	useEffect(() => {
		setPasswordInput("");
		setIsPasswordChanged(false);
		setIsRegistration(false);
		setConfirmPassword("");
	}, []);

	const authInputValue = useMemo(() => {
		const [authValue, expiresAt] = authInput.split("__|__") || ["", 0];

		if (parseInt(expiresAt) < dayjs().unix()) {
			return "";
		}

		return authValue;
	}, [authInput]);

	const setAuthInputValue = useCallback((value: string) => {
		setAuthInput(`${value}__|__${dayjs().unix() + 60 * 10}`);
	}, []);

	const inputRef = useRef<HTMLDivElement>(null);
	useEffect(() => {
		if (inputRef.current) {
			inputRef.current.focus();
			setConfirmCode(Array(6).fill(""));
		}
	}, [inputRef]);

	const onAuthBeginClickHandler = useCallback(
		async (value: string) => {
			let editedPhoneNumberOrEmail = value.replace(/[^а-яА-яёЁa-zA-Z0-9@+\-._']/g, "").trim();
			if (editedPhoneNumberOrEmail.length < 1 || apiAuthPhoneNumberBegin.isLoading) {
				return;
			}

			if (availableAuthMethodList.isAuthMethodPhoneNumberEnabled() && !isValidEmail(editedPhoneNumberOrEmail)) {
				if (editedPhoneNumberOrEmail.startsWith("89")) {
					editedPhoneNumberOrEmail =
						"+79" + editedPhoneNumberOrEmail.substring(2, editedPhoneNumberOrEmail.length);
				}

				if (editedPhoneNumberOrEmail.startsWith("79")) {
					editedPhoneNumberOrEmail =
						"+" + editedPhoneNumberOrEmail.substring(0, editedPhoneNumberOrEmail.length);
				}
			}

			try {
				if (availableAuthMethodList.isAuthMethodMailEnabled() && isValidEmail(editedPhoneNumberOrEmail)) {
					const response = await apiAuthMailBegin.mutateAsync({
						mail: editedPhoneNumberOrEmail,
						join_link:
							prepareJoinLinkError === null ||
							prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
								? window.location.href
								: undefined,
						grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
					});

					setGrecaptchaResponse(""); // сбрасываем
					setAuth(response.auth_info);
					setJoinLink(response.join_link_info);
					if (
						response.auth_info.type === APIAuthTypeRegisterByMail ||
						response.auth_info.type === APIAuthTypeRegisterByPhoneNumber
					) {
						setIsRegistration(true);
					}
					if (response.auth_info.type === APIAuthTypeRegisterByMail) {
						navigateToDialog("auth_email_register");
					} else {
						navigateToDialog("auth_email_login");
					}
				} else if (availableAuthMethodList.isAuthMethodPhoneNumberEnabled()) {
					const response = await apiAuthPhoneNumberBegin.mutateAsync({
						phone_number: editedPhoneNumberOrEmail,
						join_link:
							prepareJoinLinkError === null ||
							prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
								? window.location.href
								: undefined,
						grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
					});

					setGrecaptchaResponse(""); // сбрасываем
					setAuth(response.auth_info);
					setJoinLink(response.join_link_info);
					if (
						response.auth_info.type === APIAuthTypeRegisterByMail ||
						response.auth_info.type === APIAuthTypeRegisterByPhoneNumber
					) {
						setIsRegistration(true);
					}
					navigateToDialog("auth_phone_number_confirm_code");
				} else {
					setIsError(true);
					if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
						showToast(langStringErrorsPhoneNumberEmailIncorrectPhoneEmailError, "warning");
					} else if (availableAuthMethodList.isAuthMethodMailEnabled()) {
						showToast(langStringErrorsEmailIncorrectEmailError, "warning");
					} else {
						showToast(langStringErrorsPhoneNumberIncorrectPhoneError, "warning");
					}
					return;
				}
			} catch (error) {
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
					if (
						error.error_code === INCORRECT_LINK_ERROR_CODE ||
						error.error_code === INACTIVE_LINK_ERROR_CODE
					) {
						setIsError(true);
						setPrepareJoinLinkError({ error_code: error.error_code });
						return;
					}

					// если сказали что уже участник этой компании - то логиним без передачи joinLink
					if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {
						try {
							setPrepareJoinLinkError({ error_code: ALREADY_MEMBER_ERROR_CODE });
							if (
								availableAuthMethodList.isAuthMethodMailEnabled() &&
								isValidEmail(editedPhoneNumberOrEmail)
							) {
								const response = await apiAuthMailBegin.mutateAsync({
									mail: editedPhoneNumberOrEmail,
									grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
								});
								setAuth(response.auth_info);
								setJoinLink(response.join_link_info);
								navigateToDialog("auth_email_login");
							} else if (availableAuthMethodList.isAuthMethodPhoneNumberEnabled()) {
								const response = await apiAuthPhoneNumberBegin.mutateAsync({
									phone_number: editedPhoneNumberOrEmail,
									grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
								});
								setAuth(response.auth_info);
								setJoinLink(response.join_link_info);
								navigateToDialog("auth_phone_number_confirm_code");
							} else {
								setIsError(true);
								if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
									showToast(langStringErrorsPhoneNumberEmailIncorrectPhoneEmailError, "warning");
								} else if (availableAuthMethodList.isAuthMethodMailEnabled()) {
									showToast(langStringErrorsEmailIncorrectEmailError, "warning");
								} else {
									showToast(langStringErrorsPhoneNumberIncorrectPhoneError, "warning");
								}
								return;
							}
						} catch (error) {
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
								if (
									error.error_code === INCORRECT_LINK_ERROR_CODE ||
									error.error_code === INACTIVE_LINK_ERROR_CODE
								) {
									setIsError(true);
									setPrepareJoinLinkError({ error_code: INACTIVE_LINK_ERROR_CODE });
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
										// @ts-ignore
										if (grecaptcha.enterprise.reset !== undefined) {
											// @ts-ignore
											grecaptcha.enterprise.reset();
										}
									}
									return;
								}

								if (error.error_code === LIMIT_ERROR_CODE) {
									if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
										showToast(
											langStringErrorsPhoneNumberEmailLimitError.replace(
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

									if (availableAuthMethodList.isAuthMethodMailEnabled()) {
										showToast(
											langStringErrorsEmailLimitError.replace(
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

									if (availableAuthMethodList.isAuthMethodPhoneNumberEnabled()) {
										showToast(
											langStringErrorsPhoneNumberLimitError.replace(
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
									return;
								}

								if (error.error_code === 1708399) {
									if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
										showToast(
											langStringErrorsPhoneNumberEmailLimitError.replace(
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

									if (availableAuthMethodList.isAuthMethodMailEnabled()) {
										showToast(
											langStringErrorsEmailLimitError.replace(
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

									if (availableAuthMethodList.isAuthMethodPhoneNumberEnabled()) {
										showToast(
											langStringErrorsPhoneNumberLimitError.replace(
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
									return;
								}

								setIsError(true);
								if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
									showToast(langStringErrorsPhoneNumberEmailIncorrectPhoneEmailError, "warning");
								} else if (availableAuthMethodList.isAuthMethodMailEnabled()) {
									showToast(langStringErrorsEmailIncorrectEmailError, "warning");
								} else {
									showToast(langStringErrorsPhoneNumberIncorrectPhoneError, "warning");
								}
							}
						}
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
							// @ts-ignore
							if (grecaptcha.enterprise.reset !== undefined) {
								// @ts-ignore
								grecaptcha.enterprise.reset();
							}
						}
						return;
					}

					if (error.error_code === LIMIT_ERROR_CODE) {
						if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
							showToast(
								langStringErrorsPhoneNumberEmailLimitError.replace(
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

						if (availableAuthMethodList.isAuthMethodMailEnabled()) {
							showToast(
								langStringErrorsEmailLimitError.replace(
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

						if (availableAuthMethodList.isAuthMethodPhoneNumberEnabled()) {
							showToast(
								langStringErrorsPhoneNumberLimitError.replace(
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
						return;
					}

					if (error.error_code === 1708399) {
						if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
							showToast(
								langStringErrorsPhoneNumberEmailLimitError.replace(
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

						if (availableAuthMethodList.isAuthMethodMailEnabled()) {
							showToast(
								langStringErrorsEmailLimitError.replace(
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

						if (availableAuthMethodList.isAuthMethodPhoneNumberEnabled()) {
							showToast(
								langStringErrorsPhoneNumberLimitError.replace(
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
						return;
					}

					setIsError(true);
					if (availableAuthMethodList.isAuthMethodPhoneNumberMailEnabled()) {
						showToast(langStringErrorsPhoneNumberEmailIncorrectPhoneEmailError, "warning");
					} else if (availableAuthMethodList.isAuthMethodMailEnabled()) {
						showToast(langStringErrorsEmailIncorrectEmailError, "warning");
					} else {
						showToast(langStringErrorsPhoneNumberIncorrectPhoneError, "warning");
					}
				}
			}
		},
		[apiAuthPhoneNumberBegin, apiAuthMailBegin, navigateToDialog, navigateToPage, setAuth, prepareJoinLinkError]
	);

	if (isMobile) {
		return (
			<EmailPhoneNumberDialogContentMobile
				onAuthBeginClickHandler={onAuthBeginClickHandler}
				authInput={authInputValue}
				setAuthInput={setAuthInputValue}
				isLoading={apiAuthPhoneNumberBegin.isLoading || apiAuthMailBegin.isLoading}
				setIsError={setIsError}
				isError={isError}
				inputRef={inputRef}
				showCaptchaState={showCaptchaState}
				setShowCaptchaState={setShowCaptchaState}
				setGrecaptchaResponse={setGrecaptchaResponse}
			/>
		);
	}

	return (
		<EmailPhoneNumberDialogContentDesktop
			onAuthBeginClickHandler={onAuthBeginClickHandler}
			authInput={authInputValue}
			setAuthInput={setAuthInputValue}
			isLoading={apiAuthPhoneNumberBegin.isLoading || apiAuthMailBegin.isLoading}
			setIsError={setIsError}
			isError={isError}
			inputRef={inputRef}
			showCaptchaState={showCaptchaState}
			setShowCaptchaState={setShowCaptchaState}
			setGrecaptchaResponse={setGrecaptchaResponse}
		/>
	);
};

export default EmailPhoneNumberDialogContent;