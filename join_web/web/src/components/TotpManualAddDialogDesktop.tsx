import { Center, HStack, VStack } from "../../styled-system/jsx";
import { Portal } from "@ark-ui/react";
import { PropsWithChildren, RefObject, useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Text } from "./text.tsx";
import { Dialog, DialogBackdrop, DialogContainer, DialogContent, DialogTrigger, generateDialogId } from "./dialog.tsx";
import { useLangString } from "../lib/getLangString.ts";
import { copyToClipboard } from "../lib/copyToClipboard.ts";
import useIsMobile from "../lib/useIsMobile.ts";
import { useAtom, useAtomValue } from "jotai";
import { authLdapCredentialsState, authLdapTotpState, serverTimeOffsetState, useToastConfig } from "../api/_stores.ts";
import dayjs from "dayjs";
import { useApiFederationLdapAuthGetToken } from "../api/auth/ldap.ts";
import {
	API_COMMAND_TYPE_NEED_SETUP_TOTP,
	API_COMMAND_TYPE_NEED_TOTP_CODE,
	APINeedSetupTotpCommandData
} from "../api/_types.ts";
import { ApiCommand, ApiError, NetworkError, ServerError } from "../api/_index.ts";
import { useNavigateDialog } from "./hooks.ts";
import Toast, { useShowToast } from "../lib/Toast.tsx";
import Preloader14 from "./Preloader14.tsx";

