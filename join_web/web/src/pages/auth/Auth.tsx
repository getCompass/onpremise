import DialogMobile from "../../components/DialogMobile.tsx";
import useIsMobile from "../../lib/useIsMobile.ts";
import DialogDesktop from "../../components/DialogDesktop.tsx";
import { useCallback, useMemo, useRef } from "react";
import { useNavigateDialog } from "../../components/hooks.ts";
import EmailPhoneNumberDialogContent from "./EmailPhoneNumberDialogContent.tsx";
import CreateProfileDialogContent from "./CreateProfileDialogContent.tsx";
import EmailRegisterDialogContent from "./EmailRegisterDialogContent.tsx";
import ConfirmCodePhoneNumberDialogContent from "./ConfirmCodePhoneNumberDialogContent.tsx";
import ConfirmCodeEmailDialogContent from "./ConfirmCodeEmailDialogContent.tsx";
import EmailLoginDialogContent from "./EmailLoginDialogContent.tsx";
import { Button } from "../../components/button.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import ForgotPasswordDialogContent from "./ForgotPasswordDialogContent.tsx";
import { useAtomValue } from "jotai";
import {
	activeDialogIdState,
	authInputState,
	authLdapState,
	authState,
	isGuestAuthState,
	isLoginCaptchaRenderedState,
	joinLinkState,
	needShowForgotPasswordButtonState,
	prepareJoinLinkErrorState,
} from "../../api/_stores.ts";
import CreateNewPasswordDialogContent from "./CreateNewPasswordDialogContent.tsx";
import { useApiAuthMailCancel } from "../../api/auth/mail.ts";
import { Property } from "../../../styled-system/types/csstype";
import {
	ALREADY_MEMBER_ERROR_CODE,
	API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CURRENT_MAIL,
	API_COMMAND_TYPE_NEED_CONFIRM_LDAP_MAIL,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	JOIN_LINK_ROLE_GUEST
} from "../../api/_types.ts";
import { ApiCommand, ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import { useAtom } from "jotai/index";
import { useApiSecurityMailTryResetPassword } from "../../api/security/mail.ts";
import AuthLdapDialogContent from "./AuthLdapDialogContent.tsx";
import AuthLdap2FaAttachMailDialogContent from "./AuthLdap2FaAttachMailDialogContent.tsx";
import useLdap2FaStage from "../../lib/useLdap2FaStage.ts";
import { useApiFederationLdapMailChange } from "../../api/auth/ldap.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import { Box } from "../../../styled-system/jsx";

function Auth() {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const isMobile = useIsMobile();
	const apiAuthMailCancel = useApiAuthMailCancel();
	const apiFederationLdapMailChange = useApiFederationLdapMailChange();
	const { activeDialog, navigateToDialog } = useNavigateDialog();
	const { navigateByStage } = useLdap2FaStage();
	const [ auth, setAuth ] = useAtom(authState);
	const [ authLdap, setAuthLdap ] = useAtom(authLdapState);
	const needShowForgotPasswordButton = useAtomValue(needShowForgotPasswordButtonState);
	const [ prepareJoinLinkError, setPrepareJoinLinkError ] = useAtom(prepareJoinLinkErrorState);
	const [ joinLink, setJoinLink ] = useAtom(joinLinkState);
	const authInput = useAtomValue(authInputState);
	const isLoginCaptchaRendered = useAtomValue(isLoginCaptchaRenderedState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const dialogRef = useRef<HTMLDivElement>(null);
	const forgotPasswordButtonRef = useRef<HTMLButtonElement>(null);
	const backButtonRef = useRef<HTMLButtonElement>(null);
	const changeMailRef = useRef<HTMLButtonElement>(null);
	const apiSecurityMailTryResetPassword = useApiSecurityMailTryResetPassword();
	const isGuestJoinLink = useMemo(() => joinLink !== null && joinLink.role === JOIN_LINK_ROLE_GUEST, [ joinLink ]);
	const [ isGuestAuth, setIsGuestAuth ] = useAtom(isGuestAuthState);

	const email = useMemo(() => {
		const [ authValue, _ ] = authInput.split("__|__") || [ "", 0 ];

		return authValue;
	}, [ authInput ]);
	const isCanChangeLdapMail = useMemo(() => authLdap !== null && authLdap.scenario_data.is_manual_add_enabled === 1
		&& authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CURRENT_MAIL, [ authLdap ]);

	const langStringEmailLoginDialogBackButton = useLangString("email_login_dialog.back_button");
	const langStringEmailLoginDialogForgotPasswordButton = useLangString("email_login_dialog.forgot_password_button");
	const langStringConfirmCodeEmailDialogChangeMailButton = useLangString("confirm_code_email_dialog.change_mail_button");
	const langStringConfirmCodeEmailDialogLdap2FaChangeMailLimitError = useLangString("confirm_code_email_dialog.ldap_2fa_change_mail_limit_error");

	const onForgotPasswordButtonClick = useCallback(async () => {
		if (apiSecurityMailTryResetPassword.isLoading) {
			return;
		}

		try {
			const response = await apiSecurityMailTryResetPassword.mutateAsync({
				mail: email,
				join_link:
					prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
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
				if (error.error_code === INCORRECT_LINK_ERROR_CODE || error.error_code === INACTIVE_LINK_ERROR_CODE) {
					setPrepareJoinLinkError({ error_code: error.error_code });
					return;
				}

				if (error.error_code === 1708200) {
					navigateToDialog("auth_forgot_password");
					return;
				}

				// если сказали что уже участник этой компании - то логиним без передачи joinLink
				if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {
					try {
						const response = await apiSecurityMailTryResetPassword.mutateAsync({
							mail: email,
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

							if (error.error_code === 1708200) {
								navigateToDialog("auth_forgot_password");
								return;
							}
						}
					}
				}
			}
		}
	}, [ email, prepareJoinLinkError, apiSecurityMailTryResetPassword, window.location.href ]);

	const forgotPasswordButtonMt = useMemo<Property.MarginTop>(() => {
		if (dialogRef.current === null || forgotPasswordButtonRef.current === null) {
			return "371px";
		}

		return `${dialogRef.current.clientHeight + forgotPasswordButtonRef.current.clientHeight + 32}px`;
	}, [
		dialogRef.current?.clientHeight,
		forgotPasswordButtonRef.current?.clientHeight,
		isLoginCaptchaRendered,
		needShowForgotPasswordButton,
		activeDialog,
	]);

	const backButtonMt = useMemo<Property.MarginTop>(() => {
		if (dialogRef.current === null || forgotPasswordButtonRef.current === null || backButtonRef.current === null) {
			return "591px";
		}

		return `${
			dialogRef.current.clientHeight +
			32 +
			forgotPasswordButtonRef.current.clientHeight +
			188 +
			backButtonRef.current.clientHeight
		}px`;
	}, [
		dialogRef.current?.clientHeight,
		forgotPasswordButtonRef.current?.clientHeight,
		backButtonRef.current?.clientHeight,
		isLoginCaptchaRendered,
	]);

	const changeMailMt = useMemo<Property.MarginTop>(() => {
		if (dialogRef.current === null || changeMailRef.current === null) {
			return "388px";
		}

		console.log(`dialogRef.current.clientHeight: ${dialogRef.current.clientHeight}; changeMailRef.current.clientHeight: ${changeMailRef.current.clientHeight}`);
		return `${
			dialogRef.current.clientHeight +
			changeMailRef.current.clientHeight +
			16 +
			9
		}px`;
	}, [
		dialogRef.current?.clientHeight,
		changeMailRef.current,
		changeMailRef.current?.clientHeight,
		isLoginCaptchaRendered,
	]);

	const isNeedGuestBackButton = useMemo(() => {

		return isGuestAuth && !isGuestJoinLink
			&& activeDialog !== "auth_email_login" && activeDialog !== "auth_email_register"
			&& activeDialog !== "auth_email_confirm_code" && activeDialog !== "auth_forgot_password"
			&& activeDialog !== "auth_sso_ldap" && activeDialog !== "auth_ldap_2fa_attach_mail"
			&& activeDialog !== "auth_phone_number_confirm_code"
	}, [ isGuestAuth, isGuestJoinLink, activeDialog ]);

	const onBackButtonClicked = useCallback(() => {
		if (isNeedGuestBackButton) {

			setIsGuestAuth(false);
			return;
		}

		if (activeDialog === "auth_sso_ldap") {

			navigateToDialog("auth_email_phone_number");
			return;
		}

		if (activeDialog === "auth_ldap_2fa_attach_mail") {

			navigateToDialog("auth_sso_ldap");
			return;
		}

		if (auth !== null) {

			apiAuthMailCancel.mutate({ auth_key: auth.auth_key });
			return;
		}
	}, [ auth, authLdap, activeDialog, isNeedGuestBackButton ]);

	const onChangeMailButtonClicked = useCallback(async () => {
		if (authLdap !== null && isCanChangeLdapMail) {

			try {
				const federationLdapMailChangeResponse = await apiFederationLdapMailChange.mutateAsync({
					mail_confirm_story_key: authLdap.mail_confirm_story_key,
				});
				setAuthLdap(federationLdapMailChangeResponse.ldap_mail_confirm_story_info);
				navigateByStage(federationLdapMailChangeResponse.ldap_mail_confirm_story_info);
			} catch (error) {
				if (error instanceof NetworkError || error instanceof ServerError) {
					showToast(error instanceof NetworkError ? langStringErrorsNetworkError : langStringErrorsServerError, "warning");
				} else if (error instanceof ApiCommand) {
					if (error.type === API_COMMAND_TYPE_NEED_CONFIRM_LDAP_MAIL) {
						navigateByStage(error.data);
					}
				} else if (error instanceof ApiError) {
					switch (error.error_code) {
						default:
							showToast(langStringConfirmCodeEmailDialogLdap2FaChangeMailLimitError, "warning");
							break;
					}
				}
			}
			return;
		}
	}, [ authLdap, isCanChangeLdapMail ]);

	const content = useMemo(() => {
		if (activeDialog === "auth_email_phone_number") {
			return <EmailPhoneNumberDialogContent />;
		}

		if (activeDialog === "auth_phone_number_confirm_code") {
			return <ConfirmCodePhoneNumberDialogContent />;
		}

		if (activeDialog === "auth_create_profile") {
			return <CreateProfileDialogContent />;
		}

		if (activeDialog === "auth_email_register") {
			return <EmailRegisterDialogContent />;
		}

		if (activeDialog === "auth_email_confirm_code") {
			return <ConfirmCodeEmailDialogContent />;
		}

		if (activeDialog === "auth_email_login") {
			return <EmailLoginDialogContent />;
		}

		if (activeDialog === "auth_forgot_password") {
			return <ForgotPasswordDialogContent />;
		}

		if (activeDialog === "auth_create_new_password") {
			return <CreateNewPasswordDialogContent />;
		}

		if (activeDialog === "auth_sso_ldap") {
			return <AuthLdapDialogContent />;
		}

		if (activeDialog === "auth_ldap_2fa_attach_mail") {
			return <AuthLdap2FaAttachMailDialogContent />;
		}

		return <></>;
	}, [ activeDialog ]);

	if (isMobile) {
		return (
			<>
				{/*<OpenLangMenuButton/>*/}
				<DialogMobile content = {content} overflow = "hidden" isNeedExtraPaddingBottom = {false} />
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
				<DialogDesktop dialogRef = {dialogRef} content = {content} overflow = "hidden" />
			{activeDialog === "auth_email_login" && needShowForgotPasswordButton && (
				<Button
					ref = {forgotPasswordButtonRef}
					position = "absolute"
					size = "px0py0"
					color = "f8f8f8_30"
					textSize = "lato_13_18_400"
					onClick = {() => onForgotPasswordButtonClick()}
					style = {{
						marginTop: forgotPasswordButtonMt,
					}}
				>
					{langStringEmailLoginDialogForgotPasswordButton}
				</Button>
			)}
			{(activeDialog === "auth_email_confirm_code" && isCanChangeLdapMail) && (
				<>
					{apiFederationLdapMailChange.isLoading ? (
						<Box
							position = "absolute"
							mt = "388px"
							style = {{
								marginTop: changeMailMt,
							}}
						>
							<Preloader16 />
						</Box>
					) : (
						<Button
							ref = {changeMailRef}
							position = "absolute"
							mt = "388px"
							size = "px0py0"
							color = "f8f8f8_30"
							textSize = "lato_13_18_400"
							onClick = {() => onChangeMailButtonClicked()}
							style = {{
								marginTop: changeMailMt,
							}}
						>
							{langStringConfirmCodeEmailDialogChangeMailButton}
						</Button>
					)}
				</>
			)}
				{(activeDialog === "auth_email_login" ||
					activeDialog === "auth_email_register" ||
					activeDialog === "auth_forgot_password" ||
					activeDialog === "auth_sso_ldap" ||
					activeDialog === "auth_ldap_2fa_attach_mail" ||
					isNeedGuestBackButton) && (
					<Button
						ref = {backButtonRef}
						position = "absolute"
						mt = "575px"
						size = "pl12pr14py6"
						color = "ffffff_opacity30"
						letterSpacing = "-0.15px"
						textSize = "lato_14_20_400"
						rounded = "30px"
						onClick = {() => onBackButtonClicked()}
						style = {{
							marginTop: backButtonMt,
						}}
					>
						{langStringEmailLoginDialogBackButton}
					</Button>
				)}
		</>
	);
}

export default Auth;
