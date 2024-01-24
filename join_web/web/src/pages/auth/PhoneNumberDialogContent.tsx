import {Box, VStack} from "../../../styled-system/jsx";
import IconLogo from "../../components/IconLogo.tsx";
import {Input} from "../../components/input.tsx";
import {Button} from "../../components/button.tsx";
import {Text} from "../../components/text.tsx";
import {useLangString} from "../../lib/getLangString.ts";
import {
	activeDialogIdState,
	authInputState,
	authState, captchaPublicKeyState,
	confirmCodeState,
	joinLinkState,
	prepareJoinLinkErrorState
} from "../../api/_stores.ts";
import {useAtom, useAtomValue, useSetAtom} from "jotai";
import useIsMobile from "../../lib/useIsMobile.ts";
import {useApiAuthBegin} from "../../api/auth.ts";
import {useCallback, useEffect, useMemo, useRef, useState} from "react";
import {ApiError, NetworkError, ServerError} from "../../api/_index.ts";
import {useNavigateDialog, useNavigatePage} from "../../components/hooks.ts";
import {useShowToast} from "../../lib/Toast.tsx";
import dayjs from "dayjs";
import {plural} from "../../lib/plural.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import {
	ALREADY_MEMBER_ERROR_CODE,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	LIMIT_ERROR_CODE
} from "../../api/_types.ts";
import {Tooltip, TooltipArrow, TooltipContent, TooltipProvider, TooltipTrigger} from "../../components/tooltip.tsx";
import {Portal} from "@ark-ui/react";

type PhoneNumberDialogContentProps = {
	onAuthBeginClickHandler: (value: string) => void,
	authInput: string,
	setAuthInput: (value: string) => void,
	isLoading: boolean,
	setIsError: (value: boolean) => void,
	isError: boolean,
	inputRef: any,
	showCaptchaState: ShowGrecaptchaState,
	setShowCaptchaState: (value: ShowGrecaptchaState) => void,
	setGrecaptchaResponse: (value: string) => void,
}

const PhoneNumberDialogContentDesktop = ({
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
										 }: PhoneNumberDialogContentProps) => {

	const langStringPhoneNumberDialogTitle = useLangString("phone_number_dialog.title");
	const langStringPhoneNumberDialogDesc = useLangString("phone_number_dialog.desc");
	const langStringPhoneNumberDialogInputPlaceholder = useLangString("phone_number_dialog.input_placeholder");
	const langStringPhoneNumberDialogConfirmButton = useLangString("phone_number_dialog.confirm_button");
	const langStringPhoneNumberDialogProhibitedSymbolsTooltip = useLangString("phone_number_dialog.prohibited_symbols_tooltip");

	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);

	useEffect(() => {

		if (isToolTipVisible) {
			setTimeout(() => setIsToolTipVisible(false), 5000);
		}
	}, [isToolTipVisible]);

	const captchaContainerRef = useCallback((node: HTMLDivElement | null) => {

		if (node !== null && showCaptchaState === "need_render") {

			try {

				// @ts-ignore
				grecaptcha.render(node, {
					sitekey: captchaPublicKey,
					callback: function (grecaptchaResponse: string) {
						setGrecaptchaResponse(grecaptchaResponse);
					},
				});
			} catch (error) {
			}

			setShowCaptchaState("rendered");
		}

		if (node !== null && showCaptchaState === "rendered") {

			// @ts-ignore
			grecaptcha.reset();
		}
	}, [showCaptchaState, captchaPublicKey]);

	return (
		<VStack
			w="100%"
			gap="24px"
		>
			<VStack
				px="4px"
				mt="16px"
				gap="20px"
				w="100%"
			>
				<IconLogo/>
				<VStack
					gap="6px"
					w="100%"
				>
					<Text
						fs="18"
						lh="24"
						font="bold"
						ls="-02"
					>{langStringPhoneNumberDialogTitle}</Text>
					<Text
						fs="14"
						lh="20"
						textAlign="center"
					>{langStringPhoneNumberDialogDesc}</Text>
				</VStack>
			</VStack>
			<VStack
				w="100%"
				gap="12px"
			>
				<TooltipProvider>
					<Tooltip
						open={isToolTipVisible}
						onOpenChange={() => null}
						style="desktop"
						type="warning_desktop"
					>
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
								onChange={(changeEvent) => {

									const value = changeEvent.target.value ?? "";
									if (isNeedShowTooltip) {

										const isTooltipNotSavedSymbolsVisible = /[^а-яА-яёЁa-zA-Z0-9@+\-.']/.test(value);
										if (isTooltipNotSavedSymbolsVisible) {

											setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
											if (isTooltipNotSavedSymbolsVisible) {
												setIsNeedShowTooltip(false);
											}
										}
									}

									setAuthInput(value)
									setIsError(false)
								}}
								maxLength={80}
								placeholder={langStringPhoneNumberDialogInputPlaceholder}
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
									width: "var(--radix-tooltip-trigger-width)"
								}}
							>
								<TooltipArrow width="8px" height="5px" asChild>
									<svg width="8" height="5" viewBox="0 0 8 5" fill="none"
										 xmlns="http://www.w3.org/2000/svg">
										<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00"/>
									</svg>
								</TooltipArrow>
								{langStringPhoneNumberDialogProhibitedSymbolsTooltip}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				<Box
					ref={captchaContainerRef}
					id="path_to_captcha"
					style={{
						display: showCaptchaState === "rendered" ? "block" : "none"
					}}
				/>
				<Button
					size="full_desktop"
					textSize="xl_desktop"
					onClick={() => onAuthBeginClickHandler(authInput)}
					disabled={authInput.length < 1}
				>{isLoading ? <Preloader16/> : langStringPhoneNumberDialogConfirmButton}</Button>
			</VStack>
		</VStack>
	);
}

