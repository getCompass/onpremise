import { Box, HStack, styled, VStack } from "../../styled-system/jsx";
import { useEffect, useRef } from "react";
import { useSetAtom } from "jotai";
import {
	authInputState,
	authState,
	confirmPasswordState,
	isPasswordChangedState,
	joinLinkState,
	nameInputState,
	passwordInputState,
	prepareJoinLinkErrorState,
	useToastConfig,
} from "../api/_stores.ts";
import { useLangString } from "../lib/getLangString.ts";
import { Text } from "../components/text.tsx";
import { Button } from "../components/button.tsx";
import { useApiAuthGenerateToken } from "../api/auth.ts";
import { copyToClipboard } from "../lib/copyToClipboard.ts";
import { useApiJoinLinkAccept } from "../api/joinlink.ts";
import { useAtomValue } from "jotai";
import LogoutButtonMobile from "../components/LogoutButtonMobile.tsx";
import { ALREADY_MEMBER_ERROR_CODE, PrepareJoinLinkErrorAlreadyMemberData } from "../api/_types.ts";
import { useAtom } from "jotai/index";
import Toast, { useShowToast } from "../lib/Toast.tsx";

type PageInviteMobileProps = {
	headerContent: JSX.Element;
};

const PageInviteMobile = ({ headerContent }: PageInviteMobileProps) => {
	const langStringPageInviteOr = useLangString("page_invite.or");
	const langStringPageInviteOpenCompassMobileTitlePt1 = useLangString("page_invite.open_compass_mobile.title_pt1");
	const langStringPageInviteOpenCompassMobileTitlePt2 = useLangString("page_invite.open_compass_mobile.title_pt2");
	const langStringPageInviteOpenCompassMobileButton = useLangString("page_invite.open_compass_mobile.button");
	const langStringPageInviteOpenCompassWaitPostModerationMobileTitlePt1 = useLangString(
		"page_invite.open_compass_wait_post_moderation_mobile.title_pt1"
	);
	const langStringPageInviteOpenCompassWaitPostModerationMobileTitlePt2 = useLangString(
		"page_invite.open_compass_wait_post_moderation_mobile.title_pt2"
	);
	const langStringPageInviteOpenCompassWaitPostModerationMobileButton = useLangString(
		"page_invite.open_compass_wait_post_moderation_mobile.button"
	);
	const langStringPageInviteCopyTokenMobileTitlePt1 = useLangString("page_invite.copy_token_mobile.title_pt1");
	const langStringPageInviteCopyTokenMobileTitlePt2 = useLangString("page_invite.copy_token_mobile.title_pt2");
	const langStringPageInviteCopyTokenMobileButton = useLangString("page_invite.copy_token_mobile.button");
	const langStringCreateNewPasswordDialogSuccessTooltipMessage = useLangString(
		"create_new_password_dialog.success_tooltip_message"
	);

	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const setNameInput = useSetAtom(nameInputState);
	const setAuth = useSetAtom(authState);
	const [isPasswordChanged, setIsPasswordChanged] = useAtom(isPasswordChangedState);
	const pageToastConfig = useToastConfig("page_invite");
	const showPageToast = useShowToast("page_invite");

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
	}, []);

	useEffect(() => {
		if (!isPasswordChanged) {
			return;
		}

		showPageToast(langStringCreateNewPasswordDialogSuccessTooltipMessage, "success");
		setIsPasswordChanged(false);
	}, [isPasswordChanged]);

	const scrollableParentBlockRef = useRef<HTMLDivElement>(null);
	const tokenBoxRef = useRef<HTMLDivElement>(null);
	const copyButtonRef = useRef<HTMLButtonElement>(null);
	const apiAuthGenerateToken = useApiAuthGenerateToken(joinLink === null ? undefined : joinLink.join_link_uniq);

	// приходится извращаться с этой кнопкой на мобилках, иначе не на всех мобилках работает ховер на этой кнопке
	useEffect(() => {
		if (copyButtonRef.current) {
			copyButtonRef.current.addEventListener(
				"touchstart",
				function () {
					this.style.backgroundColor = "#049a54";
				},
				false
			);

			copyButtonRef.current.addEventListener(
				"touchend",
				function () {
					this.style.backgroundColor = "#05c46b";
				},
				false
			);
		}
	}, [copyButtonRef]);

	return (
		<>
			<Toast toastConfig={pageToastConfig} />
			<VStack
				ref={scrollableParentBlockRef}
				gap="32px"
				py="16px"
				maxWidth="100vw"
				width="100%"
				h="100%"
				className="invisible-scrollbar"
				position="relative"
			>
				<VStack gap="0px" width="100%" alignItems="end" px="16px">
					<LogoutButtonMobile />
					{headerContent}
				</VStack>
				<VStack gap="8px" width="100%">
					<Box w="100%" px="24px">
						<VStack p="16px" gap="16px" bgColor="434455" rounded="8px">
							<Text px="16px" fs="16" lh="22" color="white" textAlign="center" font="regular">
								<styled.span fontFamily="lato_semibold" fontWeight="700">
									{joinLink === null || joinLink.is_waiting_for_postmoderation === 0
										? langStringPageInviteOpenCompassMobileTitlePt1
										: langStringPageInviteOpenCompassWaitPostModerationMobileTitlePt1}
								</styled.span>
								<br />
								{joinLink === null || joinLink.is_waiting_for_postmoderation === 0
									? langStringPageInviteOpenCompassMobileTitlePt2
									: langStringPageInviteOpenCompassWaitPostModerationMobileTitlePt2}
							</Text>
							<Button
								px="16px"
								size="full"
								textSize="xl"
								onClick={() => {
									if (joinLink !== null) {
										window.location.replace(
											`getcompassonpremise://spaceJoin?spaceId=${
												joinLink.company_id
											}&inviterUserId=${joinLink.inviter_user_id}&isPostModeration=${
												joinLink.is_postmoderation === 1
											}&role=${joinLink.role}&isPreviousSpaceMember=${
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
										const prepareJoinLinkErrorData =
											prepareJoinLinkError.data as PrepareJoinLinkErrorAlreadyMemberData;
										window.location.replace(
											`getcompassonpremise://spaceJoin?spaceId=${
												prepareJoinLinkErrorData.company_id
											}&inviterUserId=${
												prepareJoinLinkErrorData.inviter_user_id
											}&isPostModeration=${
												prepareJoinLinkErrorData.is_postmoderation === 1
											}&role=${prepareJoinLinkErrorData.role}&isPreviousSpaceMember=${
												prepareJoinLinkErrorData.was_member_before === 1
											}`
										);
										return;
									}
								}}
							>
								{joinLink === null || joinLink.is_waiting_for_postmoderation === 0
									? langStringPageInviteOpenCompassMobileButton
									: langStringPageInviteOpenCompassWaitPostModerationMobileButton}
							</Button>
						</VStack>
					</Box>
					<HStack w="100%" gap="8px" py="8px">
						<Box w="100%" h="1px" bgColor="4d4e61"></Box>
						<Text color="007aff" fs="13" lh="16" font="regular">
							{langStringPageInviteOr}
						</Text>
						<Box w="100%" h="1px" bgColor="4d4e61"></Box>
					</HStack>
					<Box w="100%" px="24px">
						<VStack p="16px" gap="16px" bgColor="434455" rounded="8px">
							<Text px="16px" fs="16" lh="22" color="white" textAlign="center" font="regular">
								<styled.span fontFamily="lato_semibold" fontWeight="700">
									{langStringPageInviteCopyTokenMobileTitlePt1}
								</styled.span>
								{langStringPageInviteCopyTokenMobileTitlePt2}
							</Text>
							{apiAuthGenerateToken.isLoading ||
							!apiAuthGenerateToken.data ||
							apiJoinLinkAccept.isLoading ? (
								<VStack w="100%" bgColor="000000.01" rounded="8px" px="12px" py="12px" gap="4px">
									<Box w="100%" h="19px" bgColor="434455" rounded="3px" />
									<Box w="100%" h="19px" bgColor="434455" rounded="3px" />
									<Box w="100%" h="19px" bgColor="434455" rounded="3px" />
								</VStack>
							) : (
								<Box
									ref={tokenBoxRef}
									w="100%"
									bgColor="000000.01"
									rounded="8px"
									px="8px"
									py="12px"
									cursor="pointer"
									onClick={() => {
										if (tokenBoxRef.current && scrollableParentBlockRef.current) {
											copyToClipboard(
												apiAuthGenerateToken.data.authentication_token,
												scrollableParentBlockRef.current,
												tokenBoxRef.current
											);
										}
									}}
								>
									<Text
										overflow="breakWord"
										color="f8f8f8"
										opacity="50%"
										fs="16"
										lh="22"
										font="regular"
									>
										{apiAuthGenerateToken.data.authentication_token.substring(0, 80)}
									</Text>
								</Box>
							)}
							<Button
								ref={copyButtonRef}
								px="16px"
								size="full"
								textSize="xl"
								color="05c46b"
								onClick={() => {
									if (
										!apiAuthGenerateToken.data ||
										!scrollableParentBlockRef.current ||
										!tokenBoxRef.current
									) {
										return;
									}

									copyToClipboard(
										apiAuthGenerateToken.data.authentication_token,
										scrollableParentBlockRef.current,
										tokenBoxRef.current
									);
								}}
							>
								{langStringPageInviteCopyTokenMobileButton}
							</Button>
						</VStack>
					</Box>
				</VStack>
			</VStack>
		</>
	);
};

export default PageInviteMobile;
