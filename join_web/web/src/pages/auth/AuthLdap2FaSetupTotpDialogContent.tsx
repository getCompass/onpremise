import { Box, VStack } from "../../../styled-system/jsx";
import useIsMobile from "../../lib/useIsMobile.ts";
import { KeyIcon80 } from "../../components/KeyIcon80.tsx";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Button } from "../../components/button.tsx";
import { ReactQRCode } from "@lglab/react-qr-code";
import { useNavigateDialog } from "../../components/hooks.ts";
import { useCallback, useEffect, useMemo, useState } from "react";
import { useAtom, useAtomValue } from "jotai";
import {
	activeDialogIdState,
	authLdapCredentialsState,
	authLdapTotpState,
	serverTimeOffsetState
} from "../../api/_stores.ts";
import dayjs from "dayjs";
import { ApiCommand, ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import {
	API_COMMAND_TYPE_NEED_SETUP_TOTP,
	API_COMMAND_TYPE_NEED_TOTP_CODE,
	APINeedSetupTotpCommandData
} from "../../api/_types.ts";
import { useApiFederationLdapAuthGetToken } from "../../api/auth/ldap.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import Preloader24 from "../../components/Preloader24.tsx";
import Preloader40 from "../../components/Preloader40.tsx";

type AuthLdap2FaSetupTotpDialogContentProps = {
	isLoading: boolean
	isExpired: boolean
	totpLink: string
	onRefreshSecretKeyClickHandler: () => void
}

const AuthLdap2FaSetupTotpDialogContentDesktop = ({
	totpLink,
	isLoading,
	isExpired,
	onRefreshSecretKeyClickHandler
}: AuthLdap2FaSetupTotpDialogContentProps) => {

	const langStringLdap2FaSetupTotpDialogTitle = useLangString("ldap_2fa_setup_totp_dialog.title");
	const langStringLdap2FaSetupTotpDialogDesc = useLangString("ldap_2fa_setup_totp_dialog.desc");
	const langStringLdap2FaSetupTotpDialogConfirmButton = useLangString("ldap_2fa_setup_totp_dialog.confirm_button");

	const { navigateToDialog } = useNavigateDialog();

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="20px" minW="100%">
				<KeyIcon80 />
				<Text mt="16px" style="lato_18_24_900" ls="-02">
					{langStringLdap2FaSetupTotpDialogTitle}
				</Text>
				<Text mt="6px" textAlign="center" style="lato_14_20_400" ls="-015" maxW="328px"
					  overflow="wrapEllipsis">
					{langStringLdap2FaSetupTotpDialogDesc}
				</Text>
				<Box
					mt="16px"
					position="relative"
				>
					<Box
						borderWidth="1px"
						borderColor="rgba(240, 240, 240, 1)"
						borderRadius="16px"
						padding="15px"
						filter={isExpired ? "blur(4px)" : "none"}
					>
						<ReactQRCode
							value={totpLink}
							level="L"
							size={163}
							marginSize={0}
							dataModulesSettings={{ color: "rgba(51, 62, 73, 0.8)", style: "circle" }}
							finderPatternInnerSettings={{ color: "rgba(51, 62, 73, 0.8)", style: "rounded-lg" }}
							finderPatternOuterSettings={{ color: "rgba(51, 62, 73, 0.8)", style: "rounded-lg" }}
						/>
					</Box>
					{isExpired && (
						isLoading ? (
							<Box
								position="absolute"
								top="50%"
								left="50%"
								transform="translate(-50%, -50%)"
							>
								<Preloader24 />
							</Box>
						) : (
							<Box
								position="absolute"
								top="50%"
								left="50%"
								transform="translate(-50%, -50%)"
								cursor="pointer"
								onClick={onRefreshSecretKeyClickHandler}
							>
								<svg width="30" height="30" viewBox="0 0 30 30" fill="none"
									 xmlns="http://www.w3.org/2000/svg">
									<path
										d="M15.2235 7.76751C13.9805 7.77656 12.7467 8.05045 11.6067 8.57896C9.93028 9.35619 8.54296 10.6442 7.64356 12.2584C6.74416 13.8725 6.37886 15.73 6.60001 17.5645C6.82117 19.399 7.61742 21.1165 8.87464 22.4707C10.1319 23.8248 11.7855 24.7462 13.5986 25.1028C15.4117 25.4594 17.2911 25.2328 18.9676 24.4556C20.644 23.6784 22.0313 22.3904 22.9307 20.7762C23.8301 19.1621 24.1954 17.3046 23.9742 15.4701C23.9123 14.956 24.2787 14.4891 24.7928 14.4271C25.3068 14.3651 25.7738 14.7316 25.8358 15.2457C26.1043 17.4733 25.6607 19.7288 24.5686 21.6888C23.4765 23.6489 21.7919 25.2129 19.7562 26.1567C17.7206 27.1005 15.4384 27.3756 13.2368 26.9426C11.0352 26.5096 9.02718 25.3907 7.50054 23.7464C5.97391 22.102 5.00703 20.0166 4.73849 17.7889C4.46995 15.5613 4.91353 13.3058 6.00566 11.3457C7.09778 9.38567 8.78239 7.82168 10.818 6.8779C12.2131 6.23111 13.7239 5.89837 15.2452 5.89236L13.7584 4.34592C13.3996 3.97268 13.4113 3.3792 13.7845 3.02035C14.1577 2.6615 14.7512 2.67317 15.1101 3.04641L18.173 6.23223C18.5319 6.60547 18.5202 7.19895 18.147 7.5578L14.9612 10.6208C14.5879 10.9796 13.9944 10.968 13.6356 10.5947C13.2767 10.2215 13.2884 9.628 13.6617 9.26915L15.2235 7.76751Z"
										fill="#007AFF" />
								</svg>
							</Box>
						)
					)}
				</Box>
				<Button
					mt="24px"
					size="px12py6full"
					textSize="lato_15_23_600"
					rounded="6px"
					disabled={isExpired}
					onClick={() => navigateToDialog("auth_ldap_2fa_confirm_totp")}
				>
					{langStringLdap2FaSetupTotpDialogConfirmButton}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdap2FaSetupTotpDialogContentMobile = ({
	totpLink,
	isLoading,
	isExpired,
	onRefreshSecretKeyClickHandler
}: AuthLdap2FaSetupTotpDialogContentProps) => {

	const langStringLdapLoginDialogBackButton = useLangString("ldap_login_dialog.back_button");
	const langStringLdap2FaSetupTotpDialogTitle = useLangString("ldap_2fa_setup_totp_dialog.title");
	const langStringLdap2FaSetupTotpDialogDesc = useLangString("ldap_2fa_setup_totp_dialog.desc");
	const langStringLdap2FaSetupTotpDialogConfirmButton = useLangString("ldap_2fa_setup_totp_dialog.confirm_button");

	const { navigateToDialog } = useNavigateDialog();

	const screenWidth = useMemo(() => document.body.clientWidth, [ document.body.clientWidth ]);

	return (
		<VStack w="100%" gap="0px">
			<Box w="100%">
				<Button
					color="2574a9"
					textSize="lato_16_22_400"
					size="px0py0"
					onClick={() => navigateToDialog("auth_sso_ldap")}
					disabled={isLoading}
				>
					{langStringLdapLoginDialogBackButton}
				</Button>
			</Box>
			<VStack gap="0px" mt="-6px" minW="100%">
				<KeyIcon80 />
				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringLdap2FaSetupTotpDialogTitle}
				</Text>
				<Text
					mt="4px"
					textAlign="center"
					style="lato_16_22_400"
					maxW={screenWidth <= 390 ? "326px" : "350px"}
					overflow="wrapEllipsis"
				>
					{langStringLdap2FaSetupTotpDialogDesc}
				</Text>
				<Box
					mt="16px"
					position="relative"
				>
					<Box
						borderWidth="1px"
						borderColor="rgba(240, 240, 240, 1)"
						borderRadius="12px"
						filter={isExpired ? "blur(4px)" : "none"}
						padding="20px"
					>
						<ReactQRCode
							value={totpLink}
							level="L"
							size={294}
							marginSize={0}
							dataModulesSettings={{ color: "rgba(51, 62, 73, 0.8)", style: "circle" }}
							finderPatternInnerSettings={{ color: "rgba(51, 62, 73, 0.8)", style: "rounded-lg" }}
							finderPatternOuterSettings={{ color: "rgba(51, 62, 73, 0.8)", style: "rounded-lg" }}
						/>
					</Box>
					{isExpired && (
						isLoading ? (
							<Box
								position="absolute"
								top="50%"
								left="50%"
								transform="translate(-50%, -50%)"
							>
								<Preloader40 />
							</Box>
						) : (
							<Box
								position="absolute"
								top="50%"
								left="50%"
								transform="translate(-50%, -50%)"
								cursor="pointer"
								onClick={onRefreshSecretKeyClickHandler}
							>
								<svg width="64" height="64" viewBox="0 0 64 64" fill="none"
									 xmlns="http://www.w3.org/2000/svg">
									<path
										d="M32.4763 16.5707C29.8245 16.59 27.1923 17.1743 24.7604 18.3018C21.1841 19.9599 18.2245 22.7076 16.3057 26.1512C14.387 29.5947 13.6077 33.5573 14.0795 37.471C14.5513 41.3847 16.25 45.0485 18.9321 47.9374C21.6141 50.8264 25.142 52.792 29.0099 53.5527C32.8778 54.3134 36.8873 53.8301 40.4636 52.172C44.0399 50.5139 46.9995 47.7662 48.9183 44.3226C50.837 40.8791 51.6163 36.9165 51.1445 33.0028C51.0123 31.9062 51.7941 30.91 52.8908 30.7778C53.9874 30.6456 54.9836 31.4275 55.1157 32.5241C55.6886 37.2764 54.7423 42.0881 52.4125 46.2696C50.0826 50.451 46.4888 53.7875 42.1461 55.8009C37.8034 57.8143 32.9348 58.4012 28.238 57.4775C23.5412 56.5538 19.2575 54.167 16.0006 50.659C12.7438 47.151 10.6811 42.702 10.1083 37.9497C9.53537 33.1974 10.4817 28.3857 12.8115 24.2042C15.1414 20.0228 18.7352 16.6863 23.0779 14.6729C26.054 13.2931 29.2771 12.5832 32.5227 12.5704L29.3508 9.27134C28.5853 8.47509 28.6102 7.209 29.4064 6.44346C30.2027 5.67791 31.4687 5.7028 32.2343 6.49905L38.7686 13.2955C39.5342 14.0917 39.5093 15.3578 38.713 16.1233L31.9166 22.6577C31.1204 23.4232 29.8543 23.3983 29.0887 22.6021C28.3232 21.8058 28.3481 20.5398 29.1443 19.7742L32.4763 16.5707Z"
										fill="#007AFF" />
								</svg>
							</Box>
						)
					)}
				</Box>
				<Button
					mt="28px"
					size="px16py9full"
					textSize="lato_17_26_600"
					disabled={isExpired}
					onClick={() => navigateToDialog("auth_ldap_2fa_confirm_totp")}
				>
					{langStringLdap2FaSetupTotpDialogConfirmButton}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdap2FaSetupTotpDialogContent = () => {

	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const isMobile = useIsMobile();

	const apiFederationLdapAuthGetToken = useApiFederationLdapAuthGetToken();

	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const { navigateToDialog } = useNavigateDialog();

	const authLdapCredentials = useAtomValue(authLdapCredentialsState);
	const [ authLdapTotp, setAuthLdapTotp ] = useAtom(authLdapTotpState);
	const serverTimeOffset = useAtomValue(serverTimeOffsetState);

	const checkExpired = useCallback(() => {
		return (authLdapTotp.expires_at - (dayjs().unix() + serverTimeOffset)) < 1
	}, [ authLdapTotp ]);

	const [ isLoading, setIsLoading ] = useState(false);
	const [ isExpired, setIsExpired ] = useState<boolean>(checkExpired());

	// обновляем таймер, когда пользователь вернулся на страницу
	// нужно для того, чтобы правильно обновить таймер при выходе из бэкграунда мобильных устройств
	useEffect(() => {
		setIsExpired(checkExpired());
	}, [ authLdapTotp.expires_at, serverTimeOffset ]);

	const startExpiredCheck = useCallback(() => {
		const timer = setInterval(() => {
			setIsExpired(checkExpired());
		}, 1000);

		return () => clearInterval(timer);
	}, [ checkExpired ]);

	useEffect(() => startExpiredCheck(), [ startExpiredCheck ]);

	const onRefreshSecretKeyClickHandler = useCallback(async () => {
		if (authLdapCredentials.username.length < 1 || authLdapCredentials.password.length < 1) {

			navigateToDialog("auth_sso_ldap");
			return;
		}

		if (apiFederationLdapAuthGetToken.isLoading) {
			return;
		}

		try {
			setIsLoading(true);
			await apiFederationLdapAuthGetToken.mutateAsync({
				username: authLdapCredentials.username,
				password: authLdapCredentials.password,
			});
			setIsLoading(false);
		} catch (error) {
			if (error instanceof NetworkError || error instanceof ServerError) {
				showToast(error instanceof NetworkError ? langStringErrorsNetworkError : langStringErrorsServerError, "warning");
				setIsLoading(false);
				return;
			}

			if (error instanceof ApiCommand) {

				if (error.type === API_COMMAND_TYPE_NEED_TOTP_CODE) {

					navigateToDialog("auth_ldap_2fa_confirm_totp");
					setIsLoading(false);
					return;
				}
				if (error.type === API_COMMAND_TYPE_NEED_SETUP_TOTP) {

					setAuthLdapTotp(error.data as unknown as APINeedSetupTotpCommandData);
					setIsLoading(false);
					return;
				}
			}

			if (error instanceof ApiError) {
				setIsLoading(false);
			}
		}
		setIsLoading(false);
	}, [ authLdapCredentials ]);

	if (isMobile) {

		return <AuthLdap2FaSetupTotpDialogContentMobile
			isLoading={isLoading}
			isExpired={isExpired}
			totpLink={authLdapTotp.otpauth_uri}
			onRefreshSecretKeyClickHandler={onRefreshSecretKeyClickHandler}
		/>
	}

	return <AuthLdap2FaSetupTotpDialogContentDesktop
		isLoading={isLoading}
		isExpired={isExpired}
		totpLink={authLdapTotp.otpauth_uri}
		onRefreshSecretKeyClickHandler={onRefreshSecretKeyClickHandler}
	/>
};

export default AuthLdap2FaSetupTotpDialogContent;
