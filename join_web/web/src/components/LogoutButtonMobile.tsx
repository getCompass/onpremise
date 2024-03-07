import {Box, HStack, VStack, styled} from "../../styled-system/jsx";
import {Portal} from "@ark-ui/react";
import {useCallback, useMemo} from "react";
import {Text} from "./text.tsx";
import {
	Dialog,
	DialogBackdrop,
	DialogCloseTrigger,
	DialogContainer,
	DialogContent,
	DialogTrigger,
	generateDialogId
} from "./dialog.tsx";
import {useLangString} from "../lib/getLangString.ts";
import {Button} from "./button.tsx";
import {NetworkError, ServerError} from "../api/_index.ts";
import {useApiAuthLogout} from "../api/auth.ts";
import {useToastConfig} from "../api/_stores.ts";
import Toast, {useShowToast} from "../lib/Toast.tsx";
import {css} from "../../styled-system/css";
import Preloader16 from "./Preloader16.tsx";

const LogOutIcon = () => {

	return (
		<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="16" cy="16" r="16" fill="black" fillOpacity="0.05"/>
			<g opacity="0.5">
				<path
					d="M11.6249 7.66675C10.3593 7.66675 9.33325 8.69276 9.33325 9.95841V22.0417C9.33325 23.3074 10.3593 24.3334 11.6249 24.3334H17.4583C18.7239 24.3334 19.7499 23.3074 19.7499 22.0417V19.8542C19.7499 19.5091 19.4701 19.2292 19.1249 19.2292C18.7797 19.2292 18.4999 19.5091 18.4999 19.8542V22.0417C18.4999 22.617 18.0335 23.0834 17.4583 23.0834H11.6249C11.0496 23.0834 10.5833 22.617 10.5833 22.0417V9.95841C10.5833 9.38312 11.0496 8.91675 11.6249 8.91675H17.4583C18.0335 8.91675 18.4999 9.38312 18.4999 9.95841V12.1459C18.4999 12.4911 18.7797 12.7709 19.1249 12.7709C19.4701 12.7709 19.7499 12.4911 19.7499 12.1459V9.95841C19.7499 8.69276 18.7239 7.66675 17.4583 7.66675H11.6249Z"
					fill="#DCDCDC"/>
				<path
					d="M21.3913 13.4747C21.6354 13.2306 22.0311 13.2306 22.2752 13.4747L24.3585 15.5581C24.4758 15.6753 24.5416 15.8342 24.5416 16C24.5416 16.1658 24.4758 16.3247 24.3585 16.4419L22.2752 18.5253C22.0311 18.7694 21.6354 18.7694 21.3913 18.5253C21.1473 18.2812 21.1473 17.8855 21.3913 17.6414L22.4077 16.625H16.2083C15.8631 16.625 15.5833 16.3452 15.5833 16C15.5833 15.6548 15.8631 15.375 16.2083 15.375H22.4077L21.3913 14.3586C21.1473 14.1145 21.1473 13.7188 21.3913 13.4747Z"
					fill="#DCDCDC"/>
			</g>
		</svg>
	);
}

