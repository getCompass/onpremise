import { Box, styled, VStack } from "../../../styled-system/jsx";
import {
	activeDialogIdState,
	authState,
	confirmPasswordState,
	joinLinkState,
	passwordInputState,
} from "../../api/_stores.ts";
import { useAtomValue, useSetAtom } from "jotai";
import useIsMobile from "../../lib/useIsMobile.ts";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { KeyIcon80 } from "../../components/KeyIcon80.tsx";
import PasswordInput from "../../components/PasswordInput.tsx";
import { Button } from "../../components/button.tsx";
import { useNavigateDialog } from "../../components/hooks.ts";
import { APIAuthInfoDataTypeRegisterLoginResetPasswordByMail, AUTH_MAIL_SCENARIO_SHORT } from "../../api/_types.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import {
	useApiAuthMailCancel,
	useApiAuthMailConfirmFullAuthPassword,
	useApiAuthMailConfirmShortAuthPassword,
} from "../../api/auth/mail.ts";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import { useAtom } from "jotai/index";

function EmailRegisterDialogContentDesktop() {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const langStringEmailRegisterDialogTitle = useLangString("email_register_dialog.title");
	const langStringEmailRegisterDialogDesc = useLangString("email_register_dialog.desc");
	const langStringEmailRegisterDialogPasswordInputPlaceholder = useLangString(
		"email_register_dialog.password_input_placeholder"
	);
	const langStringEmailRegisterDialogConfirmPasswordInputPlaceholder = useLangString(
		"email_register_dialog.confirm_password_input_placeholder"
	);
	const langStringEmailRegisterDialogRegisterButton = useLangString("email_register_dialog.register_button");
	const langStringEmailRegisterDialogPasswordNotMatchError = useLangString(
		"email_register_dialog.passwords_not_match_error"
	);
	const langStringEmailRegisterDialogPasswordLessThanMinSymbolsError = useLangString(
		"email_register_dialog.password_less_than_min_symbols_error"
	);
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const auth = useAtomValue(authState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const joinLink = useAtomValue(joinLinkState);
	const [password, setPassword] = useAtom(passwordInputState);
	const [confirmPassword, setConfirmPassword] = useAtom(confirmPasswordState);
	const setAuth = useSetAtom(authState);
	const showToast = useShowToast(activeDialogId);
	const { navigateToDialog } = useNavigateDialog();
	const apiAuthMailConfirmShortAuthPassword = useApiAuthMailConfirmShortAuthPassword();
	const apiAuthMailConfirmFullAuthPassword = useApiAuthMailConfirmFullAuthPassword();

	const passwordInputRef = useRef<HTMLInputElement>(null);
	const confirmPasswordInputRef = useRef<HTMLInputElement>(null);

	const [isNeedShowTooltipPassword, setIsNeedShowTooltipPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisiblePassword, setIsToolTipVisiblePassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorPassword, setIsErrorPassword] = useState(false);
	const [isNeedShowTooltipConfirmPassword, setIsNeedShowTooltipConfirmPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisibleConfirmPassword, setIsToolTipVisibleConfirmPassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorConfirmPassword, setIsErrorConfirmPassword] = useState(false);
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);

	const onRegisterClickHandler = useCallback(async () => {
		if (auth === null) {
			navigateToDialog("auth_email_phone_number");
			return;
		}

		if (password.length >= 8 && confirmPassword.length < 8 && confirmPasswordInputRef.current !== null) {
			confirmPasswordInputRef.current.focus();
			return;
		}

		if (password.length < 8) {
			setIsErrorPassword(true);
			showToast(langStringEmailRegisterDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (confirmPassword.length < 8) {
			setIsErrorConfirmPassword(true);
			showToast(langStringEmailRegisterDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			showToast(langStringEmailRegisterDialogPasswordNotMatchError, "warning");
			return;
		}

		if ((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).scenario === AUTH_MAIL_SCENARIO_SHORT) {
			try {
				await apiAuthMailConfirmShortAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
					join_link_uniq: joinLink === null ? undefined : joinLink.join_link_uniq,
				});
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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708301) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					if (error.error_code === 1708302) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		} else {
			try {
				await apiAuthMailConfirmFullAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
				});
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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708301) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					if (error.error_code === 1708302) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		}
	}, [
		password,
		confirmPassword,
		navigateToDialog,
		apiAuthMailConfirmShortAuthPassword,
		apiAuthMailConfirmFullAuthPassword,
		joinLink,
		auth,
		passwordInputRef,
		confirmPasswordInputRef,
	]);

	const onPasswordInputFocus = useCallback(() => {
		setIsToolTipVisibleConfirmPassword(false);

		if (confirmPassword.length > 0 && confirmPassword.length < 8) {
			setIsErrorConfirmPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password === confirmPassword) {
			setIsErrorPassword(false);
			setIsErrorConfirmPassword(false);
			return;
		}
	}, [password, confirmPassword]);

	const onConfirmPasswordInputFocus = useCallback(() => {
		setIsToolTipVisiblePassword(false);

		if (password.length > 0 && password.length < 8) {
			setIsErrorPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password === confirmPassword) {
			setIsErrorPassword(false);
			setIsErrorConfirmPassword(false);
			return;
		}
	}, [password, confirmPassword]);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="20px">
				<KeyIcon80 />

				<Text mt="16px" style="lato_18_24_900" ls="-02">
					{langStringEmailRegisterDialogTitle}
				</Text>

				<Text mt="6px" textAlign="center" style="lato_14_20_400" ls="-015" maxW="328px" overflow="wrapEllipsis">
					{langStringEmailRegisterDialogDesc}
					<styled.span fontFamily="lato_bold">
						«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				<PasswordInput
					isDisabled={
						apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading
					}
					mt="20px"
					autoFocus={true}
					password={password}
					setPassword={setPassword}
					inputPlaceholder={langStringEmailRegisterDialogPasswordInputPlaceholder}
					isToolTipVisible={isToolTipVisiblePassword}
					setIsToolTipVisible={setIsToolTipVisiblePassword}
					isNeedShowTooltip={isNeedShowTooltipPassword}
					setIsNeedShowTooltip={setIsNeedShowTooltipPassword}
					isError={isErrorPassword}
					setIsError={setIsErrorPassword}
					onEnterClick={onRegisterClickHandler}
					inputRef={passwordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					onInputFocus={onPasswordInputFocus}
					inputTabIndex={1}
				/>

				<PasswordInput
					isDisabled={
						apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading
					}
					mt="8px"
					password={confirmPassword}
					setPassword={setConfirmPassword}
					inputPlaceholder={langStringEmailRegisterDialogConfirmPasswordInputPlaceholder}
					isToolTipVisible={isToolTipVisibleConfirmPassword}
					setIsToolTipVisible={setIsToolTipVisibleConfirmPassword}
					isNeedShowTooltip={isNeedShowTooltipConfirmPassword}
					setIsNeedShowTooltip={setIsNeedShowTooltipConfirmPassword}
					isError={isErrorConfirmPassword}
					setIsError={setIsErrorConfirmPassword}
					onEnterClick={onRegisterClickHandler}
					inputRef={confirmPasswordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					onInputFocus={onConfirmPasswordInputFocus}
					inputTabIndex={2}
				/>

				<Button
					mt="12px"
					size="px12py6full"
					textSize="lato_15_23_600"
					disabled={password.length < 1 || confirmPassword.length < 1}
					onClick={() => onRegisterClickHandler()}
				>
					{apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading ? (
						<Box py="3.5px">
							<Preloader16 />
						</Box>
					) : (
						langStringEmailRegisterDialogRegisterButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
}

const EmailRegisterDialogContentMobile = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const langStringEmailRegisterDialogTitle = useLangString("email_register_dialog.title");
	const langStringEmailRegisterDialogDesc = useLangString("email_register_dialog.desc");
	const langStringEmailRegisterDialogPasswordInputPlaceholder = useLangString(
		"email_register_dialog.password_input_placeholder"
	);
	const langStringEmailRegisterDialogConfirmPasswordInputPlaceholder = useLangString(
		"email_register_dialog.confirm_password_input_placeholder"
	);
	const langStringEmailRegisterDialogRegisterButton = useLangString("email_register_dialog.register_button");
	const langStringEmailRegisterDialogBackButton = useLangString("email_register_dialog.back_button");
	const langStringEmailRegisterDialogPasswordNotMatchError = useLangString(
		"email_register_dialog.passwords_not_match_error"
	);
	const langStringEmailRegisterDialogPasswordLessThanMinSymbolsError = useLangString(
		"email_register_dialog.password_less_than_min_symbols_error"
	);
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const auth = useAtomValue(authState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const joinLink = useAtomValue(joinLinkState);
	const [password, setPassword] = useAtom(passwordInputState);
	const [confirmPassword, setConfirmPassword] = useAtom(confirmPasswordState);
	const setAuth = useSetAtom(authState);
	const showToast = useShowToast(activeDialogId);
	const { navigateToDialog } = useNavigateDialog();
	const apiAuthMailConfirmShortAuthPassword = useApiAuthMailConfirmShortAuthPassword();
	const apiAuthMailConfirmFullAuthPassword = useApiAuthMailConfirmFullAuthPassword();
	const apiAuthMailCancel = useApiAuthMailCancel();

	const passwordInputRef = useRef<HTMLInputElement>(null);
	const confirmPasswordInputRef = useRef<HTMLInputElement>(null);

	const [isNeedShowTooltipPassword, setIsNeedShowTooltipPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisiblePassword, setIsToolTipVisiblePassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorPassword, setIsErrorPassword] = useState(false);
	const [isNeedShowTooltipConfirmPassword, setIsNeedShowTooltipConfirmPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisibleConfirmPassword, setIsToolTipVisibleConfirmPassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorConfirmPassword, setIsErrorConfirmPassword] = useState(false);
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);

	const screenWidth = useMemo(() => document.body.clientWidth, [document.body.clientWidth]);

	const onRegisterClickHandler = useCallback(async () => {
		if (auth === null) {
			navigateToDialog("auth_email_phone_number");
			return;
		}

		if (password.length >= 8 && confirmPassword.length < 8 && confirmPasswordInputRef.current !== null) {
			confirmPasswordInputRef.current.focus();
			return;
		}

		if (password.length < 8) {
			setIsErrorPassword(true);
			showToast(langStringEmailRegisterDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (confirmPassword.length < 8) {
			setIsErrorConfirmPassword(true);
			showToast(langStringEmailRegisterDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			showToast(langStringEmailRegisterDialogPasswordNotMatchError, "warning");
			return;
		}

		if ((auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).scenario === AUTH_MAIL_SCENARIO_SHORT) {
			try {
				await apiAuthMailConfirmShortAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
					join_link_uniq: joinLink === null ? undefined : joinLink.join_link_uniq,
				});

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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708301) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					if (error.error_code === 1708302) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		} else {
			try {
				await apiAuthMailConfirmFullAuthPassword.mutateAsync({
					auth_key: auth.auth_key,
					password: password,
				});

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
					if (error.error_code === 1708117) {
						showToast(langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip, "warning");
						return;
					}

					if (error.error_code === 1708301) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					if (error.error_code === 1708302) {
						setAuth(null);
						navigateToDialog("auth_email_phone_number");
						return;
					}

					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		}
	}, [
		password,
		confirmPassword,
		navigateToDialog,
		apiAuthMailConfirmShortAuthPassword,
		apiAuthMailConfirmFullAuthPassword,
		joinLink,
		auth,
		passwordInputRef,
		confirmPasswordInputRef,
	]);

	const onPasswordInputFocus = useCallback(() => {
		setIsToolTipVisibleConfirmPassword(false);

		if (confirmPassword.length > 0 && confirmPassword.length < 8) {
			setIsErrorConfirmPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password === confirmPassword) {
			setIsErrorPassword(false);
			setIsErrorConfirmPassword(false);
			return;
		}
	}, [password, confirmPassword]);

	const onConfirmPasswordInputFocus = useCallback(() => {
		setIsToolTipVisiblePassword(false);

		if (password.length > 0 && password.length < 8) {
			setIsErrorPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			return;
		}

		if (password.length >= 8 && confirmPassword.length >= 8 && password === confirmPassword) {
			setIsErrorPassword(false);
			setIsErrorConfirmPassword(false);
			return;
		}
	}, [password, confirmPassword]);

	if (auth === null) {
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w="100%" gap="0px">
			<Box w="100%">
				<Button
					color="2574a9"
					textSize="lato_16_22_400"
					size="px0py0"
					onClick={() => apiAuthMailCancel.mutate({ auth_key: auth.auth_key })}
				>
					{langStringEmailRegisterDialogBackButton}
				</Button>
			</Box>
			<VStack gap="0px" mt="-6px">
				<KeyIcon80 />

				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringEmailRegisterDialogTitle}
				</Text>

				<Text
					mt="4px"
					textAlign="center"
					style="lato_16_22_400"
					maxW={screenWidth <= 390 ? "326px" : "350px"}
					overflow="wrapEllipsis"
				>
					{langStringEmailRegisterDialogDesc}
					<styled.span fontFamily="lato_bold">
						«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				<PasswordInput
					isDisabled={
						apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading
					}
					mt="24px"
					autoFocus={true}
					password={password}
					setPassword={setPassword}
					inputPlaceholder={langStringEmailRegisterDialogPasswordInputPlaceholder}
					isToolTipVisible={isToolTipVisiblePassword}
					setIsToolTipVisible={setIsToolTipVisiblePassword}
					isNeedShowTooltip={isNeedShowTooltipPassword}
					setIsNeedShowTooltip={setIsNeedShowTooltipPassword}
					isError={isErrorPassword}
					setIsError={setIsErrorPassword}
					onEnterClick={onRegisterClickHandler}
					inputRef={passwordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					onInputFocus={onPasswordInputFocus}
					inputTabIndex={1}
				/>

				<PasswordInput
					isDisabled={
						apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading
					}
					mt="8px"
					password={confirmPassword}
					setPassword={setConfirmPassword}
					inputPlaceholder={langStringEmailRegisterDialogConfirmPasswordInputPlaceholder}
					isToolTipVisible={isToolTipVisibleConfirmPassword}
					setIsToolTipVisible={setIsToolTipVisibleConfirmPassword}
					isNeedShowTooltip={isNeedShowTooltipConfirmPassword}
					setIsNeedShowTooltip={setIsNeedShowTooltipConfirmPassword}
					isError={isErrorConfirmPassword}
					setIsError={setIsErrorConfirmPassword}
					onEnterClick={onRegisterClickHandler}
					inputRef={confirmPasswordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					onInputFocus={onConfirmPasswordInputFocus}
					inputTabIndex={2}
				/>

				<Button
					mt="12px"
					size="px16py9full"
					textSize="lato_17_26_600"
					disabled={password.length < 1 || confirmPassword.length < 1}
					onClick={() => onRegisterClickHandler()}
				>
					{apiAuthMailConfirmShortAuthPassword.isLoading || apiAuthMailConfirmFullAuthPassword.isLoading ? (
						<Box py="5px">
							<Preloader16 />
						</Box>
					) : (
						langStringEmailRegisterDialogRegisterButton
					)}
				</Button>
			</VStack>
		</VStack>
	);
};

const EmailRegisterDialogContent = () => {
	const isMobile = useIsMobile();

	const inputRef = useRef<HTMLDivElement>(null);
	useEffect(() => {
		if (inputRef.current) {
			inputRef.current.focus();
		}
	}, [inputRef]);

	if (isMobile) {
		return <EmailRegisterDialogContentMobile />;
	}

	return <EmailRegisterDialogContentDesktop />;
};

export default EmailRegisterDialogContent;