type copyButtonProps = {
	onCopySecretKeyClickHandler: () => void
}
const CopyButton = ({ onCopySecretKeyClickHandler }: copyButtonProps) => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return (
			<Center cursor="pointer" w="24px" h="24px" onClick={onCopySecretKeyClickHandler}>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						  d="M17.9999 3.5H8.99994C8.7238 3.5 8.49994 3.72386 8.49994 4V4.9999H6.99994V4C6.99994 2.89543 7.89537 2 8.99994 2H17.9999C19.1045 2 19.9999 2.89543 19.9999 4V17C19.9999 18.1046 19.1045 19 17.9999 19H16.9999V20C16.9999 21.1046 16.1045 22 14.9999 22H5.99994C4.89537 22 3.99994 21.1046 3.99994 20V7C3.99994 5.89543 4.89537 5 5.99994 5H14.9999C16.1045 5 16.9999 5.89543 16.9999 7V17.5H17.9999C18.2761 17.5 18.4999 17.2761 18.4999 17V4C18.4999 3.72386 18.2761 3.5 17.9999 3.5ZM5.99994 6.5H14.9999C15.2761 6.5 15.4999 6.72386 15.4999 7V20C15.4999 20.2761 15.2761 20.5 14.9999 20.5H5.99994C5.7238 20.5 5.49994 20.2761 5.49994 20V7C5.49994 6.72386 5.7238 6.5 5.99994 6.5Z"
						  fill="#B4B4B4" />
				</svg>
			</Center>
		)
	}

	return (
		<Center cursor="pointer" w="20px" h="20px" onClick={onCopySecretKeyClickHandler}>
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
				 xmlns="http://www.w3.org/2000/svg">
				<path fillRule="evenodd" clipRule="evenodd"
					  d="M14.9999 2.91663H7.49992C7.2698 2.91663 7.08325 3.10317 7.08325 3.33329V4.16654H5.83325V3.33329C5.83325 2.41282 6.57944 1.66663 7.49992 1.66663H14.9999C15.9204 1.66663 16.6666 2.41282 16.6666 3.33329V14.1666C16.6666 15.0871 15.9204 15.8333 14.9999 15.8333H14.1666V16.6666C14.1666 17.5871 13.4204 18.3333 12.4999 18.3333H4.99992C4.07944 18.3333 3.33325 17.5871 3.33325 16.6666V5.83329C3.33325 4.91282 4.07944 4.16663 4.99992 4.16663H12.4999C13.4204 4.16663 14.1666 4.91282 14.1666 5.83329V14.5833H14.9999C15.23 14.5833 15.4166 14.3967 15.4166 14.1666V3.33329C15.4166 3.10317 15.23 2.91663 14.9999 2.91663ZM4.99992 5.41663H12.4999C12.73 5.41663 12.9166 5.60317 12.9166 5.83329V16.6666C12.9166 16.8967 12.73 17.0833 12.4999 17.0833H4.99992C4.7698 17.0833 4.58325 16.8967 4.58325 16.6666V5.83329C4.58325 5.60317 4.7698 5.41663 4.99992 5.41663Z"
					  fill="#B4B4B4" />
			</svg>
		</Center>
	)
}
type refreshButtonProps = {
	onRefreshSecretKeyClickHandler?: () => void
}
const RefreshButton = ({ onRefreshSecretKeyClickHandler }: refreshButtonProps) => {
	const isMobile = useIsMobile();

	if (isMobile) {

		return (
			<Center cursor="pointer" w="24px" h="24px" onClick={onRefreshSecretKeyClickHandler}>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M10.1488 5.17834C9.32017 5.18437 8.49761 5.36696 7.73763 5.71931C6.62002 6.23746 5.69514 7.09613 5.09555 8.17223C4.49595 9.24834 4.25241 10.4866 4.39985 11.7097C4.54728 12.9327 5.07811 14.0777 5.91627 14.9804C6.75442 15.8832 7.85686 16.4975 9.06559 16.7352C10.2743 16.9729 11.5273 16.8219 12.6449 16.3037C13.7625 15.7856 14.6874 14.9269 15.287 13.8508C15.8866 12.7747 16.1301 11.5364 15.9827 10.3134C15.9413 9.97068 16.1857 9.65938 16.5284 9.61807C16.8711 9.57675 17.1824 9.82108 17.2237 10.1638C17.4027 11.6489 17.107 13.1525 16.3789 14.4592C15.6508 15.7659 14.5277 16.8086 13.1706 17.4378C11.8136 18.067 10.2921 18.2504 8.82437 17.9617C7.35664 17.6731 6.01796 16.9272 5.0002 15.8309C3.98244 14.7347 3.33786 13.3444 3.15883 11.8593C2.9798 10.3742 3.27552 8.87052 4.00361 7.56382C4.73169 6.25711 5.85476 5.21445 7.21186 4.58526C8.14189 4.15407 9.14911 3.93225 10.1633 3.92824L9.17213 2.89728C8.9329 2.64845 8.94067 2.2528 9.1895 2.01357C9.43833 1.77434 9.83398 1.78211 10.0732 2.03094L12.1152 4.15482C12.3544 4.40365 12.3467 4.7993 12.0978 5.03853L9.97395 7.08052C9.72512 7.31975 9.32947 7.31197 9.09023 7.06314C8.851 6.81432 8.85878 6.41866 9.10761 6.17943L10.1488 5.17834Z"
						fill="#007AFF" />
				</svg>
			</Center>
		)
	}

	return (
		<Center cursor="pointer" w="20px" h="20px" onClick={onRefreshSecretKeyClickHandler}>
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M10.1488 5.17834C9.32017 5.18437 8.49761 5.36696 7.73763 5.71931C6.62002 6.23746 5.69514 7.09613 5.09555 8.17223C4.49595 9.24834 4.25241 10.4866 4.39985 11.7097C4.54728 12.9327 5.07811 14.0777 5.91627 14.9804C6.75442 15.8832 7.85686 16.4975 9.06559 16.7352C10.2743 16.9729 11.5273 16.8219 12.6449 16.3037C13.7625 15.7856 14.6874 14.9269 15.287 13.8508C15.8866 12.7747 16.1301 11.5364 15.9827 10.3134C15.9413 9.97068 16.1857 9.65938 16.5284 9.61807C16.8711 9.57675 17.1824 9.82108 17.2237 10.1638C17.4027 11.6489 17.107 13.1525 16.3789 14.4592C15.6508 15.7659 14.5277 16.8086 13.1706 17.4378C11.8136 18.067 10.2921 18.2504 8.82437 17.9617C7.35664 17.6731 6.01796 16.9272 5.0002 15.8309C3.98244 14.7347 3.33786 13.3444 3.15883 11.8593C2.9798 10.3742 3.27552 8.87052 4.00361 7.56382C4.73169 6.25711 5.85476 5.21445 7.21185 4.58526C8.14189 4.15407 9.14911 3.93225 10.1633 3.92824L9.17213 2.89728C8.9329 2.64845 8.94067 2.2528 9.1895 2.01357C9.43833 1.77434 9.83398 1.78211 10.0732 2.03094L12.1152 4.15482C12.3544 4.40365 12.3467 4.7993 12.0978 5.03853L9.97395 7.08052C9.72512 7.31975 9.32947 7.31197 9.09023 7.06314C8.851 6.81432 8.85878 6.41866 9.1076 6.17943L10.1488 5.17834Z"
					fill="#007AFF" />
			</svg>
		</Center>
	)
}
const SuccessCopiedButton = () => {
	const isMobile = useIsMobile();

	if (isMobile) {

		return (
			<Center w="24px" h="24px" flexShrink={0}>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M9.99963 1.66675C14.6019 1.66675 18.3334 5.39753 18.3336 9.99976C18.3336 14.6021 14.602 18.3337 9.99963 18.3337C5.39741 18.3336 1.66663 14.602 1.66663 9.99976C1.6668 5.39764 5.39752 1.66692 9.99963 1.66675ZM13.775 7.89136C13.5309 7.64748 13.1352 7.64735 12.8912 7.89136L9.16663 11.616L7.10901 9.55835C6.86493 9.31427 6.46832 9.31427 6.22424 9.55835C5.98056 9.80239 5.98048 10.1982 6.22424 10.4421L8.72424 12.9421C8.84144 13.0593 9.00088 13.1247 9.16663 13.1248C9.33239 13.1248 9.4918 13.0593 9.60901 12.9421L13.775 8.77515C14.0191 8.53107 14.0191 8.13544 13.775 7.89136Z"
						fill="#05C46B" />
				</svg>
			</Center>
		)
	}

	return (
		<Center w="20px" h="20px">
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none"
				 xmlns="http://www.w3.org/2000/svg">
				<path
					d="M8 1.33301C11.6819 1.33301 14.667 4.3181 14.667 8C14.667 11.6819 11.6819 14.667 8 14.667C4.3181 14.667 1.33301 11.6819 1.33301 8C1.33301 4.3181 4.3181 1.33301 8 1.33301ZM11.0205 6.31348C10.8252 6.11821 10.5087 6.11821 10.3135 6.31348L7.33301 9.29297L5.68652 7.64648C5.49125 7.45138 5.1747 7.45128 4.97949 7.64648C4.78445 7.84171 4.78445 8.15829 4.97949 8.35352L6.97949 10.3535C7.07318 10.4472 7.20052 10.4999 7.33301 10.5C7.46554 10.5 7.59277 10.4472 7.68652 10.3535L11.0205 7.02051C11.2157 6.82535 11.2154 6.50876 11.0205 6.31348Z"
					fill="#05C46B" />
			</svg>

		</Center>
	)
}

