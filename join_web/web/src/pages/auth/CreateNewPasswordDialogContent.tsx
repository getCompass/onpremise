import { Box, HStack, styled, VStack } from "../../../styled-system/jsx";
import useIsMobile from "../../lib/useIsMobile.ts";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { KeyIcon80 } from "../../components/KeyIcon80.tsx";
import PasswordInput from "../../components/PasswordInput.tsx";
import { Button } from "../../components/button.tsx";
import { useNavigateDialog } from "../../components/hooks.ts";
import { useAtomValue } from "jotai/index";
import {
	activeDialogIdState,
	authState,
	confirmPasswordState,
	joinLinkState,
	passwordInputState,
} from "../../api/_stores.ts";
import { useShowToast } from "../../lib/Toast.tsx";
import ConfirmDialogClose from "../../components/ConfirmDialogClose.tsx";
import { useAtom } from "jotai";
import { APIAuthInfoDataTypeRegisterLoginResetPasswordByMail } from "../../api/_types.ts";
import { ApiError, NetworkError, ServerError } from "../../api/_index.ts";
import { useApiSecurityMailFinishResetPassword } from "../../api/security/mail.ts";
import Preloader16 from "../../components/Preloader16.tsx";
import { useApiAuthMailCancel } from "../../api/auth/mail.ts";

