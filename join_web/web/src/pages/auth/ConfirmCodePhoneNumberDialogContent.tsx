import { Box, HStack, styled, VStack } from "../../../styled-system/jsx";
import IconLogo from "../../components/IconLogo.tsx";
import { useCallback, useEffect, useMemo, useState } from "react";
import dayjs from "dayjs";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { useAtom, useAtomValue, useSetAtom } from "jotai";
import {
	activeDialogIdState,
	authState,
	captchaProviderState,
	captchaPublicKeyState,
	joinLinkState,
	prepareJoinLinkErrorState,
} from "../../api/_stores.ts";
import useIsMobile from "../../lib/useIsMobile.ts";
import { formatPhoneNumberIntl } from "react-phone-number-input";
import { useNavigateDialog } from "../../components/hooks.ts";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { plural } from "../../lib/plural.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import Preloader18 from "../../components/Preloader18.tsx";
import Preloader18Opacity30 from "../../components/Preloader18Opacity30.tsx";
import {
	APIAuthInfoDataTypeRegisterLoginByPhoneNumber,
	APIAuthTypeLoginByPhoneNumber,
	APIAuthTypeRegisterByPhoneNumber,
	INACTIVE_LINK_ERROR_CODE,
	INCORRECT_LINK_ERROR_CODE,
	LIMIT_ERROR_CODE,
} from "../../api/_types.ts";
import { useApiAuthPhoneNumberConfirm } from "../../api/auth/phonenumber.ts";
import { DynamicTimerPhoneNumber } from "../../components/DynamicTimerPhoneNumber.tsx";
import { doCaptchaRender, doCaptchaReset } from "../../lib/functions.ts";
import PinInput from "../../components/PinInput.tsx";

type RefreshButtonProps = {
	setIsServerError: (value: boolean) => void;
	setIsCompleted: (value: boolean) => void;
};

const DesktopRefreshButton = ({ setIsServerError, setIsCompleted }: RefreshButtonProps) => {
	return (
		<Box
			w = "20px"
			h = "20px"
			onClick = {() => {
				setIsServerError(false);
				setIsCompleted(true);
			}}
		>
			<svg width = "20" height = "20" viewBox = "0 0 20 20" fill = "none" xmlns = "http://www.w3.org/2000/svg">
				<path
					fillRule = "evenodd"
					clipRule = "evenodd"
					d = "M18.3327 9.99984C18.3327 14.6022 14.6017 18.3332 9.99935 18.3332C5.39698 18.3332 1.66602 14.6022 1.66602 9.99984C1.66602 5.39746 5.39698 1.6665 9.99935 1.6665C14.6017 1.6665 18.3327 5.39746 18.3327 9.99984ZM10.066 6.50046L9.283 7.28347C9.07147 7.495 9.07147 7.83797 9.283 8.0495C9.49453 8.26103 9.8375 8.26103 10.049 8.0495L11.7157 6.38283C11.9272 6.1713 11.9272 5.82833 11.7157 5.6168L10.049 3.95013C9.8375 3.7386 9.49453 3.7386 9.283 3.95013C9.07147 4.16167 9.07147 4.50463 9.283 4.71617L9.98336 5.41652C9.29596 5.41893 8.61424 5.57598 7.99016 5.88036C7.12021 6.30466 6.40692 6.99348 5.95252 7.84809C5.49811 8.7027 5.32592 9.67923 5.46063 10.6377C5.59534 11.5962 6.03002 12.4874 6.70238 13.1837C7.37475 13.8799 8.25027 14.3455 9.20347 14.5135C10.1567 14.6816 11.1386 14.5436 12.0086 14.1193C12.8785 13.695 13.5918 13.0062 14.0462 12.1516C14.5006 11.297 14.6728 10.3204 14.5381 9.36196C14.4965 9.06571 14.2225 8.85931 13.9263 8.90095C13.6301 8.94258 13.4237 9.21649 13.4653 9.51273C13.5682 10.2447 13.4367 10.9904 13.0897 11.643C12.7427 12.2956 12.198 12.8216 11.5337 13.1456C10.8693 13.4696 10.1195 13.575 9.39159 13.4467C8.66369 13.3183 7.99511 12.9628 7.48167 12.4311C6.96823 11.8995 6.63629 11.2189 6.53342 10.4869C6.43055 9.75501 6.56204 9.00929 6.90904 8.35668C7.25604 7.70407 7.80074 7.17807 8.46506 6.85405C8.96549 6.60998 9.51445 6.48996 10.066 6.50046Z"
					fill = "#FF8A00"
				/>
			</svg>
		</Box>
	);
};

