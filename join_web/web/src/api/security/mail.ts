import { useGetResponse } from "../_index.ts";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { APIAuthInfo, APIJoinLinkInfo } from "../_types.ts";
import { useNavigateDialog, useNavigatePage } from "../../components/hooks.ts";
import useIsJoinLink from "../../lib/useIsJoinLink.ts";
import { useSetAtom } from "jotai/index";
import {
	authInputState,
	authState,
	confirmPasswordState,
	firstAuthState,
	isPasswordChangedState,
	passwordInputState,
} from "../_stores.ts";

export type ApiSecurityMailTryResetPasswordArgs = {
	mail: string;
	grecaptcha_response?: string;
	join_link?: string;
};

export type ApiSecurityMailTryResetPassword = {
	auth_info: APIAuthInfo;
	join_link_info: APIJoinLinkInfo | null;
};

export function useApiSecurityMailTryResetPassword() {
	const getResponse = useGetResponse();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ mail, grecaptcha_response, join_link }: ApiSecurityMailTryResetPasswordArgs) => {
			const body = new URLSearchParams({
				mail: mail,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			if (join_link !== undefined && /join\/[a-zA-Z0-9]+\/?/.test(join_link)) {
				body.append("join_link", join_link);
			}

			return getResponse<ApiSecurityMailTryResetPassword>("security/mail/tryResetPassword", body, {
				"x-compass-captcha-method": "enterprise_google",
			});
		},
	});
}

export type ApiSecurityMailConfirmResetPasswordArgs = {
	code: string;
	auth_key: string;
	grecaptcha_response?: string;
	setIsSuccess: (value: boolean) => void;
};

export type ApiSecurityMailConfirmResetPassword = {};

export function useApiSecurityMailConfirmResetPassword() {
	const getResponse = useGetResponse();
	const { navigateToDialog } = useNavigateDialog();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ code, auth_key, grecaptcha_response }: ApiSecurityMailConfirmResetPasswordArgs) => {
			const body = new URLSearchParams({
				code: code,
				auth_key: auth_key,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			return getResponse<ApiSecurityMailConfirmResetPassword>("security/mail/confirmResetPassword", body, {
				"x-compass-captcha-method": "enterprise_google",
			});
		},
		async onSuccess(_, variables) {
			variables.setIsSuccess(true);
			navigateToDialog("auth_create_new_password");
		},
	});
}

export type ApiSecurityMailFinishResetPasswordArgs = {
	auth_key: string;
	password: string;
	join_link_uniq?: string;
};

export type ApiSecurityMailFinishResetPassword = {
	authentication_token: string;
	need_fill_profile: 0 | 1;
};

export function useApiSecurityMailFinishResetPassword() {
	const getResponse = useGetResponse();
	const isJoinLink = useIsJoinLink();
	const setAuth = useSetAtom(authState);
	const setFirstAuth = useSetAtom(firstAuthState);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const setIsPasswordChanged = useSetAtom(isPasswordChangedState);
	const queryClient = useQueryClient();
	const { navigateToDialog } = useNavigateDialog();
	const { navigateToPage } = useNavigatePage();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ auth_key, password, join_link_uniq }: ApiSecurityMailFinishResetPasswordArgs) => {
			const body = new URLSearchParams({
				auth_key: auth_key,
				password: password,
			});

			if (join_link_uniq !== undefined) {
				body.append("join_link_uniq", join_link_uniq);
			}

			return getResponse<ApiSecurityMailFinishResetPassword>("security/mail/finishResetPassword", body, {
				"x-compass-captcha-method": "enterprise_google",
			});
		},
		async onSuccess(response) {
			await queryClient.invalidateQueries({ queryKey: ["global/start"] });
			if (isJoinLink) {
				await queryClient.invalidateQueries({ queryKey: ["joinlink/prepare", window.location.href] });
			}

			setFirstAuth(true);
			setAuth(null);
			setAuthInput("");
			setPasswordInput("");
			setConfirmPassword("");
			setIsPasswordChanged(true);
			if (response.need_fill_profile === 1) {
				navigateToDialog("auth_create_profile");
			} else {
				navigateToPage("token");
			}
		},
	});
}

type ApiSecurityMailResendResetPasswordCodeArgs = {
	auth_key: string;
};

export type ApiSecurityMailResendResetPasswordCode = {
	auth_info: APIAuthInfo;
};

export function useApiSecurityMailResendResetPasswordCode() {
	const getResponse = useGetResponse();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ auth_key }: ApiSecurityMailResendResetPasswordCodeArgs) => {
			const body = new URLSearchParams({
				auth_key: auth_key,
			});

			return getResponse<ApiSecurityMailResendResetPasswordCode>("security/mail/resendResetPasswordCode", body, {
				"x-compass-captcha-method": "enterprise_google",
			});
		},
	});
}