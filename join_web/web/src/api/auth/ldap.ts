import {useGetResponse} from "../_index.ts";
import {useMutation, useQueryClient} from "@tanstack/react-query";
import {APICommandData, ApiUserInfoData, ONPREMISE_LDAP_LOGIN_TYPE,} from "../_types.ts";
import {
	authenticationSessionTimeLeftState,
	authLdapCredentialsState,
	authLdapState,
	authState,
	deviceLoginTypeState,
	firstAuthState,
	isLdapChangeMailState,
	isRegistrationState,
	userInfoDataState,
} from "../_stores.ts";
import {useSetAtom} from "jotai";
import useIsJoinLink from "../../lib/useIsJoinLink.ts";
import {useNavigateDialog, useNavigatePage} from "../../components/hooks.ts";

export type ApiFederationLdapAuthGetTokenArgs = {
	username: string;
	password: string;
	mail_confirm_story_key?: string;
};

export type ApiFederationLdapAuthGetToken = {
	ldap_auth_token: string;
};

export function useApiFederationLdapAuthGetToken() {
	const getResponse = useGetResponse("federation");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({username, password, mail_confirm_story_key}: ApiFederationLdapAuthGetTokenArgs) => {
			const body = new URLSearchParams({
				username: username,
				password: password,
			});

			if (mail_confirm_story_key !== undefined) {
				body.append("mail_confirm_story_key", mail_confirm_story_key);
			}

			return await getResponse<ApiFederationLdapAuthGetToken>("ldap/auth/getToken", body);
		},
	});
}

export type ApiFederationLdapMailAddArgs = {
	mail: string;
	mail_confirm_story_key: string;
};

export type ApiFederationLdapMailAdd = {
	ldap_mail_confirm_story_info: APICommandData;
};

export function useApiFederationLdapMailAdd() {
	const getResponse = useGetResponse("federation");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({mail, mail_confirm_story_key}: ApiFederationLdapMailAddArgs) => {
			const body = new URLSearchParams({
				mail: mail,
				mail_confirm_story_key: mail_confirm_story_key,
			});

			return await getResponse<ApiFederationLdapMailAdd>("ldap/mail/add", body);
		},
	});
}

export type ApiFederationLdapMailChangeArgs = {
	mail_confirm_story_key: string;
};

export type ApiFederationLdapMailChange = {
	ldap_mail_confirm_story_info: APICommandData;
};

export function useApiFederationLdapMailChange() {
	const getResponse = useGetResponse("federation");
	const setIsLdapChangeMail = useSetAtom(isLdapChangeMailState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({mail_confirm_story_key}: ApiFederationLdapMailChangeArgs) => {
			const body = new URLSearchParams({
				mail_confirm_story_key: mail_confirm_story_key,
			});

			return await getResponse<ApiFederationLdapMailChange>("ldap/mail/change", body);
		},
		async onSuccess() {
			setIsLdapChangeMail(true);
		},
	});
}

export type ApiFederationLdapMailConfirmArgs = {
	mail_confirm_story_key: string;
	confirm_code: string;
	setIsSuccess: (value: boolean) => void;
};

export type ApiFederationLdapMailConfirm = {
	ldap_mail_confirm_story_info: APICommandData;
};

export function useApiFederationLdapMailConfirm() {
	const getResponse = useGetResponse("federation");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({mail_confirm_story_key, confirm_code}: ApiFederationLdapMailConfirmArgs) => {
			const body = new URLSearchParams({
				mail_confirm_story_key: mail_confirm_story_key,
				confirm_code: confirm_code,
			});

			return await getResponse<ApiFederationLdapMailConfirm>("ldap/mail/confirm", body);
		}, async onSuccess(_, variables) {
			variables.setIsSuccess(true);
		},
	});
}

export type ApiFederationLdapMailResendConfirmCodeArgs = {
	mail_confirm_story_key: string;
};

export type ApiFederationLdapMailResendConfirmCode = {
	ldap_mail_confirm_story_info: APICommandData;
};

export function useApiFederationLdapMailResendConfirmCode() {
	const getResponse = useGetResponse("federation");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({mail_confirm_story_key}: ApiFederationLdapMailResendConfirmCodeArgs) => {
			const body = new URLSearchParams({
				mail_confirm_story_key: mail_confirm_story_key,
			});

			return await getResponse<ApiFederationLdapMailResendConfirmCode>("ldap/mail/resendConfirmCode", body);
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
	const setAuthLdap = useSetAtom(authLdapState);
	const setAuthLdapCredentials = useSetAtom(authLdapCredentialsState);
	const setFirstAuth = useSetAtom(firstAuthState);
	const setUserInfoDataState = useSetAtom(userInfoDataState);
	const setRegistrationState = useSetAtom(isRegistrationState);
	const queryClient = useQueryClient();
	const isJoinLink = useIsJoinLink();
	const {navigateToDialog} = useNavigateDialog();
	const {navigateToPage} = useNavigatePage();
	const setSessionTimeLeft = useSetAtom(authenticationSessionTimeLeftState);
	const setDeviceLoginType = useSetAtom(deviceLoginTypeState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ldap_auth_token, join_link}: ApiPivotAuthLdapBeginArgs) => {
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

			await queryClient.invalidateQueries({queryKey: ["global/start"]});
			if (isJoinLink) {
				await queryClient.invalidateQueries({queryKey: ["joinlink/prepare", window.location.href]});
			}

			setAuth(null);
			setAuthLdap(null);
			setAuthLdapCredentials({
				username: "",
				password: "",
			});
			setUserInfoDataState(response.user_info);
			setSessionTimeLeft(60 * 15);
			setDeviceLoginType(ONPREMISE_LDAP_LOGIN_TYPE);

			navigateToPage("token");
			navigateToDialog("token_page");
		},
	});
}
