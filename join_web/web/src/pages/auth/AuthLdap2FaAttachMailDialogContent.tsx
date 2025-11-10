import { Box, VStack } from "../../../styled-system/jsx";
import { Input } from "../../components/input.tsx";
import { Button } from "../../components/button.tsx";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import useIsMobile from "../../lib/useIsMobile.ts";
import { RefObject, useCallback, useMemo, useRef, useState } from "react";
import { KeyIcon80 } from "../../components/KeyIcon80.tsx";
import { useApiFederationLdapMailAdd } from "../../api/auth/ldap.ts";
import { ApiCommand, ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { useAtomValue } from "jotai/index";
import { activeDialogIdState, authLdapState } from "../../api/_stores.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import {
	API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL,
	API_COMMAND_TYPE_NEED_CONFIRM_LDAP_MAIL,
	APICommandData,
} from "../../api/_types.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import { useNavigateDialog } from "../../components/hooks.ts";
import useLdap2FaStage from "../../lib/useLdap2FaStage.ts";
import { Tooltip, TooltipArrow, TooltipContent, TooltipProvider, TooltipTrigger } from "../../components/tooltip.tsx";
import { Portal } from "@ark-ui/react";
import dayjs from "dayjs";
import { plural } from "../../lib/plural.ts";

type AuthLdap2FaAttachMailDialogContentProps = {
	authLdap: APICommandData | null
	onAddMailClickHandler: () => void
	mailInputRef: RefObject<HTMLInputElement>
	apiIsLoading: boolean
	isLoading: boolean
	isError: boolean
	setIsError: (value: boolean) => void
	mail: string
	setMail: (value: string) => void
	isNeedShowTooltip: boolean
	setIsNeedShowTooltip: (value: boolean) => void
	isToolTipVisible: boolean
	setIsToolTipVisible: (value: boolean) => void
	toolTipText: string
	setToolTipText: (value: string) => void
}

