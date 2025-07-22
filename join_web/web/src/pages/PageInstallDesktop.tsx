import { Box, Center, HStack, styled, VStack } from "../../styled-system/jsx";
import { Icon } from "../components/Icon.tsx";
import CompassLogo32 from "../img/install_page/desktop/CompassLogo32.svg";
import CompassLogo80 from "../img/install_page/desktop/CompassLogo80.svg";
import CompassFooterLogo24 from "../img/install_page/desktop/CompassFooterLogo24.svg";
import { Text } from "../components/text.tsx";
import { useLangString } from "../lib/getLangString.ts";
import IconDownloadButtonApple from "../img/install_page/desktop/DownloadButtonApple.svg";
import IconDownloadButtonWin from "../img/install_page/desktop/DownloadButtonWin.svg";
import IconDownloadButtonLinux from "../img/install_page/desktop/DownloadButtonLinux.svg";
import IconDownloadButtonAppStore from "../img/install_page/desktop/DownloadButtonAppStore.svg";
import IconDownloadButtonGooglePlay from "../img/install_page/desktop/DownloadButtonGooglePlay.svg";
import IconDownloadButtonAppGallery from "../img/install_page/desktop/DownloadButtonAppGallery.svg";
import Avatar1 from "../img/install_page/desktop/Avatar_1.png";
import Avatar2 from "../img/install_page/desktop/Avatar_2.png";
import Avatar3 from "../img/install_page/desktop/Avatar_3.png";
import TelegramIcon24 from "../img/install_page/desktop/TelegramIcon24.svg";
import MailIcon26 from "../img/install_page/desktop/MailIcon26.svg";
import { useMemo } from "react";
import dayjs from "dayjs";
import LandingBackground from "../components/desktop/LandingBackground.tsx";
import OnpremiseInstallDownloadMenuDesktop from "../components/desktop/OnpremiseInstallDownloadMenuDesktop.tsx";
import {
	DESKTOP_PLATFORM_LINUX_DEB,
	DESKTOP_PLATFORM_MAC_OS_INTEL,
	DESKTOP_PLATFORM_WINDOWS_7_EXE,
	MOBILE_PLATFORM_ANDROID,
	MOBILE_PLATFORM_HUAWEI,
	MOBILE_PLATFORM_IOS,
} from "../api/_types.ts";
import useDownloadLink from "../lib/useDownloadLink.ts";
import MacOsMenuItems from "../components/desktop/download/MacOsMenuItems.tsx";
import WindowsMenuItems from "../components/desktop/download/WindowsMenuItems.tsx";
import LinuxMenuItems from "../components/desktop/download/LinuxMenuItems.tsx";

const Footer = () => {
	const currentYear = useMemo(() => dayjs.unix(dayjs().unix()).format("YYYY"), []);

	return (
		<HStack w = "100%" mt = "80px" p = "32px" bgColor = "393a4d" justify = "space-between" userSelect = "none">
			<HStack
				gap = "10px"
				onClick = {() => (window.location.href = "https://getcompass.ru/on-premise")}
				cursor = "pointer"
			>
				<Icon width = "24px" height = "24px" avatar = {CompassFooterLogo24} />
				<Text style = "inter_14_17_400" letterSpacing = "-0.5px" color = "248248248.03">
					{"COMPASS © $CURRENT_YEAR".replace("$CURRENT_YEAR", currentYear)}
				</Text>
			</HStack>

			<Text style = "inter_14_17_400" color = "248248248.03">
				<styled.span>Сделано для вас&nbsp;</styled.span>
				<styled.span color = "#c7544f">❤</styled.span>
				️
			</Text>
		</HStack>
	);
};

type DownloadButtonProps = {
	icon: string;
	desc: string;
	platform: string;
	secondPlatform?: string;
	ThirdPlatform?: string;
	downloadLink: string;
	menuItems?: JSX.Element;
};

