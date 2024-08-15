import { useGetResponse } from "../_index.ts";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { APIAuthInfo, APIJoinLinkInfo, AUTH_MAIL_SCENARIO_FULL, AUTH_MAIL_SCENARIO_SHORT } from "../_types.ts";
import {useAtomValue, useSetAtom} from "jotai/index";
import {
	authInputState,
	authState,
	captchaProviderState,
	confirmPasswordState,
	firstAuthState,
	passwordInputState
} from "../_stores.ts";
import useIsJoinLink from "../../lib/useIsJoinLink.ts";
import { useNavigateDialog, useNavigatePage } from "../../components/hooks.ts";

export type ApiAuthMailBeginArgs = {
	mail: string;
	grecaptcha_response?: string;
	join_link?: string;
};

export type ApiAuthMailBegin = {
	auth_info: APIAuthInfo;
	join_link_info: APIJoinLinkInfo | null;
	scenario: AUTH_MAIL_SCENARIO_SHORT | AUTH_MAIL_SCENARIO_FULL;
};

export function useApiAuthMailBegin() {
	const getResponse = useGetResponse("pivot");
	const captchaProvider = useAtomValue(captchaProviderState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ mail, grecaptcha_response, join_link }: ApiAuthMailBeginArgs) => {
			const body = new URLSearchParams({
				mail: mail,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			if (join_link !== undefined && /join\/[a-zA-Z0-9]+\/?/.test(join_link)) {
				body.append("join_link", join_link);
			}

			return getResponse<ApiAuthMailBegin>("auth/mail/begin", body, {
				"x-compass-captcha-method": captchaProvider,
			});
		},
	});
}

export type ApiAuthMailConfirmShortAuthPasswordArgs = {
	auth_key: string;
	password: string;
	grecaptcha_response?: string;
	join_link_uniq?: string;
};

export type ApiAuthMailConfirmShortAuthPassword = {
	authentication_token: string;
	need_fill_profile: 0 | 1;
};

export function useApiAuthMailConfirmShortAuthPassword() {
	const getResponse = useGetResponse("pivot");
	const isJoinLink = useIsJoinLink();
	const setAuth = useSetAtom(authState);
	const setFirstAuth = useSetAtom(firstAuthState);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const queryClient = useQueryClient();
	const { navigateToDialog } = useNavigateDialog();
	const { navigateToPage } = useNavigatePage();
	const captchaProvider = useAtomValue(captchaProviderState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({
			auth_key,
			password,
			grecaptcha_response,
			join_link_uniq,
		}: ApiAuthMailConfirmShortAuthPasswordArgs) => {
			const body = new URLSearchParams({
				auth_key: auth_key,
				password: password,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			if (join_link_uniq !== undefined) {
				body.append("join_link_uniq", join_link_uniq);
			}

			return getResponse<ApiAuthMailConfirmShortAuthPassword>("auth/mail/confirmShortAuthPassword", body, {
				"x-compass-captcha-method": captchaProvider,
			});
		},
		async onSuccess(response) {
			setFirstAuth(true);
			await queryClient.invalidateQueries({ queryKey: ["global/start"] });
			if (isJoinLink) {
				await queryClient.invalidateQueries({ queryKey: ["joinlink/prepare", window.location.href] });
			}

			setAuth(null);
			setAuthInput("");
			setPasswordInput("");
			setConfirmPassword("");
			if (response.need_fill_profile === 1) {
				navigateToDialog("auth_create_profile");
			} else {
				navigateToPage("token");
				navigateToDialog("token_page");
			}
		},
	});
}

export type ApiAuthMailConfirmFullAuthPasswordArgs = {
	auth_key: string;
	password: string;
	grecaptcha_response?: string;
};

export type ApiAuthMailConfirmFullAuthPassword = {
	auth_info: APIAuthInfo;
};

export function useApiAuthMailConfirmFullAuthPassword() {
	const getResponse = useGetResponse("pivot");
	const setAuth = useSetAtom(authState);
	const { navigateToDialog } = useNavigateDialog();
	const captchaProvider = useAtomValue(captchaProviderState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ auth_key, password, grecaptcha_response }: ApiAuthMailConfirmFullAuthPasswordArgs) => {
			const body = new URLSearchParams({
				auth_key: auth_key,
				password: password,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			return getResponse<ApiAuthMailConfirmFullAuthPassword>("auth/mail/confirmFullAuthPassword", body, {
				"x-compass-captcha-method": captchaProvider,
			});
		},
		async onSuccess(response) {
			setAuth(response.auth_info);
			navigateToDialog("auth_email_confirm_code");
		},
	});
}