const LogoutButtonMobile = () => {

	const langStringLogoutDialogTitle = useLangString("logout_dialog.title");
	const langStringLogoutDialogDesc = useLangString("logout_dialog.desc");
	const langStringLogoutDialogCancelButton = useLangString("logout_dialog.cancel_button");
	const langStringLogoutDialogConfirmButton = useLangString("logout_dialog.confirm_button");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const dialogId = useMemo(() => generateDialogId(), []);
	const toastConfig = useToastConfig(dialogId);
	const showToast = useShowToast(dialogId);

	const apiAuthLogout = useApiAuthLogout();
	const onLogoutClickHandler = useCallback(async () => {

		if (apiAuthLogout.isLoading) {
			return;
		}

		try {
			await apiAuthLogout.mutateAsync();
		} catch (error) {

			if (error instanceof NetworkError) {

				showToast(langStringErrorsNetworkError, "warning");
				return;
			}

			if (error instanceof ServerError) {

				showToast(langStringErrorsServerError, "warning");
				return;
			}
		}
	}, [apiAuthLogout]);

	return (
		<Dialog backdrop="opacity50">
			<DialogTrigger asChild>
				<Box
					className={css({
						width: "32px",
						height: "32px",
						WebkitTapHighlightColor: "transparent",
					})}
				>
					<LogOutIcon/>
				</Box>
			</DialogTrigger>
			<Portal>
				<DialogBackdrop/>
				<DialogContainer>
					<DialogContent
						overflow="hidden"
						lazyMount
						unmountOnExit
					>
						<Toast toastConfig={toastConfig}/>
						<VStack
							mt="16px"
							gap="24px"
						>
							<VStack
								gap="16px"
							>
								<Box
									w="80px"
									h="80px"
								>
									<svg width="80" height="80" viewBox="0 0 80 80"
										 fill="none"
										 xmlns="http://www.w3.org/2000/svg">
										<path
											d="M22.4999 6.6665C17.4373 6.6665 13.3333 10.7706 13.3333 15.8332V64.1665C13.3333 69.2291 17.4373 73.3332 22.4999 73.3332H45.8333C50.8959 73.3332 54.9999 69.2291 54.9999 64.1665V55.4165C54.9999 54.0358 53.8806 52.9165 52.4999 52.9165C51.1192 52.9165 49.9999 54.0358 49.9999 55.4165V64.1665C49.9999 66.4677 48.1344 68.3332 45.8333 68.3332H22.4999C20.1987 68.3332 18.3333 66.4677 18.3333 64.1665V15.8332C18.3333 13.532 20.1987 11.6665 22.4999 11.6665H45.8333C48.1344 11.6665 49.9999 13.532 49.9999 15.8332V24.5832C49.9999 25.9639 51.1192 27.0832 52.4999 27.0832C53.8806 27.0832 54.9999 25.9639 54.9999 24.5832V15.8332C54.9999 10.7706 50.8959 6.6665 45.8333 6.6665H22.4999Z"
											fill="#B4B4B4"/>
										<path
											d="M61.5655 29.8987C62.5418 28.9224 64.1247 28.9224 65.101 29.8987L73.4344 38.2321C73.9032 38.7009 74.1666 39.3368 74.1666 39.9999C74.1666 40.6629 73.9032 41.2988 73.4343 41.7676L65.101 50.1009C64.1247 51.0772 62.5418 51.0772 61.5655 50.1009C60.5892 49.1246 60.5892 47.5417 61.5655 46.5654L65.631 42.4999H40.8333C39.4525 42.4999 38.3333 41.3806 38.3333 39.9999C38.3333 38.6192 39.4525 37.4999 40.8333 37.4999H65.6311L61.5655 33.4343C60.5892 32.458 60.5892 30.875 61.5655 29.8987Z"
											fill="#B4B4B4"/>
									</svg>
								</Box>
								<VStack
									gap="4px"
								>
									<Text
										font="bold"
										ls="-03"
										fs="20"
										lh="28"
										color="333e49"
									>{langStringLogoutDialogTitle}</Text>
									<Text
										fs="16"
										lh="22"
										color="333e49"
										textAlign="center"
										font="regular"
									>{langStringLogoutDialogDesc}</Text>
								</VStack>
							</VStack>
							<HStack
								w="100%"
								justify="space-between"
							>
								<DialogCloseTrigger asChild>
									<Button
										color="f5f5f5"
										size="px16py9"
									>{langStringLogoutDialogCancelButton}</Button>
								</DialogCloseTrigger>
								<Button
									color="ff6a64"
									size="px16py9"
									minW="108px"
									onClick={() => onLogoutClickHandler()}
								>
									{apiAuthLogout.isLoading ? (
										<Box py="5px"><Preloader16/></Box>
									) : (
										<HStack gap="4px">
											<styled.span>{langStringLogoutDialogConfirmButton}</styled.span>
											<Box w="20px" h="20px">
												<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
													 xmlns="http://www.w3.org/2000/svg">
													<path
														d="M5.62492 1.6665C4.35927 1.6665 3.33325 2.69252 3.33325 3.95817V16.0415C3.33325 17.3072 4.35927 18.3332 5.62492 18.3332H11.4583C12.7239 18.3332 13.7499 17.3072 13.7499 16.0415V13.854C13.7499 13.5088 13.4701 13.229 13.1249 13.229C12.7797 13.229 12.4999 13.5088 12.4999 13.854V16.0415C12.4999 16.6168 12.0335 17.0832 11.4583 17.0832H5.62492C5.04962 17.0832 4.58325 16.6168 4.58325 16.0415V3.95817C4.58325 3.38287 5.04962 2.9165 5.62492 2.9165H11.4583C12.0335 2.9165 12.4999 3.38287 12.4999 3.95817V6.14567C12.4999 6.49085 12.7797 6.77067 13.1249 6.77067C13.4701 6.77067 13.7499 6.49085 13.7499 6.14567V3.95817C13.7499 2.69252 12.7239 1.6665 11.4583 1.6665H5.62492Z"
														fill="white"/>
													<path
														d="M15.3913 7.47448C15.6354 7.2304 16.0311 7.2304 16.2752 7.47448L18.3585 9.55782C18.4758 9.67503 18.5416 9.834 18.5416 9.99976C18.5416 10.1655 18.4758 10.3245 18.3585 10.4417L16.2752 12.525C16.0311 12.7691 15.6354 12.7691 15.3913 12.525C15.1473 12.281 15.1473 11.8852 15.3913 11.6411L16.4077 10.6248H10.2083C9.86309 10.6248 9.58327 10.3449 9.58327 9.99976C9.58327 9.65459 9.86309 9.37476 10.2083 9.37476H16.4077L15.3913 8.35836C15.1473 8.11428 15.1473 7.71856 15.3913 7.47448Z"
														fill="white"/>
												</svg>
											</Box>
										</HStack>
									)}
								</Button>
							</HStack>
						</VStack>
					</DialogContent>
				</DialogContainer>
			</Portal>
		</Dialog>
	);
}

export default LogoutButtonMobile;