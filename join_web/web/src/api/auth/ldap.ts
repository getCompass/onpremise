import { useGetResponse } from "../_index.ts";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { ApiUserInfoData } from "../_types.ts";
import {
	authState,
	firstAuthState,
	isRegistrationState,
	userInfoDataState,
} from "../_stores.ts";
import { useSetAtom } from "jotai";
import useIsJoinLink from "../../lib/useIsJoinLink.ts";
import { useNavigateDialog, useNavigatePage } from "../../components/hooks.ts";

export type ApiFederationLdapAuthTryAuthenticateArgs = {
	username: string;
	password: string;
};

export type ApiFederationLdapAuthTryAuthenticate = {
	ldap_auth_token: string;
};

export function useApiFederationLdapAuthTryAuthenticate() {
	const getResponse = useGetResponse("federation");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ username, password }: ApiFederationLdapAuthTryAuthenticateArgs) => {
			const body = new URLSearchParams({
				username: username,
				password: password,
			});

			return await getResponse<ApiFederationLdapAuthTryAuthenticate>("ldap/auth/tryAuthenticate", body);
		},
	});
}

export type ApiPivotAuthLdapBeginArgs = {
	ldap_auth_token: string;
	join_link?: string;
};

export type ApiPivotAuthLdapBegin = {
	authentication_token: string;
	is_registration: 0 | 1;
	user_info: ApiUserInfoData;
};

export function useApiPivotAuthLdapBegin() {
	const getResponse = useGetResponse("pivot");
	const setAuth = useSetAtom(authState);
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
		mutationFn: async ({ ldap_auth_token, join_link }: ApiPivotAuthLdapBeginArgs) => {
			const body = new URLSearchParams({
				ldap_auth_token: ldap_auth_token,
			});

			if (join_link !== undefined && /join\/[a-zA-Z0-9]+\/?/.test(join_link)) {
				body.append("join_link", join_link);
			}

			return getResponse<ApiPivotAuthLdapBegin>("auth/ldap/begin", body, {});
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
			setUserInfoDataState(response.user_info);

			navigateToPage("token");
			navigateToDialog("token_page");
		},
	});
}
