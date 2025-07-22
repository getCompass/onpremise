import { useLangString } from "../../../lib/getLangString.ts";
import { MenuItemGroup } from "../../menu.tsx";
import { DESKTOP_PLATFORM_MAC_OS_ARM, DESKTOP_PLATFORM_MAC_OS_INTEL } from "../../../api/_types.ts";
import MacOsIcon from "../../../icons/MacOsIcon.tsx";
import PlatformMenuItem from "./base/PlatformMenuItem.tsx";
import AnotherPlatformsMenuItem from "./base/AnotherPlatformsMenuItem.tsx";

type MacOsMenuItemsProps = {
	isNeedAnotherPlatformItem: boolean;
}

const MacOsMenuItems = ({ isNeedAnotherPlatformItem }: MacOsMenuItemsProps) => {
	const langStringDownloadCompassDesktopBuildsMacosDownload = useLangString("download_compass.desktop_builds.macos_download");
	const langStringDownloadCompassDesktopBuildsIntelVersion = useLangString("download_compass.desktop_builds.intel_version");
	const langStringDownloadCompassDesktopBuildsArmVersion = useLangString("download_compass.desktop_builds.arm_version");

	return (
		<>
			<MenuItemGroup id = "macos_builds">
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_MAC_OS_INTEL}
					platformText = {langStringDownloadCompassDesktopBuildsMacosDownload}
					versionText = {langStringDownloadCompassDesktopBuildsIntelVersion}
					icon = {<MacOsIcon />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_MAC_OS_ARM}
					platformText = {langStringDownloadCompassDesktopBuildsMacosDownload}
					versionText = {langStringDownloadCompassDesktopBuildsArmVersion}
					icon = {<MacOsIcon />}
				/>
				{isNeedAnotherPlatformItem && <AnotherPlatformsMenuItem />}
			</MenuItemGroup>
		</>
	);
};

export default MacOsMenuItems;