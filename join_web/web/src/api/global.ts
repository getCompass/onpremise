import {useQuery} from "@tanstack/react-query";
import {ofetch} from "ofetch";
import {APIResponse, ApiGlobalStartDictionaryData, ApiUserInfoData} from "./_types.ts";
import {useSetAtom} from "jotai";
import {availableAuthMethodListState, captchaPublicKeyState, profileState, ssoProtocolState, dictionaryDataState, userInfoDataState} from "./_stores.ts";
// @ts-ignore
import {getPublicPathApi} from "../private/custom.ts";

type ApiGlobalDoStart = {
	is_authorized: number,
	need_fill_profile: number,
	captcha_public_key: string,
	available_auth_method_list: string[],
	sso_protocol: string,
	dictionary: ApiGlobalStartDictionaryData,
	user_info: ApiUserInfoData|null,
}

export function useApiGlobalDoStart() {

	const setProfile = useSetAtom(profileState);
	const setCaptchaPublicKey = useSetAtom(captchaPublicKeyState);
	const setAvailableAuthMethodList = useSetAtom(availableAuthMethodListState);
	const setSsoProtocol = useSetAtom(ssoProtocolState);
	const setStartDictionaryDataState = useSetAtom(dictionaryDataState);
	const setUserInfoDataState = useSetAtom(userInfoDataState);

	return useQuery({

		retry: false,
		networkMode: "offlineFirst",
		queryKey: ["global/start"],
		queryFn: async () => {

			const result = await ofetch<APIResponse<ApiGlobalDoStart>>(getPublicPathApi() + "/pivot/api/onpremiseweb/global/start/", {
				method: "POST",
				headers: {
					"x-compass-captcha-method": "enterprise_google",
				},
			});

			setAvailableAuthMethodList(result.response.available_auth_method_list);

			setSsoProtocol(result.response.sso_protocol);

			setStartDictionaryDataState(result.response.dictionary)

			setUserInfoDataState(result.response.user_info)

			setProfile({
				is_authorized: result.response.is_authorized === 1,
				need_fill_profile: result.response.need_fill_profile === 1,
			})

			setCaptchaPublicKey(result.response.captcha_public_key);

			return result;
		}
	});
}
