import { atom, useAtomValue } from "jotai";
import {
	APIAuthInfo,
	APIJoinLinkInfo,
	Lang,
	PrepareJoinLinkErrorInfo,
	AuthSsoInfo,
	ApiGlobalStartDictionaryData,
	ApiUserInfoData,
	ClientVersionItem,
} from "./_types.ts";
import { atomWithStorage } from "jotai/utils";
import { atomWithImmer } from "jotai-immer";
import { useMemo } from "react";

export const profileState = atomWithImmer<{
	is_authorized: boolean | null;
	need_fill_profile: boolean;
}>({
	is_authorized: null,
	need_fill_profile: false,
});

export const isLoadedState = atom(false);
export const isLoginCaptchaRenderedState = atom(false);
export const isPasswordChangedState = atom(false);

export const captchaPublicKeyState = atom("");
export const captchaProviderState = atom("");

export const authenticationTokenState = atom("");

export const downloadAppUrlState = atom<string>("");
export const availableAuthMethodListState = atom<string[]>([]);
export const ssoProtocolState = atom("");
export const serverVersionState = atom("");
export const electronVersionState = atom<ClientVersionItem>({
	max_version: "",
	max_version_code: 0,
	min_version: "",
	min_version_code: 0,
});
export const dictionaryDataState = atomWithImmer<ApiGlobalStartDictionaryData>({ auth_sso_start_button_text: "Войти через корп. портал (SSO LDAP)", auth_sso_ldap_description_text: "Для авторизации введите username и пароль от вашей корпоративной учётной записи LDAP:" });

export const userInfoDataState = atomWithImmer<ApiUserInfoData | null>(null);

export const isRegistrationState = atom(false);
export const firstAuthState = atom(false);

export const loadingState = atom(true);

export const langState = atom<Lang>("ru");
export const authenticationTokenTimeLeftState = atom(0);
export const authenticationSessionTimeLeftState = atomWithStorage<number | null>(
	"authentication_web_session_time_left",
	JSON.parse(localStorage.getItem("authentication_web_session_time_left") ?? 'null')
);

// константа времени истечения когда страница считается переоткрытой
// чтобы сбросить сессию в случае если страницу долго не открывали
export const pageReopenExpiredAtState = atomWithStorage<number>(
	"page_reopen_expired_at",
	JSON.parse(localStorage.getItem("page_reopen_expired_at") ?? '0')
);

export const deviceLoginTypeState = atomWithStorage<number>(
	"device_login_type",
	JSON.parse(localStorage.getItem("device_login_type") ?? '0')
);

export const toastConfigState = atomWithImmer<{
	[dialogId: string]: {
		message: string;
		type: string;
		size: string;
		isDialog: boolean;
		isMobile: boolean;
		isVisible: boolean;
	};
}>({});

export const useToastConfig = (dialogId: string) =>
	useAtomValue(useMemo(() => atom((get) => get(toastConfigState)[dialogId]), [dialogId]));

export const activeDialogIdState = atom("");
export const passwordInputState = atom("");
export const confirmPasswordState = atom("");
export const needShowForgotPasswordButtonState = atom(true);
export const isNeedShowCreateProfileDialogAfterSsoRegistrationState = atom(false);
export const isNeedShowCreateProfileDialogAfterLdapRegistrationState = atom(false);

export const joinLinkState = atomWithImmer<APIJoinLinkInfo | null>(null);
export const prepareJoinLinkErrorState = atom<PrepareJoinLinkErrorInfo | null>(null);

export const authState = atomWithStorage<APIAuthInfo | null>(
	"a_state",
	JSON.parse(localStorage.getItem("a_state") ?? '""')
);

export const authInputState = atomWithStorage<string>(
	"auth_input",
	JSON.parse(localStorage.getItem("auth_input") ?? '""')
);

export const nameInputState = atomWithStorage<string>(
	"name_input",
	JSON.parse(localStorage.getItem("name_input") ?? '""')
);

export const authSsoState = atomWithStorage<AuthSsoInfo | null>(
	"auth_sso_state",
	JSON.parse(localStorage.getItem("auth_sso_state") ?? "null")
);
