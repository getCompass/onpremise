import { Box, Center, HStack, styled, VStack } from "../../styled-system/jsx";
import IconLogo from "../components/IconLogo.tsx";
import { Text } from "../components/text.tsx";
import { Button } from "../components/button.tsx";
import { useLangString } from "../lib/getLangString.ts";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Portal } from "@ark-ui/react";
import { useAtomValue, useSetAtom } from "jotai";
import {
	authenticationTokenTimeLeftState,
	authInputState,
	authState,
	confirmPasswordState,
	deviceLoginTypeState, isGuestAuthState,
	isPasswordChangedState,
	isRegistrationState,
	joinLinkState,
	nameInputState,
	passwordInputState,
	prepareJoinLinkErrorState,
	useToastConfig,
} from "../api/_stores.ts";
import { useApiAuthGenerateToken, useApiAuthLogout } from "../api/auth.ts";
import { NetworkError, ServerError } from "../api/_index.ts";
import {
	Dialog,
	DialogBackdrop,
	DialogCloseTrigger,
	DialogContainer,
	DialogContent,
	DialogTrigger,
	generateDialogId,
} from "../components/dialog.tsx";
import { copyToClipboard } from "../lib/copyToClipboard.ts";
import { useApiJoinLinkAccept } from "../api/joinlink.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import Toast, { useShowToast } from "../lib/Toast.tsx";
import Preloader16 from "../components/Preloader16.tsx";
import { useAtom } from "jotai/index";
import { DynamicTimerAuthenticationToken } from "../components/DynamicTimerAuthenticationToken.tsx";
import DownloadMenu from "../components/desktop/DownloadMenu.tsx";

type StepTwoContentProps = {
	childButtonWidth: number;
	scrollableParentBlockRef: any;
	apiAuthGenerateToken: any;
	joinLink: any;
};

const StepTwoContent = ({
	childButtonWidth,
	scrollableParentBlockRef,
	apiAuthGenerateToken,
	joinLink
}: StepTwoContentProps) => {
	const langStringPageTokenStep2DescPt1Desktop = useLangString("page_token.step_2.desc_pt1_desktop");
	const langStringPageTokenStep2DescPt2Desktop = useLangString("page_token.step_2.desc_pt2_desktop");
	const langStringPageTokenStep2ButtonDesktop = useLangString("page_token.step_2.button_desktop");
	const loginType = useAtomValue(deviceLoginTypeState);
	const timeLeft = useAtomValue(authenticationTokenTimeLeftState);

	// копируем код только при закрытии поповера, чтобы избежать смены фокуса и закрытия раньше времени при копировании
	const onCopyCode = useCallback(() => {

		copyToClipboard(
			apiAuthGenerateToken.data.authentication_token,
			scrollableParentBlockRef.current,
			false
		);
	}, [ apiAuthGenerateToken, scrollableParentBlockRef.current ])

	// при открытии поповера обновляем код, если он заэкспайрился
	const onInstallClickHandler = useCallback(async () => {

		if (timeLeft <= 0) {
			await apiAuthGenerateToken.mutateAsync({
				join_link_uniq: joinLink === null ? undefined : joinLink.join_link_uniq,
				login_type: loginType === 0 ? undefined : loginType
			});
			return;
		}
	}, [ onCopyCode, joinLink, loginType, apiAuthGenerateToken, scrollableParentBlockRef.current, timeLeft ]);

	return (
		<HStack w = "100%" gap = "16px" justify = "space-between">
			<HStack gap = "12px" w = "100%">
				<Text
					w = "35px"
					h = "35px"
					bgColor = "05c46b"
					color = "white"
					fs = "20"
					lh = "24"
					rounded = "100%"
					flexShrink = "0"
					textAlign = "Center"
					pt = "5px"
					font = "regular"
				>
					2
				</Text>
				<Text color = "white" fs = "14" lh = "18" font = "regular" ls = "-015">
					<styled.span fontFamily = "lato_bold" fontWeight = "normal">
						{langStringPageTokenStep2DescPt1Desktop}
					</styled.span>
					<br />
					{langStringPageTokenStep2DescPt2Desktop}
				</Text>
			</HStack>
			<DownloadMenu
				onOpenFunction = {onInstallClickHandler}
				onCloseFunction = {onCopyCode}
				triggerEl = {
					<Button
						size = "px21py6"
						textSize = "xl_desktop"
						color = "05c46b"
						style = {{
							minWidth: `${childButtonWidth}px`,
						}}
					>
						{langStringPageTokenStep2ButtonDesktop}
					</Button>
				} />
		</HStack>
	);
};

