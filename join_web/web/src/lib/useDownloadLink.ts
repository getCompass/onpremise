import {
	APP_LINK_DESKTOP_LINUX_ASTRA,
	APP_LINK_DESKTOP_LINUX_ASTRA_BY_VERSION,
	APP_LINK_DESKTOP_LINUX_DEB,
	APP_LINK_DESKTOP_LINUX_DEB_BY_VERSION,
	APP_LINK_DESKTOP_LINUX_RPM,
	APP_LINK_DESKTOP_LINUX_RPM_BY_VERSION,
	APP_LINK_DESKTOP_LINUX_TAR,
	APP_LINK_DESKTOP_LINUX_TAR_BY_VERSION,
	APP_LINK_DESKTOP_MAC_OS_ARM,
	APP_LINK_DESKTOP_MAC_OS_ARM_BY_VERSION,
	APP_LINK_DESKTOP_MAC_OS_INTEL,
	APP_LINK_DESKTOP_MAC_OS_INTEL_BY_VERSION,
	APP_LINK_DESKTOP_WINDOWS_10_EXE,
	APP_LINK_DESKTOP_WINDOWS_10_EXE_BY_VERSION,
	APP_LINK_DESKTOP_WINDOWS_10_MSI,
	APP_LINK_DESKTOP_WINDOWS_10_MSI_BY_VERSION,
	APP_LINK_DESKTOP_WINDOWS_7_EXE,
	APP_LINK_DESKTOP_WINDOWS_7_EXE_BY_VERSION,
	APP_LINK_DESKTOP_WINDOWS_7_MSI,
	APP_LINK_DESKTOP_WINDOWS_7_MSI_BY_VERSION,
	APP_LINK_MOBILE_APP_GALLERY,
	APP_LINK_MOBILE_APP_STORE,
	APP_LINK_MOBILE_GOOGLE_PLAY,
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
	ELECTRON_VERSION_22,
	ELECTRON_VERSION_30,
	MOBILE_PLATFORM_ANDROID,
	MOBILE_PLATFORM_HUAWEI,
	MOBILE_PLATFORM_IOS,
} from "../api/_types.ts";
import {useAtomValue} from "jotai";
import {downloadAppUrlState, electronVersionState} from "../api/_stores.ts";

const useDownloadLink = () => {
	const electronVersionMap = useAtomValue(electronVersionState);
	const downloadAppUrl = useAtomValue(downloadAppUrlState);

	const getDownloadLink = (platform: string): string => {
		const maxClientVersion22 = electronVersionMap[ELECTRON_VERSION_22].max_version;
		const maxClientVersion30 = electronVersionMap[ELECTRON_VERSION_30].max_version;

		switch (platform) {

			// ==== macOS ====
			case DESKTOP_PLATFORM_MAC_OS_INTEL:
				if (maxClientVersion22.length > 0) {
					return (
						downloadAppUrl +
						APP_LINK_DESKTOP_MAC_OS_INTEL_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_22).replace(/\$VERSION/g, maxClientVersion22)
					);
				}

				return APP_LINK_DESKTOP_MAC_OS_INTEL;
			case DESKTOP_PLATFORM_MAC_OS_ARM:
				if (maxClientVersion30.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_MAC_OS_ARM_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_30).replace(/\$VERSION/g, maxClientVersion30)
					);
				}

				return APP_LINK_DESKTOP_MAC_OS_ARM;

			// ==== Windows ====
			case DESKTOP_PLATFORM_WINDOWS_10_EXE:
				if (maxClientVersion30.length > 0) {
					return downloadAppUrl + APP_LINK_DESKTOP_WINDOWS_10_EXE_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_30).replace(/\$VERSION/g, maxClientVersion30);
				}

				return APP_LINK_DESKTOP_WINDOWS_10_EXE;
			case DESKTOP_PLATFORM_WINDOWS_10_MSI:
				if (maxClientVersion30.length > 0) {
					return downloadAppUrl + APP_LINK_DESKTOP_WINDOWS_10_MSI_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_30).replace(/\$VERSION/g, maxClientVersion30);
				}

				return APP_LINK_DESKTOP_WINDOWS_10_MSI;
			case DESKTOP_PLATFORM_WINDOWS_7_EXE:
				if (maxClientVersion22.length > 0) {
					return downloadAppUrl + APP_LINK_DESKTOP_WINDOWS_7_EXE_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_22).replace(/\$VERSION/g, maxClientVersion22);
				}

				return APP_LINK_DESKTOP_WINDOWS_7_EXE;
			case DESKTOP_PLATFORM_WINDOWS_7_MSI:
				if (maxClientVersion22.length > 0) {
					return downloadAppUrl + APP_LINK_DESKTOP_WINDOWS_7_MSI_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_22).replace(/\$VERSION/g, maxClientVersion22);
				}

				return APP_LINK_DESKTOP_WINDOWS_7_MSI;


			// ==== Linux ====
			case DESKTOP_PLATFORM_LINUX_DEB:
				if (maxClientVersion22.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_LINUX_DEB_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_22).replace(/\$VERSION/g, maxClientVersion22)
					);
				}

				return APP_LINK_DESKTOP_LINUX_DEB;
			case DESKTOP_PLATFORM_LINUX_TAR:
				if (maxClientVersion22.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_LINUX_TAR_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_22).replace(/\$VERSION/g, maxClientVersion22)
					);
				}

				return APP_LINK_DESKTOP_LINUX_TAR;
			case DESKTOP_PLATFORM_LINUX_RPM:
				if (maxClientVersion22.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_LINUX_RPM_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_22).replace(/\$VERSION/g, maxClientVersion22)
					);
				}

				return APP_LINK_DESKTOP_LINUX_RPM;
			case DESKTOP_PLATFORM_LINUX_ASTRA:
				if (maxClientVersion22.length > 0) {
					return (
						downloadAppUrl + APP_LINK_DESKTOP_LINUX_ASTRA_BY_VERSION.replace(/\$ELECTRON_VERSION/g, ELECTRON_VERSION_22).replace(/\$VERSION/g, maxClientVersion22)
					);
				}

				return APP_LINK_DESKTOP_LINUX_ASTRA;

			// ==== iOS ====
			case MOBILE_PLATFORM_IOS:
				return APP_LINK_MOBILE_APP_STORE;

			// ==== Android ====
			case MOBILE_PLATFORM_ANDROID:
				return APP_LINK_MOBILE_GOOGLE_PLAY;
			case MOBILE_PLATFORM_HUAWEI:
				return APP_LINK_MOBILE_APP_GALLERY;

			default:
				return "/install";
		}
	};
	return {getDownloadLink};
};

export default useDownloadLink;
