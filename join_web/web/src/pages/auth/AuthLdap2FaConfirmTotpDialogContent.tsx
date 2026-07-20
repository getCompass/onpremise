import { Box, VStack } from "../../../styled-system/jsx";
import { Input } from "../../components/input.tsx";
import { Button } from "../../components/button.tsx";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import useIsMobile from "../../lib/useIsMobile.ts";
import { KeyIcon80 } from "../../components/KeyIcon80.tsx";
import { useCallback, useMemo, useState } from "react";
import { useNavigateDialog } from "../../components/hooks.ts";
import {
	ALREADY_MEMBER_ERROR_CODE,
	API_COMMAND_TYPE_NEED_CONFIRM_LDAP_MAIL,
	API_COMMAND_TYPE_NEED_SETUP_TOTP,
	API_COMMAND_TYPE_NEED_TOTP_CODE,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	LIMIT_ERROR_CODE,
	SSO_PROTOCOL_OIDC
} from "../../api/_types.ts";
import { ApiCommand, ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import dayjs from "dayjs";
import { plural } from "../../lib/plural.ts";
import { useAtom, useAtomValue } from "jotai";
import { activeDialogIdState, authLdapCredentialsState, prepareJoinLinkErrorState } from "../../api/_stores.ts";
import { useApiFederationLdapAuthGetToken, useApiPivotAuthLdapBegin } from "../../api/auth/ldap.ts";
import useLdap2FaStage from "../../lib/useLdap2FaStage.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import Preloader16 from "../../components/Preloader16.tsx";

type AuthLdap2FaConfirmTotpDialogContentProps = {
	confirmCode: string
	setConfirmCode: (value: string) => void
	apiIsLoading: boolean
	isLoading: boolean
	isError: boolean
	onLoginClickHandler: () => void
}

const AuthLdap2FaConfirmTotpDialogContentDesktop = ({
	confirmCode,
	setConfirmCode,
	apiIsLoading,
	isLoading,
	isError,
	onLoginClickHandler,
}: AuthLdap2FaConfirmTotpDialogContentProps) => {

	const langStringLdap2faConfirmTotpDialogTitle = useLangString("ldap_2fa_confirm_totp_dialog.title");
	const langStringLdap2faConfirmTotpDialogDesc = useLangString("ldap_2fa_confirm_totp_dialog.desc");
	const langStringLdap2faConfirmTotpDialogInputPlaceholder = useLangString("ldap_2fa_confirm_totp_dialog.input_placeholder");
	const langStringLdap2faConfirmTotpDialogConfirmButton = useLangString("ldap_2fa_confirm_totp_dialog.confirm_button");

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="20px" minW="100%">
				<KeyIcon80 />
				<Text mt="16px" style="lato_18_24_900" ls="-02">
					{langStringLdap2faConfirmTotpDialogTitle}
				</Text>
				<Text mt="6px" textAlign="center" style="lato_14_20_400" ls="-015" maxW="328px" overflow="wrapEllipsis">
					{langStringLdap2faConfirmTotpDialogDesc}
				</Text>
				<VStack w="100%" gap="0px" mt="20px">
					<Input
						disabled={apiIsLoading}
						tabIndex={1}
						type="search"
						autoFocus={true}
						autoComplete="nope"
						value={confirmCode}
						onChange={(changeEvent) => {
							const value = changeEvent.target.value ?? "";
							const digitsOnly = value.replace(/\D/g, ""); // удаляем все кроме

							setConfirmCode(digitsOnly);
						}}
						maxLength={40}
						autoCapitalize="none"
						placeholder={langStringLdap2faConfirmTotpDialogInputPlaceholder}
						size="default_desktop"
						onKeyDown={(event: React.KeyboardEvent) => {
							if (event.key === "Enter") {
								onLoginClickHandler();
							}
						}}
						input={isError ? "error_default" : "default"}
					/>
				</VStack>
				<Button
					mt="12px"
					size="px12py6full"
					textSize="lato_15_23_600"
					rounded="6px"
					disabled={confirmCode.length < 1}
					onClick={() => onLoginClickHandler()}
				>
					{isLoading ? (
						<Box py="3.5px">
							<Preloader16 />
						</Box>
					) : (
						langStringLdap2faConfirmTotpDialogConfirmButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdap2FaConfirmTotpDialogContentMobile = ({
	confirmCode,
	setConfirmCode,
	apiIsLoading,
	isLoading,
	isError,
	onLoginClickHandler,
}: AuthLdap2FaConfirmTotpDialogContentProps) => {

	const langStringLdapLoginDialogBackButton = useLangString("ldap_login_dialog.back_button");
	const langStringLdap2faConfirmTotpDialogTitle = useLangString("ldap_2fa_confirm_totp_dialog.title");
	const langStringLdap2faConfirmTotpDialogDesc = useLangString("ldap_2fa_confirm_totp_dialog.desc");
	const langStringLdap2faConfirmTotpDialogInputPlaceholder = useLangString("ldap_2fa_confirm_totp_dialog.input_placeholder");
	const langStringLdap2faConfirmTotpDialogConfirmButton = useLangString("ldap_2fa_confirm_totp_dialog.confirm_button");

	const { prevDialog, navigateToDialog } = useNavigateDialog();

	const screenWidth = useMemo(() => document.body.clientWidth, [ document.body.clientWidth ]);

	return (
		<VStack w="100%" gap="0px">
			<Box w="100%">
				<Button
					color="2574a9"
					textSize="lato_16_22_400"
					size="px0py0"
					onClick={() => navigateToDialog(prevDialog)}
					disabled={isLoading}
				>
					{langStringLdapLoginDialogBackButton}
				</Button>
			</Box>
			<VStack gap="0px" mt="-6px" minW="100%">
				<KeyIcon80 />
				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringLdap2faConfirmTotpDialogTitle}
				</Text>
				<Text
					mt="4px"
					textAlign="center"
					style="lato_16_22_400"
					maxW={screenWidth <= 390 ? "326px" : "350px"}
					overflow="wrapEllipsis"
				>
					{langStringLdap2faConfirmTotpDialogDesc}
				</Text>
				<VStack w="100%" gap="0px" mt="24px">
					<Input
						disabled={apiIsLoading}
						type="search"
						autoFocus={true}
						autoComplete="nope"
						value={confirmCode}
						onChange={(changeEvent) => {
							const value = changeEvent.target.value ?? "";
							const digitsOnly = value.replace(/\D/g, ""); // удаляем все кроме

							setConfirmCode(digitsOnly);
						}}
						maxLength={40}
						autoCapitalize="none"
						placeholder={langStringLdap2faConfirmTotpDialogInputPlaceholder}
						size="px12py10w100"
						onKeyDown={(event: React.KeyboardEvent) => {
							if (event.key === "Enter") {
								onLoginClickHandler();
							}
						}}
						input={isError ? "error_default" : "default"}
					/>
				</VStack>
				<Button
					mt="12px"
					size="px16py9full"
					textSize="lato_17_26_600"
					disabled={confirmCode.length < 1}
					onClick={() => onLoginClickHandler()}
				>
					{isLoading ? (
						<Box py="5px">
							<Preloader16 />
						</Box>
					) : (
						langStringLdap2faConfirmTotpDialogConfirmButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdap2FaConfirmTotpDialogContent = () => {

	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const langStringLdapLoginDialogIncorrectCredentialsError = useLangString(
		"ldap_login_dialog.incorrect_credentials_error"
	);
	const langStringLdapLoginDialogAuthBlocked = useLangString("ldap_login_dialog.auth_blocked");
	const langStringLdapLoginDialogUnknownError = useLangString("ldap_login_dialog.unknown_error");
	const langStringLdapLoginDialogIncorrectConfigUserSearchFilter = useLangString("ldap_login_dialog.incorrect_config_user_search_filter");
	const langStringLdapLoginDialogIncorrect2FaMailConfigAttribute = useLangString("ldap_login_dialog.incorrect_2fa_mail_config_attribute");
	const langStringErrorsAuthLdapMethodDisabled = useLangString("errors.auth_ldap_method_disabled");
	const langStringErrorsLdapRegistrationWithoutInvite = useLangString("errors.ldap_registration_without_invite");
	const langStringErrorsAuthSsoFullNameIncorrect = useLangString("errors.auth_sso_full_name_incorrect");
	const langStringErrorsAuthSsoTotpCodeIncorrect = useLangString("errors.auth_sso_totp_code_incorrect");

	const isMobile = useIsMobile();

	const apiFederationLdapAuthGetToken = useApiFederationLdapAuthGetToken();
	const apiPivotAuthLdapBegin = useApiPivotAuthLdapBegin();

	const authLdapCredentials = useAtomValue(authLdapCredentialsState);

	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const [ prepareJoinLinkError, setPrepareJoinLinkError ] = useAtom(prepareJoinLinkErrorState);
	const { navigateByStage, navigateTotp } = useLdap2FaStage();

	const [ confirmCode, setConfirmCode ] = useState<string>("");
	const [ isError, setIsError ] = useState(false);
	const [ isLoading, setIsLoading ] = useState(false);

	const onLoginClickHandler = useCallback(async () => {
		if (authLdapCredentials.username.length < 1 || authLdapCredentials.password.length < 1 || confirmCode.length < 1) {
			return;
		}

		if (apiFederationLdapAuthGetToken.isLoading || apiPivotAuthLdapBegin.isLoading) {
			return;
		}

		try {
			setIsLoading(true);
			const federationLdapAuthGetTokenResponse = await apiFederationLdapAuthGetToken.mutateAsync({
				username: authLdapCredentials.username,
				password: authLdapCredentials.password,
				totp_code: confirmCode,
			});

			await apiPivotAuthLdapBegin.mutateAsync({
				ldap_auth_token: federationLdapAuthGetTokenResponse.ldap_auth_token,
				join_link:
					prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
						? window.location.href
						: undefined,
			});
			setIsLoading(false);
		} catch (error) {
			if (error instanceof NetworkError || error instanceof ServerError) {
				showToast(error instanceof NetworkError ? langStringErrorsNetworkError : langStringErrorsServerError, "warning");
				setIsLoading(false);
				setIsError(true);
				return;
			}

			if (error instanceof ApiCommand) {

				if (error.type === API_COMMAND_TYPE_NEED_CONFIRM_LDAP_MAIL) {

					navigateByStage(error.data, setIsLoading, setIsError, {
						username: authLdapCredentials.username,
						password: authLdapCredentials.password,
					});
					return;
				}
				if (error.type === API_COMMAND_TYPE_NEED_TOTP_CODE || error.type === API_COMMAND_TYPE_NEED_SETUP_TOTP) {

					navigateTotp(error, setIsLoading, setIsError, {
						username: authLdapCredentials.username,
						password: authLdapCredentials.password,
					});
					return;
				}
			}

			if (error instanceof ApiError) {

				if ([ 1708018, 1708001, 1708002, 1708003, LIMIT_ERROR_CODE, 1708118, 1000, 1708120, 1708015,
					INCORRECT_LINK_ERROR_CODE, INACTIVE_LINK_ERROR_CODE, ].includes(error.error_code)) {

					switch (error.error_code) {
						case 1708018:
							showToast(langStringErrorsAuthSsoTotpCodeIncorrect, "warning");
							break;
						case 1708001:
							showToast(langStringLdapLoginDialogIncorrectCredentialsError, "warning");
							break;
						case 1708002:
							showToast(langStringLdapLoginDialogUnknownError, "warning");
							break;
						case 1708003:
							showToast(langStringLdapLoginDialogIncorrectConfigUserSearchFilter, "warning");
							break;
						case LIMIT_ERROR_CODE:
							showToast(
								langStringLdapLoginDialogAuthBlocked.replace(
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
							break;
						case 1708118:
							showToast(langStringErrorsAuthLdapMethodDisabled, "warning");
							break;
						case 1000:
							showToast(langStringErrorsLdapRegistrationWithoutInvite, "warning");
							break;
						case 1708120:
							showToast(langStringErrorsAuthSsoFullNameIncorrect.replace("$SSO_PROVIDER_NAME", error.sso_protocol == SSO_PROTOCOL_OIDC ? "SSO" : "LDAP"), "warning");
							break;
						case 1708015:
							showToast(langStringLdapLoginDialogIncorrect2FaMailConfigAttribute, "warning");
							break;
						case INCORRECT_LINK_ERROR_CODE:
						case INACTIVE_LINK_ERROR_CODE:
							setPrepareJoinLinkError({ error_code: error.error_code });
							break;
					}

					setIsLoading(false);
					setIsError(true);
					return;
				}

				// если сказали что уже участник этой компании - то логиним без передачи joinLink
				if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {

					try {
						const federationLdapAuthGetTokenResponse =
							await apiFederationLdapAuthGetToken.mutateAsync({
								username: authLdapCredentials.username,
								password: authLdapCredentials.password,
								totp_code: confirmCode,
							});

						await apiPivotAuthLdapBegin.mutateAsync({
							ldap_auth_token: federationLdapAuthGetTokenResponse.ldap_auth_token,
						});
					} catch (error) {
						if (error instanceof NetworkError || error instanceof ServerError) {
							showToast(error instanceof NetworkError ? langStringErrorsNetworkError : langStringErrorsServerError, "warning");
							setIsLoading(false);
							setIsError(true);
							return;
						}

						if (error instanceof ApiError) {

							if ([ 1708018, 1708001, 1708002, 1708003, LIMIT_ERROR_CODE, 1708118, 1000, 1708120, 1708015,
								INCORRECT_LINK_ERROR_CODE, INACTIVE_LINK_ERROR_CODE, ].includes(error.error_code)) {

								switch (error.error_code) {
									case 1708018:
										showToast(langStringErrorsAuthSsoTotpCodeIncorrect, "warning");
										break;
									case 1708001:
										showToast(langStringLdapLoginDialogIncorrectCredentialsError, "warning");
										break;
									case 1708002:
										showToast(langStringLdapLoginDialogUnknownError, "warning");
										break;
									case 1708003:
										showToast(langStringLdapLoginDialogIncorrectConfigUserSearchFilter, "warning");
										break;
									case LIMIT_ERROR_CODE:
										showToast(
											langStringLdapLoginDialogAuthBlocked.replace(
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
										break;
									case 1708118:
										showToast(langStringErrorsAuthLdapMethodDisabled, "warning");
										break;
									case 1000:
										showToast(langStringErrorsLdapRegistrationWithoutInvite, "warning");
										break;
									case 1708120:
										showToast(langStringErrorsAuthSsoFullNameIncorrect.replace("$SSO_PROVIDER_NAME", error.sso_protocol == SSO_PROTOCOL_OIDC ? "SSO" : "LDAP"), "warning");
										break;
									case 1708015:
										showToast(langStringLdapLoginDialogIncorrect2FaMailConfigAttribute, "warning");
										break;
									case INCORRECT_LINK_ERROR_CODE:
									case INACTIVE_LINK_ERROR_CODE:
										setPrepareJoinLinkError({ error_code: error.error_code });
										break;
								}

								setIsLoading(false);
								setIsError(true);
								return;
							}
						}
						setIsLoading(false);
					}
				}
			}
			setIsLoading(false);
		}
	}, [ authLdapCredentials, confirmCode, langStringErrorsAuthSsoTotpCodeIncorrect ]);

	if (isMobile) {

		return <AuthLdap2FaConfirmTotpDialogContentMobile
			confirmCode={confirmCode}
			setConfirmCode={setConfirmCode}
			apiIsLoading={apiFederationLdapAuthGetToken.isLoading || apiPivotAuthLdapBegin.isLoading}
			isLoading={isLoading}
			isError={isError}
			onLoginClickHandler={onLoginClickHandler}
		/>
	}

	return <AuthLdap2FaConfirmTotpDialogContentDesktop
		confirmCode={confirmCode}
		setConfirmCode={setConfirmCode}
		apiIsLoading={apiFederationLdapAuthGetToken.isLoading || apiPivotAuthLdapBegin.isLoading}
		isLoading={isLoading}
		isError={isError}
		onLoginClickHandler={onLoginClickHandler}
	/>
};

export default AuthLdap2FaConfirmTotpDialogContent;