export type ApiAuthMailConfirmFullAuthCodeArgs = {
	auth_key: string;
	code: string;
	setIsSuccess: (value: boolean) => void;
	grecaptcha_response?: string;
	join_link_uniq?: string;
};

export type ApiAuthMailConfirmFullAuthCode = {
	authentication_token: string;
	need_fill_profile: 0 | 1;
};

export function useApiAuthMailConfirmFullAuthCode() {
	const getResponse = useGetResponse("pivot");
	const isJoinLink = useIsJoinLink();
	const setAuth = useSetAtom(authState);
	const setFirstAuth = useSetAtom(firstAuthState);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const queryClient = useQueryClient();
	const { navigateToDialog } = useNavigateDialog();
	const { navigateToPage } = useNavigatePage();
	const captchaProvider = useAtomValue(captchaProviderState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({
			auth_key,
			code,
			grecaptcha_response,
			join_link_uniq,
		}: ApiAuthMailConfirmFullAuthCodeArgs) => {
			const body = new URLSearchParams({
				auth_key: auth_key,
				code: code,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			if (join_link_uniq !== undefined) {
				body.append("join_link_uniq", join_link_uniq);
			}

			return getResponse<ApiAuthMailConfirmFullAuthCode>("auth/mail/confirmFullAuthCode", body, {
				"x-compass-captcha-method": captchaProvider,
			});
		},
		async onSuccess(response, variables) {
			variables.setIsSuccess(true);
			setFirstAuth(true);

			await queryClient.invalidateQueries({ queryKey: ["global/start"] });
			if (isJoinLink) {
				await queryClient.invalidateQueries({ queryKey: ["joinlink/prepare", window.location.href] });
			}

			setAuth(null);
			setAuthInput("");
			setPasswordInput("");
			setConfirmPassword("");
			if (response.need_fill_profile === 1) {
				navigateToDialog("auth_create_profile");
			} else {
				navigateToPage("token");
				navigateToDialog("token_page");
			}
		},
	});
}

type ApiAuthMailResendFullAuthCodeArgs = {
	auth_key: string;
};

export type ApiAuthMailResendFullAuthCode = {
	auth_info: APIAuthInfo;
};

export function useApiAuthMailResendFullAuthCode() {
	const getResponse = useGetResponse("pivot");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ auth_key }: ApiAuthMailResendFullAuthCodeArgs) => {
			const body = new URLSearchParams({
				auth_key: auth_key,
			});

			return getResponse<ApiAuthMailResendFullAuthCode>("auth/mail/resendFullAuthCode", body);
		},
	});
}

type ApiAuthMailCancelArgs = {
	auth_key: string;
};

export type ApiAuthMailCancel = {};

export function useApiAuthMailCancel() {
	const getResponse = useGetResponse("pivot");
	const setAuth = useSetAtom(authState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const { navigateToDialog } = useNavigateDialog();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ auth_key }: ApiAuthMailCancelArgs) => {
			const body = new URLSearchParams({
				auth_key: auth_key,
			});

			return getResponse<ApiAuthMailCancel>("auth/mail/cancel", body);
		},
		async onSuccess() {
			setAuth(null);
			setPasswordInput("");
			setConfirmPassword("");
			navigateToDialog("auth_email_phone_number");
		},
	});
}