type StepOneContentProps = {
	scrollableParentBlockRef: any;
	parentButtonRef: any;
	apiAuthGenerateToken: any;
	joinLink: any;
};

const StepOneContent = ({
	scrollableParentBlockRef,
	parentButtonRef,
	apiAuthGenerateToken,
	joinLink
}: StepOneContentProps) => {
	const langStringPageTokenStep1RegisterDescPt1 = useLangString("page_token.step_1.register_desc_pt1");
	const langStringPageTokenStep1RegisterDescPt2 = useLangString("page_token.step_1.register_desc_pt2");
	const langStringPageTokenStep1RegisterButton = useLangString("page_token.step_1.register_button");
	const langStringPageTokenStep1LoginDescPt1 = useLangString("page_token.step_1.login_desc_pt1");
	const langStringPageTokenStep1LoginDescPt2Desktop = useLangString("page_token.step_1.login_desc_pt2_desktop");
	const langStringPageTokenStep1LoginButton = useLangString("page_token.step_1.login_button");

	const tokenBoxRef = useRef<HTMLDivElement>(null);
	const isRegistration = useAtomValue(isRegistrationState);
	const timeLeft = useAtomValue(authenticationTokenTimeLeftState);
	const isAuthenticationTokenExpired = useMemo(() => timeLeft <= 0, [ timeLeft ]);

	const loginType = useAtomValue(deviceLoginTypeState);

	const onRegistrationClickHandler = useCallback(() => {
		if (!apiAuthGenerateToken.data || !tokenBoxRef.current) {
			return;
		}
		copyToClipboard(
			apiAuthGenerateToken.data.authentication_token,
			scrollableParentBlockRef.current,
			tokenBoxRef.current
		);
	}, [ apiAuthGenerateToken, scrollableParentBlockRef.current, tokenBoxRef.current ]);

	const onLoginClickHandler = useCallback(() => {

		copyToClipboard(
			apiAuthGenerateToken.data.authentication_token,
			scrollableParentBlockRef.current,
			false
		);
		window.location.replace(`getcompassonpremise://`);
	}, [ apiAuthGenerateToken, scrollableParentBlockRef.current ]);

	useEffect(() => {
		apiAuthGenerateToken.mutate({
			join_link_uniq: joinLink === null ? undefined : joinLink.join_link_uniq,
			login_type: loginType === 0 ? undefined : loginType
		});
	}, []);

	return (
		<VStack gap = "0px">
			<HStack w = "100%" gap = "16px" justify = "space-between">
				<HStack gap = "12px">
					<Text
						w = "35px"
						h = "35px"
						bgColor = "007aff"
						color = "white"
						fs = "20"
						lh = "24"
						rounded = "100%"
						flexShrink = "0"
						textAlign = "Center"
						pt = "5px"
						font = "regular"
					>
						1
					</Text>
					<Text color = "white" fs = "14" lh = "18" font = "regular" ls = "-015">
						<styled.span fontFamily = "lato_bold" fontWeight = "normal">
							{isRegistration
								? langStringPageTokenStep1RegisterDescPt1
								: langStringPageTokenStep1LoginDescPt1}
						</styled.span>
						<br />
						{isRegistration
							? langStringPageTokenStep1RegisterDescPt2
							: langStringPageTokenStep1LoginDescPt2Desktop}
					</Text>
				</HStack>
				<Button
					size = "px20py6"
					textSize = "xl_desktop"
					disabled = {isAuthenticationTokenExpired}
					ref = {parentButtonRef}
					onClick = {() => isRegistration ? onRegistrationClickHandler() : onLoginClickHandler()}
				>
					{isRegistration ? langStringPageTokenStep1RegisterButton : langStringPageTokenStep1LoginButton}
				</Button>
			</HStack>
			{apiAuthGenerateToken.isLoading || !apiAuthGenerateToken.data ? (
				<>
					<VStack
						w = "100%"
						minWidth = "528px"
						bgColor = "000000.01"
						rounded = "8px"
						px = "12px"
						py = "8px"
						gap = "4px"
						alignItems = "start"
						mt = "16px"
					>
						<Box w = "100%" h = "18px" bgColor = "434455" rounded = "3px" />
						<Box w = "48%" h = "18px" bgColor = "434455" rounded = "3px" />
					</VStack>
					<Box w = "248px" h = "20px" mt = "12px" bgColor = "000000.01" rounded = "3px" />
				</>
			) : (
				<VStack w = "100%" rounded = "8px" gap = "0px" alignItems = "start">
					<Box
						ref = {tokenBoxRef}
						w = "100%"
						bgColor = "000000.01"
						rounded = "8px"
						px = "12px"
						py = "8px"
						cursor = {!isAuthenticationTokenExpired ? "pointer" : "inherit"}
						mt = "16px"
						onClick = {() => {
							if (tokenBoxRef.current && !isAuthenticationTokenExpired) {
								copyToClipboard(
									apiAuthGenerateToken.data.authentication_token,
									scrollableParentBlockRef.current,
									tokenBoxRef.current
								);
							}
						}}
					>
						<Text
							overflow = "breakWord"
							color = "f8f8f8"
							opacity = {!isAuthenticationTokenExpired ? "50%" : "10%"}
							fs = "13"
							lh = "20"
							font = "regular"
						>
							{apiAuthGenerateToken.data.authentication_token.substring(0, 120)}
						</Text>
					</Box>
					<DynamicTimerAuthenticationToken
						key = "desktop_dynamic_timer"
						apiAuthGenerateToken = {apiAuthGenerateToken}
					/>
				</VStack>
			)}
		</VStack>
	);
};