const PhoneNumberDialogContentMobile = ({
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
										}: PhoneNumberDialogContentProps) => {

	const langStringPhoneNumberDialogTitle = useLangString("phone_number_dialog.title");
	const langStringPhoneNumberDialogDesc = useLangString("phone_number_dialog.desc");
	const langStringPhoneNumberDialogInputPlaceholder = useLangString("phone_number_dialog.input_placeholder");
	const langStringPhoneNumberDialogConfirmButton = useLangString("phone_number_dialog.confirm_button");

	const langStringPhoneNumberDialogProhibitedSymbolsTooltip = useLangString("phone_number_dialog.prohibited_symbols_tooltip");

	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);

	useEffect(() => {

		if (isToolTipVisible) {
			setTimeout(() => setIsToolTipVisible(false), 5000);
		}
	}, [isToolTipVisible]);

	const captchaContainerRef = useCallback((node: HTMLDivElement | null) => {

		if (node !== null && showCaptchaState === "need_render") {

			try {

				// @ts-ignore
				grecaptcha.render(node, {
					sitekey: captchaPublicKey,
					callback: function (grecaptchaResponse: string) {
						setGrecaptchaResponse(grecaptchaResponse);
					},
				});
			} catch (error) {
			}

			setShowCaptchaState("rendered");
		}

		if (node !== null && showCaptchaState === "rendered") {

			// @ts-ignore
			grecaptcha.reset();
		}
	}, [showCaptchaState, captchaPublicKey]);

	return (
		<VStack
			w="100%"
			gap="24px"
		>
			<VStack
				mt="16px"
				gap="16px"
				w="100%"
			>
				<IconLogo/>
				<VStack
					gap="4px"
					w="100%"
				>
					<Text
						fs="20"
						lh="28"
						font="bold"
						ls="-03"
					>{langStringPhoneNumberDialogTitle}</Text>
					<Text
						fs="16"
						lh="22"
						textAlign="center"
					>{langStringPhoneNumberDialogDesc}</Text>
				</VStack>
			</VStack>
			<VStack
				w="100%"
				gap={showCaptchaState === "rendered" ? "16px" : "12px"}
			>
				<TooltipProvider>
					<Tooltip
						open={isToolTipVisible}
						onOpenChange={() => null}
						style="mobile"
						type="warning_mobile"
					>
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
								onChange={(changeEvent) => {

									const value = changeEvent.target.value ?? "";
									if (isNeedShowTooltip) {

										const isTooltipNotSavedSymbolsVisible = /[^а-яА-яёЁa-zA-Z0-9@+\-.']/.test(value);
										if (isTooltipNotSavedSymbolsVisible) {

											setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
											if (isTooltipNotSavedSymbolsVisible) {
												setIsNeedShowTooltip(false);
											}
										}
									}

									setAuthInput(value)
									setIsError(false)
								}}
								maxLength={80}
								placeholder={langStringPhoneNumberDialogInputPlaceholder}
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
									width: "var(--radix-tooltip-trigger-width)"
								}}
							>
								<TooltipArrow width="8px" height="5px" asChild>
									<svg width="8" height="5" viewBox="0 0 8 5" fill="none"
										 xmlns="http://www.w3.org/2000/svg">
										<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00"/>
									</svg>
								</TooltipArrow>
								{langStringPhoneNumberDialogProhibitedSymbolsTooltip}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				<Box
					ref={captchaContainerRef}
					id="path_to_captcha"
					style={{
						display: showCaptchaState === "rendered" ? "block" : "none"
					}}
				/>
				<Button
					onClick={() => onAuthBeginClickHandler(authInput)}
					disabled={authInput.length < 1}
				>
					{isLoading ? <Preloader16/> : langStringPhoneNumberDialogConfirmButton}
				</Button>
			</VStack>
		</VStack>
	);
}

