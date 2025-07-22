import { useLangString } from "../../../lib/getLangString.ts";
import { MenuItemGroup } from "../../menu.tsx";
import { Text } from "../../text.tsx";
import {
	DESKTOP_PLATFORM_WINDOWS_10_EXE,
	DESKTOP_PLATFORM_WINDOWS_10_MSI,
	DESKTOP_PLATFORM_WINDOWS_7_EXE,
	DESKTOP_PLATFORM_WINDOWS_7_MSI,
	Size
} from "../../../api/_types.ts";
import WindowsIcon from "../../../icons/WindowsIcon.tsx";
import PlatformMenuItem from "./base/PlatformMenuItem.tsx";
import AnotherPlatformsMenuItem from "./base/AnotherPlatformsMenuItem.tsx";

type WindowsMenuItemsProps = {
	size: Size;
}

const WindowsMenuItems = ({ size }: WindowsMenuItemsProps) => {
	const langStringPageDesktopDownloadCompassBuildsWindowsDownload = useLangString("desktop.download_compass.builds.windows_download");
	const langStringPageDesktopDownloadCompassBuildsExeVersion = useLangString("desktop.download_compass.builds.exe_version");
	const langStringPageDesktopDownloadCompassBuildsMsiVersion = useLangString("desktop.download_compass.builds.msi_version");
	const langStringPageDesktopDownloadCompassBuildsWindows10 = useLangString("desktop.download_compass.builds.windows_10");
	const langStringPageDesktopDownloadCompassBuildsWindows7 = useLangString("desktop.download_compass.builds.windows_7");

	return (
		<>
			<MenuItemGroup id = "windows_builds">
				<Text style = "inter_14_21_500" color = "b4b4b4" padding = "8px 16px 4px 16px">
					{langStringPageDesktopDownloadCompassBuildsWindows10}
				</Text>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_10_EXE}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsWindowsDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsExeVersion}
					icon = {<WindowsIcon size = {size} />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_10_MSI}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsWindowsDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsMsiVersion}
					icon = {<WindowsIcon size = {size} />}
				/>
				<Text style = "inter_14_21_500" color = "b4b4b4" padding = "8px 16px 4px 16px">
					{langStringPageDesktopDownloadCompassBuildsWindows7}
				</Text>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_7_EXE}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsWindowsDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsExeVersion}
					icon = {<WindowsIcon size = {size} />}
				/>
				<PlatformMenuItem
					id = {DESKTOP_PLATFORM_WINDOWS_7_MSI}
					size = {size}
					platformText = {langStringPageDesktopDownloadCompassBuildsWindowsDownload}
					versionText = {langStringPageDesktopDownloadCompassBuildsMsiVersion}
					icon = {<WindowsIcon size = {size} />}
				/>
				<AnotherPlatformsMenuItem size = {size} />
			</MenuItemGroup>
		</>
	);
};

export default WindowsMenuItems;