import { Box, VStack } from "../../../styled-system/jsx";
import { Input } from "../../components/input.tsx";
import { Button } from "../../components/button.tsx";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import useIsMobile from "../../lib/useIsMobile.ts";
import { useCallback, useMemo, useRef, useState } from "react";
import { KeyIcon80 } from "../../components/KeyIcon80.tsx";
import PasswordInput from "../../components/PasswordInput.tsx";
import { useApiFederationLdapAuthTryAuthenticate, useApiPivotAuthLdapBegin } from "../../api/auth/ldap.ts";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { useAtom, useAtomValue } from "jotai/index";
import { activeDialogIdState, prepareJoinLinkErrorState } from "../../api/_stores.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import {
	ALREADY_MEMBER_ERROR_CODE,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	LIMIT_ERROR_CODE, SSO_PROTOCOL_OIDC,
} from "../../api/_types.ts";
import dayjs from "dayjs";
import { plural } from "../../lib/plural.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import { useNavigateDialog } from "../../components/hooks.ts";

const AuthLdapDialogContentDesktop = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const langStringLdapLoginDialogTitle = useLangString("ldap_login_dialog.title");
	const langStringLdapLoginDialogDesc = useLangString("ldap_login_dialog.desc");
	const langStringLdapLoginDialogUsernameInputPlaceholder = useLangString(
		"ldap_login_dialog.username_input_placeholder"
	);
	const langStringLdapLoginDialogPasswordInputPlaceholder = useLangString(
		"ldap_login_dialog.password_input_placeholder"
	);
	const langStringLdapLoginDialogLoginButton = useLangString("ldap_login_dialog.login_button");
	const langStringLdapLoginDialogIncorrectCredentialsError = useLangString(
		"ldap_login_dialog.incorrect_credentials_error"
	);
	const langStringLdapLoginDialogAuthBlocked = useLangString("ldap_login_dialog.auth_blocked");
	const langStringLdapLoginDialogUnknownError = useLangString("ldap_login_dialog.unknown_error");
	const langStringErrorsAuthLdapMethodDisabled = useLangString("errors.auth_ldap_method_disabled");
	const langStringErrorsLdapRegistrationWithoutInvite = useLangString("errors.ldap_registration_without_invite");
	const langStringErrorsAuthSsoFullNameIncorrect = useLangString("errors.auth_sso_full_name_incorrect");

	const apiFederationLdapAuthTryAuthenticate = useApiFederationLdapAuthTryAuthenticate();
	const apiPivotAuthLdapBegin = useApiPivotAuthLdapBegin();

	const activeDialogId = useAtomValue(activeDialogIdState);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);

	const usernameInputRef = useRef<HTMLInputElement>(null);
	const passwordInputRef = useRef<HTMLInputElement>(null);

	const [username, setUsername] = useState("");
	const [password, setPassword] = useState("");
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);
	const [isError, setIsError] = useState(false);
	const [isLoading, setIsLoading] = useState(false);

	const showToast = useShowToast(activeDialogId);

	const onLoginClickHandler = useCallback(async () => {
		if (username.length < 1 || password.length < 1) {
			return;
		}

		if (apiFederationLdapAuthTryAuthenticate.isLoading || apiPivotAuthLdapBegin.isLoading) {
			return;
		}

		try {
			setIsLoading(true);
			const federationLdapAuthTryAuthenticateResponse = await apiFederationLdapAuthTryAuthenticate.mutateAsync({
				username: username,
				password: password,
			});

			await apiPivotAuthLdapBegin.mutateAsync({
				ldap_auth_token: federationLdapAuthTryAuthenticateResponse.ldap_auth_token,
				join_link:
					prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
						? window.location.href
						: undefined,
			});
			setIsLoading(false);
		} catch (error) {
			if (error instanceof NetworkError) {
				showToast(langStringErrorsNetworkError, "warning");
				setIsLoading(false);
				setIsError(true);
				return;
			}

			if (error instanceof ServerError) {
				showToast(langStringErrorsServerError, "warning");
				setIsLoading(false);
				setIsError(true);
				return;
			}

			if (error instanceof ApiError) {
				if (error.error_code === 1708001) {
					showToast(langStringLdapLoginDialogIncorrectCredentialsError, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === 1708002) {
					showToast(langStringLdapLoginDialogUnknownError, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === LIMIT_ERROR_CODE) {
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
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === 1708118) {
					showToast(langStringErrorsAuthLdapMethodDisabled, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === 1000) {
					showToast(langStringErrorsLdapRegistrationWithoutInvite, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}

				if (error.error_code === INCORRECT_LINK_ERROR_CODE || error.error_code === INACTIVE_LINK_ERROR_CODE) {
					setPrepareJoinLinkError({ error_code: error.error_code });
					setIsLoading(false);
					setIsError(true);
					return;
				}

				// если сказали что уже участник этой компании - то логиним без передачи joinLink
				if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {
					try {
						const federationLdapAuthTryAuthenticateResponse =
							await apiFederationLdapAuthTryAuthenticate.mutateAsync({
								username: username,
								password: password,
							});

						await apiPivotAuthLdapBegin.mutateAsync({
							ldap_auth_token: federationLdapAuthTryAuthenticateResponse.ldap_auth_token,
						});
					} catch (error) {
						if (error instanceof NetworkError) {
							showToast(langStringErrorsNetworkError, "warning");
							setIsLoading(false);
							setIsError(true);
							return;
						}

						if (error instanceof ServerError) {
							showToast(langStringErrorsServerError, "warning");
							setIsLoading(false);
							setIsError(true);
							return;
						}

						if (error instanceof ApiError) {
							if (error.error_code === 1708001) {
								showToast(langStringLdapLoginDialogIncorrectCredentialsError, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === 1708002) {
								showToast(langStringLdapLoginDialogUnknownError, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === LIMIT_ERROR_CODE) {
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
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === 1708118) {
								showToast(langStringErrorsAuthLdapMethodDisabled, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === 1000) {
								showToast(langStringErrorsLdapRegistrationWithoutInvite, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === 1708120) {
								showToast(langStringErrorsAuthSsoFullNameIncorrect.replace("$SSO_PROVIDER_NAME", error.sso_protocol == SSO_PROTOCOL_OIDC ? "SSO" : "LDAP"), "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}

							if (
								error.error_code === INCORRECT_LINK_ERROR_CODE ||
								error.error_code === INACTIVE_LINK_ERROR_CODE
							) {
								setPrepareJoinLinkError({ error_code: error.error_code });
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
	}, [username, password]);

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="20px">
				<KeyIcon80 />
				<Text mt="16px" style="lato_18_24_900" ls="-02">
					{langStringLdapLoginDialogTitle}
				</Text>
				<Text mt="6px" textAlign="center" style="lato_14_20_400" ls="-015" maxW="328px" overflow="wrapEllipsis">
					{langStringLdapLoginDialogDesc}
				</Text>
				<Input
					disabled={apiFederationLdapAuthTryAuthenticate.isLoading || apiPivotAuthLdapBegin.isLoading}
					ref={usernameInputRef}
					tabIndex={1}
					mt="20px"
					type="search"
					autoFocus={true}
					autoComplete="nope"
					value={username}
					onChange={(changeEvent) => {
						setUsername(changeEvent.target.value ?? "");
						setIsError(false);
					}}
					autoCapitalize="none"
					placeholder={langStringLdapLoginDialogUsernameInputPlaceholder}
					size="default_desktop"
					onKeyDown={(event: React.KeyboardEvent) => {
						if (event.key === "Enter") {
							if (password.length < 1 && passwordInputRef.current !== null) {
								passwordInputRef.current.focus();
								return;
							}
							onLoginClickHandler();
						}
					}}
					input={isError ? "error_default" : "default"}
				/>
				<PasswordInput
					isDisabled={apiFederationLdapAuthTryAuthenticate.isLoading || apiPivotAuthLdapBegin.isLoading}
					mt="8px"
					autoFocus={false}
					password={password}
					setPassword={setPassword}
					inputPlaceholder={langStringLdapLoginDialogPasswordInputPlaceholder}
					isToolTipVisible={false}
					setIsToolTipVisible={() => null}
					isNeedShowTooltip={false}
					setIsNeedShowTooltip={() => null}
					isError={isError}
					setIsError={setIsError}
					onEnterClick={() => {
						if (username.length < 1 && usernameInputRef.current !== null) {
							usernameInputRef.current.focus();
							return;
						}
						onLoginClickHandler();
					}}
					inputRef={passwordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					maxLength={9999}
					inputTabIndex={2}
				/>
				<Button
					mt="12px"
					size="px12py6full"
					textSize="lato_15_23_600"
					rounded="6px"
					disabled={username.length < 1 || password.length < 1}
					onClick={() => onLoginClickHandler()}
				>
					{isLoading ? (
						<Box py="3.5px">
							<Preloader16 />
						</Box>
					) : (
						langStringLdapLoginDialogLoginButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdapDialogContentMobile = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const langStringLdapLoginDialogTitle = useLangString("ldap_login_dialog.title");
	const langStringLdapLoginDialogDesc = useLangString("ldap_login_dialog.desc");
	const langStringLdapLoginDialogUsernameInputPlaceholder = useLangString(
		"ldap_login_dialog.username_input_placeholder"
	);
	const langStringLdapLoginDialogPasswordInputPlaceholder = useLangString(
		"ldap_login_dialog.password_input_placeholder"
	);
	const langStringLdapLoginDialogBackButton = useLangString("ldap_login_dialog.back_button");
	const langStringLdapLoginDialogLoginButton = useLangString("ldap_login_dialog.login_button");
	const langStringLdapLoginDialogIncorrectCredentialsError = useLangString(
		"ldap_login_dialog.incorrect_credentials_error"
	);
	const langStringLdapLoginDialogAuthBlocked = useLangString("ldap_login_dialog.auth_blocked");
	const langStringLdapLoginDialogUnknownError = useLangString("ldap_login_dialog.unknown_error");
	const langStringErrorsAuthLdapMethodDisabled = useLangString("errors.auth_ldap_method_disabled");
	const langStringErrorsAuthSsoFullNameIncorrect = useLangString("errors.auth_sso_full_name_incorrect");
	const langStringErrorsLdapRegistrationWithoutInvite = useLangString("errors.ldap_registration_without_invite");

	const { navigateToDialog } = useNavigateDialog();

	const apiFederationLdapAuthTryAuthenticate = useApiFederationLdapAuthTryAuthenticate();
	const apiPivotAuthLdapBegin = useApiPivotAuthLdapBegin();

	const activeDialogId = useAtomValue(activeDialogIdState);
	const [prepareJoinLinkError, setPrepareJoinLinkError] = useAtom(prepareJoinLinkErrorState);

	const usernameInputRef = useRef<HTMLInputElement>(null);
	const passwordInputRef = useRef<HTMLInputElement>(null);

	const [username, setUsername] = useState("");
	const [password, setPassword] = useState("");
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);
	const [isError, setIsError] = useState(false);
	const [isLoading, setIsLoading] = useState(false);

	const screenWidth = useMemo(() => document.body.clientWidth, [document.body.clientWidth]);

	const showToast = useShowToast(activeDialogId);

	const onLoginClickHandler = useCallback(async () => {
		if (username.length < 1 || password.length < 1) {
			return;
		}

		if (apiFederationLdapAuthTryAuthenticate.isLoading || apiPivotAuthLdapBegin.isLoading) {
			return;
		}

		try {
			setIsLoading(true);
			const federationLdapAuthTryAuthenticateResponse = await apiFederationLdapAuthTryAuthenticate.mutateAsync({
				username: username,
				password: password,
			});

			await apiPivotAuthLdapBegin.mutateAsync({
				ldap_auth_token: federationLdapAuthTryAuthenticateResponse.ldap_auth_token,
				join_link:
					prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
						? window.location.href
						: undefined,
			});
			setIsLoading(false);
		} catch (error) {
			if (error instanceof NetworkError) {
				showToast(langStringErrorsNetworkError, "warning");
				setIsLoading(false);
				setIsError(true);
				return;
			}

			if (error instanceof ServerError) {
				showToast(langStringErrorsServerError, "warning");
				setIsLoading(false);
				setIsError(true);
				return;
			}

			if (error instanceof ApiError) {
				if (error.error_code === 1708001) {
					showToast(langStringLdapLoginDialogIncorrectCredentialsError, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === 1708002) {
					showToast(langStringLdapLoginDialogUnknownError, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === LIMIT_ERROR_CODE) {
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
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === 1708118) {
					showToast(langStringErrorsAuthLdapMethodDisabled, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === 1708120) {
					showToast(langStringErrorsAuthSsoFullNameIncorrect.replace("$SSO_PROVIDER_NAME", error.sso_protocol == SSO_PROTOCOL_OIDC ? "SSO" : "LDAP"), "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}
				if (error.error_code === 1000) {
					showToast(langStringErrorsLdapRegistrationWithoutInvite, "warning");
					setIsLoading(false);
					setIsError(true);
					return;
				}

				if (error.error_code === INCORRECT_LINK_ERROR_CODE || error.error_code === INACTIVE_LINK_ERROR_CODE) {
					setPrepareJoinLinkError({ error_code: error.error_code });
					setIsLoading(false);
					setIsError(true);
					return;
				}

				// если сказали что уже участник этой компании - то логиним без передачи joinLink
				if (error.error_code === ALREADY_MEMBER_ERROR_CODE) {
					try {
						const federationLdapAuthTryAuthenticateResponse =
							await apiFederationLdapAuthTryAuthenticate.mutateAsync({
								username: username,
								password: password,
							});

						await apiPivotAuthLdapBegin.mutateAsync({
							ldap_auth_token: federationLdapAuthTryAuthenticateResponse.ldap_auth_token,
						});
					} catch (error) {
						if (error instanceof NetworkError) {
							showToast(langStringErrorsNetworkError, "warning");
							setIsLoading(false);
							setIsError(true);
							return;
						}

						if (error instanceof ServerError) {
							showToast(langStringErrorsServerError, "warning");
							setIsLoading(false);
							setIsError(true);
							return;
						}

						if (error instanceof ApiError) {
							if (error.error_code === 1708001) {
								showToast(langStringLdapLoginDialogIncorrectCredentialsError, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === 1708002) {
								showToast(langStringLdapLoginDialogUnknownError, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === LIMIT_ERROR_CODE) {
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
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === 1708118) {
								showToast(langStringErrorsAuthLdapMethodDisabled, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}
							if (error.error_code === 1000) {
								showToast(langStringErrorsLdapRegistrationWithoutInvite, "warning");
								setIsLoading(false);
								setIsError(true);
								return;
							}

							if (
								error.error_code === INCORRECT_LINK_ERROR_CODE ||
								error.error_code === INACTIVE_LINK_ERROR_CODE
							) {
								setPrepareJoinLinkError({ error_code: error.error_code });
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
	}, [username, password]);

	return (
		<VStack w="100%" gap="0px">
			<Box w="100%">
				<Button
					color="2574a9"
					textSize="lato_16_22_400"
					size="px0py0"
					onClick={() => navigateToDialog("auth_email_phone_number")}
					disabled={isLoading}
				>
					{langStringLdapLoginDialogBackButton}
				</Button>
			</Box>
			<VStack gap="0px" mt="-6px">
				<KeyIcon80 />
				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringLdapLoginDialogTitle}
				</Text>
				<Text
					mt="4px"
					textAlign="center"
					style="lato_16_22_400"
					maxW={screenWidth <= 390 ? "326px" : "350px"}
					overflow="wrapEllipsis"
				>
					{langStringLdapLoginDialogDesc}
				</Text>
				<Input
					disabled={apiFederationLdapAuthTryAuthenticate.isLoading || apiPivotAuthLdapBegin.isLoading}
					ref={usernameInputRef}
					mt="24px"
					type="search"
					autoFocus={true}
					autoComplete="nope"
					value={username}
					onChange={(changeEvent) => {
						setUsername(changeEvent.target.value ?? "");
						setIsError(false);
					}}
					autoCapitalize="none"
					placeholder={langStringLdapLoginDialogUsernameInputPlaceholder}
					size="px12py10w100"
					onKeyDown={(event: React.KeyboardEvent) => {
						if (event.key === "Enter") {
							if (password.length < 1 && passwordInputRef.current !== null) {
								passwordInputRef.current.focus();
								return;
							}
							onLoginClickHandler();
						}
					}}
					input={isError ? "error_default" : "default"}
				/>
				<PasswordInput
					isDisabled={apiFederationLdapAuthTryAuthenticate.isLoading || apiPivotAuthLdapBegin.isLoading}
					mt="8px"
					autoFocus={false}
					password={password}
					setPassword={setPassword}
					inputPlaceholder={langStringLdapLoginDialogPasswordInputPlaceholder}
					isToolTipVisible={false}
					setIsToolTipVisible={() => null}
					isNeedShowTooltip={false}
					setIsNeedShowTooltip={() => null}
					isError={isError}
					setIsError={setIsError}
					onEnterClick={() => {
						if (username.length < 1 && usernameInputRef.current !== null) {
							usernameInputRef.current.focus();
							return;
						}
						onLoginClickHandler();
					}}
					inputRef={passwordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					maxLength={9999}
				/>
				<Button
					mt="12px"
					size="px16py9full"
					textSize="lato_17_26_600"
					disabled={username.length < 1 || password.length < 1}
					onClick={() => onLoginClickHandler()}
				>
					{isLoading ? (
						<Box py="5px">
							<Preloader16 />
						</Box>
					) : (
						langStringLdapLoginDialogLoginButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdapDialogContent = () => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return <AuthLdapDialogContentMobile />;
	}

	return <AuthLdapDialogContentDesktop />;
};

export default AuthLdapDialogContent;
