import {Box, HStack, VStack} from "../../../styled-system/jsx";
import {Input} from "../../components/input.tsx";
import {Button} from "../../components/button.tsx";
import {Text} from "../../components/text.tsx";
import {useLangString} from "../../lib/getLangString.ts";
import {useCallback, useEffect, useMemo, useRef, useState} from "react";
import {useAtom, useSetAtom} from "jotai";
import {
	activeDialogIdState,
	authInputState,
	authState,
	confirmPasswordState,
	nameInputState,
	passwordInputState
} from "../../api/_stores.ts";
import useIsMobile from "../../lib/useIsMobile.ts";
import {
	Dialog,
	DialogBackdrop,
	DialogCloseTrigger,
	DialogContainer,
	DialogContent,
	DialogTrigger
} from "../../components/dialog.tsx";
import {Portal} from "@ark-ui/react";
import {useApiAuthLogout} from "../../api/auth.ts";
import {ApiError, NetworkError, ServerError} from "../../api/_index.ts";
import {useApiProfileSet} from "../../api/profile.ts";
import {useShowToast} from "../../lib/Toast.tsx";
import {useAtomValue} from "jotai";
import {Tooltip, TooltipProvider, TooltipArrow, TooltipContent, TooltipTrigger} from "../../components/tooltip.tsx";
import dayjs from "dayjs";
import {CreateProfileIcon80} from "../../components/CreateProfileIcon80.tsx";
import Preloader16 from "../../components/Preloader16.tsx";

const NextIcon = () => {

	return (
		<Box
			w="20px"
			h="20px"
		>
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M11.6918 7.05824C11.4477 6.81416 11.052 6.81416 10.8079 7.05824C10.5638 7.30232 10.5638 7.69805 10.8079 7.94212L12.241 9.37518H6.24984C5.90466 9.37518 5.62484 9.655 5.62484 10.0002C5.62484 10.3454 5.90466 10.6252 6.24984 10.6252H12.241L10.8079 12.0582C10.5638 12.3023 10.5638 12.698 10.8079 12.9421C11.052 13.1862 11.4477 13.1862 11.6918 12.9421L14.1918 10.4421C14.4359 10.198 14.4359 9.80232 14.1918 9.55824L11.6918 7.05824Z"
					fill="white"/>
				<path fillRule="evenodd" clipRule="evenodd"
					  d="M18.3332 10.0001C18.3332 14.6025 14.6022 18.3334 9.99984 18.3334C5.39746 18.3334 1.6665 14.6025 1.6665 10.0001C1.6665 5.39771 5.39746 1.66675 9.99984 1.66675C14.6022 1.66675 18.3332 5.39771 18.3332 10.0001ZM17.0832 10.0001C17.0832 13.9121 13.9119 17.0834 9.99984 17.0834C6.08782 17.0834 2.9165 13.9121 2.9165 10.0001C2.9165 6.08806 6.08782 2.91675 9.99984 2.91675C13.9119 2.91675 17.0832 6.08806 17.0832 10.0001Z"
					  fill="white"/>
			</svg>
		</Box>
	);
}

const AvatarStub40 = () => {

	return (
		<Box
			w="40px"
			h="40px"
		>
			<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fillRule="evenodd" clipRule="evenodd"
					  d="M20 40C31.0457 40 40 31.0457 40 20C40 8.9543 31.0457 0 20 0C8.9543 0 0 8.9543 0 20C0 31.0457 8.9543 40 20 40Z"
					  fill="url(#paint0_linear_709_15112)"/>
				<path
					d="M24.5002 15C24.5002 17.7614 22.4855 20 20.0002 20C17.5149 20 15.5002 17.7614 15.5002 15C15.5002 12.2386 17.5149 10 20.0002 10C22.4855 10 24.5002 12.2386 24.5002 15Z"
					fill="white" fillOpacity="0.4"/>
				<path
					d="M12.0002 30C11.4479 30 10.9953 29.5511 11.0413 29.0007C11.4195 24.4775 14.4058 21 20.0002 21C25.5946 21 28.5809 24.4775 28.9591 29.0007C29.0051 29.5511 28.5525 30 28.0002 30H12.0002Z"
					fill="white" fillOpacity="0.4"/>
				<defs>
					<linearGradient id="paint0_linear_709_15112" x1="1.10909" y1="2.21819" x2="1.10909" y2="40"
									gradientUnits="userSpaceOnUse">
						<stop stopColor="#45CD80"/>
						<stop offset="1" stopColor="#1C9B42"/>
					</linearGradient>
				</defs>
			</svg>
		</Box>
	);
}

