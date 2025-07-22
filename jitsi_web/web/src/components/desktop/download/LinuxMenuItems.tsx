import { useLangString } from "../../../lib/getLangString.ts";
import { MenuItemGroup } from "../../menu.tsx";
import {
	DESKTOP_PLATFORM_LINUX_ASTRA,
	DESKTOP_PLATFORM_LINUX_DEB,
	DESKTOP_PLATFORM_LINUX_RPM,
	DESKTOP_PLATFORM_LINUX_TAR,
	Size
} from "../../../api/_types.ts";
import LinuxDebIcon from "../../../icons/LinuxDebIcon.tsx";
import LinuxTarIcon from "../../../icons/LinuxTarIcon.tsx";
import LinuxRpmIcon from "../../../icons/LinuxRpmIcon.tsx";
import LinuxAstraIcon from "../../../icons/LinuxAstraIcon.tsx";
import PlatformMenuItem from "./base/PlatformMenuItem.tsx";
import AnotherPlatformsMenuItem from "./base/AnotherPlatformsMenuItem.tsx";

type LinuxMenuItemsProps = {
	size: Size;
}

const LinuxMenuItems = ({ size }: LinuxMenuItemsProps) => {
	const langStringPageDesktopDownloadCompassBuildsLinuxDownload = useLangString("desktop.download_compass.builds.linux_download");
	const langStringPageDesktopDownloadCompassBuildsLinuxAstraDownload = useLangString("desktop.download_compass.builds.linux_astra_download");
	const langStringPageDesktopDownloadCompassBuildsDebVersion = useLangString("desktop.download_compass.builds.deb_version");
	const langStringPageDesktopDownloadCompassBuildsTarVersion = useLangString("desktop.download_compass.builds.tar_version");
	const langStringPageDesktopDownloadCompassBuildsRpmVersion = useLangString("desktop.download_compass.builds.rpm_version");
	const langStringPageDesktopDownloadCompassBuildsAstraVersion = useLangString("desktop.download_compass.builds.astra_version");

	return (
		<>
			<MenuItemGroup id = "linux_builds">
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_DEB}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsLinuxDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsDebVersion}
					icon = {<LinuxDebIcon size = {size} />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_TAR}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsLinuxDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsTarVersion}
					icon = {<LinuxTarIcon size = {size} />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_RPM}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsLinuxDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsRpmVersion}
					icon = {<LinuxRpmIcon size = {size} />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_ASTRA}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsLinuxAstraDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsAstraVersion}
					icon = {<LinuxAstraIcon size = {size} />}
				/>
				<AnotherPlatformsMenuItem size = {size} />
			</MenuItemGroup>
		</>
	);
};

export default LinuxMenuItems;