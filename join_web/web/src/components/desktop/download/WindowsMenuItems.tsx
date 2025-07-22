import { useLangString } from "../../../lib/getLangString.ts";
import { MenuItemGroup } from "../../menu.tsx";
import { Text } from "../../text.tsx";
import {
	DESKTOP_PLATFORM_WINDOWS_10_EXE,
	DESKTOP_PLATFORM_WINDOWS_10_MSI,
	DESKTOP_PLATFORM_WINDOWS_7_EXE, DESKTOP_PLATFORM_WINDOWS_7_MSI
} from "../../../api/_types.ts";
import WindowsIcon from "../../../icons/WindowsIcon.tsx";
import PlatformMenuItem from "./base/PlatformMenuItem.tsx";
import AnotherPlatformsMenuItem from "./base/AnotherPlatformsMenuItem.tsx";

type WindowsMenuItemsProps = {
	isNeedAnotherPlatformItem: boolean;
}

const WindowsMenuItems = ({ isNeedAnotherPlatformItem }: WindowsMenuItemsProps) => {
	const langStringDownloadCompassDesktopBuildsWindowsDownload = useLangString("download_compass.desktop_builds.windows_download");
	const langStringDownloadCompassDesktopBuildsExeVersion = useLangString("download_compass.desktop_builds.exe_version");
	const langStringDownloadCompassDesktopBuildsMsiVersion = useLangString("download_compass.desktop_builds.msi_version");
	const langStringDownloadCompassDesktopBuildsWindows10 = useLangString("download_compass.desktop_builds.windows_10");
	const langStringDownloadCompassDesktopBuildsWindows7 = useLangString("download_compass.desktop_builds.windows_7");

	return (
		<>
			<MenuItemGroup id = "windows_builds">
				<Text style = "inter_14_21_500" color = "b4b4b4" padding = "8px 16px 4px 16px">
					{langStringDownloadCompassDesktopBuildsWindows10}
				</Text>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_10_EXE}
					platformText = {langStringDownloadCompassDesktopBuildsWindowsDownload}
					versionText = {langStringDownloadCompassDesktopBuildsExeVersion}
					icon = {<WindowsIcon />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_10_MSI}
					platformText = {langStringDownloadCompassDesktopBuildsWindowsDownload}
					versionText = {langStringDownloadCompassDesktopBuildsMsiVersion}
					icon = {<WindowsIcon />}
				/>
				<Text style = "inter_14_21_500" color = "b4b4b4" padding = "8px 16px 4px 16px">
					{langStringDownloadCompassDesktopBuildsWindows7}
				</Text>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_7_EXE}
					platformText = {langStringDownloadCompassDesktopBuildsWindowsDownload}
					versionText = {langStringDownloadCompassDesktopBuildsExeVersion}
					icon = {<WindowsIcon />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_7_MSI}
					platformText = {langStringDownloadCompassDesktopBuildsWindowsDownload}
					versionText = {langStringDownloadCompassDesktopBuildsMsiVersion}
					icon = {<WindowsIcon />}
				/>
				{isNeedAnotherPlatformItem && <AnotherPlatformsMenuItem />}
			</MenuItemGroup>
		</>
	);
};

export default WindowsMenuItems;