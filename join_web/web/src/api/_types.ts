export type Lang = "ru" | "en" | "de" | "fr" | "es" | "it";
export const LANG_CODES: Lang[] = ["ru", "en", "de", "fr", "es", "it"];

export const AUTH_MAIL_SCENARIO_SHORT = "short_confirm";
export const AUTH_MAIL_SCENARIO_FULL = "full_confirm";

export const CAPTCHA_PROVIDER_ENTERPRISE_GOOGLE = "enterprise_google";
export const CAPTCHA_PROVIDER_YANDEX = "yandex_cloud";
export const CAPTCHA_PROVIDER_DEFAULT = CAPTCHA_PROVIDER_ENTERPRISE_GOOGLE;

export const APP_LINK_DESKTOP_MAC_OS_INTEL = "https://getcompass.ru/on-premise/app/macos-intel";
export const APP_LINK_DESKTOP_MAC_OS_INTEL_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_mac_x64.dmg";
export const APP_LINK_DESKTOP_MAC_OS_ARM =
	"https://getcompass.ru/on-premise/app/macos-silicon";
export const APP_LINK_DESKTOP_MAC_OS_ARM_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_mac_arm64.dmg";

export const APP_LINK_DESKTOP_WINDOWS_10_EXE = "https://getcompass.ru/on-premise/app/windows-10-exe";
export const APP_LINK_DESKTOP_WINDOWS_10_EXE_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_win.exe";
export const APP_LINK_DESKTOP_WINDOWS_10_MSI = "https://getcompass.ru/on-premise/app/windows-10-msi";
export const APP_LINK_DESKTOP_WINDOWS_10_MSI_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_win_x64.msi";
export const APP_LINK_DESKTOP_WINDOWS_7_EXE = "https://getcompass.ru/on-premise/app/windows-7-exe";
export const APP_LINK_DESKTOP_WINDOWS_7_EXE_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_win.exe";
export const APP_LINK_DESKTOP_WINDOWS_7_MSI = "https://getcompass.ru/on-premise/app/windows-7-msi";
export const APP_LINK_DESKTOP_WINDOWS_7_MSI_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_win_x64.msi";

export const APP_LINK_DESKTOP_LINUX_DEB = "https://getcompass.ru/on-premise/app/linux-deb";
export const APP_LINK_DESKTOP_LINUX_DEB_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_linux_amd64.deb";
export const APP_LINK_DESKTOP_LINUX_TAR = "https://getcompass.ru/on-premise/app/linux-tar";
export const APP_LINK_DESKTOP_LINUX_TAR_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_linux.tar";
export const APP_LINK_DESKTOP_LINUX_RPM = "https://getcompass.ru/on-premise/app/linux-rpm";
export const APP_LINK_DESKTOP_LINUX_RPM_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_linux_x86_64.rpm";
export const APP_LINK_DESKTOP_LINUX_ASTRA = "https://getcompass.ru/on-premise/app/linux-astra";
export const APP_LINK_DESKTOP_LINUX_ASTRA_BY_VERSION = "$ELECTRON_VERSION/$VERSION/compass_linux_amd64.deb";

export const APP_LINK_MOBILE_APP_STORE = "https://apps.apple.com/app/id6469516890";

export const APP_LINK_MOBILE_GOOGLE_PLAY =
	"https://play.google.com/store/apps/details?id=com.getcompass.android.enterprise";

export const APP_LINK_MOBILE_APP_GALLERY = "https://appgallery.huawei.com/app/C109414583";

export const DESKTOP_PLATFORM_MAC_OS_INTEL = "mac_os_intel";
export const DESKTOP_PLATFORM_MAC_OS_ARM = "mac_os_arm";
export const DESKTOP_PLATFORM_WINDOWS_10_EXE = "windows_10_exe";
export const DESKTOP_PLATFORM_WINDOWS_10_MSI = "windows_10_msi";
export const DESKTOP_PLATFORM_WINDOWS_7_EXE = "windows_7_exe";
export const DESKTOP_PLATFORM_WINDOWS_7_MSI = "windows_7_msi";
export const DESKTOP_PLATFORM_LINUX_DEB = "linux_deb";
export const DESKTOP_PLATFORM_LINUX_TAR = "linux_tar";
export const DESKTOP_PLATFORM_LINUX_RPM = "linux_rpm";
export const DESKTOP_PLATFORM_LINUX_ASTRA = "linux_astra";
export const MOBILE_PLATFORM_IOS = "ios";
export const MOBILE_PLATFORM_ANDROID = "android";
export const MOBILE_PLATFORM_HUAWEI = "huawei";

