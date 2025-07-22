import { useLangString } from "../../../lib/getLangString.ts";
import { MenuItemGroup } from "../../menu.tsx";
import { DESKTOP_PLATFORM_MAC_OS_ARM, DESKTOP_PLATFORM_MAC_OS_INTEL, Size } from "../../../api/_types.ts";
import MacOsIcon from "../../../icons/MacOsIcon.tsx";
import PlatformMenuItem from "./base/PlatformMenuItem.tsx";
import AnotherPlatformsMenuItem from "./base/AnotherPlatformsMenuItem.tsx";

type MacOsMenuItemsProps = {
	size: Size;
}

const MacOsMenuItems = ({ size }: MacOsMenuItemsProps) => {
	const langStringPageDesktopDownloadCompassBuildsMacosDownload = useLangString("desktop.download_compass.builds.macos_download");
	const langStringPageDesktopDownloadCompassBuildsIntelVersion = useLangString("desktop.download_compass.builds.intel_version");
	const langStringPageDesktopDownloadCompassBuildsArmVersion = useLangString("desktop.download_compass.builds.arm_version");

	return (
		<>
			<MenuItemGroup id = "macos_builds">
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_MAC_OS_INTEL}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsMacosDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsIntelVersion}
					icon = {<MacOsIcon size = {size} />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_MAC_OS_ARM}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsMacosDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsArmVersion}
					icon = {<MacOsIcon size = {size} />}
				/>
				<AnotherPlatformsMenuItem size = {size} />
			</MenuItemGroup>
		</>
	);
};

export default MacOsMenuItems;