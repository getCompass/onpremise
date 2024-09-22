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
	APIAuthInfoDataTypeRegisterLoginResetPasswordByMail,
	APIAuthTypeLoginByMail,
	APIAuthTypeRegisterByMail,
	APIAuthTypeResetPasswordByMail,
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

const ConfirmCodeEmailDialogContentDesktop = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsConfirmCodeIncorrectCodeError = useLangString("errors.confirm_code_incorrect_code_error");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const langStringConfirmCodeEmailDialogTitle = useLangString("confirm_code_email_dialog.title_desktop");
	const langStringConfirmCodeEmailDialogDesc = useLangString("confirm_code_email_dialog.desc");
	const langStringConfirmCodeEmailDialogBackButton = useLangString("confirm_code_email_dialog.back_button");
	const langStringConfirmCodeEmailDialogAuthBlocked = useLangString("confirm_code_email_dialog.auth_blocked");

	const apiAuthMailConfirmFullAuthCode = useApiAuthMailConfirmFullAuthCode();
	const apiSecurityMailConfirmResetPassword = useApiSecurityMailConfirmResetPassword();
	const apiAuthMailCancel = useApiAuthMailCancel();
	const { navigateToDialog } = useNavigateDialog();
	const activeDialogId = useAtomValue(activeDialogIdState);
	const auth = useAtomValue(authState);
	const joinLink = useAtomValue(joinLinkState);
	const setPrepareJoinLinkError = useSetAtom(prepareJoinLinkErrorState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const showToast = useShowToast(activeDialogId);

	const [isAuthBlocked, setIsAuthBlocked] = useState(false);
	const [isCompleted, setCompleted] = useState<boolean>(false);
	const [confirmCode, setConfirmCode] = useState<string>("");
	const [nextAttempt, setNextAttempt] = useState(0);
	const [nextResend, setNextResend] = useState(0);
	const [isLoading, setIsLoading] = useState(false);
	const [isSuccess, setIsSuccess] = useState(false);
	const [isError, setIsError] = useState(false);
	const [isNetworkError, setIsNetworkError] = useState(false);
	const [isServerError, setIsServerError] = useState(false);

	useEffect(() => {
		// сбрасываем пароль если это логин
		if (auth !== null && auth.type === APIAuthTypeLoginByMail) {
			setPasswordInput("");
		}
	}, []);

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
	}, [auth]);

	const renderedPreloaderButton = useMemo(() => {
		if (isNetworkError) {
			return <DesktopRefreshButton setIsServerError={setIsNetworkError} setIsCompleted={setCompleted} />;
		}

		if (isServerError) {
			return <DesktopRefreshButton setIsServerError={setIsServerError} setIsCompleted={setCompleted} />;
		}

		if (isCompleted || isLoading) {
			return <Preloader18 />;
		}

		return <></>;
	}, [isCompleted, isServerError, isNetworkError, isLoading]);

	const renderedPinInput = useMemo(() => {
		if (isAuthBlocked && auth !== null) {
			return (
				<Text
					mt="16px"
					py="10px"
					px="16px"
					w="100%"
					bgColor="255106100.01"
					color="333e49"
					textAlign="center"
					rounded="8px"
					style="lato_13_18_400"
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
			<HStack w="100%" gap="10px" justify="center" mt="20px">
				<Box w="20px" h="20px" flexShrink="0" />
				<PinInput
					confirmCode={confirmCode}
					onChange={(newValue: string) => {
						setConfirmCode(newValue);
						setIsError(false);
					}}
					onComplete={() => setCompleted(confirmCode.length === 6)}
					isError={isError}
					isSuccess={isSuccess}
					isCompleted={isCompleted}
					style="Desktop"
				/>
				<Box w="20px" h="20px" flexShrink="0">
					{renderedPreloaderButton}
				</Box>
			</HStack>
		);
	}, [confirmCode, auth, isSuccess, isError, isCompleted, isLoading, nextAttempt, isAuthBlocked]);

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
								if (
									error.error_code === INCORRECT_LINK_ERROR_CODE ||
									error.error_code === INACTIVE_LINK_ERROR_CODE
								) {
									setConfirmCode("");
									setCompleted(false);
									setPrepareJoinLinkError({ error_code: error.error_code });
									return;
								}

								if (error.error_code === 1708113) {
									setIsError(true);
									setConfirmCode("");
									setCompleted(false);
									showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
									setTimeout(() => setIsError(false), 2900); // должен быть на 100ms меньше времени пропадания тостера
									return;
								}

								if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
									setIsAuthBlocked(true);
									setNextAttempt(error.next_attempt);
									setConfirmCode("");
									setCompleted(false);
									return;
								}

								setIsError(true);
								setCompleted(false);
								showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
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
								if (
									error.error_code === INCORRECT_LINK_ERROR_CODE ||
									error.error_code === INACTIVE_LINK_ERROR_CODE
								) {
									setConfirmCode("");
									setCompleted(false);
									setPrepareJoinLinkError({ error_code: error.error_code });
									return;
								}

								if (error.error_code === 1708113) {
									setIsError(true);
									setConfirmCode("");
									setCompleted(false);
									showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
									setTimeout(() => setIsError(false), 2900); // должен быть на 100ms меньше времени пропадания тостера
									return;
								}

								if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
									setIsAuthBlocked(true);
									setNextAttempt(error.next_attempt);
									setConfirmCode("");
									setCompleted(false);
									return;
								}

								setIsError(true);
								setCompleted(false);
								showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
							}
						},
					}
				);

				return;
			}
		}
	}, [isCompleted, joinLink, auth]);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="20px">
				<MailIcon80 />

				<Text mt="16px" style="lato_18_24_900" ls="-02">
					{langStringConfirmCodeEmailDialogTitle}
				</Text>

				<Text mt="6px" textAlign="center" style="lato_14_20_400" ls="-015" maxW="328px" overflow="wrapEllipsis">
					{langStringConfirmCodeEmailDialogDesc}
					<styled.span fontFamily="lato_bold">
						«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				{renderedPinInput}

				<HStack w="100%" justify="space-between" mt="22px">
					<Button
						className="confirm_code_next_button"
						size="px8py8"
						textSize="md_desktop"
						color="2574a9"
						onClick={() => {
							if (auth.type == APIAuthTypeRegisterByMail) {
								navigateToDialog("auth_email_register");
								return;
							}

							apiAuthMailCancel.mutate({ auth_key: auth.auth_key });
						}}
					>
						<HStack gap="2px">
							<svg
								className="next_button_svg_icon"
								width="14"
								height="14"
								viewBox="0 0 14 14"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
							>
								<path
									d="M0.702026 6.82698L4.40703 3.11548L4.80353 3.52498C4.89019 3.61165 4.92269 3.70048 4.90103 3.79148C4.88369 3.87814 4.83819 3.96048 4.76453 4.03848L2.89903 5.89098C2.69536 6.09465 2.50903 6.26148 2.34003 6.39148C2.55669 6.36548 2.78419 6.34381 3.02253 6.32648C3.26519 6.30915 3.51436 6.30048 3.77003 6.30048H12.233V7.35998H3.77003C3.51436 7.35998 3.26519 7.35131 3.02253 7.33398C2.78419 7.31665 2.55669 7.29498 2.34003 7.26898C2.50036 7.39031 2.68669 7.55498 2.89903 7.76298L4.77753 9.63498C4.85553 9.71298 4.90319 9.79531 4.92053 9.88198C4.93786 9.96865 4.90536 10.0553 4.82303 10.142L4.42003 10.558L0.702026 6.82698Z"
									fill="#2574A9"
								/>
							</svg>
							<styled.span fontSize="13px" lineHeight="18px">
								{langStringConfirmCodeEmailDialogBackButton}
							</styled.span>
						</HStack>
					</Button>
					<DynamicTimerEmail
						key="desktop_dynamic_timer"
						endTimeUnix={nextResend}
						setNextResend={setNextResend}
						setConfirmCode={setConfirmCode}
						setIsLoading={setIsLoading}
						setIsError={setIsError}
						setCompleted={setCompleted}
						size="px8py8"
						textSize="lato_13_18_400"
						isCompleted={isCompleted}
						authKey={auth.auth_key}
						authType={auth.type}
						activeDialogId={activeDialogId}
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
	const screenWidth = useMemo(() => document.body.clientWidth, [document.body.clientWidth]);

	if (screenWidth <= 375) {
		return (
			<PinInput
				confirmCode={confirmCode}
				onChange={(newValue: string) => {
					setConfirmCode(newValue);
					setIsError(false);
				}}
				onComplete={() => setCompleted(confirmCode.length === 6)}
				isError={isError}
				isSuccess={isSuccess}
				isCompleted={isCompleted}
				style="MobileSmall"
			/>
		);
	}

	return (
		<PinInput
			confirmCode={confirmCode}
			onChange={(newValue: string) => {
				setConfirmCode(newValue);
				setIsError(false);
			}}
			onComplete={() => setCompleted(confirmCode.length === 6)}
			isError={isError}
			isSuccess={isSuccess}
			isCompleted={isCompleted}
			style="Mobile"
		/>
	);
};

