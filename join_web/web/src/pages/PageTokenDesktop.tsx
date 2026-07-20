import { Box, Center, HStack, styled, VStack } from "../../styled-system/jsx";
import IconLogo from "../components/IconLogo.tsx";
import { Text } from "../components/text.tsx";
import { Button } from "../components/button.tsx";
import { useLangString } from "../lib/getLangString.ts";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useAtomValue, useSetAtom } from "jotai";
import {
	authenticationTokenTimeLeftState,
	authInputState,
	authState,
	confirmPasswordState,
	deviceLoginTypeState, isGuestAuthState, isLdapChangeMailState,
	isPasswordChangedState,
	isRegistrationState,
	joinLinkState,
	nameInputState,
	passwordInputState,
	prepareJoinLinkErrorState,
	useToastConfig,
} from "../api/_stores.ts";
import { useApiAuthGenerateToken } from "../api/auth.ts";

import { copyToClipboard } from "../lib/copyToClipboard.ts";
import { useApiJoinLinkAccept } from "../api/joinlink.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import Toast, { useShowToast } from "../lib/Toast.tsx";
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
	const langStringPageTokenTitle = useLangString("page_token.title");
	const langStringPageTokenDesc = useLangString("page_token.desc");
	const langStringCreateNewPasswordDialogSuccessTooltipMessage = useLangString(
		"create_new_password_dialog.success_tooltip_message"
	);
	const langStringLdap2FaAddMailDialogChangeMailToastSuccess = useLangString(
		"ldap_2fa_add_mail_dialog.change_mail_toast_success"
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
	const [ isLdapChangeMail, setIsLdapChangeMail ] = useAtom(isLdapChangeMailState);
	const pageToastConfig = useToastConfig("page_token");
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

		if (isLdapChangeMail) {
			pageShowToast(langStringLdap2FaAddMailDialogChangeMailToastSuccess, "success");
		}
		setIsLdapChangeMail(false);
	}, [])

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
