import { useLangString } from "../../../lib/getLangString.ts";
import { MenuItemGroup } from "../../menu.tsx";
import {
	DESKTOP_PLATFORM_LINUX_ASTRA,
	DESKTOP_PLATFORM_LINUX_DEB,
	DESKTOP_PLATFORM_LINUX_RPM,
	DESKTOP_PLATFORM_LINUX_TAR
} from "../../../api/_types.ts";
import LinuxDebIcon from "../../../icons/LinuxDebIcon.tsx";
import LinuxTarIcon from "../../../icons/LinuxTarIcon.tsx";
import LinuxRpmIcon from "../../../icons/LinuxRpmIcon.tsx";
import LinuxAstraIcon from "../../../icons/LinuxAstraIcon.tsx";
import PlatformMenuItem from "./base/PlatformMenuItem.tsx";
import AnotherPlatformsMenuItem from "./base/AnotherPlatformsMenuItem.tsx";

type LinuxMenuItemsProps = {
	isNeedAnotherPlatformItem: boolean;
}

const LinuxMenuItems = ({ isNeedAnotherPlatformItem }: LinuxMenuItemsProps) => {
	const langStringDownloadCompassDesktopBuildsLinuxDownload = useLangString("download_compass.desktop_builds.linux_download");
	const langStringDownloadCompassDesktopBuildsLinuxAstraDownload = useLangString("download_compass.desktop_builds.linux_astra_download");
	const langStringDownloadCompassDesktopBuildsDebVersion = useLangString("download_compass.desktop_builds.deb_version");
	const langStringDownloadCompassDesktopBuildsTarVersion = useLangString("download_compass.desktop_builds.tar_version");
	const langStringDownloadCompassDesktopBuildsRpmVersion = useLangString("download_compass.desktop_builds.rpm_version");
	const langStringDownloadCompassDesktopBuildsAstraVersion = useLangString("download_compass.desktop_builds.astra_version");

	return (
		<>
			<MenuItemGroup id = "linux_builds">
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_DEB}
					platformText = {langStringDownloadCompassDesktopBuildsLinuxDownload}
					versionText = {langStringDownloadCompassDesktopBuildsDebVersion}
					icon = {<LinuxDebIcon />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_TAR}
					platformText = {langStringDownloadCompassDesktopBuildsLinuxDownload}
					versionText = {langStringDownloadCompassDesktopBuildsTarVersion}
					icon = {<LinuxTarIcon />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_RPM}
					platformText = {langStringDownloadCompassDesktopBuildsLinuxDownload}
					versionText = {langStringDownloadCompassDesktopBuildsRpmVersion}
					icon = {<LinuxRpmIcon />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_LINUX_ASTRA}
					platformText = {langStringDownloadCompassDesktopBuildsLinuxAstraDownload}
					versionText = {langStringDownloadCompassDesktopBuildsAstraVersion}
					icon = {<LinuxAstraIcon />}
				/>
				{isNeedAnotherPlatformItem && <AnotherPlatformsMenuItem />}
			</MenuItemGroup>
		</>
	);
};

export default LinuxMenuItems;