const ConfirmCodeEmailDialogContentMobile = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsConfirmCodeIncorrectCodeError = useLangString("errors.confirm_code_incorrect_code_error");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const langStringConfirmCodeEmailDialogTitle = useLangString("confirm_code_email_dialog.title_mobile");
	const langStringConfirmCodeEmailDialogDesc = useLangString("confirm_code_email_dialog.desc");
	const langStringConfirmCodeEmailDialogBackButton = useLangString("confirm_code_email_dialog.back_button");
	const langStringConfirmCodeEmailDialogAuthBlocked = useLangString("confirm_code_email_dialog.auth_blocked");

	const apiAuthMailConfirmFullAuthCode = useApiAuthMailConfirmFullAuthCode();
	const apiSecurityMailConfirmResetPassword = useApiSecurityMailConfirmResetPassword();
	const apiAuthMailCancel = useApiAuthMailCancel();
	const { navigateToDialog } = useNavigateDialog();
	const activeDialogId = useAtomValue(activeDialogIdState);
	const auth = useAtomValue(authState);
	const joinLink = useAtomValue(joinLinkState);
	const setPrepareJoinLinkError = useSetAtom(prepareJoinLinkErrorState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const showToast = useShowToast(activeDialogId);

	const [isAuthBlocked, setIsAuthBlocked] = useState(false);
	const [isCompleted, setCompleted] = useState<boolean>(false);
	const [confirmCode, setConfirmCode] = useState<string>("");
	const [nextAttempt, setNextAttempt] = useState(0);
	const [nextResend, setNextResend] = useState(0);
	const [isLoading, setIsLoading] = useState(false);
	const [isSuccess, setIsSuccess] = useState(false);
	const [isError, setIsError] = useState(false);
	const [isNetworkError, setIsNetworkError] = useState(false);
	const [isServerError, setIsServerError] = useState(false);

	useEffect(() => {
		// сбрасываем пароль если это логин
		if (auth !== null && auth.type === APIAuthTypeLoginByMail) {
			setPasswordInput("");
		}
	}, []);

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
	}, [auth]);

	const screenWidth = useMemo(() => document.body.clientWidth, [document.body.clientWidth]);

	const renderedPreloaderButton = useMemo(() => {
		if (isServerError) {
			if (screenWidth <= 375) {
				return (
					<MobileRefreshButtonSmall
						key="mobile_refresh_button_small"
						setIsServerError={setIsServerError}
						setIsCompleted={setCompleted}
					/>
				);
			}

			return (
				<MobileRefreshButton
					key="mobile_refresh_button"
					setIsServerError={setIsServerError}
					setIsCompleted={setCompleted}
				/>
			);
		}
		if (isNetworkError) {
			if (screenWidth <= 375) {
				return (
					<MobileRefreshButtonSmall
						key="mobile_refresh_button_small"
						setIsServerError={setIsNetworkError}
						setIsCompleted={setCompleted}
					/>
				);
			}

			return (
				<MobileRefreshButton
					key="mobile_refresh_button"
					setIsServerError={setIsNetworkError}
					setIsCompleted={setCompleted}
				/>
			);
		}

		if (isCompleted || isLoading) {
			return <Preloader18Opacity30 key="mobile_preloader18_opacity30" />;
		}

		return <></>;
	}, [isCompleted, isServerError, isNetworkError, isLoading, screenWidth]);

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
								if (
									error.error_code === INCORRECT_LINK_ERROR_CODE ||
									error.error_code === INACTIVE_LINK_ERROR_CODE
								) {
									setConfirmCode("");
									setCompleted(false);
									setPrepareJoinLinkError({ error_code: error.error_code });
									return;
								}

								if (error.error_code === 1708113) {
									setIsError(true);
									setConfirmCode("");
									setCompleted(false);
									showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
									setTimeout(() => setIsError(false), 2900); // должен быть на 100ms меньше времени пропадания тостера
									return;
								}

								if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
									setIsAuthBlocked(true);
									setNextAttempt(error.next_attempt);
									setConfirmCode("");
									setCompleted(false);
									return;
								}

								setIsError(true);
								setCompleted(false);
								showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
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
								if (
									error.error_code === INCORRECT_LINK_ERROR_CODE ||
									error.error_code === INACTIVE_LINK_ERROR_CODE
								) {
									setConfirmCode("");
									setCompleted(false);
									setPrepareJoinLinkError({ error_code: error.error_code });
									return;
								}

								if (error.error_code === 1708113) {
									setIsError(true);
									setConfirmCode("");
									setCompleted(false);
									showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
									setTimeout(() => setIsError(false), 2900); // должен быть на 100ms меньше времени пропадания тостера
									return;
								}

								if (error.error_code === 1708399 || error.error_code === LIMIT_ERROR_CODE) {
									setIsAuthBlocked(true);
									setNextAttempt(error.next_attempt);
									setConfirmCode("");
									setCompleted(false);
									return;
								}

								setIsError(true);
								setCompleted(false);
								showToast(langStringErrorsConfirmCodeIncorrectCodeError, "warning");
							}
						},
					}
				);

				return;
			}
		}
	}, [isCompleted, joinLink, auth]);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="16px">
				<MailIcon80 />

				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringConfirmCodeEmailDialogTitle}
				</Text>

				<Text
					mt="4px"
					textAlign="center"
					style="lato_16_22_400"
					maxW={screenWidth <= 390 ? "326px" : "350px"}
					overflow="wrapEllipsis"
				>
					{langStringConfirmCodeEmailDialogDesc}
					<styled.span fontFamily="lato_bold">
						«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				{isAuthBlocked ? (
					<Text
						mt="18px"
						p="12px"
						w="100%"
						bgColor="255106100.01"
						color="333e49"
						textAlign="center"
						rounded="8px"
						style="lato_16_22_400"
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
					<HStack key="mobile_small_pininput" w="100%" gap="8px" justify="center" mt="24px">
						<Box w="18px" h="18px" flexShrink="0" />
						<ConfirmCodePhoneNumberDialogContentMobilePinInput
							isCompleted={isCompleted}
							setCompleted={setCompleted}
							isError={isError}
							setIsError={setIsError}
							isSuccess={isSuccess}
							confirmCode={confirmCode}
							setConfirmCode={setConfirmCode}
						/>
						<Box w="18px" h="18px" display="flex" alignItems="center" flexShrink="0">
							{renderedPreloaderButton}
						</Box>
					</HStack>
				) : (
					<HStack key="mobile_pininput" w="100%" gap="8px" justify="center" mt="24px">
						<Box w="28px" h="28px" flexShrink="0" />
						<ConfirmCodePhoneNumberDialogContentMobilePinInput
							isCompleted={isCompleted}
							setCompleted={setCompleted}
							isError={isError}
							setIsError={setIsError}
							isSuccess={isSuccess}
							confirmCode={confirmCode}
							setConfirmCode={setConfirmCode}
						/>
						<Box w="28px" h="28px" display="flex" alignItems="center" flexShrink="0">
							{renderedPreloaderButton}
						</Box>
					</HStack>
				)}

				<HStack w="100%" justify="space-between" pt="28px">
					<Button
						className="confirm_code_next_button"
						size={screenWidth <= 375 ? "px0py8" : "px8py8"}
						textSize="lato_16_22_400"
						color="2574a9"
						disabled={isCompleted}
						onClick={() => {
							if (auth.type == APIAuthTypeRegisterByMail) {
								navigateToDialog("auth_email_register");
								return;
							}

							apiAuthMailCancel.mutate({ auth_key: auth.auth_key });
						}}
					>
						<HStack gap="4px">
							<svg
								className="next_button_svg_icon"
								width="16"
								height="14"
								viewBox="0 0 16 14"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
							>
								<path
									d="M0.86377 6.8639L5.42377 2.2959L5.91177 2.7999C6.01844 2.90657 6.05844 3.0159 6.03177 3.1279C6.01044 3.23456 5.95444 3.3359 5.86377 3.4319L3.56777 5.7119C3.3171 5.96257 3.08777 6.1679 2.87977 6.3279C3.14644 6.2959 3.42644 6.26923 3.71977 6.2479C4.01844 6.22657 4.3251 6.2159 4.63977 6.2159H15.0558V7.5199H4.63977C4.3251 7.5199 4.01844 7.50923 3.71977 7.4879C3.42644 7.46657 3.14644 7.4399 2.87977 7.4079C3.0771 7.55723 3.30644 7.7599 3.56777 8.0159L5.87977 10.3199C5.97577 10.4159 6.03444 10.5172 6.05577 10.6239C6.0771 10.7306 6.0371 10.8372 5.93577 10.9439L5.43977 11.4559L0.86377 6.8639Z"
									fill="#2574A9"
								/>
							</svg>
							<styled.span fontSize="16px" lineHeight="22px">
								{langStringConfirmCodeEmailDialogBackButton}
							</styled.span>
						</HStack>
					</Button>
					<DynamicTimerEmail
						key="mobile_dynamic_timer"
						endTimeUnix={nextResend}
						setNextResend={setNextResend}
						setConfirmCode={setConfirmCode}
						setIsLoading={setIsLoading}
						setIsError={setIsError}
						setCompleted={setCompleted}
						size={screenWidth <= 375 ? "px0py8" : "px8py8"}
						textSize="lato_16_22_400"
						isCompleted={isCompleted}
						authKey={auth.auth_key}
						authType={auth.type}
						activeDialogId={activeDialogId}
					/>
				</HStack>
			</VStack>
		</VStack>
	);
};

const ConfirmCodeEmailDialogContent = () => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return <ConfirmCodeEmailDialogContentMobile />;
	}

	return <ConfirmCodeEmailDialogContentDesktop />;
};

export default ConfirmCodeEmailDialogContent;
