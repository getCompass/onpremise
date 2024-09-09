import {
	APP_LINK_DESKTOP_LINUX_DEB,
	APP_LINK_DESKTOP_LINUX_DEB_BY_VERSION,
	APP_LINK_DESKTOP_LINUX_TAR,
	APP_LINK_DESKTOP_LINUX_TAR_BY_VERSION,
	APP_LINK_DESKTOP_MAC_OS_ARM,
	APP_LINK_DESKTOP_MAC_OS_ARM_BY_VERSION,
	APP_LINK_DESKTOP_MAC_OS_INTEL,
	APP_LINK_DESKTOP_MAC_OS_INTEL_BY_VERSION,
	APP_LINK_DESKTOP_WINDOWS,
	APP_LINK_DESKTOP_WINDOWS_BY_VERSION,
	APP_LINK_MOBILE_APP_GALLERY,
	APP_LINK_MOBILE_APP_STORE,
	APP_LINK_MOBILE_GOOGLE_PLAY,
	DESKTOP_PLATFORM_LINUX_DEB,
	DESKTOP_PLATFORM_LINUX_TAR,
	DESKTOP_PLATFORM_MAC_OS_ARM,
	DESKTOP_PLATFORM_MAC_OS_INTEL,
	DESKTOP_PLATFORM_WINDOWS,
	MOBILE_PLATFORM_ANDROID,
	MOBILE_PLATFORM_HUAWEI,
	MOBILE_PLATFORM_IOS,
} from "../api/_types.ts";
import { useAtomValue } from "jotai";
import { downloadAppUrlState, electronVersionState } from "../api/_stores.ts";

const useDownloadLink = () => {
	const electronVersion = useAtomValue(electronVersionState);
	const downloadAppUrl = useAtomValue(downloadAppUrlState);

	// проверка на наличие метода аутентификации по номеру телефона
	const getDownloadLink = (platform: string): string => {
		const maxClientVersion = electronVersion.max_version;

		switch (platform) {
			case DESKTOP_PLATFORM_MAC_OS_INTEL:
				if (maxClientVersion.length > 0) {
					return (
						downloadAppUrl +
						APP_LINK_DESKTOP_MAC_OS_INTEL_BY_VERSION.replace(/\$VERSION/g, maxClientVersion)
					);
				}

				return downloadAppUrl + APP_LINK_DESKTOP_MAC_OS_INTEL;
			case DESKTOP_PLATFORM_MAC_OS_ARM:
				if (maxClientVersion.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_MAC_OS_ARM_BY_VERSION.replace(/\$VERSION/g, maxClientVersion)
					);
				}

				return downloadAppUrl + APP_LINK_DESKTOP_MAC_OS_ARM;
			case DESKTOP_PLATFORM_WINDOWS:
				if (maxClientVersion.length > 0) {
					return downloadAppUrl + APP_LINK_DESKTOP_WINDOWS_BY_VERSION.replace(/\$VERSION/g, maxClientVersion);
				}

				return downloadAppUrl + APP_LINK_DESKTOP_WINDOWS;
			case DESKTOP_PLATFORM_LINUX_DEB:
				if (maxClientVersion.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_LINUX_DEB_BY_VERSION.replace(/\$VERSION/g, maxClientVersion)
					);
				}

				return downloadAppUrl + APP_LINK_DESKTOP_LINUX_DEB;
			case DESKTOP_PLATFORM_LINUX_TAR:
				if (maxClientVersion.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_LINUX_TAR_BY_VERSION.replace(/\$VERSION/g, maxClientVersion)
					);
				}

				return downloadAppUrl + APP_LINK_DESKTOP_LINUX_TAR;
			case MOBILE_PLATFORM_IOS:
				return APP_LINK_MOBILE_APP_STORE;
			case MOBILE_PLATFORM_ANDROID:
				return APP_LINK_MOBILE_GOOGLE_PLAY;
			case MOBILE_PLATFORM_HUAWEI:
				return APP_LINK_MOBILE_APP_GALLERY;
			default:
				return "";
		}
	};
	return { getDownloadLink };
};

export default useDownloadLink;