const AvatarStub = () => {

	return (
		<Box
			w="44px"
			h="44px"
		>
			<svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fillRule="evenodd" clipRule="evenodd"
					  d="M22 44C34.1503 44 44 34.1503 44 22C44 9.84974 34.1503 0 22 0C9.84974 0 0 9.84974 0 22C0 34.1503 9.84974 44 22 44Z"
					  fill="url(#paint0_linear_469_13353)"/>
				<path
					d="M26.875 15.5834C26.875 18.575 24.6923 21.0001 22 21.0001C19.3076 21.0001 17.125 18.575 17.125 15.5834C17.125 12.5919 19.3076 10.1667 22 10.1667C24.6923 10.1667 26.875 12.5919 26.875 15.5834Z"
					fill="white" fillOpacity="0.4"/>
				<path
					d="M13.3333 31.8334C12.735 31.8334 12.2447 31.3471 12.2945 30.7508C12.7042 25.8507 15.9393 22.0834 22 22.0834C28.0606 22.0834 31.2957 25.8507 31.7054 30.7508C31.7553 31.3471 31.2649 31.8334 30.6666 31.8334H13.3333Z"
					fill="white" fillOpacity="0.4"/>
				<defs>
					<linearGradient id="paint0_linear_469_13353" x1="1.22" y1="2.44" x2="1.22" y2="44"
									gradientUnits="userSpaceOnUse">
						<stop stopColor="#45CD80"/>
						<stop offset="1" stopColor="#1C9B42"/>
					</linearGradient>
				</defs>
			</svg>
		</Box>
	);
}

