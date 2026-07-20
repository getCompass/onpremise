import { Box, Center, HStack, styled, VStack } from "../../styled-system/jsx";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useAtomValue, useSetAtom } from "jotai";
import {
	authenticationTokenTimeLeftState,
	authInputState,
	authState,
	confirmPasswordState,
	deviceLoginTypeState, isGuestAuthState, isLdapChangeMailState,
	isPasswordChangedState,
	joinLinkState,
	nameInputState,
	passwordInputState,
	prepareJoinLinkErrorState,
	useToastConfig,
} from "../api/_stores.ts";
import { Text } from "../components/text.tsx";
import { Button } from "../components/button.tsx";
import { useLangString } from "../lib/getLangString.ts";
import { useApiAuthGenerateToken } from "../api/auth.ts";
import { copyToClipboard } from "../lib/copyToClipboard.ts";

import { useApiJoinLinkAccept } from "../api/joinlink.ts";
import { ALREADY_MEMBER_ERROR_CODE, PrepareJoinLinkErrorAlreadyMemberData, } from "../api/_types.ts";
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
	const langStringPageInviteInstallAppDesktopTitlePt1 = useLangString("page_invite.install_app_desktop.title_pt1");
	const langStringPageInviteInstallAppDesktopTitlePt2 = useLangString("page_invite.install_app_desktop.title_pt2");
	const langStringPageInviteInstallAppDesktopButton = useLangString("page_invite.install_app_desktop.button");
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
			<HStack gap = "8px" w = "100%">
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
						{langStringPageInviteInstallAppDesktopTitlePt1}
					</styled.span>
					<br />
					{langStringPageInviteInstallAppDesktopTitlePt2}
				</Text>
			</HStack>
			<DownloadMenu
				onOpenFunction = {onInstallClickHandler}
				onCloseFunction = {onCopyCode}
				triggerEl = {
					<Button
						size = "px16py6"
						textSize = "xl_desktop"
						color = "05c46b"
						style = {{
							minWidth: `${childButtonWidth}px`,
						}}
					>
						{langStringPageInviteInstallAppDesktopButton}
					</Button>
				}
			/>
		</HStack>
	);
};

type StepOneContentProps = {
	scrollableParentBlockRef: any;
	parentButtonRef: any;
	apiAuthGenerateToken: any;
	joinLinkAcceptIsLoading: boolean;
};