const MobileRefreshButtonSmall = ({ setIsServerError, setIsCompleted }: RefreshButtonProps) => {
	return (
		<Box
			w = "18px"
			h = "18px"
			flexShrink = "0"
			onClick = {() => {
				setIsServerError(false);
				setIsCompleted(true);
			}}
		>
			<svg width = "18" height = "18" viewBox = "0 0 18 18" fill = "none" xmlns = "http://www.w3.org/2000/svg">
				<path
					fillRule = "evenodd"
					clipRule = "evenodd"
					d = "M16.5 8.99951C16.5 13.1416 13.1421 16.4995 9 16.4995C4.85786 16.4995 1.5 13.1416 1.5 8.99951C1.5 4.85738 4.85786 1.49951 9 1.49951C13.1421 1.49951 16.5 4.85738 16.5 8.99951ZM9.06031 5.85012L8.35545 6.55498C8.16507 6.74536 8.16507 7.05402 8.35545 7.24441C8.54583 7.43479 8.8545 7.43479 9.04488 7.24441L10.5449 5.74441C10.7353 5.55402 10.7353 5.24536 10.5449 5.05498L9.04488 3.55498C8.8545 3.3646 8.54583 3.3646 8.35545 3.55498C8.16507 3.74536 8.16507 4.05402 8.35545 4.24441L8.9856 4.87456C8.36678 4.87665 7.75305 5.01799 7.19123 5.29201C6.40828 5.67388 5.76631 6.29382 5.35735 7.06297C4.94839 7.83211 4.79342 8.71099 4.91465 9.57363C5.03589 10.4363 5.42711 11.2384 6.03223 11.865C6.63736 12.4916 7.42533 12.9106 8.28321 13.0619C9.14109 13.2131 10.0248 13.0889 10.8078 12.7071C11.5907 12.3252 12.2327 11.7053 12.6417 10.9361C13.0506 10.167 13.2056 9.28808 13.0844 8.42545C13.0469 8.15883 12.8004 7.97307 12.5338 8.01054C12.2671 8.04801 12.0814 8.29452 12.1189 8.56114C12.2114 9.21988 12.0931 9.89102 11.7808 10.4784C11.4685 11.0657 10.9783 11.5391 10.3804 11.8307C9.78249 12.1223 9.10763 12.2172 8.45252 12.1017C7.79741 11.9862 7.19569 11.6662 6.73359 11.1877C6.27149 10.7092 5.97274 10.0967 5.88016 9.43793C5.78758 8.77919 5.90593 8.10805 6.21822 7.5207C6.53052 6.93335 7.02075 6.45995 7.61864 6.16833C8.06927 5.94855 8.56363 5.84053 9.06031 5.85012Z"
					fill = "#FF8A00"
				/>
			</svg>
		</Box>
	);
};

