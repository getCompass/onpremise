import UAParser from "ua-parser-js";
// @ts-ignore
export const PUBLIC_PATH_API = `${API_PATH}`;
// @ts-ignore
export const DEEPLINK_URL_SCHEME_ELECTRON = `${ELECTRON_DEEPLINK_URL_SCHEME}`;
// @ts-ignore
export const DEEPLINK_URL_SCHEME_IOS = `${IOS_DEEPLINK_URL_SCHEME}`;
// @ts-ignore
export const DEEPLINK_URL_SCHEME_ANDROID = `${ANDROID_DEEPLINK_URL_SCHEME}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_MAC_OS_INTEL = `${DESKTOP_DOWNLOAD_LINK_MAC_OS_INTEL}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_MAC_OS_ARM = `${DESKTOP_DOWNLOAD_LINK_MAC_OS_ARM}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_10_EXE = `${DESKTOP_DOWNLOAD_LINK_WINDOWS_10_EXE}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_10_MSI = `${DESKTOP_DOWNLOAD_LINK_WINDOWS_10_MSI}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_7_EXE = `${DESKTOP_DOWNLOAD_LINK_WINDOWS_7_EXE}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_7_MSI = `${DESKTOP_DOWNLOAD_LINK_WINDOWS_7_MSI}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_DEB = `${DESKTOP_DOWNLOAD_LINK_LINUX_DEB}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_TAR = `${DESKTOP_DOWNLOAD_LINK_LINUX_TAR}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_RPM = `${DESKTOP_DOWNLOAD_LINK_LINUX_RPM}`;
// @ts-ignore
export const DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_ASTRA = `${DESKTOP_DOWNLOAD_LINK_LINUX_ASTRA}`;
// @ts-ignore
export const MOBILE_COMPASS_DOWNLOAD_LINK_APP_STORE = `${MOBILE_DOWNLOAD_LINK_APP_STORE}`;
// @ts-ignore
export const MOBILE_COMPASS_DOWNLOAD_LINK_GOOGLE_PLAY = `${MOBILE_DOWNLOAD_LINK_GOOGLE_PLAY}`;
// @ts-ignore
export const MOBILE_COMPASS_DOWNLOAD_LINK_APP_GALLERY = `${MOBILE_DOWNLOAD_LINK_APP_GALLERY}`;
// @ts-ignore
export const COMPASS_DOWNLOAD_LINK_ANOTHER_PLATFORMS = `${DOWNLOAD_LINK_ANOTHER_PLATFORMS}`;

export function getPublicPathApi(): string {
	if (PUBLIC_PATH_API.length < 1) {
		return "";
	}

	return "/" + PUBLIC_PATH_API;
}

export function getDeeplinkUrlScheme(): string {
	const parser = new UAParser(navigator.userAgent);

	const device = parser.getDevice();
	const os = parser.getOS()?.name?.toLowerCase() ?? "";
	const isMobile = device.type === "mobile" || device.type === "tablet";
	const isAndroid = os === "android";
	const isMobileHuawei = isMobile && device.vendor === "Huawei";
	const isMobileApple = isMobile && device.vendor === "Apple";
	const isMobileAndroid = isMobile && !isMobileHuawei && isAndroid;

	// у мобилок свои диплинки
	if (isMobile) {
		if ((isMobileHuawei || isMobileAndroid) && DEEPLINK_URL_SCHEME_ANDROID.length > 0) {
			return DEEPLINK_URL_SCHEME_ANDROID;
		}

		if (isMobileApple && DEEPLINK_URL_SCHEME_IOS.length > 0) {
			return DEEPLINK_URL_SCHEME_IOS;
		}

		return "getcompass://";
	}

	// у электрона свои
	if (DEEPLINK_URL_SCHEME_ELECTRON.length < 1) {
		return "compass://";
	}

	return DEEPLINK_URL_SCHEME_ELECTRON;
}