const CreateProfileDialogContentDesktop = () => {

	const langStringCreateProfileDialogTitle = useLangString("create_profile_dialog.title");
	const langStringCreateProfileDialogDesc = useLangString("create_profile_dialog.desc");
	const langStringCreateProfileDialogInputPlaceholder = useLangString("create_profile_dialog.input_placeholder");
	const langStringCreateProfileDialogCancelButton = useLangString("create_profile_dialog.cancel_button");
	const langStringCreateProfileDialogConfirmButton = useLangString("create_profile_dialog.confirm_button");
	const langStringCreateProfileDialogConfirmCancelDesktopTitle = useLangString("create_profile_dialog.confirm_cancel_desktop.title");
	const langStringCreateProfileDialogConfirmCancelDesktopDesc = useLangString("create_profile_dialog.confirm_cancel_desktop.desc");
	const langStringCreateProfileDialogConfirmCancelDesktopCancelButton = useLangString("create_profile_dialog.confirm_cancel_desktop.cancel_button");
	const langStringCreateProfileDialogConfirmCancelDesktopConfirmButton = useLangString("create_profile_dialog.confirm_cancel_desktop.confirm_button");
	const langStringCreateProfileDialogIncorrectNameTooltip = useLangString("create_profile_dialog.incorrect_name_tooltip");
	const langStringCreateProfileDialogNotSavedSymbolsTooltip = useLangString("create_profile_dialog.not_saved_symbols_tooltip");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsCreateProfileIncorrectNameError = useLangString("errors.create_profile_incorrect_name_error");

	const [nameValue, setNameValue] = useAtom(nameInputState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);

	const apiAuthLogout = useApiAuthLogout();
	const onLogoutClickHandler = useCallback(async () => {

		try {
			await apiAuthLogout.mutateAsync()
		} catch (error) {

			if (error instanceof NetworkError) {

				showToast(langStringErrorsNetworkError, "warning");
				return;
			}

			if (error instanceof ServerError) {

				showToast(langStringErrorsServerError, "warning");
				return;
			}
		}
	}, [apiAuthLogout]);

	const inputRef = useRef<HTMLInputElement>(null)
	useEffect(() => {

		if (inputRef.current) {
			inputRef.current.focus()
		}
	}, [inputRef]);

	const name = useMemo(() => {

		const [name, expiresAt] = nameValue.split("__|__") || ["", 0]

		if (parseInt(expiresAt) < dayjs().unix()) {
			return "";
		}

		return name;
	}, [nameValue]);

	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState)
	const setConfirmPassword = useSetAtom(confirmPasswordState)
	const setAuth = useSetAtom(authState);
	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const [tooltipText, setTooltipText] = useState("");
	const [tooltipType, setTooltipType] = useState<"default" | "warning">("default");

	const apiProfileSet = useApiProfileSet();
	const onClickHandler = useCallback(async (value: string) => {

		if (apiProfileSet.isLoading) {
			return;
		}

		try {

			await apiProfileSet.mutateAsync({name: value});
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

				if (error.error_code === 1708020) {

					showToast(langStringErrorsCreateProfileIncorrectNameError, "warning");
					return;
				}
			}
		}
	}, [apiProfileSet])

	const renderedTooltipArrow = useMemo(() => {

		if (tooltipType === "warning") {

			return (
				<TooltipArrow width="8px" height="5px" asChild>
					<svg width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00"/>
					</svg>
				</TooltipArrow>
			);
		}

		return (
			<TooltipArrow width="8px" height="5px"/>
		);
	}, [tooltipType]);

	useEffect(() => {

		if (isToolTipVisible) {
			setTimeout(() => setIsToolTipVisible(false), 5000);
		}
	}, [isToolTipVisible]);

	// очищаем из local storage
	useEffect(() => {

		setAuthInput("")
		setPasswordInput("");
		setConfirmPassword("");
		setAuth(null);
	}, []);

	return (
		<VStack
			w="100%"
			gap="22px"
		>
			<VStack
				px="6px"
				mt="20px"
				gap="16px"
				w="100%"
			>
				<CreateProfileIcon80/>
				<VStack
					gap="6px"
					w="100%"
				>
					<Text
						fs="18"
						lh="24"
						font="bold"
						ls="-02"
					>{langStringCreateProfileDialogTitle}</Text>
					<Text
						fs="14"
						lh="20"
						textAlign="center"
						font="regular"
					>{langStringCreateProfileDialogDesc}</Text>
				</VStack>
			</VStack>
			<VStack
				w="100%"
				gap="22px"
			>
				<TooltipProvider>
					<Tooltip
						open={isToolTipVisible}
						onOpenChange={() => null}
						style="desktop"
						type={tooltipType === "default" ? "default_desktop" : "warning_desktop"}
					>
						<VStack w="100%" gap="0px">
							<TooltipTrigger
								style={{
									width: "100%",
									height: "0px",
									opacity: "0%",
								}}
							/>
							<HStack
								w="100%"
								gap="8px"
							>
								<AvatarStub40/>
								<Input
									ref={inputRef}
									type="search"
									autoComplete="nope"
									value={name}
									onChange={(changeEvent) => {

										const value = changeEvent.target.value ?? "";
										if (isNeedShowTooltip) {

											const isTooltipNotSavedSymbolsVisible = /[^а-яА-яёЁa-zA-Z0-9ẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñœ\-' ]/.test(value);
											if (isTooltipNotSavedSymbolsVisible) {

												setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
												setTooltipText(langStringCreateProfileDialogNotSavedSymbolsTooltip);
												setTooltipType("warning");
												if (isTooltipNotSavedSymbolsVisible) {
													setIsNeedShowTooltip(false);
												}
											} else {

												const isToolTipIncorrectNameVisible = /[^а-яА-яёЁ0-9\-' ]/.test(value);
												setIsToolTipVisible(isToolTipIncorrectNameVisible);
												setTooltipText(langStringCreateProfileDialogIncorrectNameTooltip);
												setTooltipType("default");
												if (isToolTipIncorrectNameVisible) {
													setIsNeedShowTooltip(false);
												}
											}
										}
										setNameValue(`${value}__|__${dayjs().unix() + 60 * 10}`);
									}}
									maxLength={40}
									placeholder={langStringCreateProfileDialogInputPlaceholder}
									size="default_desktop"
									onKeyDown={(event: React.KeyboardEvent) => {

										if (event.key === "Enter") {
											onClickHandler(name.replace(/[^а-яА-яёЁa-zA-Z0-9ẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñœ\-' ]/g, "").trim());
										}
									}}
								/>
							</HStack>
						</VStack>
						<Portal>
							<TooltipContent
								onClick={() => setIsToolTipVisible(false)}
								onEscapeKeyDown={() => setIsToolTipVisible(false)}
								onPointerDownOutside={() => setIsToolTipVisible(false)}
								sideOffset={4}
								avoidCollisions={false}
								style={{
									maxWidth: tooltipType === "warning" ? "256px" : "none",
									width: "var(--radix-tooltip-trigger-width)"
								}}
							>
								{renderedTooltipArrow}
								{tooltipText}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				<HStack
					w="100%"
					justify="space-between"
				>
					<Dialog
						style="desktop"
						position="start"
						size="medium"
						backdrop="opacity80"
					>
						<DialogTrigger asChild>
							<Button
								size="px16py6"
								color="f5f5f5"
								textSize="xl_desktop"
							>{langStringCreateProfileDialogCancelButton}</Button>
						</DialogTrigger>
						<Portal>
							<DialogBackdrop/>
							<DialogContainer>
								<DialogContent
									overflow="hidden"
									lazyMount
									unmountOnExit
								>
									<VStack
										w="100%"
										gap="8px"
									>
										<Text
											pt="4px"
											px="4px"
											fs="18"
											lh="24"
											font="bold900"
											ls="-02"
											color="333e49"
											textAlign="start"
											w="100%"
										>{langStringCreateProfileDialogConfirmCancelDesktopTitle}
										</Text>
										<Text
											px="4px"
											fs="15"
											lh="20"
											color="333e49"
											textAlign="start"
											w="100%"
											font="regular"
										>{langStringCreateProfileDialogConfirmCancelDesktopDesc}
										</Text>
										<HStack
											pt="16px"
											w="100%"
											justify="space-between"
										>
											<DialogCloseTrigger asChild>
												<Button
													size="px16py6"
													color="f5f5f5"
													textSize="xl_desktop"
												>
													{langStringCreateProfileDialogConfirmCancelDesktopCancelButton}
												</Button>
											</DialogCloseTrigger>
											<Button
												size="px16py6"
												color="ff6a64"
												textSize="xl_desktop"
												pr="14px"
												onClick={() => onLogoutClickHandler()}
												disabled={apiAuthLogout.isLoading}
											>
												<HStack gap="4px">
													<Box>{langStringCreateProfileDialogConfirmCancelDesktopConfirmButton}</Box>
													<Box w="20px" h="21px">
														<svg width="20" height="21" viewBox="0 0 20 21" fill="none"
															 xmlns="http://www.w3.org/2000/svg">
															<path
																d="M5.62565 2.6665C4.36 2.6665 3.33398 3.69252 3.33398 4.95817V17.0415C3.33398 18.3072 4.36 19.3332 5.62565 19.3332H11.459C12.7246 19.3332 13.7507 18.3072 13.7507 17.0415V14.854C13.7507 14.5088 13.4708 14.229 13.1257 14.229C12.7805 14.229 12.5007 14.5088 12.5007 14.854V17.0415C12.5007 17.6168 12.0343 18.0832 11.459 18.0832H5.62565C5.05035 18.0832 4.58398 17.6168 4.58398 17.0415V4.95817C4.58398 4.38287 5.05035 3.9165 5.62565 3.9165H11.459C12.0343 3.9165 12.5007 4.38287 12.5007 4.95817V7.14567C12.5007 7.49085 12.7805 7.77067 13.1257 7.77067C13.4708 7.77067 13.7507 7.49085 13.7507 7.14567V4.95817C13.7507 3.69252 12.7246 2.6665 11.459 2.6665H5.62565Z"
																fill="white"/>
															<path
																d="M15.3921 8.47448C15.6361 8.2304 16.0319 8.2304 16.2759 8.47448L18.3593 10.5578C18.4765 10.675 18.5423 10.834 18.5423 10.9998C18.5423 11.1655 18.4765 11.3245 18.3593 11.4417L16.2759 13.525C16.0319 13.7691 15.6361 13.7691 15.3921 13.525C15.148 13.281 15.148 12.8852 15.3921 12.6411L16.4085 11.6248H10.209C9.86383 11.6248 9.584 11.3449 9.584 10.9998C9.584 10.6546 9.86383 10.3748 10.209 10.3748H16.4085L15.3921 9.35836C15.148 9.11428 15.148 8.71856 15.3921 8.47448Z"
																fill="white"/>
														</svg>
													</Box>
												</HStack>
											</Button>
										</HStack>
									</VStack>
								</DialogContent>
							</DialogContainer>
						</Portal>
					</Dialog>
					<Button
						size="px16py6"
						disabled={name.trim().length < 1}
						textSize="xl_desktop"
						minW="101px"
						onClick={() => onClickHandler(name.replace(/[^а-яА-яёЁa-zA-Z0-9ẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñœ\-' ]/g, "").trim())}
					>
						{apiProfileSet.isLoading ? (
							<Box py="3.5px"><Preloader16/></Box>
						) : (
							<HStack
								gap="4px"
							>
								<Box>{langStringCreateProfileDialogConfirmButton}</Box>
								<NextIcon/>
							</HStack>
						)}
					</Button>
				</HStack>
			</VStack>
		</VStack>
	);
}

const CreateProfileDialogContentMobile = () => {

	const langStringCreateProfileDialogTitle = useLangString("create_profile_dialog.title");
	const langStringCreateProfileDialogDesc = useLangString("create_profile_dialog.desc");
	const langStringCreateProfileDialogInputPlaceholder = useLangString("create_profile_dialog.input_placeholder");
	const langStringCreateProfileDialogCancelButton = useLangString("create_profile_dialog.cancel_button");
	const langStringCreateProfileDialogConfirmButton = useLangString("create_profile_dialog.confirm_button");
	const langStringCreateProfileDialogConfirmCancelMobileTitle = useLangString("create_profile_dialog.confirm_cancel_mobile.title");
	const langStringCreateProfileDialogConfirmCancelMobileShortTitle = useLangString("create_profile_dialog.confirm_cancel_mobile.short_title");
	const langStringCreateProfileDialogConfirmCancelMobileConfirmButton = useLangString("create_profile_dialog.confirm_cancel_mobile.confirm_button");
	const langStringCreateProfileDialogConfirmCancelMobileCancelButton = useLangString("create_profile_dialog.confirm_cancel_mobile.cancel_button");
	const langStringCreateProfileDialogIncorrectNameTooltip = useLangString("create_profile_dialog.incorrect_name_tooltip");
	const langStringCreateProfileDialogNotSavedSymbolsTooltip = useLangString("create_profile_dialog.not_saved_symbols_tooltip");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsCreateProfileIncorrectNameError = useLangString("errors.create_profile_incorrect_name_error");

	const [nameValue, setNameValue] = useAtom(nameInputState);
	const [isCanceling, setIsCanceling] = useState(false);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);

	const cancelTextRef = useRef<HTMLDivElement>(null);
	const [cancelTextWidth, setCancelTextWidth] = useState(0);

	const inputRef = useRef<HTMLInputElement>(null);
	useEffect(() => {

		if (inputRef.current) {
			inputRef.current.focus()
		}
	}, [inputRef]);

	const name = useMemo(() => {

		const [name, expiresAt] = nameValue.split("__|__") || ["", 0]

		if (parseInt(expiresAt) < dayjs().unix()) {
			return "";
		}

		return name;
	}, [nameValue]);

	const setAuthInput = useSetAtom(authInputState)
	const setPasswordInput = useSetAtom(passwordInputState)
	const setConfirmPassword = useSetAtom(confirmPasswordState)
	const setAuth = useSetAtom(authState);
	const [isNeedShowTooltip, setIsNeedShowTooltip] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisible, setIsToolTipVisible] = useState(false); // видно ли тултип прям сейчас
	const [tooltipText, setTooltipText] = useState("");
	const [tooltipType, setTooltipType] = useState<"default" | "warning">("default");

	const apiProfileSet = useApiProfileSet();
	const onClickHandler = useCallback(async (value: string) => {

		if (apiProfileSet.isLoading) {
			return;
		}

		try {

			await apiProfileSet.mutateAsync({name: value});
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

				if (error.error_code === 1708020) {

					showToast(langStringErrorsCreateProfileIncorrectNameError, "warning");
					return;
				}
			}
		}
	}, [apiProfileSet]);

	// очищаем из local storage
	useEffect(() => {

		setAuthInput("")
		setPasswordInput("");
		setConfirmPassword("");
		setAuth(null)
	}, []);
	const apiAuthLogout = useApiAuthLogout();

	useEffect(() => {

		if (cancelTextRef.current) {

			const currentWidth = cancelTextRef.current.offsetWidth;
			setCancelTextWidth(currentWidth);
		}
	}, [cancelTextRef]);

	const onLogoutClickHandler = useCallback(async () => {

		try {
			await apiAuthLogout.mutateAsync()
		} catch (error) {

			if (error instanceof NetworkError) {

				showToast(langStringErrorsNetworkError, "warning");
				return;
			}

			if (error instanceof ServerError) {

				showToast(langStringErrorsServerError, "warning");
				return;
			}
		}
	}, [apiAuthLogout]);

	const renderedTooltipArrow = useMemo(() => {

		if (tooltipType === "warning") {

			return (
				<TooltipArrow width="8px" height="5px" asChild>
					<svg width="8" height="5" viewBox="0 0 8 5" fill="none"
						 xmlns="http://www.w3.org/2000/svg">
						<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00"/>
					</svg>
				</TooltipArrow>
			);
		}

		return (
			<TooltipArrow width="8px" height="5px"/>
		);
	}, [tooltipType]);

	useEffect(() => {

		if (isToolTipVisible) {
			setTimeout(() => setIsToolTipVisible(false), 5000);
		}
	}, [isToolTipVisible]);

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
				<CreateProfileIcon80/>
				<VStack
					gap="4px"
					w="100%"
				>
					<Text
						fs="20"
						lh="28"
						font="bold"
						ls="-03"
					>{langStringCreateProfileDialogTitle}</Text>
					<Text
						fs="16"
						lh="22"
						textAlign="center"
						font="regular"
					>{langStringCreateProfileDialogDesc}</Text>
				</VStack>
			</VStack>
			<VStack
				w="100%"
				gap="24px"
			>
				<TooltipProvider>
					<Tooltip
						open={isToolTipVisible}
						onOpenChange={() => null}
						style="mobile"
						type={tooltipType === "default" ? "default_mobile" : "warning_mobile"}
					>
						<VStack w="100%" gap="0px">
							<TooltipTrigger
								style={{
									width: "100%",
									height: "0px",
									opacity: "0%",
								}}
							/>
							<HStack
								w="100%"
								gap="8px"
							>
								<AvatarStub/>
								<Input
									ref={inputRef}
									type="search"
									autoComplete="nope"
									value={name}
									onChange={(changeEvent) => {

										const value = changeEvent.target.value ?? "";
										if (isNeedShowTooltip) {

											const isTooltipNotSavedSymbolsVisible = /[^а-яА-яёЁa-zA-Z0-9ẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñœ\-' ]/.test(value);
											if (isTooltipNotSavedSymbolsVisible) {

												setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
												setTooltipText(langStringCreateProfileDialogNotSavedSymbolsTooltip);
												setTooltipType("warning");
												if (isTooltipNotSavedSymbolsVisible) {
													setIsNeedShowTooltip(false);
												}
											} else {

												const isToolTipIncorrectNameVisible = /[^а-яА-яёЁ0-9\-' ]/.test(value);
												setIsToolTipVisible(isToolTipIncorrectNameVisible);
												setTooltipText(langStringCreateProfileDialogIncorrectNameTooltip);
												setTooltipType("default");
												if (isToolTipIncorrectNameVisible) {
													setIsNeedShowTooltip(false);
												}
											}
										}
										setNameValue(`${value}__|__${dayjs().unix() + 60 * 10}`);
									}}
									maxLength={40}
									placeholder={langStringCreateProfileDialogInputPlaceholder}
									onKeyDown={(event: React.KeyboardEvent) => {

										if (event.key === "Enter") {
											onClickHandler(name.replace(/[^а-яА-яёЁa-zA-Z0-9ẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñœ\-' ]/g, "").trim());
										}
									}}
								/>
							</HStack>
						</VStack>
						<Portal>
							<TooltipContent
								onClick={() => setIsToolTipVisible(false)}
								onEscapeKeyDown={() => setIsToolTipVisible(false)}
								onPointerDownOutside={() => setIsToolTipVisible(false)}
								sideOffset={4}
								avoidCollisions={false}
								style={{
									maxWidth: tooltipType === "warning" ? "256px" : "none",
									width: "var(--radix-tooltip-trigger-width)"
								}}
							>
								{renderedTooltipArrow}
								{tooltipText}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				{isCanceling ? (
					<HStack
						w="100%"
						justify="space-between"
					>
						<Text
							font="bold"
							ls="-03"
							fs="16"
							lh="20"
							color="333e49"
							ref={cancelTextRef}
						>{cancelTextWidth < 190 ? langStringCreateProfileDialogConfirmCancelMobileShortTitle : langStringCreateProfileDialogConfirmCancelMobileTitle}</Text>
						<HStack
							gap="12px"
						>
							<Button
								size="px16py9"
								color="ff6a64"
								onClick={() => onLogoutClickHandler()}
								disabled={apiAuthLogout.isLoading}
							>{langStringCreateProfileDialogConfirmCancelMobileConfirmButton}</Button>
							<Button
								size="px16py9"
								color="f5f5f5"
								onClick={() => setIsCanceling(false)}
							>{langStringCreateProfileDialogConfirmCancelMobileCancelButton}</Button>
						</HStack>
					</HStack>
				) : (
					<HStack
						w="100%"
						justify="space-between"
					>
						<Button
							size="px16py9"
							color="f5f5f5"
							onClick={() => setIsCanceling(true)}
						>{langStringCreateProfileDialogCancelButton}</Button>
						<Button
							size="px16py9"
							disabled={name.trim().length < 1}
							minW="107px"
							onClick={() => onClickHandler(name.replace(/[^а-яА-яёЁa-zA-Z0-9ẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñœ\-' ]/g, "").trim())}
						>
							{apiProfileSet.isLoading ? (
								<Box py="5px"><Preloader16/></Box>
							) : (
								<HStack
									gap="4px"
								>
									<Box>{langStringCreateProfileDialogConfirmButton}</Box>
									<NextIcon/>
								</HStack>
							)}
						</Button>
					</HStack>
				)}
			</VStack>
		</VStack>
	);
}

const CreateProfileDialogContent = () => {

	const isMobile = useIsMobile();
	if (isMobile) {
		return <CreateProfileDialogContentMobile/>
	}

	return <CreateProfileDialogContentDesktop/>
}

export default CreateProfileDialogContent;