const StepOneContent = ({
	scrollableParentBlockRef,
	parentButtonRef,
	apiAuthGenerateToken,
	joinLinkAcceptIsLoading,
}: StepOneContentProps) => {
	const langStringPageInviteOpenCompassDesktopTitlePt1 = useLangString("page_invite.open_compass_desktop.title_pt1");
	const langStringPageInviteOpenCompassDesktopTitlePt2 = useLangString("page_invite.open_compass_desktop.title_pt2");
	const langStringPageInviteOpenCompassDesktopButton = useLangString("page_invite.open_compass_desktop.button");

	const tokenBoxRef = useRef<HTMLDivElement>(null);
	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const timeLeft = useAtomValue(authenticationTokenTimeLeftState);
	const isAuthenticationTokenExpired = useMemo(() => timeLeft <= 0, [ timeLeft ]);
	const loginType = useAtomValue(deviceLoginTypeState);

	useEffect(() => {
		let join_link_uniq = joinLink === null ? undefined : joinLink.join_link_uniq;

		if (
			prepareJoinLinkError !== null &&
			prepareJoinLinkError.error_code === ALREADY_MEMBER_ERROR_CODE &&
			prepareJoinLinkError.data !== undefined
		) {
			const prepareJoinLinkErrorData = prepareJoinLinkError.data as PrepareJoinLinkErrorAlreadyMemberData;

			join_link_uniq = prepareJoinLinkErrorData.join_link_uniq;
		}
		apiAuthGenerateToken.mutate({
			join_link_uniq: join_link_uniq,
			login_type: loginType === 0 ? undefined : loginType
		});
	}, []);

	const onClickHandler = useCallback(() => {

		if (apiAuthGenerateToken.data !== undefined) {
			copyToClipboard(
				apiAuthGenerateToken.data.authentication_token,
				scrollableParentBlockRef.current,
				false
			);
		}
		if (joinLink !== null) {
			window.location.replace(
				`getcompassonpremise://spaceJoin?spaceId=${joinLink.company_id}&inviterUserId=${
					joinLink.inviter_user_id
				}&isPostModeration=${joinLink.is_postmoderation === 1}&role=${joinLink.role}&isPreviousSpaceMember=${
					joinLink.was_member_before === 1
				}`
			);
			return;
		}

		if (
			prepareJoinLinkError !== null &&
			prepareJoinLinkError.error_code === ALREADY_MEMBER_ERROR_CODE &&
			prepareJoinLinkError.data !== undefined
		) {
			const prepareJoinLinkErrorData = prepareJoinLinkError.data as PrepareJoinLinkErrorAlreadyMemberData;
			window.location.replace(
				`getcompassonpremise://spaceJoin?spaceId=${prepareJoinLinkErrorData.company_id}&inviterUserId=${
					prepareJoinLinkErrorData.inviter_user_id
				}&isPostModeration=${prepareJoinLinkErrorData.is_postmoderation === 1}&role=${
					prepareJoinLinkErrorData.role
				}&isPreviousSpaceMember=${prepareJoinLinkErrorData.was_member_before === 1}`
			);
			return;
		}
	}, [ apiAuthGenerateToken, scrollableParentBlockRef, joinLink, prepareJoinLinkError ]);

	return (
		<VStack gap = "0px">
			<HStack w = "100%" gap = "16px" justify = "space-between">
				<HStack gap = "8px">
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
							{langStringPageInviteOpenCompassDesktopTitlePt1}
						</styled.span>
						<br />
						{langStringPageInviteOpenCompassDesktopTitlePt2}
					</Text>
				</HStack>
				<Button
					size = "px16py6"
					disabled = {isAuthenticationTokenExpired}
					textSize = "xl_desktop"
					ref = {parentButtonRef}
					onClick = {() => onClickHandler()}
				>
					{langStringPageInviteOpenCompassDesktopButton}
				</Button>
			</HStack>
			{apiAuthGenerateToken.isLoading || !apiAuthGenerateToken.data || joinLinkAcceptIsLoading ? (
				<VStack
					w = "100%"
					bgColor = "000000.01"
					rounded = "8px"
					px = "12px"
					py = "8px"
					gap = "4px"
					alignItems = "start"
					mt = "16px"
				>
					<Box w = "504px" h = "16px" bgColor = "434455" rounded = "3px" />
					<Box w = "48%" h = "16px" bgColor = "434455" rounded = "3px" />
				</VStack>
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
							if (
								!isAuthenticationTokenExpired &&
								tokenBoxRef.current &&
								scrollableParentBlockRef.current
							) {
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

type PageInviteDesktopProps = {
	headerContent: JSX.Element;
};

const PageInviteDesktop = ({ headerContent }: PageInviteDesktopProps) => {
	const langStringCreateNewPasswordDialogSuccessTooltipMessage = useLangString(
		"create_new_password_dialog.success_tooltip_message"
	);
	const langStringLdap2FaAddMailDialogChangeMailToastSuccess = useLangString(
		"ldap_2fa_add_mail_dialog.change_mail_toast_success"
	);

	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const setNameInput = useSetAtom(nameInputState);
	const setAuth = useSetAtom(authState);
	const setIsGuestAuth = useSetAtom(isGuestAuthState);
	const [ isPasswordChanged, setIsPasswordChanged ] = useAtom(isPasswordChangedState);
	const [ isLdapChangeMail, setIsLdapChangeMail ] = useAtom(isLdapChangeMailState);
	const pageToastConfig = useToastConfig("page_invite");
	const showPageToast = useShowToast("page_invite");

	const apiAuthGenerateToken = useApiAuthGenerateToken();

	const scrollableParentBlockRef = useRef(null);
	const parentButtonRef = useRef(null);
	const [ childButtonWidth, setChildButtonWidth ] = useState(0);

	const apiJoinLinkAccept = useApiJoinLinkAccept();
	useEffect(() => {
		// если нет инфы по ссылке
		if (joinLink === null || prepareJoinLinkError !== null) {
			return;
		}

		// если уже отправили заявку на постмодерацию - повторно не кидаем
		if (joinLink.is_waiting_for_postmoderation === 1) {
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
			showPageToast(langStringLdap2FaAddMailDialogChangeMailToastSuccess, "success");
		}
		setIsLdapChangeMail(false);
	}, [])

	useEffect(() => {
		if (!isPasswordChanged) {
			return;
		}

		showPageToast(langStringCreateNewPasswordDialogSuccessTooltipMessage, "success");
		setIsPasswordChanged(false);
	}, [ isPasswordChanged ]);

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
					{headerContent}
					<VStack gap = "8px" w = "100%">
						<Box w = "100%" p = "16px" gap = "16px" bgColor = "434455" rounded = "12px">
							<StepOneContent
								scrollableParentBlockRef = {scrollableParentBlockRef}
								parentButtonRef = {parentButtonRef}
								apiAuthGenerateToken = {apiAuthGenerateToken}
								joinLinkAcceptIsLoading = {apiJoinLinkAccept.isLoading}
							/>
						</Box>
						<Box w = "100%" p = "16px" gap = "16px" bgColor = "434455" rounded = "8px">
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

export default PageInviteDesktop;
