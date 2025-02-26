import {useGetResponse} from "../_index.ts";
import {useMutation, useQueryClient} from "@tanstack/react-query";
import {
	APIAuthInfo,
	APIJoinLinkInfo,
	ONPREMISE_SMS_LOGIN_TYPE
} from "../_types.ts";
import {useSetAtom} from "jotai";
import {
	authenticationSessionTimeLeftState,
	authInputState,
	authState,
	captchaProviderState,
	firstAuthState,
	deviceLoginTypeState,
} from "../_stores.ts";
import {useNavigateDialog, useNavigatePage} from "../../components/hooks.ts";
import useIsJoinLink from "../../lib/useIsJoinLink.ts";
import {useAtomValue} from "jotai/index";

export type ApiAuthPhoneNumberBeginArgs = {
	phone_number: string;
	grecaptcha_response?: string;
	join_link?: string;
};

export type ApiAuthPhoneNumberBegin = {
	auth_info: APIAuthInfo;
	join_link_info: APIJoinLinkInfo | null;
};

export function useApiAuthPhoneNumberBegin() {

	const getResponse = useGetResponse("pivot");
	const captchaProvider = useAtomValue(captchaProviderState);

	return useMutation({

		retry: false,
		networkMode: "always",
		mutationFn: async ({phone_number, grecaptcha_response, join_link}: ApiAuthPhoneNumberBeginArgs) => {

			const body = new URLSearchParams({
				phone_number: phone_number,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			if (join_link !== undefined && /join\/[a-zA-Z0-9]+\/?/.test(join_link)) {
				body.append("join_link", join_link);
			}

			return getResponse<ApiAuthPhoneNumberBegin>("auth/begin", body, {
				"x-compass-captcha-method": captchaProvider,
			});
		},
	});
}

export type ApiAuthPhoneNumberConfirmArgs = {
	sms_code: string;
	auth_key: string;
	setIsSuccess: (value: boolean) => void;
	join_link_uniq?: string;
};

export type ApiAuthPhoneNumberConfirm = {
	authentication_token: string;
	need_fill_profile: number;
};

export function useApiAuthPhoneNumberConfirm() {

	const getResponse = useGetResponse("pivot");
	const isJoinLink = useIsJoinLink();
	const setAuth = useSetAtom(authState)
	const setFirstAuth = useSetAtom(firstAuthState);
	const setAuthInput = useSetAtom(authInputState)
	const queryClient = useQueryClient();
	const {navigateToDialog} = useNavigateDialog();
	const {navigateToPage} = useNavigatePage();
	const setSessionTimeLeft = useSetAtom(authenticationSessionTimeLeftState);
	const setDeviceLoginType = useSetAtom(deviceLoginTypeState);

	return useMutation({

		retry: false,
		networkMode: "always",
		mutationFn: async ({sms_code, auth_key, join_link_uniq}: ApiAuthPhoneNumberConfirmArgs) => {

			const body = new URLSearchParams({
				sms_code: sms_code,
				auth_key: auth_key,
			});

			if (join_link_uniq !== undefined) {
				body.append("join_link_uniq", join_link_uniq);
			}

			return getResponse<ApiAuthPhoneNumberConfirm>("auth/confirm", body);
		},
		async onSuccess(response, variables) {

			variables.setIsSuccess(true);
			setFirstAuth(true);

			await queryClient.invalidateQueries({queryKey: ["global/start"]});
			if (isJoinLink) {
				await queryClient.invalidateQueries({queryKey: ["joinlink/prepare", window.location.href]});
			}

			setAuth(null);
			setAuthInput("");
			setSessionTimeLeft(60 * 15);
			setDeviceLoginType(ONPREMISE_SMS_LOGIN_TYPE);
			if (response.need_fill_profile === 1) {
				navigateToDialog("auth_create_profile");
			} else {
				navigateToPage("token");
				navigateToDialog("token_page");
			}
		},
	});
}

type ApiAuthPhoneNumberRetryArgs = {
	auth_key: string;
	grecaptcha_response?: string;
}

export function useApiAuthPhoneNumberRetry() {

	const getResponse = useGetResponse("pivot");
	const captchaProvider = useAtomValue(captchaProviderState);

	return useMutation({

		retry: false,
		networkMode: "always",
		mutationFn: async ({auth_key, grecaptcha_response}: ApiAuthPhoneNumberRetryArgs) => {

			const body = new URLSearchParams({
				auth_key: auth_key,
			});

			if (grecaptcha_response !== undefined) {
				body.append("grecaptcha_response", grecaptcha_response);
			}

			return getResponse<APIAuthInfo>("auth/retry", body, {
				"x-compass-captcha-method": captchaProvider,
			});
		},
	});
}
