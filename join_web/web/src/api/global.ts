import { useQuery } from "@tanstack/react-query";
import { ofetch } from "ofetch";
import { ApiGlobalStartDictionaryData, APIResponse, ApiUserInfoData, CAPTCHA_PROVIDER_DEFAULT } from "./_types.ts";
import { useSetAtom } from "jotai";
import {
	availableAuthMethodListState,
	captchaProviderState,
	captchaPublicKeyState,
	dictionaryDataState, downloadAppUrlState,
	profileState,
	serverVersionState,
	ssoProtocolState,
	userInfoDataState,
} from "./_stores.ts";
// @ts-ignore
import { getPublicPathApi } from "../private/custom.ts";

type ApiGlobalDoStart = {
	is_authorized: number;
	need_fill_profile: number;
	server_version: string;
	captcha_public_data: {
		provider_list: { [key: string]: string }[];
	};
	available_auth_method_list: string[];
	sso_protocol: string;
	dictionary: ApiGlobalStartDictionaryData;
	user_info: ApiUserInfoData | null;
	connect_check_url: string;
	download_app_url: string;
};

export function useApiGlobalDoStart() {
	const setProfile = useSetAtom(profileState);
	const setCaptchaPublicKey = useSetAtom(captchaPublicKeyState);
	const setCaptchaProvider = useSetAtom(captchaProviderState);
	const setAvailableAuthMethodList = useSetAtom(availableAuthMethodListState);
	const setSsoProtocol = useSetAtom(ssoProtocolState);
	const setServerVersion = useSetAtom(serverVersionState);
	const setStartDictionaryDataState = useSetAtom(dictionaryDataState);
	const setUserInfoDataState = useSetAtom(userInfoDataState);
	const setDownloadAppUrl = useSetAtom(downloadAppUrlState);

	return useQuery({
		retry: false,
		networkMode: "offlineFirst",
		queryKey: ["global/start"],
		queryFn: async () => {
			const result = await ofetch<APIResponse<ApiGlobalDoStart>>(
				getPublicPathApi() + "/pivot/api/onpremiseweb/global/start/",
				{
					method: "POST",
				}
			);

			setAvailableAuthMethodList(result.response.available_auth_method_list);

			setSsoProtocol(result.response.sso_protocol);

			setServerVersion(result.response.server_version ?? "");

			setStartDictionaryDataState(result.response.dictionary);

			setUserInfoDataState(result.response.user_info);

			setDownloadAppUrl(result.response.download_app_url);

			setProfile({
				is_authorized: result.response.is_authorized === 1,
				need_fill_profile: result.response.need_fill_profile === 1,
			});

			let captcha_public_key: string = "";
			let captcha_provider: string = "";
			let provider_list = result.response.captcha_public_data?.provider_list ?? {};

			for (let provider in provider_list) {
				captcha_provider = provider;
				captcha_public_key = provider_list[provider]["client_public_key"];
				if (provider == CAPTCHA_PROVIDER_DEFAULT) {
					break;
				}
			}

			setCaptchaPublicKey(captcha_public_key);
			setCaptchaProvider(captcha_provider);

			return result;
		},
	});
}