function showNotification(parentScrollableBlock: HTMLElement, referenceElement: HTMLElement, setIsCopied: (state: boolean) => void, isMobile: boolean): void {

	// если элемента не существует
	if (!referenceElement) return;

	setIsCopied(true);
	const computedStyles = window.getComputedStyle(referenceElement);

	// создаем элемент уведомления
	const notification = document.createElement("div");

	// получаем координаты referenceElement относительно окна просмотра
	const referenceRect = referenceElement.getBoundingClientRect();
	const parentRect = parentScrollableBlock.getBoundingClientRect();

	const relativeTop = referenceRect.top - parentRect.top + parentScrollableBlock.scrollTop;
	const relativeLeft = referenceRect.left - parentRect.left + parentScrollableBlock.scrollLeft;

	notification.style.position = "absolute";
	notification.style.top = `${relativeTop}px`;
	notification.style.left = `${relativeLeft}px`;
	notification.style.width = `${referenceRect.width}px`;
	notification.style.height = `${referenceRect.height}px`;
	notification.style.borderRadius = computedStyles.borderRadius;
	if (isMobile) {
		notification.style.border = "1px solid rgba(5, 196, 107, 1)";
	}
	notification.style.padding = isMobile ? "8px 8px 8px 10px" : "8px 16px 8px 8px";
	notification.style.background = "rgba(5, 196, 107, 0.05)";
	notification.style.opacity = "0";
	notification.style.transition = "opacity 200ms ease-out";
	notification.style.pointerEvents = "none"; // делаем элемент "прозрачным" для событий нажатий и прочего

	// добавляем на страницу
	parentScrollableBlock.appendChild(notification);

	// анимация появления
	setTimeout(() => {
		notification.style.opacity = "1";
	}, 0);

	// анимация исчезновения и удаление элемента
	setTimeout(() => {

		notification.style.opacity = "0";
		setTimeout(() => {
			parentScrollableBlock.removeChild(notification);
			setIsCopied(false)
		}, 200); // должно соответствовать длительности анимации
	}, 1500); // время отображения
}

