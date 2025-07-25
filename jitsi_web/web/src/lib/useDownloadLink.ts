import {
	DESKTOP_PLATFORM_LINUX_ASTRA,
	DESKTOP_PLATFORM_LINUX_DEB,
	DESKTOP_PLATFORM_LINUX_RPM,
	DESKTOP_PLATFORM_LINUX_TAR,
	DESKTOP_PLATFORM_MAC_OS_ARM,
	DESKTOP_PLATFORM_MAC_OS_INTEL,
	DESKTOP_PLATFORM_WINDOWS_10_EXE,
	DESKTOP_PLATFORM_WINDOWS_10_MSI,
	DESKTOP_PLATFORM_WINDOWS_7_EXE,
	DESKTOP_PLATFORM_WINDOWS_7_MSI,
	MOBILE_PLATFORM_ANDROID,
	MOBILE_PLATFORM_HUAWEI,
	MOBILE_PLATFORM_IOS,
} from "../api/_types.ts";
import {
	COMPASS_DOWNLOAD_LINK_ANOTHER_PLATFORMS,
	DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_ASTRA,
	DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_DEB,
	DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_RPM,
	DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_TAR,
	DESKTOP_COMPASS_DOWNLOAD_LINK_MAC_OS_ARM,
	DESKTOP_COMPASS_DOWNLOAD_LINK_MAC_OS_INTEL,
	DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_10_EXE,
	DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_10_MSI,
	DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_7_EXE,
	DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_7_MSI,
	MOBILE_COMPASS_DOWNLOAD_LINK_APP_GALLERY,
	MOBILE_COMPASS_DOWNLOAD_LINK_APP_STORE,
	MOBILE_COMPASS_DOWNLOAD_LINK_GOOGLE_PLAY,
} from "../private/custom.ts";

const useDownloadLink = () => {

	const getDownloadLink = (platform: string): string => {

		switch (platform) {

			// ==== macOS ====
			case DESKTOP_PLATFORM_MAC_OS_INTEL:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_MAC_OS_INTEL;

			case DESKTOP_PLATFORM_MAC_OS_ARM:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_MAC_OS_ARM;

			// ==== Windows ====
			case DESKTOP_PLATFORM_WINDOWS_10_EXE:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_10_EXE;
			case DESKTOP_PLATFORM_WINDOWS_10_MSI:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_10_MSI;
			case DESKTOP_PLATFORM_WINDOWS_7_EXE:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_7_EXE;
			case DESKTOP_PLATFORM_WINDOWS_7_MSI:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_WINDOWS_7_MSI;


			// ==== Linux ====
			case DESKTOP_PLATFORM_LINUX_DEB:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_DEB;
			case DESKTOP_PLATFORM_LINUX_TAR:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_TAR;
			case DESKTOP_PLATFORM_LINUX_RPM:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_RPM;
			case DESKTOP_PLATFORM_LINUX_ASTRA:
				return DESKTOP_COMPASS_DOWNLOAD_LINK_LINUX_ASTRA;

			// ==== iOS ====
			case MOBILE_PLATFORM_IOS:
				return MOBILE_COMPASS_DOWNLOAD_LINK_APP_STORE;

			// ==== Android ====
			case MOBILE_PLATFORM_ANDROID:
				return MOBILE_COMPASS_DOWNLOAD_LINK_GOOGLE_PLAY;
			case MOBILE_PLATFORM_HUAWEI:
				return MOBILE_COMPASS_DOWNLOAD_LINK_APP_GALLERY;

			default:
				return COMPASS_DOWNLOAD_LINK_ANOTHER_PLATFORMS;
		}
	};
	return {getDownloadLink};
};

export default useDownloadLink;