export const SSO_PROTOCOL_OIDC = "oidc";
export const SSO_PROTOCOL_LDAP = "ldap";

export type AUTH_MAIL_SCENARIO_SHORT = "short_confirm";
export type AUTH_MAIL_SCENARIO_FULL = "full_confirm";

export const AUTH_MAIL_STAGE_ENTERING_PASSWORD = "entering_password";
export const AUTH_MAIL_STAGE_ENTERING_CODE = "entering_code";
export const AUTH_MAIL_STAGE_FINISHED = "finished";

export type AUTH_MAIL_STAGE_ENTERING_PASSWORD = "entering_password";
export type AUTH_MAIL_STAGE_ENTERING_CODE = "entering_code";
export type AUTH_MAIL_STAGE_FINISHED = "finished";

export const JOIN_LINK_ROLE_GUEST = "guest";

const LANG_MAP: Record<Lang, string> = {
	ru: "Русский",
	en: "English",
	de: "Deutsch",
	fr: "Français",
	es: "Español",
	it: "Italiano",
};

export function getLangFullName(langCode: Lang): string {
	return LANG_MAP[langCode] || "";
}

// словарь с текстами, возвращаемый в api-методе /global/start/
export type ApiGlobalStartDictionaryData = {
	auth_sso_start_button_text: string;
	auth_sso_ldap_description_text: string;
};

// информация о пользователе, возвращаемая в различных api-методах
export type ApiUserInfoData = {
	user_id: number;
	full_name: string;
};

export const isValidEmail = (email: string): boolean => {
	// регулярное выражение для проверки электронной почты
	// это выражение проверяет, что email начинается с английских букв, цифр, точек, подчеркиваний или дефисов
	// затем следует символ "@", после которого идет имя домена, состоящее из английских букв, дефисов или точек
	// домен верхнего уровня должен состоять из английских букв, тире или цифр(привет punycode) и быть длиной от 2 до 30 символов
	const regex = /^[a-zA-ZА-Яа-яЁё0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]{2,30}$/;
	return regex.test(email);
};

export const LIMIT_ERROR_CODE = 423;
export const NEED_FINISH_SPACE_LEAVING_BEFORE_JOIN = 1708011;
export const INCORRECT_LINK_ERROR_CODE = 1711001;
export const INACTIVE_LINK_ERROR_CODE = 1711002;
export const ALREADY_MEMBER_ERROR_CODE = 1711006;

export const APIAuthTypeRegisterByPhoneNumber = 1;
export const APIAuthTypeLoginByPhoneNumber = 2;
export const APIAuthTypeRegisterByMail = 3;
export const APIAuthTypeLoginByMail = 4;
export const APIAuthTypeResetPasswordByMail = 5;

export type APIAuthTypeRegisterByPhoneNumber = 1;
export type APIAuthTypeLoginByPhoneNumber = 2;
export type APIAuthTypeRegisterByMail = 3;
export type APIAuthTypeLoginByMail = 4;
export type APIAuthTypeResetPasswordByMail = 5;

export type APIAuthInfoDataTypeRegisterLoginByPhoneNumber = {
	next_resend: number;
	available_attempts: number;
	expire_at: number;
	phone_mask: string;
	phone_number: string;
};
export type APIAuthInfoDataTypeRegisterLoginResetPasswordByMail = {
	next_resend: number;
	password_available_attempts: number;
	code_available_attempts: number;
	expire_at: number;
	mail: string;
	scenario: AUTH_MAIL_SCENARIO_SHORT | AUTH_MAIL_SCENARIO_FULL;
	stage: AUTH_MAIL_STAGE_ENTERING_PASSWORD | AUTH_MAIL_STAGE_ENTERING_CODE | AUTH_MAIL_STAGE_FINISHED;
};

