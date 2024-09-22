export function getCompassDownloadLink(isMobile: boolean, platform: "android" | "ios" | "huawei" | "windows" | "linux_deb" | "linux_tar" | "mac_apple" | "mac_intel"): string {

    if (isMobile) {

        switch (platform) {

            case "android":
                return __MOBILE_DOWNLOAD_LINK_GOOGLE_PLAY__.toString();

            case "ios":
                return __MOBILE_DOWNLOAD_LINK_APP_STORE__.toString();

            case "huawei":
                return __MOBILE_DOWNLOAD_LINK_APP_GALLERY__.toString();
            default:
                return __MOBILE_DOWNLOAD_LINK_APP_STORE__.toString();

        }
    }

    switch (platform) {

        case "windows":
            return __DESKTOP_DOWNLOAD_LINK_WINDOWS__.toString();

        case "linux_deb":
            return __DESKTOP_DOWNLOAD_LINK_LINUX_DEB__.toString();

        case "linux_tar":
            return __DESKTOP_DOWNLOAD_LINK_LINUX_TAR__.toString();

        case "mac_apple":
            return __DESKTOP_DOWNLOAD_LINK_MAC_OS_ARM__.toString();

        case "mac_intel":
            return __DESKTOP_DOWNLOAD_LINK_MAC_OS_INTEL__.toString();

        default:
            return __DESKTOP_DOWNLOAD_LINK_WINDOWS__.toString();
    }
}
