import { useGetResponse } from "../_index.ts";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useSetAtom } from "jotai/index";
import {
	authSsoState,
	authState,
	firstAuthState,
	isRegistrationState,
	userInfoDataState,
} from "../_stores.ts";
import { useNavigateDialog, useNavigatePage } from "../../components/hooks.ts";
import { ApiUserInfoData } from "../_types.ts";
import useIsJoinLink from "../../lib/useIsJoinLink.ts";

/* параметры api-метода federation/api/onpremiseweb/sso/auth/begin */
export type ApiFederationSsoAuthBeginArgs = {
	redirect_url: string;
};

/* структура ответа api-метода federation/api/onpremiseweb/sso/auth/begin */
export type ApiFederationSsoAuthBegin = {
	link: string;
	sso_auth_token: string;
	signature: string;
};

export function useApiFederationSsoAuthBegin() {
	const getResponse = useGetResponse("federation");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ redirect_url }: ApiFederationSsoAuthBeginArgs) => {
			const body = new URLSearchParams({
				redirect_url: redirect_url,
			});
			return getResponse<ApiFederationSsoAuthBegin>("sso/auth/begin", body, {});
		},
	});
}

/* параметры api-метода federation/api/onpremiseweb/sso/auth/getStatus */
export type ApiFederationSsoAuthGetStatusArgs = {
	sso_auth_token: string;
	signature: string;
};

/* структура ответа api-метода federation/api/onpremiseweb/sso/auth/getStatus */
export type ApiFederationSsoAuthGetStatus = {
	status: "wait" | "expired" | "ready" | "completed";
};

export function useApiFederationSsoAuthGetStatus() {
	const getResponse = useGetResponse("federation");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ sso_auth_token, signature }: ApiFederationSsoAuthGetStatusArgs) => {
			const body = new URLSearchParams({
				sso_auth_token: sso_auth_token,
				signature: signature,
			});
			return getResponse<ApiFederationSsoAuthGetStatus>("sso/auth/getStatus", body, {});
		},
	});
}

/* параметры api-метода pivot/api/onpremiseweb/auth/sso/begin */
export type ApiPivotAuthSsoBeginArgs = {
	sso_auth_token: string;
	signature: string;
	join_link?: string;
};

/* структура ответа api-метода pivot/api/onpremiseweb/auth/sso/begin */
export type ApiPivotAuthSsoBegin = {
	authentication_token: string;
	is_registration: 0 | 1;
	user_info: ApiUserInfoData;
};

export function useApiPivotAuthSsoBegin() {
	const getResponse = useGetResponse("pivot");
	const setAuth = useSetAtom(authState);
	const setAuthSso = useSetAtom(authSsoState);
	const setFirstAuth = useSetAtom(firstAuthState);
	const setUserInfoDataState = useSetAtom(userInfoDataState);
	const setRegistrationState = useSetAtom(isRegistrationState);
	const queryClient = useQueryClient();
	const isJoinLink = useIsJoinLink();
	const { navigateToDialog } = useNavigateDialog();
	const { navigateToPage } = useNavigatePage();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ sso_auth_token, signature, join_link }: ApiPivotAuthSsoBeginArgs) => {
			const body = new URLSearchParams({
				sso_auth_token: sso_auth_token,
				signature: signature,
			});

			if (join_link !== undefined && /join\/[a-zA-Z0-9]+\/?/.test(join_link)) {
				body.append("join_link", join_link);
			}

			return getResponse<ApiPivotAuthSsoBegin>("auth/sso/begin", body, {});
		},

		async onSuccess(response) {
			setFirstAuth(true);

			if (response.is_registration === 1) {
				setRegistrationState(true);
			}

			await queryClient.invalidateQueries({ queryKey: ["global/start"] });
			if (isJoinLink) {
				await queryClient.invalidateQueries({ queryKey: ["joinlink/prepare", window.location.href] });
			}

			setAuth(null);
			setAuthSso(null);
			setUserInfoDataState(response.user_info);

			navigateToPage("token");
			navigateToDialog("token_page");
		},
	});
}