const AuthLdap2FaAttachMailDialogContentDesktop = ({
	authLdap,
	onAddMailClickHandler,
	mailInputRef,
	apiIsLoading,
	isLoading,
	isError,
	setIsError,
	mail,
	setMail,
	isNeedShowTooltip,
	setIsNeedShowTooltip,
	isToolTipVisible,
	setIsToolTipVisible,
	toolTipText,
	setToolTipText
}: AuthLdap2FaAttachMailDialogContentProps) => {
	const langStringLdap2FaAddMailDialogTitle = useLangString("ldap_2fa_add_mail_dialog.title");
	const langStringLdap2FaAddMailDialogDesc = useLangString("ldap_2fa_add_mail_dialog.desc");
	const langStringLdap2FaAddMailDialogDescChangeMail = useLangString("ldap_2fa_add_mail_dialog.desc_change_mail");
	const langStringLdap2FaAddMailDialogMailInputPlaceholder = useLangString(
		"ldap_2fa_add_mail_dialog.mail_input_placeholder"
	);
	const langStringLdap2FaAddMailDialogConfirmButton = useLangString("ldap_2fa_add_mail_dialog.confirm_button");
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="20px" minW="100%">
				<KeyIcon80 />
				<Text mt="16px" style="lato_18_24_900" ls="-02">
					{langStringLdap2FaAddMailDialogTitle}
				</Text>
				<Text mt="6px" textAlign="center" style="lato_14_20_400" ls="-015" maxW="328px"
					  overflow="wrapEllipsis">
					{(authLdap !== null && authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL) ?
						langStringLdap2FaAddMailDialogDesc : langStringLdap2FaAddMailDialogDescChangeMail}
				</Text>
				<TooltipProvider>
					<Tooltip
						open={isToolTipVisible}
						onOpenChange={() => null}
						style="desktop"
						type="warning_desktop"
					>
						<VStack w="100%" gap="0px" mt="20px">
							<TooltipTrigger
								style={{
									width: "100%",
									height: "0px",
									opacity: "0%",
								}}
							/>
							<Input
								disabled={apiIsLoading}
								ref={mailInputRef}
								tabIndex={1}
								type="search"
								autoFocus={true}
								autoComplete="nope"
								value={mail}
								onChange={(changeEvent) => {
									const value = changeEvent.target.value ?? "";
									if (isNeedShowTooltip) {

										setToolTipText(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip);
										const isTooltipNotSavedSymbolsVisible =
											/[^а-яА-яёЁa-zA-Z0-9@+\-._ ']/.test(value);
										if (isTooltipNotSavedSymbolsVisible) {
											setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
											if (isTooltipNotSavedSymbolsVisible) {
												setIsNeedShowTooltip(false);
											}
										}
									}

									setMail(value);
									setIsError(false);
								}}
								maxLength={80}
								autoCapitalize="none"
								placeholder={langStringLdap2FaAddMailDialogMailInputPlaceholder}
								size="default_desktop"
								onKeyDown={(event: React.KeyboardEvent) => {
									if (event.key === "Enter") {
										onAddMailClickHandler();
									}
								}}
								input={isError ? "error_default" : "default"}
							/>
						</VStack>
						<Portal>
							<TooltipContent
								onClick={() => setIsToolTipVisible(false)}
								onEscapeKeyDown={() => setIsToolTipVisible(false)}
								onPointerDownOutside={() => setIsToolTipVisible(false)}
								sideOffset={4}
								avoidCollisions={false}
								style={{
									maxWidth: "256px",
									width: "var(--radix-tooltip-trigger-width)",
								}}
							>
								<TooltipArrow width="8px" height="5px" asChild>
									<svg
										width="8"
										height="5"
										viewBox="0 0 8 5"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00" />
									</svg>
								</TooltipArrow>
								{toolTipText}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				<Button
					mt="12px"
					size="px12py6full"
					textSize="lato_15_23_600"
					rounded="6px"
					disabled={mail.length < 1}
					onClick={() => onAddMailClickHandler()}
				>
					{isLoading ? (
						<Box py="3.5px">
							<Preloader16 />
						</Box>
					) : (
						langStringLdap2FaAddMailDialogConfirmButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdap2FaAttachMailDialogContentMobile = ({
	authLdap,
	onAddMailClickHandler,
	mailInputRef,
	apiIsLoading,
	isLoading,
	isError,
	setIsError,
	mail,
	setMail,
	isNeedShowTooltip,
	setIsNeedShowTooltip,
	isToolTipVisible,
	setIsToolTipVisible,
	toolTipText,
	setToolTipText
}: AuthLdap2FaAttachMailDialogContentProps) => {
	const langStringLdap2FaAddMailDialogTitle = useLangString("ldap_2fa_add_mail_dialog.title");
	const langStringLdap2FaAddMailDialogDesc = useLangString("ldap_2fa_add_mail_dialog.desc");
	const langStringLdap2FaAddMailDialogDescChangeMail = useLangString("ldap_2fa_add_mail_dialog.desc_change_mail");
	const langStringLdap2FaAddMailDialogMailInputPlaceholder = useLangString(
		"ldap_2fa_add_mail_dialog.mail_input_placeholder"
	);
	const langStringLdap2FaAddMailDialogConfirmButton = useLangString("ldap_2fa_add_mail_dialog.confirm_button");
	const langStringLdapLoginDialogBackButton = useLangString("ldap_login_dialog.back_button");
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const { navigateToDialog } = useNavigateDialog();

	const screenWidth = useMemo(() => document.body.clientWidth, [ document.body.clientWidth ]);

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
			<VStack gap="0px" mt="-6px" minW="100%">
				<KeyIcon80 />
				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringLdap2FaAddMailDialogTitle}
				</Text>
				<Text
					mt="4px"
					textAlign="center"
					style="lato_16_22_400"
					maxW={screenWidth <= 390 ? "326px" : "350px"}
					overflow="wrapEllipsis"
				>
					{(authLdap !== null && authLdap.scenario_data.stage === API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL) ?
						langStringLdap2FaAddMailDialogDesc : langStringLdap2FaAddMailDialogDescChangeMail}
				</Text>
				<TooltipProvider>
					<Tooltip
						open={isToolTipVisible}
						onOpenChange={() => null}
						style="mobile"
						type="warning_mobile"
					>
						<VStack w="100%" gap="0px" mt="24px">
							<TooltipTrigger
								style={{
									width: "100%",
									height: "0px",
									opacity: "0%",
								}}
							/>
							<Input
								disabled={apiIsLoading}
								ref={mailInputRef}
								type="search"
								autoFocus={true}
								autoComplete="nope"
								value={mail}
								onChange={(changeEvent) => {
									const value = changeEvent.target.value ?? "";
									if (isNeedShowTooltip) {

										setToolTipText(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip);
										const isTooltipNotSavedSymbolsVisible =
											/[^а-яА-яёЁa-zA-Z0-9@+\-._ ']/.test(value);
										if (isTooltipNotSavedSymbolsVisible) {
											setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
											if (isTooltipNotSavedSymbolsVisible) {
												setIsNeedShowTooltip(false);
											}
										}
									}

									setMail(value);
									setIsError(false);
								}}
								maxLength={80}
								autoCapitalize="none"
								placeholder={langStringLdap2FaAddMailDialogMailInputPlaceholder}
								size="px12py10w100"
								onKeyDown={(event: React.KeyboardEvent) => {
									if (event.key === "Enter") {
										onAddMailClickHandler();
									}
								}}
								input={isError ? "error_default" : "default"}
							/>
						</VStack>
						<Portal>
							<TooltipContent
								onClick={() => setIsToolTipVisible(false)}
								onEscapeKeyDown={() => setIsToolTipVisible(false)}
								onPointerDownOutside={() => setIsToolTipVisible(false)}
								sideOffset={4}
								avoidCollisions={false}
								style={{
									maxWidth: "256px",
									width: "var(--radix-tooltip-trigger-width)",
								}}
							>
								<TooltipArrow width="8px" height="5px" asChild>
									<svg
										width="8"
										height="5"
										viewBox="0 0 8 5"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00" />
									</svg>
								</TooltipArrow>
								{toolTipText}
							</TooltipContent>
						</Portal>
					</Tooltip>
				</TooltipProvider>
				<Button
					mt="12px"
					size="px16py9full"
					textSize="lato_17_26_600"
					disabled={mail.length < 1}
					onClick={() => onAddMailClickHandler()}
				>
					{isLoading ? (
						<Box py="5px">
							<Preloader16 />
						</Box>
					) : (
						langStringLdap2FaAddMailDialogConfirmButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
};

const AuthLdap2FaAttachMailDialogContent = () => {
	const isMobile = useIsMobile();

	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringErrorsConfirmCodeConfirmIsExpiredError = useLangString("errors.confirm_code_confirm_is_expired_error");
	const langStringErrorsConfirmCode2FaIsDisabledError = useLangString("errors.confirm_code_2fa_is_disabled_error");
	const langStringErrorsEmailIncorrectEmailError = useLangString("errors.email_incorrect_email_error");
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);
	const langStringConfirmCodeEmailDialogAuthBlocked = useLangString("confirm_code_email_dialog.auth_blocked");
	const langStringErrorsAddMail2FaLdapNotAllowedDomainError = useLangString("errors.add_mail_2fa_ldap_not_allowed_domain_error");
	const langStringErrorsAddMail2FaLdapNotAllowedDomainsError = useLangString("errors.add_mail_2fa_ldap_not_allowed_domains_error");

	const langStringOneMinute = useLangString("one_minute");
	const langStringTwoMinutes = useLangString("two_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");

	const apiFederationLdapMailAdd = useApiFederationLdapMailAdd();

	const activeDialogId = useAtomValue(activeDialogIdState);
	const authLdap = useAtomValue(authLdapState);
	const { navigateToDialog } = useNavigateDialog();
	const { navigateByStage } = useLdap2FaStage();

	const mailInputRef = useRef<HTMLInputElement>(null);

	const [ mail, setMail ] = useState("");
	const [ isError, setIsError ] = useState(false);
	const [ isLoading, setIsLoading ] = useState(false);
	const [ isNeedShowTooltip, setIsNeedShowTooltip ] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [ isToolTipVisible, setIsToolTipVisible ] = useState(false); // видно ли тултип прям сейчас
	const [ toolTipText, setToolTipText ] = useState(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip); // текст тултипа

	const showToast = useShowToast(activeDialogId);

	const onAddMailClickHandler = useCallback(async () => {
		if (mail.length < 1) {
			return;
		}

		if (authLdap === null) {

			navigateToDialog("auth_sso_ldap");
			return;
		}

		if (apiFederationLdapMailAdd.isLoading) {
			return;
		}

		try {
			setIsLoading(true);
			const federationLdapMailAddResponse = await apiFederationLdapMailAdd.mutateAsync({
				mail: mail,
				mail_confirm_story_key: authLdap.mail_confirm_story_key,
			});
			navigateByStage(federationLdapMailAddResponse.ldap_mail_confirm_story_info, setIsLoading, setIsError);
			setIsLoading(false);
		} catch (error) {
			if (error instanceof NetworkError || error instanceof ServerError) {
				showToast(error instanceof NetworkError ? langStringErrorsNetworkError : langStringErrorsServerError, "warning");
				setIsLoading(false);
				setIsError(true);
			} else if (error instanceof ApiCommand) {
				if (error.type === API_COMMAND_TYPE_NEED_CONFIRM_LDAP_MAIL) {
					navigateByStage(error.data, setIsLoading, setIsError);
				}
			} else if (error instanceof ApiError) {
				switch (error.error_code) {
					case 1708006:
					case 1708008:
					case 1708009:
						showToast(langStringConfirmCodeEmailDialogAuthBlocked.replace(
							"$MINUTES",
							`${Math.ceil((error.expires_at - dayjs().unix()) / 60)}${plural(
								Math.ceil((error.expires_at - dayjs().unix()) / 60),
								langStringOneMinute,
								langStringTwoMinutes,
								langStringFiveMinutes
							)}`
						), "warning");
						break;
					case 1708013:
					case 1708012:
						navigateToDialog("auth_sso_ldap");
						showToast(langStringErrorsConfirmCodeConfirmIsExpiredError, "warning");
						break;
					case 1708011:

						if (error.mail_allowed_domains.length > 0) {

							if (error.mail_allowed_domains.length === 1) {
								setToolTipText(langStringErrorsAddMail2FaLdapNotAllowedDomainError.replace("$DOMAIN", error.mail_allowed_domains[0]));
							} else {

								const allowedDomains = error.mail_allowed_domains.map(v => `«@${v}»`).join(', ');
								setToolTipText(langStringErrorsAddMail2FaLdapNotAllowedDomainsError.replace("$DOMAINS", allowedDomains));
							}
							setIsToolTipVisible(true);
						} else {

							setIsError(true);
							showToast(langStringErrorsEmailIncorrectEmailError, "warning");
						}
						break;
					case 1708016:
						navigateToDialog("auth_sso_ldap");
						showToast(langStringErrorsConfirmCode2FaIsDisabledError, "warning");
						break;
					case 1708017:
					default:
						setIsError(true);
						showToast(langStringErrorsEmailIncorrectEmailError, "warning");
						break;
				}
			}
		} finally {
			// в любом случае выключаем спиннер
			setIsLoading(false);
		}
	}, [ mail ]);

	if (isMobile) {

		return <AuthLdap2FaAttachMailDialogContentMobile
			authLdap={authLdap}
			onAddMailClickHandler={onAddMailClickHandler}
			mailInputRef={mailInputRef}
			apiIsLoading={apiFederationLdapMailAdd.isLoading}
			isLoading={isLoading}
			isError={isError}
			setIsError={setIsError}
			mail={mail}
			setMail={setMail}
			isNeedShowTooltip={isNeedShowTooltip}
			setIsNeedShowTooltip={setIsNeedShowTooltip}
			isToolTipVisible={isToolTipVisible}
			setIsToolTipVisible={setIsToolTipVisible}
			toolTipText={toolTipText}
			setToolTipText={setToolTipText}
		/>;
	}

	return <AuthLdap2FaAttachMailDialogContentDesktop
		authLdap={authLdap}
		onAddMailClickHandler={onAddMailClickHandler}
		mailInputRef={mailInputRef}
		apiIsLoading={apiFederationLdapMailAdd.isLoading}
		isLoading={isLoading}
		isError={isError}
		setIsError={setIsError}
		mail={mail}
		setMail={setMail}
		isNeedShowTooltip={isNeedShowTooltip}
		setIsNeedShowTooltip={setIsNeedShowTooltip}
		isToolTipVisible={isToolTipVisible}
		setIsToolTipVisible={setIsToolTipVisible}
		toolTipText={toolTipText}
		setToolTipText={setToolTipText}
	/>;
};

export default AuthLdap2FaAttachMailDialogContent;
