import {useQuery} from "@tanstack/react-query";
import {ofetch} from "ofetch";
import {APIResponse} from "./_types.ts";
import {useSetAtom} from "jotai";
import {availableAuthMethodListState, captchaPublicKeyState, profileState} from "./_stores.ts";

type ApiGlobalDoStart = {
	is_authorized: number,
	need_fill_profile: number,
	captcha_public_key: string,
	available_auth_method_list: string[],
}

export function useApiGlobalDoStart() {

	const setProfile = useSetAtom(profileState);
	const setCaptchaPublicKey = useSetAtom(captchaPublicKeyState);
	const setAvailableAuthMethodList = useSetAtom(availableAuthMethodListState);

	return useQuery({

		retry: false,
		networkMode: "offlineFirst",
		queryKey: ["global/start"],
		queryFn: async () => {

			const result = await ofetch<APIResponse<ApiGlobalDoStart>>("/pivot/api/onpremiseweb/global/start/", {
				method: "POST",
				headers: {
					"x-compass-captcha-method": "enterprise_google",
				},
			});

			setAvailableAuthMethodList(result.response.available_auth_method_list);

			setProfile({
				is_authorized: result.response.is_authorized === 1,
				need_fill_profile: result.response.need_fill_profile === 1,
			})

			setCaptchaPublicKey(result.response.captcha_public_key);

			return result;
		}
	});
}