type stepBlockProps = {
	step: number
	text: string
	secretKey?: string
	containerRef?: RefObject<HTMLElement>
	isLoading?: boolean
	isExpired?: boolean
	onRefreshSecretKeyClickHandler?: () => void,
}
const StepBlock = ({
	step,
	text,
	secretKey,
	containerRef,
	isLoading,
	isExpired,
	onRefreshSecretKeyClickHandler
}: stepBlockProps) => {

	const isMobile = useIsMobile();

	const secretKeyRowRef = useRef<HTMLDivElement>(null);
	const [ isCopied, setIsCopied ] = useState(false);

	const onCopySecretKeyClickHandler = useCallback(() => {

		if (!secretKey) {
			return;
		}

		copyToClipboard(
			secretKey,
			undefined,
			false,
			containerRef?.current ?? undefined,
		);
		if (containerRef?.current && secretKeyRowRef.current) {
			showNotification(containerRef?.current, secretKeyRowRef.current, setIsCopied, isMobile);
		}
	}, [ secretKey, containerRef, isMobile ]);


	return (
		<HStack
			minWidth={isMobile ? "374px" : "440px"}
			borderRadius="12px"
			backgroundColor="#f8f8f8"
			padding={isMobile ? "12px" : "12px 16px 14px 16px"}
			gap="8px"
			alignItems="start"
		>
			<Center
				w="32px" h="32px" minW="32px" minH="32px"
				color="#009fe6"
				borderRadius="100%"
				fontSize={isMobile ? "20px" : "15px"}
				fontFamily="lato_medium"
				fontWeight="normal"
				bgColor="#ffffff"
				userSelect="none"
			>
				{step}
			</Center>
			<VStack gap="7px" alignItems="start" width="100%">
				<Text style={isMobile ? "lato_16_22_400" : "lato_14_20_400"} letterSpacing={isMobile ? 0 : "-0.15px"}
					  userSelect="none">{text}</Text>
				{secretKey && (
					<HStack
						gap={isMobile ? "8px" : "12px"}
						ref={secretKeyRowRef}
						borderRadius={isMobile ? "8px" : "5px"}
						backgroundColor="#ffffff"
						padding={isMobile ? "8px 8px 8px 10px" : "8px 12px 8px 8px"}
						width="100%"
						alignItems="start"
						justifyContent="space-between"
						border={isMobile ? "1px solid transparent" : "none"}
					>
						<Text
							style={isMobile ? "lato_16_22_400" : "lato_14_20_400"}
							letterSpacing={isMobile ? 0 : "-0.15px"}
							opacity={isExpired ? "10%" : "100%"}
							wordBreak={isMobile ? "break-all" : "none"}
							userSelect={isExpired ? "none" : "auto"}
						>
							{secretKey}
						</Text>
						{isExpired ? (
							isLoading ? (
								<Center w={isMobile ? "24px" : "20px"} h={isMobile ? "24px" : "20px"}>
									<Preloader14 />
								</Center>
							) : (
								<RefreshButton onRefreshSecretKeyClickHandler={onRefreshSecretKeyClickHandler} />
							)
						) : (
							isCopied ? (
								<SuccessCopiedButton />
							) : (
								<CopyButton onCopySecretKeyClickHandler={onCopySecretKeyClickHandler} />
							)
						)}
					</HStack>
				)}
			</VStack>
		</HStack>
	)
}

type TotpManualAddDialogProps = PropsWithChildren<{
	totpSeed: string;
	isLoading: boolean;
	isExpired: boolean;
	onRefreshSecretKeyClickHandler: () => void,
	dialogId: string,
}>;
const TotpManualAddDialogDesktop = ({
	children,
	totpSeed,
	isLoading,
	isExpired,
	onRefreshSecretKeyClickHandler,
	dialogId,
}: TotpManualAddDialogProps) => {

	const toastConfig = useToastConfig(dialogId);

	const contentRef = useRef<HTMLDivElement>(null);

	const langStringLdap2faSetupTotpCantScanQrDialogTitle = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.title");
	const langStringLdap2faSetupTotpCantScanQrDialogFirstStepDescDesktop = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.first_step_desc_desktop");
	const langStringLdap2faSetupTotpCantScanQrDialogSecondStepDesc = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.second_step_desc");
	const langStringLdap2faSetupTotpCantScanQrDialogThirdStepDesc = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.third_step_desc");

	return (
		<Dialog backdrop="opacity50" size="small">
			<DialogTrigger asChild>
				{children}
			</DialogTrigger>
			<Portal>
				<DialogBackdrop />
				<DialogContainer>
					<DialogContent
						ref={contentRef}
						overflow="hidden"
						lazyMount
						unmountOnExit
						padding="20px"
						width="480px"
					>
						<Toast toastConfig={toastConfig} />
						<VStack
							gap="20px"
							alignItems="start"
						>
							<Text style="lato_18_24_900" letterSpacing="-0.2px" userSelect="none">
								{langStringLdap2faSetupTotpCantScanQrDialogTitle}
							</Text>
							<VStack gap="8px">
								<StepBlock
									step={1}
									text={langStringLdap2faSetupTotpCantScanQrDialogFirstStepDescDesktop}
								/>
								<StepBlock
									step={2}
									text={langStringLdap2faSetupTotpCantScanQrDialogSecondStepDesc}
									onRefreshSecretKeyClickHandler={onRefreshSecretKeyClickHandler}
									secretKey={totpSeed}
									containerRef={contentRef}
									isLoading={isLoading}
									isExpired={isExpired}
								/>
								<StepBlock
									step={3}
									text={langStringLdap2faSetupTotpCantScanQrDialogThirdStepDesc}
								/>
							</VStack>
						</VStack>
					</DialogContent>
				</DialogContainer>
			</Portal>
		</Dialog>
	);
}