const DownloadButton = ({
	icon,
	desc,
	platform,
	secondPlatform,
	ThirdPlatform,
	downloadLink,
	menuItems,
}: DownloadButtonProps) => {
	if (secondPlatform !== undefined && menuItems !== undefined) {
		return (
			<OnpremiseInstallDownloadMenuDesktop
				triggerEl = {
					<Center
						w = "100%"
						h = "206px"
						bgColor = "rgba(255, 255, 255, 0.4)"
						cursor = "pointer"
						flexDirection = "column"
						rounded = "14px"
						_hover = {{
							bgColor: "rgba(255, 255, 255, 0.8)",
						}}
					>
						<Icon width = "76px" height = "76px" avatar = {icon} />
						<Text mt = "12px" style = "inter_18_25_400" textAlign = "center">
							{desc}
						</Text>
						<HStack gap = "8px">
							<Text
								mt = "15px"
								bgColor = "rgba(103, 115, 128, 0.1)"
								padding = "4px 8px"
								style = "inter_18_22_400"
								color = "677380"
								rounded = "8px"
							>
								{platform}
							</Text>
							<Text
								mt = "15px"
								bgColor = "rgba(103, 115, 128, 0.1)"
								padding = "4px 8px"
								style = "inter_18_22_400"
								color = "677380"
								rounded = "8px"
							>
								{secondPlatform}
							</Text>
							{ThirdPlatform && (
								<Text
									mt = "15px"
									bgColor = "rgba(103, 115, 128, 0.1)"
									padding = "4px 8px"
									style = "inter_18_22_400"
									color = "677380"
									rounded = "8px"
								>
									{ThirdPlatform}
								</Text>
							)}
						</HStack>
					</Center>
				}
				menuItems = {menuItems}
			/>
		);
	}

	return (
		<styled.a
			href = {downloadLink}
			style = {{
				width: "100%",
				height: "100%",
			}}
		>
			<Center
				w = "100%"
				h = "206px"
				bgColor = "rgba(255, 255, 255, 0.4)"
				cursor = "pointer"
				flexDirection = "column"
				rounded = "14px"
				_hover = {{
					bgColor: "rgba(255, 255, 255, 0.8)",
				}}
			>
				<Icon width = "76px" height = "76px" avatar = {icon} />
				<Text mt = "12px" style = "inter_18_25_400" textAlign = "center">
					{desc}
				</Text>
				<Text
					mt = "15px"
					bgColor = "rgba(103, 115, 128, 0.1)"
					padding = "4px 8px"
					style = "inter_18_22_400"
					color = "677380"
					rounded = "8px"
				>
					{platform}
				</Text>
			</Center>
		</styled.a>
	);
};

