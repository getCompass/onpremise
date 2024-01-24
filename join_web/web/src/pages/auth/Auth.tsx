import DialogMobile from "../../components/DialogMobile.tsx";
import useIsMobile from "../../lib/useIsMobile.ts";
import DialogDesktop from "../../components/DialogDesktop.tsx";
import {useMemo} from "react";
import {useNavigateDialog} from "../../components/hooks.ts";
import PhoneNumberDialogContent from "./PhoneNumberDialogContent.tsx";
import ConfirmCodeDialogContent from "./ConfirmCodeDialogContent.tsx";
import CreateProfileDialogContent from "./CreateProfileDialogContent.tsx";

const Auth = () => {

	const isMobile = useIsMobile();
	const {activeDialog} = useNavigateDialog();

	const content = useMemo(() => {

		if (activeDialog === "auth_phone_number") {
			return <PhoneNumberDialogContent/>;
		}

		if (activeDialog === "auth_confirm_code") {
			return <ConfirmCodeDialogContent/>;
		}

		if (activeDialog === "auth_create_profile") {
			return <CreateProfileDialogContent/>;
		}

		return <></>;
	}, [activeDialog]);

	if (isMobile) {

		return (
			<>
				{/*<OpenLangMenuButton/>*/}
				<DialogMobile
					content={content}
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
				content={content}
				overflow="hidden"
			/>
		</>
	);
}

export default Auth;