const MobileRefreshButton = ({ setIsServerError, setIsCompleted }: RefreshButtonProps) => {
	return (
		<Box
			w = "28px"
			h = "28px"
			flexShrink = "0"
			onClick = {() => {
				setIsServerError(false);
				setIsCompleted(true);
			}}
		>
			<svg width = "28" height = "28" viewBox = "0 0 28 28" fill = "none" xmlns = "http://www.w3.org/2000/svg">
				<path
					fillRule = "evenodd"
					clipRule = "evenodd"
					d = "M25.6673 13.9997C25.6673 20.443 20.444 25.6663 14.0007 25.6663C7.55733 25.6663 2.33398 20.443 2.33398 13.9997C2.33398 7.55635 7.55733 2.33301 14.0007 2.33301C20.444 2.33301 25.6673 7.55635 25.6673 13.9997ZM14.0938 9.10056L12.9978 10.1966C12.7016 10.4927 12.7016 10.9729 12.9978 11.269C13.2939 11.5652 13.7741 11.5652 14.0702 11.269L16.4035 8.93567C16.6997 8.63952 16.6997 8.15937 16.4035 7.86322L14.0702 5.52989C13.7741 5.23374 13.2939 5.23374 12.9978 5.52989C12.7016 5.82604 12.7016 6.30619 12.9978 6.60234L13.9785 7.58305C13.016 7.58638 12.0615 7.80624 11.1878 8.23242C9.96984 8.82644 8.97123 9.79079 8.33506 10.9872C7.6989 12.1837 7.45783 13.5508 7.64642 14.8927C7.83501 16.2346 8.44357 17.4823 9.38488 18.4571C10.3262 19.4318 11.5519 20.0836 12.8864 20.3189C14.2209 20.5542 15.5956 20.361 16.8135 19.7669C18.0315 19.1729 19.0301 18.2086 19.6662 17.0121C20.3024 15.8157 20.5435 14.4485 20.3549 13.1067C20.2966 12.6919 19.9131 12.403 19.4984 12.4612C19.0836 12.5195 18.7947 12.903 18.853 13.3177C18.997 14.3424 18.8129 15.3864 18.3271 16.3001C17.8413 17.2137 17.0787 17.9502 16.1487 18.4038C15.2186 18.8574 14.1688 19.0049 13.1498 18.8252C12.1307 18.6456 11.1947 18.1479 10.4759 17.4035C9.75706 16.6592 9.29234 15.7063 9.14833 14.6816C9.00432 13.6569 9.1884 12.6129 9.6742 11.6993C10.16 10.7856 10.9226 10.0492 11.8526 9.59559C12.5532 9.25391 13.3216 9.08589 14.0938 9.10056Z"
					fill = "#FF8A00"
				/>
			</svg>
		</Box>
	);
};

type ConfirmCodePhoneNumberDialogContent = {
	showCaptchaState: ShowGrecaptchaState;
	setShowCaptchaState: (value: ShowGrecaptchaState) => void;
	grecaptchaResponse: string;
	setGrecaptchaResponse: (value: string) => void;
};

