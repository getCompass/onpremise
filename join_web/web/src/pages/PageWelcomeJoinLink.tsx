import DialogMobile from "../components/DialogMobile.tsx";
import useIsMobile from "../lib/useIsMobile.ts";
import DialogDesktop from "../components/DialogDesktop.tsx";
import {useCallback, useMemo} from "react";
import {useNavigateDialog, useNavigatePage} from "../components/hooks.ts";
import {useLangString} from "../lib/getLangString.ts";
import {useAtomValue} from "jotai";
import {joinLinkState, prepareJoinLinkErrorState, profileState} from "../api/_stores.ts";
import {styled, VStack} from "../../styled-system/jsx";
import IconLogo from "../components/IconLogo.tsx";
import {Text} from "../components/text.tsx";
import {Button} from "../components/button.tsx";
import {ALREADY_MEMBER_ERROR_CODE, PrepareJoinLinkErrorAlreadyMemberData} from "../api/_types.ts";

type WelcomeDialogProps = {
	navigateToNextPage: () => void,
}

const WelcomeDialogDesktop = ({navigateToNextPage}: WelcomeDialogProps) => {

	const langStringWelcomeDialogTitle = useLangString("welcome_dialog.title");
	const langStringWelcomeDialogDescDesktop = useLangString("welcome_dialog.desc_desktop");
	const langStringWelcomeDialogConfirmButton = useLangString("welcome_dialog.confirm_button");

	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const inviterFullName = useMemo(() => {

		if (prepareJoinLinkError !== null && prepareJoinLinkError.error_code === ALREADY_MEMBER_ERROR_CODE) {
			return (prepareJoinLinkError.data as PrepareJoinLinkErrorAlreadyMemberData).inviter_full_name;
		}

		if (joinLink === null) {
			return "";
		}

		return joinLink.inviter_full_name;
	}, [joinLink, prepareJoinLinkError]);

	return (
		<VStack
			gap="24px"
		>
			<VStack
				px="4px"
				mt="16px"
				gap="16px"
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
					>{langStringWelcomeDialogTitle}</Text>
					<Text
						w="100%"
						fs="14"
						lh="20"
						textAlign="center"
					>
						<styled.span
							fontFamily="lato_bold"
							fontWeight="700"
						>{inviterFullName}
						</styled.span>
						{langStringWelcomeDialogDescDesktop}</Text>
				</VStack>
			</VStack>
			<Button
				onClick={() => navigateToNextPage()}
				textSize="xl_desktop"
				size="full_desktop"
			>
				{langStringWelcomeDialogConfirmButton}
			</Button>
		</VStack>
	);
}

const WelcomeDialogMobile = ({navigateToNextPage}: WelcomeDialogProps) => {

	const langStringWelcomeDialogTitle = useLangString("welcome_dialog.title");
	const langStringWelcomeDialogDescMobile = useLangString("welcome_dialog.desc_mobile");
	const langStringWelcomeDialogConfirmButton = useLangString("welcome_dialog.confirm_button");

	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const inviterFullName = useMemo(() => {

		if (prepareJoinLinkError !== null && prepareJoinLinkError.error_code === ALREADY_MEMBER_ERROR_CODE) {
			return (prepareJoinLinkError.data as PrepareJoinLinkErrorAlreadyMemberData).inviter_full_name;
		}

		if (joinLink === null) {
			return "";
		}

		return joinLink.inviter_full_name;
	}, [joinLink, prepareJoinLinkError]);

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
					>{langStringWelcomeDialogTitle}</Text>
					<Text
						px="8px"
						fs="16"
						lh="22"
						textAlign="center"
					>
						<styled.span
							fontFamily="lato_semibold"
							fontWeight="700"
						>{inviterFullName}
						</styled.span>
						{langStringWelcomeDialogDescMobile}</Text>
				</VStack>
			</VStack>
			<Button
				onClick={() => navigateToNextPage()}
			>
				{langStringWelcomeDialogConfirmButton}
			</Button>
		</VStack>
	);
}

const PageWelcomeJoinLink = () => {

	const isMobile = useIsMobile();
	const {navigateToDialog} = useNavigateDialog();
	const {navigateToPage} = useNavigatePage();
	const {is_authorized, need_fill_profile} = useAtomValue(profileState);

	const navigateToNextPage = useCallback(() => {

		if (!is_authorized || need_fill_profile) {

			navigateToPage("auth");
			if (need_fill_profile) {

				navigateToDialog("auth_create_profile");
				return;
			}

			navigateToDialog("auth_phone_number");
			return;
		}

		navigateToPage("token");
	}, [is_authorized, need_fill_profile]);

	if (isMobile) {

		return (
			<>
				{/*<OpenLangMenuButton/>*/}
				<DialogMobile
					content={<WelcomeDialogMobile navigateToNextPage={navigateToNextPage}/>}
					overflow="hidden"
					isNeedExtraPaddingBottom={false}
				/>
			</>
		);
	}

	return (
		<>
			{/*<HStack*/}
			{/*	w="100%"*/}
			{/*	justify="end"*/}
			{/*	position="absolute"*/}
			{/*	top="0px"*/}
			{/*	pt="32px"*/}
			{/*	px="40px"*/}
			{/*>*/}
			{/*	<LangMenuSelectorDesktop/>*/}
			{/*</HStack>*/}
			<DialogDesktop
				content={<WelcomeDialogDesktop navigateToNextPage={navigateToNextPage}/>}
				overflow="hidden"
			/>
		</>
	);
}

export default PageWelcomeJoinLink;
