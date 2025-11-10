import { useEffect, useMemo, useState } from "react";
import useIsMobile from "../../lib/useIsMobile.ts";
import { Box, HStack, styled, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { MailIcon80 } from "../../components/MailIcon80.tsx";
import { Button } from "../../components/button.tsx";
import { useAtomValue } from "jotai";
import {
	activeDialogIdState,
	authLdapCredentialsState,
	authLdapState,
	authState,
	joinLinkState,
	passwordInputState,
	prepareJoinLinkErrorState,
} from "../../api/_stores.ts";
import Preloader18 from "../../components/Preloader18.tsx";
import { useNavigateDialog } from "../../components/hooks.ts";
import Preloader18Opacity30 from "../../components/Preloader18Opacity30.tsx";
import { DesktopRefreshButton } from "../../components/DesktopRefreshButton.tsx";
import { MobileRefreshButton, MobileRefreshButtonSmall } from "../../components/MobileRefreshButton.tsx";
import dayjs from "dayjs";
import { plural } from "../../lib/plural.ts";
import {
	ALREADY_MEMBER_ERROR_CODE,
	API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL,
	API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL,
	API_COMMAND_SCENARIO_DATA_STAGE_GET_LDAP_AUTH_TOKEN,
	APIAuthInfo,
	APIAuthInfoDataTypeRegisterLoginResetPasswordByMail,
	APIAuthTypeLoginByMail,
	APIAuthTypeRegisterByMail,
	APIAuthTypeResetPasswordByMail,
	APICommandData,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	LIMIT_ERROR_CODE,
} from "../../api/_types.ts";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { useApiAuthMailCancel, useApiAuthMailConfirmFullAuthCode } from "../../api/auth/mail.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import { useSetAtom } from "jotai/index";
import { DynamicTimerEmail } from "../../components/DynamicTimerEmail.tsx";
import { useApiSecurityMailConfirmResetPassword } from "../../api/security/mail.ts";
import PinInput from "../../components/PinInput.tsx";
import {
	useApiFederationLdapAuthGetToken,
	useApiFederationLdapMailConfirm,
	useApiPivotAuthLdapBegin
} from "../../api/auth/ldap.ts";
import useLdap2FaStage from "../../lib/useLdap2FaStage.ts";

type ConfirmCodeEmailDialogContentProps = {
	auth: APIAuthInfo | null,
	authLdap: APICommandData | null,
	isLdapConfirm: boolean,
	isAuthBlocked: boolean,
	isCompleted: boolean,
	setCompleted: (value: boolean) => void,
	confirmCode: string,
	setConfirmCode: (value: string) => void,
	nextResend: number,
	setNextResend: (value: number) => void,
	nextAttempt: number,
	isLoading: boolean,
	setIsLoading: (value: boolean) => void,
	isSuccess: boolean,
	isError: boolean,
	setIsError: (value: boolean) => void,
	isNetworkError: boolean,
	setIsNetworkError: (value: boolean) => void,
	isServerError: boolean,
	setIsServerError: (value: boolean) => void,
	activeDialogId: string,
}

const ConfirmCodeEmailDialogContentDesktop = ({
	auth,
	authLdap,
	isLdapConfirm,
	isAuthBlocked,
	isCompleted,
	setCompleted,
	confirmCode,
	setConfirmCode,
	nextResend,
	setNextResend,
	nextAttempt,
	isLoading,
	setIsLoading,
	isSuccess,
	isError,
	setIsError,
	isNetworkError,
	setIsNetworkError,
	isServerError,
	setIsServerError,
	activeDialogId,
}: ConfirmCodeEmailDialogContentProps) => {
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const langStringConfirmCodeEmailDialogTitle = useLangString("confirm_code_email_dialog.title");
	const langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmCurrent = useLangString("confirm_code_email_dialog.title_ldap_change_mail_confirm_current");
	const langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmNew = useLangString("confirm_code_email_dialog.title_ldap_change_mail_confirm_new");
	const langStringConfirmCodeEmailDialogDesc = useLangString("confirm_code_email_dialog.desc");
	const langStringConfirmCodeEmailDialogDescLdapChangeMailConfirmCurrent = useLangString("confirm_code_email_dialog.desc_ldap_change_mail_confirm_current");
	const langStringConfirmCodeEmailDialogBackButton = useLangString("confirm_code_email_dialog.back_button");
	const langStringConfirmCodeEmailDialogAuthBlocked = useLangString("confirm_code_email_dialog.auth_blocked");

	const apiAuthMailCancel = useApiAuthMailCancel();
	const { navigateToDialog } = useNavigateDialog();

	const renderedPreloaderButton = useMemo(() => {
		if (isNetworkError) {
			return <DesktopRefreshButton setIsServerError = {setIsNetworkError} setIsCompleted = {setCompleted} />;
		}

		if (isServerError) {
			return <DesktopRefreshButton setIsServerError = {setIsServerError} setIsCompleted = {setCompleted} />;
		}

		if (isCompleted || isLoading) {
			return <Preloader18 />;
		}

		return <></>;
	}, [ isCompleted, isServerError, isNetworkError, isLoading ]);

	const renderedPinInput = useMemo(() => {
		if (isAuthBlocked && (auth !== null || authLdap !== null)) {
			return (
				<Text
					mt = "16px"
					py = "10px"
					px = "16px"
					w = "100%"
					bgColor = "255106100.01"
					color = "333e49"
					textAlign = "center"
					rounded = "8px"
					style = "lato_13_18_400"
				>
					{langStringConfirmCodeEmailDialogAuthBlocked.replace(
						"$MINUTES",
						`${Math.ceil((nextAttempt - dayjs().unix()) / 60)}${plural(
							Math.ceil((nextAttempt - dayjs().unix()) / 60),
							langStringOneMinute,
							langStringTwoMinutes,
							langStringFiveMinutes
						)}`
					)}
				</Text>
			);
		}

		return (
			<HStack w = "100%" gap = "10px" justify = "center" mt = "20px">
				<Box w = "20px" h = "20px" flexShrink = "0" />
				<PinInput
					confirmCode = {confirmCode}
					onChange = {(newValue: string) => {
						setConfirmCode(newValue);
						setIsError(false);
					}}
					onComplete = {() => setCompleted(confirmCode.length === 6)}
					isError = {isError}
					isSuccess = {isSuccess}
					isCompleted = {isCompleted}
					style = "Desktop"
				/>
				<Box w = "20px" h = "20px" flexShrink = "0">
					{renderedPreloaderButton}
				</Box>
			</HStack>
		);
	}, [ confirmCode, auth, authLdap, isSuccess, isError, isCompleted, isLoading, nextAttempt, isAuthBlocked ]);

	return (
		<VStack w = "100%" gap = "0px">
			<VStack gap = "0px" mt = "20px">
				<MailIcon80 />

				<Text mt = "16px" style = "lato_18_24_900" ls = "-02">
					{authLdap === null ? langStringConfirmCodeEmailDialogTitle : (
						authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL ? langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmCurrent
							: authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL ? langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmNew : langStringConfirmCodeEmailDialogTitle
					)}
				</Text>

				<Text
					mt = "6px"
					textAlign = "center"
					style = "lato_14_20_400"
					ls = "-015"
					maxW = "328px"
					overflow = "wrapEllipsis"
					userSelect = "text"
				>
					{(authLdap !== null && authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL)
						? langStringConfirmCodeEmailDialogDescLdapChangeMailConfirmCurrent : langStringConfirmCodeEmailDialogDesc}
					<styled.span fontFamily = "lato_bold">
						«{isLdapConfirm ? authLdap?.scenario_data.mail_mask : (auth?.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				{renderedPinInput}

				<HStack w = "100%" justify = "space-between" mt = "22px">
					<Button
						className = "confirm_code_next_button"
						size = "px8py8"
						textSize = "md_desktop"
						color = "2574a9"
						onClick = {() => {
							if (auth?.type == APIAuthTypeRegisterByMail) {
								navigateToDialog("auth_email_register");
								return;
							}

							if (isLdapConfirm) {

								if (authLdap !== null && authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL) {

									navigateToDialog("auth_ldap_2fa_attach_mail");
									return;
								}

								navigateToDialog("auth_sso_ldap");
								return;
							}

							apiAuthMailCancel.mutate({ auth_key: auth?.auth_key ?? "" });
						}}
					>
						<HStack gap = "2px">
							<svg
								className = "next_button_svg_icon"
								width = "14"
								height = "14"
								viewBox = "0 0 14 14"
								fill = "none"
								xmlns = "http://www.w3.org/2000/svg"
							>
								<path
									d = "M0.702026 6.82698L4.40703 3.11548L4.80353 3.52498C4.89019 3.61165 4.92269 3.70048 4.90103 3.79148C4.88369 3.87814 4.83819 3.96048 4.76453 4.03848L2.89903 5.89098C2.69536 6.09465 2.50903 6.26148 2.34003 6.39148C2.55669 6.36548 2.78419 6.34381 3.02253 6.32648C3.26519 6.30915 3.51436 6.30048 3.77003 6.30048H12.233V7.35998H3.77003C3.51436 7.35998 3.26519 7.35131 3.02253 7.33398C2.78419 7.31665 2.55669 7.29498 2.34003 7.26898C2.50036 7.39031 2.68669 7.55498 2.89903 7.76298L4.77753 9.63498C4.85553 9.71298 4.90319 9.79531 4.92053 9.88198C4.93786 9.96865 4.90536 10.0553 4.82303 10.142L4.42003 10.558L0.702026 6.82698Z"
									fill = "#2574A9"
								/>
							</svg>
							<styled.span fontSize = "13px" lineHeight = "18px">
								{langStringConfirmCodeEmailDialogBackButton}
							</styled.span>
						</HStack>
					</Button>
					<DynamicTimerEmail
						key = "desktop_dynamic_timer"
						endTimeUnix = {nextResend}
						setNextResend = {setNextResend}
						setConfirmCode = {setConfirmCode}
						setIsLoading = {setIsLoading}
						setIsError = {setIsError}
						setCompleted = {setCompleted}
						size = "px8py8"
						textSize = "lato_13_18_400"
						isCompleted = {isCompleted}
						authKey = {auth?.auth_key}
						authType = {auth?.type}
						mailConfirmStoryKey = {authLdap?.mail_confirm_story_key}
						isLdapConfirm = {isLdapConfirm}
						activeDialogId = {activeDialogId}
					/>
				</HStack>
			</VStack>
		</VStack>
	);
};

type ConfirmCodePhoneNumberDialogContentMobilePinInputProps = {
	isCompleted: boolean;
	setCompleted: (value: boolean) => void;
	isError: boolean;
	setIsError: (value: boolean) => void;
	isSuccess: boolean;
	confirmCode: string;
	setConfirmCode: (value: string) => void;
};

const ConfirmCodePhoneNumberDialogContentMobilePinInput = ({
	isCompleted,
	setCompleted,
	isError,
	setIsError,
	isSuccess,
	confirmCode,
	setConfirmCode,
}: ConfirmCodePhoneNumberDialogContentMobilePinInputProps) => {
	const screenWidth = useMemo(() => document.body.clientWidth, [ document.body.clientWidth ]);

	if (screenWidth <= 375) {
		return (
			<PinInput
				confirmCode = {confirmCode}
				onChange = {(newValue: string) => {
					setConfirmCode(newValue);
					setIsError(false);
				}}
				onComplete = {() => setCompleted(confirmCode.length === 6)}
				isError = {isError}
				isSuccess = {isSuccess}
				isCompleted = {isCompleted}
				style = "MobileSmall"
			/>
		);
	}

	return (
		<PinInput
			confirmCode = {confirmCode}
			onChange = {(newValue: string) => {
				setConfirmCode(newValue);
				setIsError(false);
			}}
			onComplete = {() => setCompleted(confirmCode.length === 6)}
			isError = {isError}
			isSuccess = {isSuccess}
			isCompleted = {isCompleted}
			style = "Mobile"
		/>
	);
};

const ConfirmCodeEmailDialogContentMobile = ({
	auth,
	authLdap,
	isLdapConfirm,
	isAuthBlocked,
	isCompleted,
	setCompleted,
	confirmCode,
	setConfirmCode,
	nextResend,
	setNextResend,
	nextAttempt,
	isLoading,
	setIsLoading,
	isSuccess,
	isError,
	setIsError,
	isNetworkError,
	setIsNetworkError,
	isServerError,
	setIsServerError,
	activeDialogId,
}: ConfirmCodeEmailDialogContentProps) => {
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const langStringConfirmCodeEmailDialogTitle = useLangString("confirm_code_email_dialog.title");
	const langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmCurrent = useLangString("confirm_code_email_dialog.title_ldap_change_mail_confirm_current");
	const langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmNew = useLangString("confirm_code_email_dialog.title_ldap_change_mail_confirm_new");
	const langStringConfirmCodeEmailDialogDesc = useLangString("confirm_code_email_dialog.desc");
	const langStringConfirmCodeEmailDialogDescLdapChangeMailConfirmCurrent = useLangString("confirm_code_email_dialog.desc_ldap_change_mail_confirm_current");
	const langStringConfirmCodeEmailDialogBackButton = useLangString("confirm_code_email_dialog.back_button");
	const langStringConfirmCodeEmailDialogAuthBlocked = useLangString("confirm_code_email_dialog.auth_blocked");

	const apiAuthMailCancel = useApiAuthMailCancel();
	const { navigateToDialog } = useNavigateDialog();

	const screenWidth = useMemo(() => document.body.clientWidth, [ document.body.clientWidth ]);

	const renderedPreloaderButton = useMemo(() => {
		if (isServerError) {
			if (screenWidth <= 375) {
				return (
					<MobileRefreshButtonSmall
						key = "mobile_refresh_button_small"
						setIsServerError = {setIsServerError}
						setIsCompleted = {setCompleted}
					/>
				);
			}

			return (
				<MobileRefreshButton
					key = "mobile_refresh_button"
					setIsServerError = {setIsServerError}
					setIsCompleted = {setCompleted}
				/>
			);
		}
		if (isNetworkError) {
			if (screenWidth <= 375) {
				return (
					<MobileRefreshButtonSmall
						key = "mobile_refresh_button_small"
						setIsServerError = {setIsNetworkError}
						setIsCompleted = {setCompleted}
					/>
				);
			}

			return (
				<MobileRefreshButton
					key = "mobile_refresh_button"
					setIsServerError = {setIsNetworkError}
					setIsCompleted = {setCompleted}
				/>
			);
		}

		if (isCompleted || isLoading) {
			return <Preloader18Opacity30 key = "mobile_preloader18_opacity30" />;
		}

		return <></>;
	}, [ isCompleted, isServerError, isNetworkError, isLoading, screenWidth ]);

	return (
		<VStack w = "100%" gap = "0px">
			<VStack gap = "0px" mt = "16px">
				<MailIcon80 />

				<Text mt = "16px" style = "lato_20_28_700" ls = "-03">
					{authLdap === null ? langStringConfirmCodeEmailDialogTitle : (
						authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL ? langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmCurrent
							: authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL ? langStringConfirmCodeEmailDialogTitleLdapChangeMailConfirmNew : langStringConfirmCodeEmailDialogTitle
					)}
				</Text>

				<Text
					mt = "4px"
					textAlign = "center"
					style = "lato_16_22_400"
					maxW = {screenWidth <= 390 ? "326px" : "350px"}
					overflow = "wrapEllipsis"
					userSelect = "text"
				>
					{(authLdap !== null && authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL)
						? langStringConfirmCodeEmailDialogDescLdapChangeMailConfirmCurrent : langStringConfirmCodeEmailDialogDesc}
					<styled.span fontFamily = "lato_bold">
						«{isLdapConfirm ? authLdap?.scenario_data.mail_mask : (auth?.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				{isAuthBlocked ? (
					<Text
						mt = "18px"
						p = "12px"
						w = "100%"
						bgColor = "255106100.01"
						color = "333e49"
						textAlign = "center"
						rounded = "8px"
						style = "lato_16_22_400"
					>
						{langStringConfirmCodeEmailDialogAuthBlocked.replace(
							"$MINUTES",
							`${Math.ceil((nextAttempt - dayjs().unix()) / 60)}${plural(
								Math.ceil((nextAttempt - dayjs().unix()) / 60),
								langStringOneMinute,
								langStringTwoMinutes,
								langStringFiveMinutes
							)}`
						)}
					</Text>
				) : screenWidth <= 375 ? (
					<HStack key = "mobile_small_pininput" w = "100%" gap = "8px" justify = "center" mt = "24px">
						<Box w = "18px" h = "18px" flexShrink = "0" />
						<ConfirmCodePhoneNumberDialogContentMobilePinInput
							isCompleted = {isCompleted}
							setCompleted = {setCompleted}
							isError = {isError}
							setIsError = {setIsError}
							isSuccess = {isSuccess}
							confirmCode = {confirmCode}
							setConfirmCode = {setConfirmCode}
						/>
						<Box w = "18px" h = "18px" display = "flex" alignItems = "center" flexShrink = "0">
							{renderedPreloaderButton}
						</Box>
					</HStack>
				) : (
					<HStack key = "mobile_pininput" w = "100%" gap = "8px" justify = "center" mt = "24px">
						<Box w = "28px" h = "28px" flexShrink = "0" />
						<ConfirmCodePhoneNumberDialogContentMobilePinInput
							isCompleted = {isCompleted}
							setCompleted = {setCompleted}
							isError = {isError}
							setIsError = {setIsError}
							isSuccess = {isSuccess}
							confirmCode = {confirmCode}
							setConfirmCode = {setConfirmCode}
						/>
						<Box w = "28px" h = "28px" display = "flex" alignItems = "center" flexShrink = "0">
							{renderedPreloaderButton}
						</Box>
					</HStack>
				)}

				<HStack w = "100%" justify = "space-between" pt = "28px">
					<Button
						className = "confirm_code_next_button"
						size = {screenWidth <= 375 ? "px0py8" : "px8py8"}
						textSize = "lato_16_22_400"
						color = "2574a9"
						disabled = {isCompleted}
						onClick = {() => {
							if (auth?.type == APIAuthTypeRegisterByMail) {
								navigateToDialog("auth_email_register");
								return;
							}

							if (isLdapConfirm) {

								if (authLdap !== null && authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL) {

									navigateToDialog("auth_ldap_2fa_attach_mail");
									return;
								}

								navigateToDialog("auth_sso_ldap");
								return;
							}

							apiAuthMailCancel.mutate({ auth_key: auth?.auth_key ?? "" });
						}}
					>
						<HStack gap = "4px">
							<svg
								className = "next_button_svg_icon"
								width = "16"
								height = "14"
								viewBox = "0 0 16 14"
								fill = "none"
								xmlns = "http://www.w3.org/2000/svg"
							>
								<path
									d = "M0.86377 6.8639L5.42377 2.2959L5.91177 2.7999C6.01844 2.90657 6.05844 3.0159 6.03177 3.1279C6.01044 3.23456 5.95444 3.3359 5.86377 3.4319L3.56777 5.7119C3.3171 5.96257 3.08777 6.1679 2.87977 6.3279C3.14644 6.2959 3.42644 6.26923 3.71977 6.2479C4.01844 6.22657 4.3251 6.2159 4.63977 6.2159H15.0558V7.5199H4.63977C4.3251 7.5199 4.01844 7.50923 3.71977 7.4879C3.42644 7.46657 3.14644 7.4399 2.87977 7.4079C3.0771 7.55723 3.30644 7.7599 3.56777 8.0159L5.87977 10.3199C5.97577 10.4159 6.03444 10.5172 6.05577 10.6239C6.0771 10.7306 6.0371 10.8372 5.93577 10.9439L5.43977 11.4559L0.86377 6.8639Z"
									fill = "#2574A9"
								/>
							</svg>
							<styled.span fontSize = "16px" lineHeight = "22px">
								{langStringConfirmCodeEmailDialogBackButton}
							</styled.span>
						</HStack>
					</Button>
					<DynamicTimerEmail
						key = "mobile_dynamic_timer"
						endTimeUnix = {nextResend}
						setNextResend = {setNextResend}
						setConfirmCode = {setConfirmCode}
						setIsLoading = {setIsLoading}
						setIsError = {setIsError}
						setCompleted = {setCompleted}
						size = {screenWidth <= 375 ? "px0py8" : "px8py8"}
						textSize = "lato_16_22_400"
						isCompleted = {isCompleted}
						authKey = {auth?.auth_key}
						authType = {auth?.type}
						mailConfirmStoryKey = {authLdap?.mail_confirm_story_key}
						isLdapConfirm = {isLdapConfirm}
						activeDialogId = {activeDialogId}
					/>
				</HStack>
			</VStack>
		</VStack>
	);
};

const ConfirmCodeEmailDialogContent = () => {
	const isMobile = useIsMobile();

	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsConfirmCodeConfirmIsExpiredError = useLangString("errors.confirm_code_confirm_is_expired_error");
	const langStringErrorsConfirmCode2FaIsDisabledError = useLangString("errors.confirm_code_2fa_is_disabled_error");
	const langStringErrorsConfirmCodeIncorrectCodeError = useLangString("errors.confirm_code_incorrect_code_error");

	const apiAuthMailConfirmFullAuthCode = useApiAuthMailConfirmFullAuthCode();
	const apiSecurityMailConfirmResetPassword = useApiSecurityMailConfirmResetPassword();

	const apiFederationLdapMailConfirm = useApiFederationLdapMailConfirm();
	const apiFederationLdapAuthGetToken = useApiFederationLdapAuthGetToken();
	const apiPivotAuthLdapBegin = useApiPivotAuthLdapBegin();

	const { navigateToDialog } = useNavigateDialog();
	const { navigateByStage } = useLdap2FaStage();

	const joinLink = useAtomValue(joinLinkState);
	const auth = useAtomValue(authState);
	const authLdap = useAtomValue(authLdapState);
	const authLdapCredentials = useAtomValue(authLdapCredentialsState);
	const setPrepareJoinLinkError = useSetAtom(prepareJoinLinkErrorState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const setPasswordInput = useSetAtom(passwordInputState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);

	const isLdapConfirm = useMemo(() => authLdap !== null, [ authLdap ])
	const [ isAuthBlocked, setIsAuthBlocked ] = useState(false);
	const [ isCompleted, setCompleted ] = useState<boolean>(false);
	const [ confirmCode, setConfirmCode ] = useState<string>("");
	const [ nextResend, setNextResend ] = useState(0);
	const [ nextAttempt, setNextAttempt ] = useState(0);
	const [ isLoading, setIsLoading ] = useState(false);
	const [ isSuccess, setIsSuccess ] = useState(false);
	const [ isError, setIsError ] = useState(false);
	const [ isNetworkError, setIsNetworkError ] = useState(false);
	const [ isServerError, setIsServerError ] = useState(false);

	useEffect(() => {
		// сбрасываем пароль если это логин
		if (auth !== null && auth.type === APIAuthTypeLoginByMail) {
			setPasswordInput("");
		}
	}, []);

	// next_resend для обычной почты
	useEffect(() => {
		if (
			auth === null ||
			(auth.type !== APIAuthTypeRegisterByMail &&
				auth.type !== APIAuthTypeLoginByMail &&
				auth.type !== APIAuthTypeResetPasswordByMail)
		) {
			return;
		}

		if ((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).code_available_attempts < 1) {
			setIsAuthBlocked(true);
			setNextAttempt((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).expire_at);
			setNextResend((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).expire_at);
			return;
		}

		setNextResend((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).next_resend);
	}, [ auth ]);

	// next_resend для 2fa ldap
	useEffect(() => {
		if (authLdap === null) {
			return;
		}

		if (authLdap.scenario_data.code_available_attempts < 1) {
			setIsAuthBlocked(true);
			setNextAttempt(authLdap.scenario_data.expires_at);
			setNextResend(authLdap.scenario_data.expires_at);
			return;
		}

		setNextResend(authLdap.scenario_data.next_resend_at);
	}, [ authLdap ]);

	// подтверждение обычной авторизации
	useEffect(() => {
		if (isCompleted && auth !== null) {
			if (confirmCode.length != 6) {
				return;
			}

			if (auth.type === APIAuthTypeRegisterByMail || auth.type === APIAuthTypeLoginByMail) {
				apiAuthMailConfirmFullAuthCode.mutate(
					{
						auth_key: auth.auth_key,
						code: confirmCode,
						setIsSuccess: setIsSuccess,
						join_link_uniq: joinLink?.join_link_uniq ?? undefined,
					}, {
						onError: (error) => {
							if (error instanceof NetworkError) {
								setIsNetworkError(true);
								setCompleted(false);
								showToast(langStringErrorsNetworkError, "warning");
								return;
							}

							if (error instanceof ServerError) {
								setIsServerError(true);
								setCompleted(false);
								showToast(langStringErrorsServerError, "warning");
								return;
							}

							if (error instanceof ApiError) {

								switch (error.error_code) {
									case INCORRECT_LINK_ERROR_CODE:
									case INACTIVE_LINK_ERROR_CODE:
										setConfirmCode("");
										setCompleted(false);
										setPrepareJoinLinkError({ error_code: error.error_code });
										break;
									case 1708113:
										setIsError(true);
										setConfirmCode("");
										setCompleted(false);
										showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
										setTimeout(() => setIsError(false), 2900); // должен быть на 100ms меньше времени пропадания тостера
										break;
									case 1708399:
									case LIMIT_ERROR_CODE:
										setIsAuthBlocked(true);
										setNextAttempt(error.next_attempt);
										setConfirmCode("");
										setCompleted(false);
										break;
									default:
										setIsError(true);
										setCompleted(false);
										showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
										break;
								}
							}
						},
					}
				);

				return;
			}

			if (auth.type === APIAuthTypeResetPasswordByMail) {
				apiSecurityMailConfirmResetPassword.mutate(
					{
						auth_key: auth.auth_key,
						code: confirmCode,
						setIsSuccess: setIsSuccess,
					},
					{
						onError: (error) => {
							if (error instanceof NetworkError) {
								setIsNetworkError(true);
								setCompleted(false);
								showToast(langStringErrorsNetworkError, "warning");
								return;
							}

							if (error instanceof ServerError) {
								setIsServerError(true);
								setCompleted(false);
								showToast(langStringErrorsServerError, "warning");
								return;
							}

							if (error instanceof ApiError) {
								switch (error.error_code) {
									case INCORRECT_LINK_ERROR_CODE:
									case INACTIVE_LINK_ERROR_CODE:
										setConfirmCode("");
										setCompleted(false);
										setPrepareJoinLinkError({ error_code: error.error_code });
										break;
									case 1708113:
										setIsError(true);
										setConfirmCode("");
										setCompleted(false);
										showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
										setTimeout(() => setIsError(false), 2900); // должен быть на 100ms меньше времени пропадания тостера
										break;
									case 1708399:
									case LIMIT_ERROR_CODE:
										setIsAuthBlocked(true);
										setNextAttempt(error.next_attempt);
										setConfirmCode("");
										setCompleted(false);
										break;
									default:
										setIsError(true);
										setCompleted(false);
										showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
										break;
								}
							}
						},
					}
				);

				return;
			}
		}
	}, [ isCompleted, joinLink, auth ]);

	// подтверждение ldap авторизации
	useEffect(() => {
		if (isCompleted && authLdap !== null) {
			if (confirmCode.length != 6) {
				return;
			}

			setIsLoading(true);
			(async () => {
				try {
					const federationLdapMailConfirmResponse = await apiFederationLdapMailConfirm.mutateAsync(
						{
							mail_confirm_story_key: authLdap.mail_confirm_story_key,
							confirm_code: confirmCode,
							setIsSuccess,
						}
					);

					// если время получать токен не пришло и нужно сходить в другую локацию
					if (federationLdapMailConfirmResponse.ldap_mail_confirm_story_info.scenario_data.stage !== API_COMMAND_SCENARIO_DATA_STAGE_GET_LDAP_AUTH_TOKEN) {

						navigateByStage(federationLdapMailConfirmResponse.ldap_mail_confirm_story_info);
						return;
					}

					const { ldap_auth_token } = await apiFederationLdapAuthGetToken.mutateAsync({
						username: authLdapCredentials.username,
						password: authLdapCredentials.password,
						mail_confirm_story_key: authLdap.mail_confirm_story_key,
					});

					await apiPivotAuthLdapBegin.mutateAsync({
						ldap_auth_token,
						join_link:
							prepareJoinLinkError === null || prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE
								? window.location.href
								: undefined,
					});
				} catch (error) {
					if (error instanceof NetworkError) {
						setIsNetworkError(true);
						showToast(langStringErrorsNetworkError, "warning");
					} else if (error instanceof ServerError) {
						setIsServerError(true);
						showToast(langStringErrorsServerError, "warning");
					} else if (error instanceof ApiError) {
						switch (error.error_code) {
							case 1708005:
							case 1708006:
							case 1708008:
							case 1708013:
								navigateToDialog("auth_sso_ldap");
								showToast(langStringErrorsConfirmCodeConfirmIsExpiredError, "warning");
								break;
							case 1708007:
								setIsError(true);
								setConfirmCode("");
								showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
								setTimeout(() => setIsError(false), 2900);
								break;
							case 1708009:
								setIsAuthBlocked(true);
								setNextAttempt(error.expires_at);
								setConfirmCode("");
								break;
							case 1708016:
								navigateToDialog("auth_sso_ldap");
								showToast(langStringErrorsConfirmCode2FaIsDisabledError, "warning");
								break;
							default:
								setIsError(true);
								showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
								break;
						}
					}
				} finally {
					// в любом случае выключаем спиннер
					setIsLoading(false);
					setCompleted(false);
				}
			})();
		}
	}, [ isCompleted, joinLink, authLdap ]);

	if (auth === null && authLdap === null) {

		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	if (isMobile) {
		return <ConfirmCodeEmailDialogContentMobile
			auth = {auth}
			authLdap = {authLdap}
			isLdapConfirm = {isLdapConfirm}
			isAuthBlocked = {isAuthBlocked}
			isCompleted = {isCompleted}
			setCompleted = {setCompleted}
			confirmCode = {confirmCode}
			setConfirmCode = {setConfirmCode}
			nextResend = {nextResend}
			setNextResend = {setNextResend}
			nextAttempt = {nextAttempt}
			isLoading = {isLoading}
			setIsLoading = {setIsLoading}
			isSuccess = {isSuccess}
			isError = {isError}
			setIsError = {setIsError}
			isNetworkError = {isNetworkError}
			setIsNetworkError = {setIsNetworkError}
			isServerError = {isServerError}
			setIsServerError = {setIsServerError}
			activeDialogId = {activeDialogId}
		/>;
	}

	return <ConfirmCodeEmailDialogContentDesktop
		auth = {auth}
		authLdap = {authLdap}
		isLdapConfirm = {isLdapConfirm}
		isAuthBlocked = {isAuthBlocked}
		isCompleted = {isCompleted}
		setCompleted = {setCompleted}
		confirmCode = {confirmCode}
		setConfirmCode = {setConfirmCode}
		nextResend = {nextResend}
		setNextResend = {setNextResend}
		nextAttempt = {nextAttempt}
		isLoading = {isLoading}
		setIsLoading = {setIsLoading}
		isSuccess = {isSuccess}
		isError = {isError}
		setIsError = {setIsError}
		isNetworkError = {isNetworkError}
		setIsNetworkError = {setIsNetworkError}
		isServerError = {isServerError}
		setIsServerError = {setIsServerError}
		activeDialogId = {activeDialogId}
	/>;
};

export default ConfirmCodeEmailDialogContent;
