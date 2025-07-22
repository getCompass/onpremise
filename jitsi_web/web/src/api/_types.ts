export type Lang = "ru" | "en" | "de" | "fr" | "es" | "it";
export const LANG_CODES: Lang[] = ["ru", "en", "de", "fr", "es", "it"];

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

// поддерживаемые версии desktop браузеров
export const SUPPORTED_DESKTOP_CHROME_VERSION = 88;
export const SUPPORTED_DESKTOP_FIREFOX_VERSION = 78;
export const SUPPORTED_DESKTOP_SAFARI_VERSION = 14;
export const SUPPORTED_DESKTOP_EDGE_VERSION = 88;

// поддерживаемые версии mobile iOS браузеров
export const SUPPORTED_MOBILE_IOS_SAFARI_VERSION = 14.6;

// поддерживаемые версии mobile android браузеров
export const SUPPORTED_MOBILE_ANDROID_CHROME_VERSION = 88;
export const SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION = 78;

export const API_JITSI_CONFERENCE_CODE_ERROR_LIMIT = 423;
export const API_JITSI_GET_CONFERENCE_CODE_ERROR_ATTEMPT_JOIN_TO_PRIVATE_CONFERENCE = 1619001;
export const API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_ENDED = 1619002;
export const API_JITSI_GET_CONFERENCE_CODE_ERROR_CONFERENCE_NOT_FOUND = 1619004;

export const CLICK_COUNT_AFTER_SHOW_DOWNLOAD_POPOVER = 1;

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

export type Size = "default" | "small";

export type APIConferenceData = {
	conference_id: string;
	link: string;
	created_at: number;
	is_private: boolean;
	is_lobby: boolean;
};

export type APIAction = {
	type: string;
	data: APIActionProfile;
};

export type APIActionProfile = {
	logged_in: boolean;
	manager_id: number;
};

export type APIResponse<T> = {
	jscss_version: number;
	response: T;
	status: "ok" | "error";
	actions?: APIAction[];
};
