import { useQuery } from "@tanstack/react-query";
import { ofetch } from "ofetch";
import { ApiGlobalStartDictionaryData, APIResponse, ApiUserInfoData, CAPTCHA_PROVIDER_DEFAULT } from "./_types.ts";
import {useSetAtom, useAtom} from "jotai";
import {
	authenticationSessionTimeLeftState,
	pageReopenExpiredAtState,
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
import {useEffect, useMemo} from "react";
import dayjs from "dayjs";
import {useApiAuthLogout} from "./auth";
import {NetworkError, ServerError} from "./_index";
import {useLangString} from "../lib/getLangString";
import {useShowToast} from "../lib/Toast";
import {generateDialogId} from "../components/dialog";

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
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const dialogId = useMemo(() => generateDialogId(), []);
	const showToast = useShowToast(dialogId);

	const [sessionTimeLeft, setSessionTimeLeft] = useAtom(authenticationSessionTimeLeftState);
	const [pageReopenTimeLeftAt, setPageReopenTimeLeft] = useAtom(pageReopenExpiredAtState);

	// если время когда страничка открыта истекло - устанавливаем, что сессия тоже истекла
	if (pageReopenTimeLeftAt != 0 && pageReopenTimeLeftAt < dayjs().unix()) {
		setSessionTimeLeft(0)
	}

	// вешаем интервал, чтобы понимать когда истекает время открытия странички
	useEffect(() => {

		const timer = setInterval(() => {

			setPageReopenTimeLeft(dayjs().unix() + 10);
		}, 5000);
		return () => clearInterval(timer);
	}, []);

	// вешаем интервал на проверку времени активности сессии
	useEffect(() => {

		// в этом месте устанавливаем sessionTimeLeft только если тот уже объявлен
		const timer = setInterval(() => {

			setSessionTimeLeft((prevTime) => {
				return prevTime !== null ? prevTime - 1 : prevTime;
			});
		}, 1000);
		return () => clearInterval(timer);
	}, []);

	// получаем флаг активна ли ещё сессия
	const isAuthenticationSessionExpired = useMemo(() => sessionTimeLeft !== undefined && sessionTimeLeft !== null && sessionTimeLeft <= 0, [sessionTimeLeft]);

	// разлогиниваем если сессия уже неактивна
	const apiAuthLogout = useApiAuthLogout();
	useEffect(() => {

		if (isAuthenticationSessionExpired) {

			if (apiAuthLogout.isLoading) {
				return;
			}

			try {
				apiAuthLogout.mutateAsync();
			} catch (error) {
				if (error instanceof NetworkError) {
					showToast(langStringErrorsNetworkError, "warning");
					return;
				}

				if (error instanceof ServerError) {
					showToast(langStringErrorsServerError, "warning");
					return;
				}
			}
		}
	}, [isAuthenticationSessionExpired]);

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