const CreateNewPasswordDialogContentDesktop = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const langStringCreateNewPasswordDialogTitle = useLangString("create_new_password_dialog.title");
	const langStringCreateNewPasswordDialogDesc = useLangString("create_new_password_dialog.desc");
	const langStringCreateNewPasswordDialogPasswordInputPlaceholder = useLangString(
		"create_new_password_dialog.password_input_placeholder"
	);
	const langStringCreateNewPasswordDialogConfirmPasswordInputPlaceholder = useLangString(
		"create_new_password_dialog.confirm_password_input_placeholder"
	);
	const langStringCreateNewPasswordDialogCancelButton = useLangString("create_new_password_dialog.cancel_button");
	const langStringCreateNewPasswordDialogConfirmButton = useLangString("create_new_password_dialog.confirm_button");
	const langStringCreateNewPasswordDialogPasswordNotMatchError = useLangString(
		"create_new_password_dialog.passwords_not_match_error"
	);
	const langStringCreateNewPasswordDialogPasswordLessThanMinSymbolsError = useLangString(
		"create_new_password_dialog.password_less_than_min_symbols_error"
	);
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const auth = useAtomValue(authState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const joinLink = useAtomValue(joinLinkState);
	const [password, setPassword] = useAtom(passwordInputState);
	const [confirmPassword, setConfirmPassword] = useAtom(confirmPasswordState);

	const apiSecurityMailFinishResetPassword = useApiSecurityMailFinishResetPassword();
	const apiAuthMailCancel = useApiAuthMailCancel();
	const showToast = useShowToast(activeDialogId);
	const { navigateToDialog } = useNavigateDialog();

	const passwordInputRef = useRef<HTMLInputElement>(null);
	const confirmPasswordInputRef = useRef<HTMLInputElement>(null);
	const [isNeedShowTooltipPassword, setIsNeedShowTooltipPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisiblePassword, setIsToolTipVisiblePassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorPassword, setIsErrorPassword] = useState(false);
	const [isNeedShowTooltipConfirmPassword, setIsNeedShowTooltipConfirmPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisibleConfirmPassword, setIsToolTipVisibleConfirmPassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorConfirmPassword, setIsErrorConfirmPassword] = useState(false);
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);

	const [isNeedShowConfirmCloseDialog, setIsNeedShowConfirmCloseDialog] = useState(false);
	const [isDialogCloseConfirmed, setIsDialogCloseConfirmed] = useState(false);

	useEffect(() => openDialog(), []);

	const onCreateClickHandler = useCallback(async () => {
		if (apiSecurityMailFinishResetPassword.isLoading) {
			return;
		}

		if (auth === null) {
			setPassword("");
			navigateToDialog("auth_email_phone_number");
			return;
		}

		if (password.length >= 8 && confirmPassword.length < 8 && confirmPasswordInputRef.current !== null) {
			confirmPasswordInputRef.current.focus();
			return;
		}

		if (password.length < 8) {
			setIsErrorPassword(true);
			showToast(langStringCreateNewPasswordDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (confirmPassword.length < 8) {
			setIsErrorConfirmPassword(true);
			showToast(langStringCreateNewPasswordDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			showToast(langStringCreateNewPasswordDialogPasswordNotMatchError, "warning");
			return;
		}

		try {
			await apiSecurityMailFinishResetPassword.mutateAsync({
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

				showToast(langStringErrorsServerError, "warning");
				return;
			}
		}
	}, [
		password,
		confirmPassword,
		navigateToDialog,
		apiSecurityMailFinishResetPassword,
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

	const openDialog = () => {
		setIsNeedShowConfirmCloseDialog(false);
		setIsDialogCloseConfirmed(false);
		setPassword("");
		setConfirmPassword("");
	};

	const closeDialog = () => {
		if (isNeedShowConfirmCloseDialog && !isDialogCloseConfirmed) {
			return;
		}

		if ((password !== "" || confirmPassword !== "") && !isNeedShowConfirmCloseDialog && !isDialogCloseConfirmed) {
			setIsNeedShowConfirmCloseDialog(true);
			return;
		}

		if (auth !== null) {
			apiAuthMailCancel.mutate({ auth_key: auth.auth_key });
		}
	};

	const cancelCloseConfirm = () => {
		setIsNeedShowConfirmCloseDialog(false);
	};

	const closeConfirm = () => {
		if (auth !== null) {
			apiAuthMailCancel.mutate({ auth_key: auth.auth_key });
		}
		setIsDialogCloseConfirmed(true);
	};

	if (auth === null) {
		setPassword("");
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<>
			<ConfirmDialogClose
				isNeedShowConfirmCloseDialog={isNeedShowConfirmCloseDialog}
				onCancel={() => cancelCloseConfirm()}
				onConfirm={() => closeConfirm()}
			/>
			<VStack w="100%" gap="0px">
				<VStack gap="0px" mt="20px">
					<KeyIcon80 />

					<Text mt="16px" style="lato_18_24_900" ls="-02">
						{langStringCreateNewPasswordDialogTitle}
					</Text>

					<Text
						mt="6px"
						textAlign="center"
						style="lato_14_20_400"
						ls="-015"
						maxW="328px"
						overflow="wrapEllipsis"
					>
						{langStringCreateNewPasswordDialogDesc}
						<styled.span fontFamily="lato_bold">
							«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
						</styled.span>
					</Text>

					<PasswordInput
						isDisabled={apiSecurityMailFinishResetPassword.isLoading}
						mt="20px"
						autoFocus={true}
						password={password}
						setPassword={setPassword}
						inputPlaceholder={langStringCreateNewPasswordDialogPasswordInputPlaceholder}
						isToolTipVisible={isToolTipVisiblePassword}
						setIsToolTipVisible={setIsToolTipVisiblePassword}
						isNeedShowTooltip={isNeedShowTooltipPassword}
						setIsNeedShowTooltip={setIsNeedShowTooltipPassword}
						isError={isErrorPassword}
						setIsError={setIsErrorPassword}
						onEnterClick={onCreateClickHandler}
						inputRef={passwordInputRef}
						isPasswordVisible={isPasswordVisible}
						setIsPasswordVisible={setIsPasswordVisible}
						onInputFocus={onPasswordInputFocus}
						inputTabIndex={1}
					/>

					<PasswordInput
						isDisabled={apiSecurityMailFinishResetPassword.isLoading}
						mt="8px"
						password={confirmPassword}
						setPassword={setConfirmPassword}
						inputPlaceholder={langStringCreateNewPasswordDialogConfirmPasswordInputPlaceholder}
						isToolTipVisible={isToolTipVisibleConfirmPassword}
						setIsToolTipVisible={setIsToolTipVisibleConfirmPassword}
						isNeedShowTooltip={isNeedShowTooltipConfirmPassword}
						setIsNeedShowTooltip={setIsNeedShowTooltipConfirmPassword}
						isError={isErrorConfirmPassword}
						setIsError={setIsErrorConfirmPassword}
						onEnterClick={onCreateClickHandler}
						inputRef={confirmPasswordInputRef}
						isPasswordVisible={isPasswordVisible}
						setIsPasswordVisible={setIsPasswordVisible}
						onInputFocus={onConfirmPasswordInputFocus}
						inputTabIndex={2}
					/>

					<HStack w="100%" justify="space-between" mt="24px">
						<Button color="f5f5f5" size="px16py6" textSize="lato_15_23_600" onClick={() => closeDialog()}>
							{langStringCreateNewPasswordDialogCancelButton}
						</Button>

						<Button
							size="pl16pr14py6"
							textSize="lato_15_23_600"
							disabled={password.length < 1 || confirmPassword.length < 1}
							onClick={() => onCreateClickHandler()}
							minW="111px"
						>
							{apiSecurityMailFinishResetPassword.isLoading ? (
								<Box py="3.5px">
									<Preloader16 />
								</Box>
							) : (
								<HStack gap="4px">
									{langStringCreateNewPasswordDialogConfirmButton}
									<Box w="20px" h="20px">
										<svg
											width="20"
											height="21"
											viewBox="0 0 20 21"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M13.7753 9.77503C14.0194 9.53095 14.0194 9.13522 13.7753 8.89115C13.5313 8.64707 13.1355 8.64707 12.8915 8.89115L9.16673 12.6159L7.10867 10.5578C6.86459 10.3137 6.46886 10.3137 6.22479 10.5578C5.98071 10.8019 5.98071 11.1976 6.22479 11.4417L8.72479 13.9417C8.842 14.0589 9.00097 14.1248 9.16673 14.1248C9.33249 14.1248 9.49146 14.0589 9.60867 13.9417L13.7753 9.77503Z"
												fill="white"
											/>
											<path
												fillRule="evenodd"
												clipRule="evenodd"
												d="M18.3334 10.9998C18.3334 15.6022 14.6025 19.3332 10.0001 19.3332C5.39771 19.3332 1.66675 15.6022 1.66675 10.9998C1.66675 6.39746 5.39771 2.6665 10.0001 2.6665C14.6025 2.6665 18.3334 6.39746 18.3334 10.9998ZM17.0834 10.9998C17.0834 14.9119 13.9121 18.0832 10.0001 18.0832C6.08806 18.0832 2.91675 14.9119 2.91675 10.9998C2.91675 7.08782 6.08806 3.9165 10.0001 3.9165C13.9121 3.9165 17.0834 7.08782 17.0834 10.9998Z"
												fill="white"
											/>
										</svg>
									</Box>
								</HStack>
							)}
						</Button>
					</HStack>
				</VStack>
			</VStack>
		</>
	);
};

const CreateNewPasswordDialogContentMobile = () => {
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");

	const langStringCreateNewPasswordDialogTitle = useLangString("create_new_password_dialog.title");
	const langStringCreateNewPasswordDialogDesc = useLangString("create_new_password_dialog.desc");
	const langStringCreateNewPasswordDialogPasswordInputPlaceholder = useLangString(
		"create_new_password_dialog.password_input_placeholder"
	);
	const langStringCreateNewPasswordDialogConfirmPasswordInputPlaceholder = useLangString(
		"create_new_password_dialog.confirm_password_input_placeholder"
	);
	const langStringCreateNewPasswordDialogCancelButton = useLangString("create_new_password_dialog.cancel_button");
	const langStringCreateNewPasswordDialogConfirmButton = useLangString("create_new_password_dialog.confirm_button");
	const langStringCreateNewPasswordDialogPasswordNotMatchError = useLangString(
		"create_new_password_dialog.passwords_not_match_error"
	);
	const langStringCreateNewPasswordDialogPasswordLessThanMinSymbolsError = useLangString(
		"create_new_password_dialog.password_less_than_min_symbols_error"
	);
	const langStringConfirmCloseDialogTitle = useLangString("confirm_close_dialog.title");
	const langStringConfirmCloseDialogDesc = useLangString("confirm_close_dialog.desc");
	const langStringConfirmCloseDialogConfirmButton = useLangString("confirm_close_dialog.confirm_button");
	const langStringConfirmCloseDialogCancelButton = useLangString("confirm_close_dialog.cancel_button");
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	const auth = useAtomValue(authState);
	const activeDialogId = useAtomValue(activeDialogIdState);
	const [password, setPassword] = useAtom(passwordInputState);
	const [confirmPassword, setConfirmPassword] = useAtom(confirmPasswordState);
	const joinLink = useAtomValue(joinLinkState);

	const apiSecurityMailFinishResetPassword = useApiSecurityMailFinishResetPassword();
	const apiAuthMailCancel = useApiAuthMailCancel();
	const showToast = useShowToast(activeDialogId);
	const { navigateToDialog } = useNavigateDialog();

	const passwordInputRef = useRef<HTMLInputElement>(null);
	const confirmPasswordInputRef = useRef<HTMLInputElement>(null);
	const [isNeedShowTooltipPassword, setIsNeedShowTooltipPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisiblePassword, setIsToolTipVisiblePassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorPassword, setIsErrorPassword] = useState(false);
	const [isNeedShowTooltipConfirmPassword, setIsNeedShowTooltipConfirmPassword] = useState(true); // нужно ли показывать тултип(показываем всего 1 раз)
	const [isToolTipVisibleConfirmPassword, setIsToolTipVisibleConfirmPassword] = useState(false); // видно ли тултип прям сейчас
	const [isErrorConfirmPassword, setIsErrorConfirmPassword] = useState(false);
	const [isPasswordVisible, setIsPasswordVisible] = useState(false);

	const [isNeedShowConfirmCloseDialog, setIsNeedShowConfirmCloseDialog] = useState(false);
	const [isDialogCloseConfirmed, setIsDialogCloseConfirmed] = useState(false);

	useEffect(() => openDialog(), []);
	const screenWidth = useMemo(() => document.body.clientWidth, [document.body.clientWidth]);

	const onCreateClickHandler = useCallback(async () => {
		if (apiSecurityMailFinishResetPassword.isLoading) {
			return;
		}

		if (auth === null) {
			setPassword("");
			navigateToDialog("auth_email_phone_number");
			return;
		}

		if (password.length >= 8 && confirmPassword.length < 8 && confirmPasswordInputRef.current !== null) {
			confirmPasswordInputRef.current.focus();
			return;
		}

		if (password.length < 8) {
			setIsErrorPassword(true);
			showToast(langStringCreateNewPasswordDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (confirmPassword.length < 8) {
			setIsErrorConfirmPassword(true);
			showToast(langStringCreateNewPasswordDialogPasswordLessThanMinSymbolsError, "warning");
			return;
		}

		if (password !== confirmPassword) {
			setIsErrorPassword(true);
			setIsErrorConfirmPassword(true);
			showToast(langStringCreateNewPasswordDialogPasswordNotMatchError, "warning");
			return;
		}

		try {
			await apiSecurityMailFinishResetPassword.mutateAsync({
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

				showToast(langStringErrorsServerError, "warning");
				return;
			}
		}
	}, [
		password,
		confirmPassword,
		navigateToDialog,
		apiSecurityMailFinishResetPassword,
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

	const openDialog = () => {
		setIsNeedShowConfirmCloseDialog(false);
		setIsDialogCloseConfirmed(false);
		setPassword("");
		setConfirmPassword("");
	};

	const closeDialog = () => {
		if (isNeedShowConfirmCloseDialog && !isDialogCloseConfirmed) {
			return;
		}

		if ((password !== "" || confirmPassword !== "") && !isNeedShowConfirmCloseDialog && !isDialogCloseConfirmed) {
			setIsNeedShowConfirmCloseDialog(true);
			return;
		}

		if (auth !== null) {
			apiAuthMailCancel.mutate({ auth_key: auth.auth_key });
		}
	};

	const cancelCloseConfirm = () => {
		setIsNeedShowConfirmCloseDialog(false);
	};

	const closeConfirm = () => {
		if (auth !== null) {
			apiAuthMailCancel.mutate({ auth_key: auth.auth_key });
		}
		setIsDialogCloseConfirmed(true);
	};

	if (auth === null) {
		setPassword("");
		navigateToDialog("auth_email_phone_number");
		return <></>;
	}

	return (
		<VStack w="100%" gap="0px">
			<VStack gap="0px" mt="16px">
				<KeyIcon80 />

				<Text mt="16px" style="lato_20_28_700" ls="-03">
					{langStringCreateNewPasswordDialogTitle}
				</Text>

				<Text
					mt="4px"
					textAlign="center"
					style="lato_16_22_400"
					maxW={screenWidth <= 390 ? "326px" : "350px"}
					overflow="wrapEllipsis"
				>
					{langStringCreateNewPasswordDialogDesc}
					<styled.span fontFamily="lato_bold">
						«{(auth.data as APIAuthInfoDataTypeRegisterLoginResetPasswordByMail).mail}»
					</styled.span>
				</Text>

				<PasswordInput
					isDisabled={apiSecurityMailFinishResetPassword.isLoading}
					mt="24px"
					autoFocus={true}
					password={password}
					setPassword={setPassword}
					inputPlaceholder={langStringCreateNewPasswordDialogPasswordInputPlaceholder}
					isToolTipVisible={isToolTipVisiblePassword}
					setIsToolTipVisible={setIsToolTipVisiblePassword}
					isNeedShowTooltip={isNeedShowTooltipPassword}
					setIsNeedShowTooltip={setIsNeedShowTooltipPassword}
					isError={isErrorPassword}
					setIsError={setIsErrorPassword}
					onEnterClick={onCreateClickHandler}
					inputRef={passwordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					onInputFocus={onPasswordInputFocus}
					inputTabIndex={1}
				/>

				<PasswordInput
					isDisabled={apiSecurityMailFinishResetPassword.isLoading}
					mt="8px"
					password={confirmPassword}
					setPassword={setConfirmPassword}
					inputPlaceholder={langStringCreateNewPasswordDialogConfirmPasswordInputPlaceholder}
					isToolTipVisible={isToolTipVisibleConfirmPassword}
					setIsToolTipVisible={setIsToolTipVisibleConfirmPassword}
					isNeedShowTooltip={isNeedShowTooltipConfirmPassword}
					setIsNeedShowTooltip={setIsNeedShowTooltipConfirmPassword}
					isError={isErrorConfirmPassword}
					setIsError={setIsErrorConfirmPassword}
					onEnterClick={onCreateClickHandler}
					inputRef={confirmPasswordInputRef}
					isPasswordVisible={isPasswordVisible}
					setIsPasswordVisible={setIsPasswordVisible}
					onInputFocus={onConfirmPasswordInputFocus}
					inputTabIndex={2}
				/>

				{isNeedShowConfirmCloseDialog ? (
					<HStack w="100%" justify="space-between" mt="24px">
						<VStack gap="6px" alignItems="start">
							<Text style="lato_16_20_700">{langStringConfirmCloseDialogTitle}</Text>
							<Text style="lato_13_18_400">{langStringConfirmCloseDialogDesc}</Text>
						</VStack>

						<HStack gap="12px">
							<Button
								size="px16py9"
								textSize="lato_17_26_600"
								color="ff6a64"
								onClick={() => closeConfirm()}
							>
								{langStringConfirmCloseDialogConfirmButton}
							</Button>

							<Button
								size="px16py9"
								textSize="lato_17_26_600"
								color="f5f5f5"
								onClick={() => cancelCloseConfirm()}
							>
								{langStringConfirmCloseDialogCancelButton}
							</Button>
						</HStack>
					</HStack>
				) : (
					<HStack w="100%" justify="space-between" mt="24px">
						<Button size="px16py9" textSize="lato_17_26_600" color="f5f5f5" onClick={() => closeDialog()}>
							{langStringCreateNewPasswordDialogCancelButton}
						</Button>

						<Button
							size="pl16pr14py9"
							textSize="lato_17_26_600"
							disabled={password.length < 1 || confirmPassword.length < 1}
							onClick={() => onCreateClickHandler()}
							minW="119px"
						>
							{apiSecurityMailFinishResetPassword.isLoading ? (
								<Box py="5px">
									<Preloader16 />
								</Box>
							) : (
								<HStack gap="4px">
									{langStringCreateNewPasswordDialogConfirmButton}
									<Box w="20px" h="20px">
										<svg
											width="20"
											height="20"
											viewBox="0 0 20 20"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M13.7752 8.77521C14.0193 8.53114 14.0193 8.13541 13.7752 7.89133C13.5312 7.64725 13.1354 7.64725 12.8913 7.89133L9.16662 11.6161L7.10856 9.558C6.86449 9.31392 6.46876 9.31392 6.22468 9.558C5.9806 9.80208 5.9806 10.1978 6.22468 10.4419L8.72468 12.9419C8.84189 13.0591 9.00086 13.1249 9.16662 13.1249C9.33238 13.1249 9.49135 13.0591 9.60857 12.9419L13.7752 8.77521Z"
												fill="white"
											/>
											<path
												fillRule="evenodd"
												clipRule="evenodd"
												d="M18.3333 9.99984C18.3333 14.6022 14.6023 18.3332 9.99996 18.3332C5.39759 18.3332 1.66663 14.6022 1.66663 9.99984C1.66663 5.39746 5.39759 1.6665 9.99996 1.6665C14.6023 1.6665 18.3333 5.39746 18.3333 9.99984ZM17.0833 9.99984C17.0833 13.9119 13.912 17.0832 9.99996 17.0832C6.08794 17.0832 2.91663 13.9119 2.91663 9.99984C2.91663 6.08782 6.08794 2.9165 9.99996 2.9165C13.912 2.9165 17.0833 6.08782 17.0833 9.99984Z"
												fill="white"
											/>
										</svg>
									</Box>
								</HStack>
							)}
						</Button>
					</HStack>
				)}
			</VStack>
		</VStack>
	);
};

const CreateNewPasswordDialogContent = () => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return <CreateNewPasswordDialogContentMobile />;
	}

	return <CreateNewPasswordDialogContentDesktop />;
};

export default CreateNewPasswordDialogContent;