type ShowGrecaptchaState =
	| null
	| "need_render"
	| "rendered"

const PhoneNumberDialogContent = () => {

	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsPhoneNumberLimitError = useLangString("errors.phone_number_limit_error");
	const langStringErrorsPhoneNumberIncorrectPhoneError = useLangString("errors.phone_number_incorrect_phone_error");
	const langStringErrorsIncorrectCaptcha = useLangString("errors.incorrect_captcha");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const isMobile = useIsMobile();
	const setConfirmCode = useSetAtom(confirmCodeState);

	const [showCaptchaState, setShowCaptchaState] = useState<ShowGrecaptchaState>(null);
	const [grecaptchaResponse, setGrecaptchaResponse] = useState("");
	const {navigateToDialog} = useNavigateDialog();
	const {navigateToPage} = useNavigatePage();
	const apiAuthBegin = useApiAuthBegin();
	const setAuth = useSetAtom(authState);
	const setJoinLink = useSetAtom(joinLinkState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const [isError, setIsError] = useState(false);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);
	const [authInput, setAuthInput] = useAtom(authInputState);

	const authInputValue = useMemo(() => {

		const [authValue, expiresAt] = authInput.split("__|__") || ["", 0]

		if (parseInt(expiresAt) < dayjs().unix()) {
			return "";
		}

		return authValue;
	}, [authInput]);

	const setAuthInputValue = useCallback((value: string) => {
		setAuthInput(`${value}__|__${dayjs().unix() + 60 * 10}`);
	}, [])

	const inputRef = useRef<HTMLDivElement>(null)
	useEffect(() => {

		if (inputRef.current) {

			inputRef.current.focus();
			setConfirmCode(Array(6).fill(""));
		}
	}, [inputRef]);

	const onAuthBeginClickHandler = useCallback(async (value: string) => {

		if (value.length < 1 || apiAuthBegin.isLoading) {
			return;
		}

		let editedPhoneNumber = value;
		if (editedPhoneNumber.startsWith("89")) {
			editedPhoneNumber = "+79" + editedPhoneNumber.substring(2, editedPhoneNumber.length);
		}

		if (editedPhoneNumber.startsWith("79")) {
			editedPhoneNumber = "+" + editedPhoneNumber.substring(0, editedPhoneNumber.length);
		}
		try {

			const response = await apiAuthBegin.mutateAsync({
				phone_number: editedPhoneNumber,
				join_link: prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE ? window.location.href : undefined,
				grecaptcha_response: grecaptchaResponse.length < 1 ? undefined : grecaptchaResponse,
			});
			setGrecaptchaResponse(""); // сбрасываем
			setAuth(response.auth_info);
			setJoinLink(response.join_link_info);
			navigateToDialog("auth_confirm_code");
		} catch (error) {

			setGrecaptchaResponse(""); // сбрасываем

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

					setIsError(true);
					setPrepareJoinLinkError({error_code: error.error_code});
					return;
				}

				// если сказали что уже участник этой компании - то логиним без передачи joinLink
				if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {

					try {

						setPrepareJoinLinkError({error_code: ALREADY_MEMBER_ERROR_CODE});
						const response = await apiAuthBegin.mutateAsync({
							phone_number: editedPhoneNumber,
						});
						setAuth(response.auth_info);
						setJoinLink(response.join_link_info);
						navigateToDialog("auth_confirm_code");
					} catch (error) {

						setGrecaptchaResponse(""); // сбрасываем

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

								setIsError(true);
								setPrepareJoinLinkError({error_code: INACTIVE_LINK_ERROR_CODE});
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
									grecaptcha.reset();
								}
								return;
							}


							if (error.error_code === LIMIT_ERROR_CODE) {

								showToast(langStringErrorsPhoneNumberLimitError.replace("$MINUTES", `${Math.ceil((error.expires_at - dayjs().unix()) / 60)}${plural(Math.ceil((error.expires_at - dayjs().unix()) / 60), langStringOneMinute, langStringTwoMinutes, langStringFiveMinutes)}`), "warning");
								return;
							}

							if (error.error_code === 1708399) {

								showToast(langStringErrorsPhoneNumberLimitError.replace("$MINUTES", `${Math.ceil((error.next_attempt - dayjs().unix()) / 60)}${plural(Math.ceil((error.next_attempt - dayjs().unix()) / 60), langStringOneMinute, langStringTwoMinutes, langStringFiveMinutes)}`), "warning");
								return;
							}

							setIsError(true);
							showToast(langStringErrorsPhoneNumberIncorrectPhoneError, "warning");
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
						grecaptcha.reset();
					}
					return;
				}

				if (error.error_code === LIMIT_ERROR_CODE) {

					showToast(langStringErrorsPhoneNumberLimitError.replace("$MINUTES", `${Math.ceil((error.expires_at - dayjs().unix()) / 60)}${plural(Math.ceil((error.expires_at - dayjs().unix()) / 60), langStringOneMinute, langStringTwoMinutes, langStringFiveMinutes)}`), "warning");
					return;
				}

				if (error.error_code === 1708399) {

					showToast(langStringErrorsPhoneNumberLimitError.replace("$MINUTES", `${Math.ceil((error.next_attempt - dayjs().unix()) / 60)}${plural(Math.ceil((error.next_attempt - dayjs().unix()) / 60), langStringOneMinute, langStringTwoMinutes, langStringFiveMinutes)}`), "warning");
					return;
				}

				setIsError(true);
				showToast(langStringErrorsPhoneNumberIncorrectPhoneError, "warning");
			}
		}
	}, [apiAuthBegin, navigateToDialog, navigateToPage, setAuth, prepareJoinLinkError]);

	if (isMobile) {
		return <PhoneNumberDialogContentMobile
			onAuthBeginClickHandler={onAuthBeginClickHandler}
			authInput={authInputValue}
			setAuthInput={setAuthInputValue}
			isLoading={apiAuthBegin.isLoading}
			setIsError={setIsError}
			isError={isError}
			inputRef={inputRef}
			showCaptchaState={showCaptchaState}
			setShowCaptchaState={setShowCaptchaState}
			setGrecaptchaResponse={setGrecaptchaResponse}
		/>
	}

	return <PhoneNumberDialogContentDesktop
		onAuthBeginClickHandler={onAuthBeginClickHandler}
		authInput={authInputValue}
		setAuthInput={setAuthInputValue}
		isLoading={apiAuthBegin.isLoading}
		setIsError={setIsError}
		isError={isError}
		inputRef={inputRef}
		showCaptchaState={showCaptchaState}
		setShowCaptchaState={setShowCaptchaState}
		setGrecaptchaResponse={setGrecaptchaResponse}
	/>
}

export default PhoneNumberDialogContent;