const ConfirmCodePhoneNumberDialogContentDesktop = ({
	showCaptchaState,
	setShowCaptchaState,
	grecaptchaResponse,
	setGrecaptchaResponse,
}: ConfirmCodePhoneNumberDialogContent) => {
	const langStringConfirmCodePhoneNumberDialogTitle = useLangString("confirm_code_phone_number_dialog.title");
	const langStringConfirmCodePhoneNumberDialogDesc = useLangString("confirm_code_phone_number_dialog.desc");
	const langStringConfirmCodePhoneNumberDialogBackButton = useLangString(
		"confirm_code_phone_number_dialog.back_button"
	);
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const langStringErrorsConfirmCodeIncorrectCodeError = useLangString("errors.confirm_code_incorrect_code_error");
	const langStringConfirmCodePhoneNumberDialogAuthBlocked = useLangString(
		"confirm_code_phone_number_dialog.auth_blocked"
	);

	const [ confirmCode, setConfirmCode ] = useState<string>("");
	const [ auth, setAuth ] = useAtom(authState);
	const setPrepareJoinLinkError = useSetAtom(prepareJoinLinkErrorState);

	const [ isCompleted, setCompleted ] = useState<boolean>(false);
	const joinLink = useAtomValue(joinLinkState);
	const { navigateToDialog } = useNavigateDialog();
	const apiAuthPhoneNumberConfirm = useApiAuthPhoneNumberConfirm();
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const [ isSuccess, setIsSuccess ] = useState(false);
	const [ isLoading, setIsLoading ] = useState(false);
	const [ isError, setIsError ] = useState(false);
	const [ isAuthBlocked, setIsAuthBlocked ] = useState(false);
	const [ isNetworkError, setIsNetworkError ] = useState(false);
	const [ isServerError, setIsServerError ] = useState(false);
	const [ nextAttempt, setNextAttempt ] = useState(0);
	const [ nextResend, setNextResend ] = useState(0);
	const [ widgetId, setWidgetId ] = useState("");
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);
	const captchaProvider = useAtomValue(captchaProviderState);

	useEffect(() => {
		if (
			auth === null ||
			(auth.type !== APIAuthTypeRegisterByPhoneNumber && auth.type !== APIAuthTypeLoginByPhoneNumber)
		) {
			return;
		}

		setNextResend((auth.data as APIAuthInfoDataTypeRegisterLoginByPhoneNumber).next_resend);
	}, [ auth ]);

	const renderedPreloaderButton = useMemo(() => {
		if (isServerError) {
			return <DesktopRefreshButton setIsServerError = {setIsServerError} setIsCompleted = {setCompleted} />;
		}

		if (isNetworkError) {
			return <DesktopRefreshButton setIsServerError = {setIsNetworkError} setIsCompleted = {setCompleted} />;
		}

		if (isCompleted || isLoading) {
			return <Preloader18 />;
		}

		return <></>;
	}, [ isCompleted, isServerError, isNetworkError, isLoading ]);

	const renderedPinInput = useMemo(() => {
		if (isAuthBlocked) {
			return (
				<Text
					py = "10px"
					px = "16px"
					w = "100%"
					bgColor = "255106100.01"
					fs = "13"
					lh = "18"
					color = "333e49"
					textAlign = "center"
					rounded = "8px"
					font = "regular"
				>
					{langStringConfirmCodePhoneNumberDialogAuthBlocked.replace(
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
			<HStack w = "100%" gap = "10px" justify = "center">
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
	}, [ confirmCode, isSuccess, isError, isCompleted, isLoading, nextAttempt ]);

	useEffect(() => {
		if (isCompleted && auth !== null) {
			if (confirmCode.length != 6) {
				return;
			}

			apiAuthPhoneNumberConfirm.mutate(
				{
					auth_key: auth.auth_key,
					sms_code: confirmCode,
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
								setCompleted(false);
								setIsError(true);
								setConfirmCode("");
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
		}
	}, [ isCompleted, joinLink, auth ]);

	useEffect(() => {
		if (grecaptchaResponse === "" && showCaptchaState === "rendered") {
			doCaptchaReset(captchaProvider, widgetId);
		}
	}, [ grecaptchaResponse ]);

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					setWidgetId(doCaptchaRender(node, captchaPublicKey, captchaProvider, setGrecaptchaResponse));
				} catch (error) {
				}

				setShowCaptchaState("rendered");
			}

			if (node !== null && showCaptchaState === "rendered") {
				doCaptchaReset(captchaProvider, widgetId);
			}
		},
		[ showCaptchaState, captchaPublicKey, captchaProvider ]
	);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w = "100%" gap = "16px">
			<VStack mt = "16px" gap = "16px" w = "100%">
				<IconLogo />
				<VStack gap = "6px" w = "100%">
					<Text fs = "18" lh = "24" font = "bold" ls = "-02">
						{langStringConfirmCodePhoneNumberDialogTitle}
					</Text>
					<Text fs = "14" lh = "20" textAlign = "center" font = "regular" userSelect = "text">
						{langStringConfirmCodePhoneNumberDialogDesc}
						{formatPhoneNumberIntl(
							(auth.data as APIAuthInfoDataTypeRegisterLoginByPhoneNumber).phone_number
						)}
					</Text>
				</VStack>
			</VStack>
			<VStack w = "100%" gap = "30px">
				{renderedPinInput}
				<Box
					ref = {captchaContainerRef}
					id = "path_to_captcha"
					style = {{
						display: showCaptchaState === "rendered" ? "block" : "none",
					}}
				/>
				<HStack w = "100%" justify = "space-between">
					<Button
						className = "confirm_code_next_button"
						size = "px8py8"
						textSize = "md_desktop"
						color = "2574a9"
						disabled = {isCompleted}
						onClick = {() => {
							setAuth(null);
							navigateToDialog("auth_email_phone_number");
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
								{langStringConfirmCodePhoneNumberDialogBackButton}
							</styled.span>
						</HStack>
					</Button>
					{!isAuthBlocked && (
						<DynamicTimerPhoneNumber
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
							authKey = {auth.auth_key}
							activeDialogId = {activeDialogId}
							showCaptchaState = {showCaptchaState}
							setShowCaptchaState = {setShowCaptchaState}
							grecaptchaResponse = {grecaptchaResponse}
							setGrecaptchaResponse = {setGrecaptchaResponse}
							captchaWidgetId = {widgetId}
						/>
					)}
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

const ConfirmCodePhoneNumberDialogContentMobile = ({
	showCaptchaState,
	setShowCaptchaState,
	grecaptchaResponse,
	setGrecaptchaResponse,
}: ConfirmCodePhoneNumberDialogContent) => {
	const langStringConfirmCodePhoneNumberDialogTitle = useLangString("confirm_code_phone_number_dialog.title");
	const langStringConfirmCodePhoneNumberDialogDesc = useLangString("confirm_code_phone_number_dialog.desc");
	const langStringConfirmCodePhoneNumberDialogBackButton = useLangString(
		"confirm_code_phone_number_dialog.back_button"
	);
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringConfirmCodePhoneNumberDialogAuthBlocked = useLangString(
		"confirm_code_phone_number_dialog.auth_blocked"
	);
	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const langStringErrorsConfirmCodeIncorrectCodeError = useLangString("errors.confirm_code_incorrect_code_error");

	const [ confirmCode, setConfirmCode ] = useState<string>("");
	const setPrepareJoinLinkError = useSetAtom(prepareJoinLinkErrorState);

	const [ isCompleted, setCompleted ] = useState(false);
	const [ auth, setAuth ] = useAtom(authState);
	const joinLink = useAtomValue(joinLinkState);
	const { navigateToDialog } = useNavigateDialog();
	const apiAuthPhoneNumberConfirm = useApiAuthPhoneNumberConfirm();
	const activeDialogId = useAtomValue(activeDialogIdState);
	const showToast = useShowToast(activeDialogId);
	const [ isLoading, setIsLoading ] = useState(false);
	const [ isError, setIsError ] = useState(false);
	const [ isSuccess, setIsSuccess ] = useState(false);
	const [ isAuthBlocked, setIsAuthBlocked ] = useState(false);
	const [ nextAttempt, setNextAttempt ] = useState(0);
	const [ nextResend, setNextResend ] = useState(0);
	const [ isNetworkError, setIsNetworkError ] = useState(false);
	const [ isServerError, setIsServerError ] = useState(false);
	const [ widgetId, setWidgetId ] = useState("");
	const captchaPublicKey = useAtomValue(captchaPublicKeyState);
	const captchaProvider = useAtomValue(captchaProviderState);

	useEffect(() => {
		if (
			auth === null ||
			(auth.type !== APIAuthTypeRegisterByPhoneNumber && auth.type !== APIAuthTypeLoginByPhoneNumber)
		) {
			return;
		}

		setNextResend((auth.data as APIAuthInfoDataTypeRegisterLoginByPhoneNumber).next_resend);
	}, [ auth ]);

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

	useEffect(() => {
		if (isCompleted && auth !== null) {
			if (confirmCode.length != 6 || auth.auth_key.length < 1) {
				return;
			}

			apiAuthPhoneNumberConfirm.mutate(
				{
					auth_key: auth.auth_key,
					sms_code: confirmCode,
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
								setCompleted(false);
								setIsError(true);
								setConfirmCode("");
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
		}
	}, [ isCompleted, joinLink, auth ]);

	useEffect(() => {
		if (grecaptchaResponse === "" && showCaptchaState === "rendered") {
			doCaptchaReset(captchaProvider, widgetId);
		}
	}, [ grecaptchaResponse ]);

	const captchaContainerRef = useCallback(
		(node: HTMLDivElement | null) => {
			if (node !== null && showCaptchaState === "need_render") {
				try {
					setWidgetId(doCaptchaRender(node, captchaPublicKey, captchaProvider, setGrecaptchaResponse));
				} catch (error) {
				}

				setShowCaptchaState("rendered");
			}

			if (node !== null && showCaptchaState === "rendered") {
				doCaptchaReset(captchaProvider, widgetId);
			}
		},
		[ showCaptchaState, captchaPublicKey ]
	);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w = "100%" gap = "24px">
			<VStack mt = "16px" gap = "16px" w = "100%">
				<IconLogo />
				<VStack gap = "4px" w = "100%">
					<Text fs = "20" lh = "28" font = "bold" ls = "-03">
						{langStringConfirmCodePhoneNumberDialogTitle}
					</Text>
					<Text fs = "16" lh = "22" textAlign = "center" font = "regular" userSelect = "text">
						{langStringConfirmCodePhoneNumberDialogDesc}
						{formatPhoneNumberIntl(
							(auth.data as APIAuthInfoDataTypeRegisterLoginByPhoneNumber).phone_number
						)}
					</Text>
				</VStack>
			</VStack>
			<VStack w = "100%" gap = {showCaptchaState === "rendered" ? "16px" : "12px"}>
				{isAuthBlocked ? (
					<Text
						key = "mobile_text_auth_blocked"
						p = "12px"
						w = "100%"
						bgColor = "255106100.01"
						fs = "16"
						lh = "20"
						color = "333e49"
						textAlign = "center"
						rounded = "8px"
						font = "regular"
					>
						{langStringConfirmCodePhoneNumberDialogAuthBlocked.replace(
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
					<HStack key = "mobile_small_pininput" w = "100%" gap = "8px" justify = "center">
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
					<HStack key = "mobile_pininput" w = "100%" gap = "8px" justify = "center">
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
				<Box
					ref = {captchaContainerRef}
					id = "path_to_captcha"
					style = {{
						display: showCaptchaState === "rendered" ? "block" : "none",
					}}
				/>
				<HStack w = "100%" justify = "space-between" pt = "16px">
					<Button
						className = "confirm_code_next_button"
						size = {screenWidth <= 375 ? "px0py8" : "px8py8"}
						textSize = "md"
						color = "2574a9"
						disabled = {isCompleted}
						onClick = {() => {
							setAuth(null);
							navigateToDialog("auth_email_phone_number");
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
								{langStringConfirmCodePhoneNumberDialogBackButton}
							</styled.span>
						</HStack>
					</Button>
					{!isAuthBlocked && (
						<DynamicTimerPhoneNumber
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
							authKey = {auth.auth_key}
							activeDialogId = {activeDialogId}
							showCaptchaState = {showCaptchaState}
							setShowCaptchaState = {setShowCaptchaState}
							grecaptchaResponse = {grecaptchaResponse}
							setGrecaptchaResponse = {setGrecaptchaResponse}
							captchaWidgetId = {widgetId}
						/>
					)}
				</HStack>
			</VStack>
		</VStack>
	);
};

type ShowGrecaptchaState = null | "need_render" | "rendered";

const ConfirmCodePhoneNumberDialogContent = () => {
	const isMobile = useIsMobile();
	const [ showCaptchaState, setShowCaptchaState ] = useState<ShowGrecaptchaState>(null);
	const [ grecaptchaResponse, setGrecaptchaResponse ] = useState("");

	if (isMobile) {
		return (
			<ConfirmCodePhoneNumberDialogContentMobile
				showCaptchaState = {showCaptchaState}
				setShowCaptchaState = {setShowCaptchaState}
				grecaptchaResponse = {grecaptchaResponse}
				setGrecaptchaResponse = {setGrecaptchaResponse}
			/>
		);
	}

	return (
		<ConfirmCodePhoneNumberDialogContentDesktop
			showCaptchaState = {showCaptchaState}
			setShowCaptchaState = {setShowCaptchaState}
			grecaptchaResponse = {grecaptchaResponse}
			setGrecaptchaResponse = {setGrecaptchaResponse}
		/>
	);
};

export default ConfirmCodePhoneNumberDialogContent;