const PageTokenDesktop = () => {
	const langStringLogoutDialogTitle = useLangString("logout_dialog.title");
	const langStringLogoutDialogDesc = useLangString("logout_dialog.desc");
	const langStringLogoutDialogCancelButton = useLangString("logout_dialog.cancel_button");
	const langStringLogoutDialogConfirmButton = useLangString("logout_dialog.confirm_button");
	const langStringPageTokenTitle = useLangString("page_token.title");
	const langStringPageTokenDesc = useLangString("page_token.desc");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringCreateNewPasswordDialogSuccessTooltipMessage = useLangString(
		"create_new_password_dialog.success_tooltip_message"
	);

	const isJoinLink = useIsJoinLink();
	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const setNameInput = useSetAtom(nameInputState);
	const setIsGuestAuth = useSetAtom(isGuestAuthState);
	const setAuth = useSetAtom(authState);
	const [ isPasswordChanged, setIsPasswordChanged ] = useAtom(isPasswordChangedState);
	const apiAuthLogout = useApiAuthLogout();
	const dialogId = useMemo(() => generateDialogId(), []);
	const toastConfig = useToastConfig(dialogId);
	const pageToastConfig = useToastConfig("page_token");
	const showToast = useShowToast(dialogId);
	const pageShowToast = useShowToast("page_token");

	const apiAuthGenerateToken = useApiAuthGenerateToken();

	const apiJoinLinkAccept = useApiJoinLinkAccept();
	useEffect(() => {
		// если это не join ссылка
		if (!isJoinLink) {
			return;
		}

		// если нет инфы по ссылке
		if (joinLink === null || prepareJoinLinkError !== null) {
			return;
		}

		// если уже отправили заявку на постмодерацию - повторно не кидаем
		if (joinLink.is_waiting_for_postmoderation === 1) {
			return;
		}

		// если уже совершаем запрос
		if (apiJoinLinkAccept.isLoading) {
			return;
		}

		apiJoinLinkAccept.mutateAsync({ join_link_uniq: joinLink.join_link_uniq });
	}, []);

	// очищаем из local storage
	useEffect(() => {
		setAuthInput("");
		setPasswordInput("");
		setConfirmPassword("");
		setNameInput("");
		setAuth(null);
		setIsGuestAuth(false);
	}, []);

	useEffect(() => {
		if (!isPasswordChanged) {
			return;
		}

		pageShowToast(langStringCreateNewPasswordDialogSuccessTooltipMessage, "success");
		setIsPasswordChanged(false);
	}, [ isPasswordChanged ]);

	const scrollableParentBlockRef = useRef(null);
	const parentButtonRef = useRef(null);
	const [ childButtonWidth, setChildButtonWidth ] = useState(0);

	useEffect(() => {
		if (parentButtonRef.current) {
			const { offsetWidth } = parentButtonRef.current;
			setChildButtonWidth(offsetWidth);
		}
	}, [ parentButtonRef.current ]);

	const onLogoutClickHandler = useCallback(async () => {
		if (apiAuthLogout.isLoading) {
			return;
		}

		try {
			await apiAuthLogout.mutateAsync();
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
	}, [ apiAuthLogout ]);

	return (
		<VStack
			ref = {scrollableParentBlockRef}
			gap = "0px"
			maxWidth = "100vw"
			width = "100%"
			maxHeight = "100vh"
			h = "100%"
			className = "invisible-scrollbar"
			position = "relative"
		>
			<HStack w = "100%" justify = "end" position = "absolute" top = "0px" pt = "32px" px = "40px" gap = "12px">
				{/*<LangMenuSelectorDesktop/>*/}
				<Dialog style = "desktop" size = "small" backdrop = "opacity50">
					<DialogTrigger asChild>
						<Box
							bgColor = "000000.005"
							py = "7px"
							pl = "8px"
							pr = "6px"
							rounded = "100%"
							cursor = "pointer"
							_hover = {{
								bgColor: "000000.005.hover",
								opacity: "100%",
							}}
						>
							<svg
								width = "18"
								height = "18"
								viewBox = "0 0 18 18"
								fill = "none"
								xmlns = "http://www.w3.org/2000/svg"
							>
								<g opacity = "0.3">
									<path
										d = "M5.0625 1.5C3.92341 1.5 3 2.42341 3 3.5625V14.4375C3 15.5766 3.92341 16.5 5.0625 16.5H10.3125C11.4516 16.5 12.375 15.5766 12.375 14.4375V12.4688C12.375 12.1581 12.1232 11.9062 11.8125 11.9062C11.5018 11.9062 11.25 12.1581 11.25 12.4688V14.4375C11.25 14.9553 10.8303 15.375 10.3125 15.375H5.0625C4.54473 15.375 4.125 14.9553 4.125 14.4375V3.5625C4.125 3.04473 4.54473 2.625 5.0625 2.625H10.3125C10.8303 2.625 11.25 3.04473 11.25 3.5625V5.53125C11.25 5.84191 11.5018 6.09375 11.8125 6.09375C12.1232 6.09375 12.375 5.84191 12.375 5.53125V3.5625C12.375 2.42341 11.4516 1.5 10.3125 1.5H5.0625Z"
										fill = "#A4A4A5"
									/>
									<path
										d = "M13.8523 6.72725C14.0719 6.50758 14.4281 6.50758 14.6477 6.72725L16.5227 8.60226C16.6282 8.70775 16.6875 8.85082 16.6875 9.00001C16.6875 9.14919 16.6282 9.29227 16.5227 9.39776L14.6477 11.2727C14.4281 11.4924 14.0719 11.4924 13.8523 11.2727C13.6326 11.0531 13.6326 10.6969 13.8523 10.4773L14.767 9.56251H9.1875C8.87684 9.56251 8.625 9.31067 8.625 9.00001C8.625 8.68935 8.87684 8.43751 9.1875 8.43751H14.767L13.8523 7.52275C13.6326 7.30308 13.6326 6.94692 13.8523 6.72725Z"
										fill = "#A4A4A5"
									/>
								</g>
							</svg>
						</Box>
					</DialogTrigger>
					<Portal>
						<DialogBackdrop />
						<DialogContainer>
							<DialogContent overflow = "hidden" lazyMount unmountOnExit>
								<Toast toastConfig = {toastConfig} />
								<VStack mt = "16px" gap = "24px">
									<VStack gap = "16px" px = "4px">
										<Box w = "80px" h = "80px">
											<svg
												width = "80"
												height = "80"
												viewBox = "0 0 80 80"
												fill = "none"
												xmlns = "http://www.w3.org/2000/svg"
											>
												<path
													d = "M22.4997 6.66669C17.4371 6.66669 13.333 10.7707 13.333 15.8334V64.1667C13.333 69.2293 17.4371 73.3334 22.4997 73.3334H45.833C50.8956 73.3334 54.9997 69.2293 54.9997 64.1667V55.4167C54.9997 54.036 53.8804 52.9167 52.4997 52.9167C51.119 52.9167 49.9997 54.036 49.9997 55.4167V64.1667C49.9997 66.4679 48.1342 68.3334 45.833 68.3334H22.4997C20.1985 68.3334 18.333 66.4679 18.333 64.1667V15.8334C18.333 13.5322 20.1985 11.6667 22.4997 11.6667H45.833C48.1342 11.6667 49.9997 13.5322 49.9997 15.8334V24.5834C49.9997 25.9641 51.119 27.0834 52.4997 27.0834C53.8804 27.0834 54.9997 25.9641 54.9997 24.5834V15.8334C54.9997 10.7707 50.8956 6.66669 45.833 6.66669H22.4997Z"
													fill = "#B4B4B4"
												/>
												<path
													d = "M61.5652 29.8989C62.5416 28.9226 64.1245 28.9226 65.1008 29.8989L73.4341 38.2323C73.9029 38.7011 74.1663 39.337 74.1663 40.0001C74.1663 40.6631 73.9029 41.299 73.4341 41.7678L65.1008 50.1011C64.1245 51.0774 62.5415 51.0774 61.5652 50.1011C60.5889 49.1248 60.5889 47.5419 61.5652 46.5656L65.6308 42.5H40.833C39.4523 42.5 38.333 41.3808 38.333 40C38.333 38.6193 39.4523 37.5001 40.833 37.5001H65.6308L61.5652 33.4344C60.5889 32.4581 60.5889 30.8752 61.5652 29.8989Z"
													fill = "#B4B4B4"
												/>
											</svg>
										</Box>
										<VStack gap = "6px">
											<Text font = "bold900" ls = "-02" fs = "18" lh = "24" color = "333e49">
												{langStringLogoutDialogTitle}
											</Text>
											<Text fs = "14" lh = "20" color = "333e49" textAlign = "center"
												  font = "regular">
												{langStringLogoutDialogDesc}
											</Text>
										</VStack>
									</VStack>
									<HStack w = "100%" justify = "space-between">
										<DialogCloseTrigger asChild>
											<Button color = "f5f5f5" size = "px16py6" textSize = "xl_desktop">
												{langStringLogoutDialogCancelButton}
											</Button>
										</DialogCloseTrigger>
										<Button
											color = "ff6a64"
											size = "px16py6"
											textSize = "xl_desktop"
											minW = "102px"
											onClick = {() => onLogoutClickHandler()}
										>
											{apiAuthLogout.isLoading ? (
												<Box py = "3.5px">
													<Preloader16 />
												</Box>
											) : (
												<HStack gap = "4px">
													<Box>{langStringLogoutDialogConfirmButton}</Box>
													<Box w = "20px" h = "21px">
														<svg
															width = "20"
															height = "21"
															viewBox = "0 0 20 21"
															fill = "none"
															xmlns = "http://www.w3.org/2000/svg"
														>
															<path
																d = "M5.62467 2.66663C4.35902 2.66663 3.33301 3.69264 3.33301 4.95829V17.0416C3.33301 18.3073 4.35902 19.3333 5.62467 19.3333H11.458C12.7237 19.3333 13.7497 18.3073 13.7497 17.0416V14.8541C13.7497 14.5089 13.4699 14.2291 13.1247 14.2291C12.7795 14.2291 12.4997 14.5089 12.4997 14.8541V17.0416C12.4997 17.6169 12.0333 18.0833 11.458 18.0833H5.62467C5.04938 18.0833 4.58301 17.6169 4.58301 17.0416V4.95829C4.58301 4.383 5.04938 3.91663 5.62467 3.91663H11.458C12.0333 3.91663 12.4997 4.383 12.4997 4.95829V7.14579C12.4997 7.49097 12.7795 7.77079 13.1247 7.77079C13.4699 7.77079 13.7497 7.49097 13.7497 7.14579V4.95829C13.7497 3.69264 12.7237 2.66663 11.458 2.66663H5.62467Z"
																fill = "white"
															/>
															<path
																d = "M15.3911 8.47468C15.6351 8.23061 16.0309 8.23061 16.2749 8.47469L18.3583 10.558C18.4755 10.6752 18.5413 10.8342 18.5413 11C18.5413 11.1657 18.4755 11.3247 18.3583 11.4419L16.2749 13.5252C16.0309 13.7693 15.6351 13.7693 15.3911 13.5252C15.147 13.2812 15.147 12.8854 15.3911 12.6413L16.4075 11.625H10.208C9.86283 11.625 9.58301 11.3451 9.58301 11C9.58301 10.6548 9.86283 10.375 10.208 10.375H16.4075L15.3911 9.35857C15.147 9.11449 15.147 8.71876 15.3911 8.47468Z"
																fill = "white"
															/>
														</svg>
													</Box>
												</HStack>
											)}
										</Button>
									</HStack>
								</VStack>
							</DialogContent>
						</DialogContainer>
					</Portal>
				</Dialog>
			</HStack>
			<Toast toastConfig = {pageToastConfig} />
			<Center gap = "8px" maxWidth = "560px" h = "100vh" className = "invisible-scrollbar">
				<VStack w = "100%" gap = "24px" userSelect = "none">
					<VStack w = "100%" gap = "16px">
						<IconLogo />
						<VStack w = "100%" alignItems = "center" gap = "6px">
							<Text w = "100%" textAlign = "center" fs = "18" lh = "24" color = "white" font = "bold900"
								  ls = "-02">
								{langStringPageTokenTitle}
							</Text>
							<Text w = "100%" textAlign = "center" fs = "14" lh = "20" color = "white" font = "regular">
								{langStringPageTokenDesc}
							</Text>
						</VStack>
					</VStack>
					<VStack w = "100%" gap = "16px">
						<Box w = "100%" bgColor = "434455" px = "16px" pb = "16px" pt = "15px" rounded = "12px">
							<StepOneContent
								scrollableParentBlockRef = {scrollableParentBlockRef}
								parentButtonRef = {parentButtonRef}
								apiAuthGenerateToken = {apiAuthGenerateToken}
								joinLink = {joinLink}
							/>
						</Box>
						<Box w = "100%" bgColor = "434455" px = "16px" pb = "16px" pt = "15px" rounded = "12px">
							<StepTwoContent
								childButtonWidth = {childButtonWidth}
								scrollableParentBlockRef = {scrollableParentBlockRef}
								apiAuthGenerateToken = {apiAuthGenerateToken}
								joinLink = {joinLink}
							/>
						</Box>
					</VStack>
				</VStack>
			</Center>
		</VStack>
	);
};

export default PageTokenDesktop;