const TotpManualAddDialogMobile = ({
	children,
	totpSeed,
	isLoading,
	isExpired,
	onRefreshSecretKeyClickHandler,
	dialogId
}: TotpManualAddDialogProps) => {

	const toastConfig = useToastConfig(dialogId);

	const contentRef = useRef<HTMLDivElement>(null);

	const langStringLdap2faSetupTotpCantScanQrDialogTitle = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.title");
	const langStringLdap2faSetupTotpCantScanQrDialogFirstStepDescMobile = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.first_step_desc_mobile");
	const langStringLdap2faSetupTotpCantScanQrDialogSecondStepDesc = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.second_step_desc");
	const langStringLdap2faSetupTotpCantScanQrDialogThirdStepDesc = useLangString("ldap_2fa_setup_totp_cant_scan_qr_dialog.third_step_desc");

	return (
		<Dialog backdrop="opacity50" size="mobile_bottom" position="mobile_bottom" style="mobile_bottom"
				onClose={() => {
					window.scrollBy(0, 1);
					window.scrollBy(0, -1);
				}}>
			<DialogTrigger asChild>
				{children}
			</DialogTrigger>
			<Portal>
				<DialogBackdrop />
				<DialogContainer>
					<DialogContent
						ref={contentRef}
						overflow="hidden"
						lazyMount
						unmountOnExit
						padding="16px 20px"
					>
						<Toast toastConfig={toastConfig} />
						<VStack
							gap="22px"
							alignItems="start"
						>
							<Text style="lato_18_24_900" letterSpacing="-0.2px" userSelect="none">
								{langStringLdap2faSetupTotpCantScanQrDialogTitle}
							</Text>
							<VStack gap="8px">
								<StepBlock
									step={1}
									text={langStringLdap2faSetupTotpCantScanQrDialogFirstStepDescMobile}
								/>
								<StepBlock
									step={2}
									text={langStringLdap2faSetupTotpCantScanQrDialogSecondStepDesc}
									secretKey={totpSeed}
									containerRef={contentRef}
									isLoading={isLoading}
									isExpired={isExpired}
									onRefreshSecretKeyClickHandler={onRefreshSecretKeyClickHandler}
								/>
								<StepBlock
									step={3}
									text={langStringLdap2faSetupTotpCantScanQrDialogThirdStepDesc}
								/>
							</VStack>
						</VStack>
					</DialogContent>
				</DialogContainer>
			</Portal>
		</Dialog>
	);
}

const TotpManualAddDialog = ({ children }: PropsWithChildren) => {

	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const isMobile = useIsMobile();

	const apiFederationLdapAuthGetToken = useApiFederationLdapAuthGetToken();

	const dialogId = useMemo(() => generateDialogId(), []);
	const showToast = useShowToast(dialogId);
	const { navigateToDialog } = useNavigateDialog();

	const authLdapCredentials = useAtomValue(authLdapCredentialsState);
	const [ authLdapTotp, setAuthLdapTotp ] = useAtom(authLdapTotpState);
	const serverTimeOffset = useAtomValue(serverTimeOffsetState);

	const checkExpired = useCallback(() => {
		return (authLdapTotp.expires_at - (dayjs().unix() + serverTimeOffset)) < 1
	}, [ authLdapTotp.expires_at ]);
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
		return <TotpManualAddDialogMobile
			children={children}
			totpSeed={authLdapTotp.totp_seed}
			isLoading={isLoading}
			isExpired={isExpired}
			onRefreshSecretKeyClickHandler={onRefreshSecretKeyClickHandler}
			dialogId={dialogId}
		/>
	}

	return <TotpManualAddDialogDesktop
		children={children}
		totpSeed={authLdapTotp.totp_seed}
		isLoading={isLoading}
		isExpired={isExpired}
		onRefreshSecretKeyClickHandler={onRefreshSecretKeyClickHandler}
		dialogId={dialogId}
	/>
}

export default TotpManualAddDialog;