export type APIAuthInfoType =
	| APIAuthTypeRegisterByPhoneNumber
	| APIAuthTypeLoginByPhoneNumber
	| APIAuthTypeRegisterByMail
	| APIAuthTypeLoginByMail
	| APIAuthTypeResetPasswordByMail;

export type APIAuthInfoData =
	| APIAuthInfoDataTypeRegisterLoginByPhoneNumber
	| APIAuthInfoDataTypeRegisterLoginResetPasswordByMail;

export type APIAuthInfo = {
	type: APIAuthInfoType;
	auth_key: string;
	data: APIAuthInfoData;
};

export type PrepareJoinLinkErrorInfo = {
	error_code: number;
	data?: PrepareJoinLinkErrorAlreadyMemberData | PrepareJoinLinkErrorLimitData;
};

export type PrepareJoinLinkErrorLimitData = {
	expires_at: number;
};

export type PrepareJoinLinkErrorAlreadyMemberData = {
	company_id: number;
	inviter_user_id: number;
	inviter_full_name: string;
	is_postmoderation: number;
	is_waiting_for_postmoderation: number;
	role: number;
	was_member_before: number;
	join_link_uniq: string;
};

export type APIJoinLinkInfo = {
	join_link_uniq: string;
	company_id: number;
	company_name: string;
	inviter_user_id: number;
	inviter_full_name: string;
	entry_option: number;
	role: "member" | "guest";
	is_postmoderation: number;
	is_waiting_for_postmoderation: number;
	was_member_before: number;
	is_exit_status_in_progress: number;
};

export type AuthSsoInfo = {
	state: "none" | "in_progress";
	data: {
		link: string;
		sso_auth_token: string;
		signature: string;
	};
};

export type LdapAuthCredentials = {
	username: string
	password: string
}

export const ELECTRON_VERSION_22 = "22";
export const ELECTRON_VERSION_30 = "30";

export type ClientVersionMap = Record<string, ClientVersionItem>;

export type ClientVersionItem = {
	max_version: string;
	max_version_code: number;
	min_version: string;
	min_version_code: number;
};

export type APIResponse<T> = {
	server_time: number;
	response: T;
	status: "ok" | "error";
};

export const ONPREMISE_SMS_LOGIN_TYPE = 100;
export const ONPREMISE_EMAIL_LOGIN_TYPE = 101;
export const ONPREMISE_LDAP_LOGIN_TYPE = 102;
export const ONPREMISE_SSO_LOGIN_TYPE = 103;

export const API_COMMAND_TYPE_NEED_CONFIRM_LDAP_MAIL = "need_confirm_ldap_mail";

export const API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CURRENT_MAIL = "confirm_current_mail";
export const API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL = "confirm_changing_mail";
export const API_COMMAND_SCENARIO_DATA_STAGE_ENTER_NEW_MAIL = "enter_new_mail";
export const API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL = "confirm_new_mail";
export const API_COMMAND_SCENARIO_DATA_STAGE_GET_LDAP_AUTH_TOKEN = "get_ldap_auth_token";

export type APICommandScenarioDefaultConfirm = "default_confirm";

export type APICommandScenarioDataStageConfirmCurrentMail = "confirm_current_mail";
export type APICommandScenarioDataStageConfirmChangingMail = "confirm_changing_mail";
export type APICommandScenarioDataStageEnterNewMail = "enter_new_mail";
export type APICommandScenarioDataStageConfirmNewMail = "confirm_new_mail";
export type APICommandScenarioDataStageGetLdapAuthToken = "get_ldap_auth_token";

export type APICommandData = {
	mail_confirm_story_key: string,
	scenario: APICommandScenarioDefaultConfirm,
	scenario_data: {
		code_available_attempts: number
		expires_at: number
		is_manual_add_enabled: number
		mail_mask: string
		next_resend_at: number
		stage: APICommandScenarioDataStageConfirmCurrentMail |
			APICommandScenarioDataStageConfirmChangingMail |
			APICommandScenarioDataStageEnterNewMail |
			APICommandScenarioDataStageConfirmNewMail |
			APICommandScenarioDataStageGetLdapAuthToken
	},
};