export default function PageInstallDesktop() {
	const langStringDesktopLogoTitle = useLangString("install_page.desktop.logo.title");
	const langStringDesktopLogoOnpremiseTitle = useLangString("install_page.desktop.logo.onpremise_title");
	const langStringPageTitle = useLangString("install_page.desktop.page.title");
	const langStringPageDesc = useLangString("install_page.desktop.page.desc");
	const langStringDownloadCompassDesktopBuildsMacOsDownload = useLangString("download_compass.desktop_builds.macos_download");
	const langStringPageDownloadIosDesc = useLangString("install_page.desktop.page.download_ios.desc");
	const langStringPageDownloadIosPlatformAppStore = useLangString(
		"install_page.desktop.page.download_ios.platform_app_store"
	);
	const langStringPageDownloadAndroidDesc = useLangString("install_page.desktop.page.download_android.desc");
	const langStringPageDownloadAndroidPlatformGooglePlay = useLangString(
		"install_page.desktop.page.download_android.platform_google_play"
	);
	const langStringPageDownloadHuaweiDesc = useLangString("install_page.desktop.page.download_huawei.desc");
	const langStringPageDownloadHuaweiPlatformAppGallery = useLangString(
		"install_page.desktop.page.download_huawei.platform_app_gallery"
	);
	const langStringPageSupportBlockTitle = useLangString("install_page.desktop.page.support_block.title");
	const langStringPageSupportBlockDesc = useLangString("install_page.desktop.page.support_block.desc");
	const langStringPageSupportBlockTelegram = useLangString("install_page.desktop.page.support_block.telegram");
	const langStringPageSupportBlockMail = useLangString("install_page.desktop.page.support_block.mail");
	const langStringDownloadCompassDesktopBuildsIntelVersion = useLangString("download_compass.desktop_builds.intel_version");
	const langStringDownloadCompassDesktopBuildsArmVersion = useLangString("download_compass.desktop_builds.arm_version");
	const langStringDownloadCompassDesktopBuildsWindowsDownload = useLangString("download_compass.desktop_builds.windows_download");
	const langStringDownloadCompassDesktopBuildsExeVersion = useLangString("download_compass.desktop_builds.exe_version");
	const langStringDownloadCompassDesktopBuildsMsiVersion = useLangString("download_compass.desktop_builds.msi_version");
	const langStringDownloadCompassDesktopBuildsLinuxDownload = useLangString("download_compass.desktop_builds.linux_download");
	const langStringDownloadCompassDesktopBuildsDebVersion = useLangString("download_compass.desktop_builds.deb_version");
	const langStringDownloadCompassDesktopBuildsTarVersion = useLangString("download_compass.desktop_builds.tar_version");
	const langStringDownloadCompassDesktopBuildsRpmVersion = useLangString("download_compass.desktop_builds.rpm_version");

	const { getDownloadLink } = useDownloadLink();

	return (
		<>
			<LandingBackground />
			<VStack w = "100%" minHeight = "100vh" gap = "0">
				<HStack w = "100%" justifyContent = "start" pt = "24px" px = "32px">
					<HStack
						gap = "12px"
						onClick = {() => (window.location.href = "https://getcompass.ru/on-premise")}
						cursor = "pointer"
					>
						<Icon width = "32px" height = "32px" avatar = {CompassLogo32} />
						<HStack>
							<Text textTransform = "uppercase" style = "lato_15_21_700">
								{langStringDesktopLogoTitle}
							</Text>
							<Box w = "1px" h = "14px" bgColor = "2d343c" opacity = "30%" />
							<Text style = "inter_15_21_400">{langStringDesktopLogoOnpremiseTitle}</Text>
						</HStack>
					</HStack>
				</HStack>
				<VStack mt = "50px" w = "100%" gap = "0" px = "32px">
					<Icon width = "80px" height = "80px" avatar = {CompassLogo80} />
					<VStack pt = "24px" gap = "0">
						<Text style = "inter_40_48_700" letterSpacing = "-0.5px" textAlign = "center">
							{langStringPageTitle}
						</Text>
						<Text pt = "16px" style = "inter_20_28_400" textAlign = "center">
							{langStringPageDesc.split("\n").map((line, index) => (
								<div key = {index}>{line}</div>
							))}
						</Text>
					</VStack>
					<HStack w = "100%" maxWidth = "1008px" justify = "center" mt = "54px" gap = "16px"
							userSelect = "none">
						<DownloadButton
							icon = {IconDownloadButtonApple}
							desc = {langStringDownloadCompassDesktopBuildsMacOsDownload}
							platform = {langStringDownloadCompassDesktopBuildsIntelVersion}
							secondPlatform = {langStringDownloadCompassDesktopBuildsArmVersion}
							downloadLink = {getDownloadLink(DESKTOP_PLATFORM_MAC_OS_INTEL)}
							menuItems = {<MacOsMenuItems isNeedAnotherPlatformItem = {false} />}
						/>
						<DownloadButton
							icon = {IconDownloadButtonWin}
							desc = {langStringDownloadCompassDesktopBuildsWindowsDownload}
							platform = {langStringDownloadCompassDesktopBuildsExeVersion}
							secondPlatform = {langStringDownloadCompassDesktopBuildsMsiVersion}
							downloadLink = {getDownloadLink(DESKTOP_PLATFORM_WINDOWS_7_EXE)}
							menuItems = {<WindowsMenuItems isNeedAnotherPlatformItem = {false} />}
						/>
						<DownloadButton
							icon = {IconDownloadButtonLinux}
							desc = {langStringDownloadCompassDesktopBuildsLinuxDownload}
							platform = {langStringDownloadCompassDesktopBuildsDebVersion}
							secondPlatform = {langStringDownloadCompassDesktopBuildsTarVersion}
							ThirdPlatform = {langStringDownloadCompassDesktopBuildsRpmVersion}
							downloadLink = {getDownloadLink(DESKTOP_PLATFORM_LINUX_DEB)}
							menuItems = {<LinuxMenuItems isNeedAnotherPlatformItem = {false} />}
						/>
					</HStack>
					<HStack w = "100%" justify = "center" mt = "16px" gap = "16px" userSelect = "none"
							maxWidth = "1008px">
						<DownloadButton
							icon = {IconDownloadButtonAppStore}
							desc = {langStringPageDownloadIosDesc}
							platform = {langStringPageDownloadIosPlatformAppStore}
							downloadLink = {getDownloadLink(MOBILE_PLATFORM_IOS)}
						/>
						<DownloadButton
							icon = {IconDownloadButtonGooglePlay}
							desc = {langStringPageDownloadAndroidDesc}
							platform = {langStringPageDownloadAndroidPlatformGooglePlay}
							downloadLink = {getDownloadLink(MOBILE_PLATFORM_ANDROID)}
						/>
						<DownloadButton
							icon = {IconDownloadButtonAppGallery}
							desc = {langStringPageDownloadHuaweiDesc}
							platform = {langStringPageDownloadHuaweiPlatformAppGallery}
							downloadLink = {getDownloadLink(MOBILE_PLATFORM_HUAWEI)}
						/>
					</HStack>
					<VStack
						width = "100%"
						maxWidth = "1008px"
						mt = "54px"
						padding = "64px 64px 54px 64px"
						gap = "0"
						rounded = "40px"
						bgColor = "255255255.03"
					>
						<HStack gap = "0px">
							<Box
								w = "90px"
								h = "90px"
								bgPosition = "center"
								bgSize = "cover"
								bgRepeat = "no-repeat"
								flexShrink = "0"
								style = {{
									backgroundImage: `url(${Avatar1})`,
								}}
							/>
							<Box
								w = "90px"
								h = "90px"
								bgPosition = "center"
								bgSize = "cover"
								bgRepeat = "no-repeat"
								flexShrink = "0"
								marginLeft = "-24px"
								style = {{
									backgroundImage: `url(${Avatar2})`,
								}}
							/>
							<Box
								w = "90px"
								h = "90px"
								bgPosition = "center"
								bgSize = "cover"
								bgRepeat = "no-repeat"
								flexShrink = "0"
								marginLeft = "-24px"
								style = {{
									backgroundImage: `url(${Avatar3})`,
								}}
							/>
						</HStack>
						<Text mt = "24px" style = "inter_40_48_700" letterSpacing = "-0.5px" textAlign = "center">
							{langStringPageSupportBlockTitle}
						</Text>
						<Text mt = "16px" style = "inter_20_28_400" textAlign = "center">
							{langStringPageSupportBlockDesc.split("\n").map((line, index) => (
								<div key = {index}>{line}</div>
							))}
						</Text>
						<HStack mt = "24px" gap = "16px">
							<styled.a href = "https://t.me/getcompass">
								<HStack
									className = "group"
									cursor = "pointer"
									gap = "6px"
									border = "1px solid rgba(103, 115, 128, 0.3)"
									rounded = "12px"
									p = "15px 31px"
									_hover = {{ border: "1px solid rgba(103, 115, 128, 0.6)" }}
								>
									<Icon width = "24px" height = "24px" avatar = {TelegramIcon24} />
									<Text color = "677380" style = "inter_20_24_500"
										  _groupHover = {{ color: "333e49" }}>
										{langStringPageSupportBlockTelegram}
									</Text>
								</HStack>
							</styled.a>
						</HStack>
						<styled.a href = {`mailto:${langStringPageSupportBlockMail}`}>
							<HStack mt = "24px" cursor = "pointer" gap = "10px">
								<Icon width = "26px" height = "26px" avatar = {MailIcon26} />
								<Text color = "009fe6" style = "inter_20_24_500">
									{langStringPageSupportBlockMail}
								</Text>
							</HStack>
						</styled.a>
					</VStack>
				</VStack>
				<Footer />
			</VStack>
		</>
	);
}
