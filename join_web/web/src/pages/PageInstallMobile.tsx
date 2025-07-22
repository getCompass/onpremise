import { Box, Center, HStack, styled, VStack } from "../../styled-system/jsx";
import { useLangString } from "../lib/getLangString.ts";
import { Text } from "../components/text.tsx";
import { Icon } from "../components/Icon.tsx";
import CompassLogo32 from "../img/install_page/mobile/CompassLogo32.svg";
import CompassLogo80 from "../img/install_page/mobile/CompassLogo80.svg";
import DownloadButtonAppStore from "../img/install_page/mobile/DownloadButtonAppStore.svg";
import DownloadButtonGooglePlay from "../img/install_page/mobile/DownloadButtonGooglePlay.svg";
import DownloadButtonAppGallery from "../img/install_page/mobile/DownloadButtonAppGallery.svg";
import { copyToClipboardInstall } from "../lib/copyToClipboardInstall.ts";
import Avatar1 from "../img/install_page/mobile/Avatar_1.png";
import Avatar2 from "../img/install_page/mobile/Avatar_2.png";
import Avatar3 from "../img/install_page/mobile/Avatar_3.png";
import TelegramIcon22 from "../img/install_page/mobile/TelegramIcon22.svg";
import MailIcon21 from "../img/install_page/mobile/MailIcon21.svg";
import { useCallback } from "react";
import { useIsLowWidthMobile } from "../lib/useIsMobile.ts";
import LandingBackground from "../components/mobile/LandingBackground.tsx";
import OnpremiseInstallDownloadMenuMobile from "../components/mobile/OnpremiseInstallDownloadMenuMobile.tsx";
import {
	DESKTOP_PLATFORM_LINUX_DEB,
	DESKTOP_PLATFORM_MAC_OS_INTEL,
	DESKTOP_PLATFORM_WINDOWS_7_EXE,
	MOBILE_PLATFORM_ANDROID,
	MOBILE_PLATFORM_HUAWEI,
	MOBILE_PLATFORM_IOS,
} from "../api/_types.ts";
import useDownloadLink from "../lib/useDownloadLink.ts";
import { useAtomValue } from "jotai/index";
import { downloadAppUrlState } from "../api/_stores.ts";
import MacOsMenuItems from "../components/desktop/download/MacOsMenuItems.tsx";
import LinuxMenuItems from "../components/desktop/download/LinuxMenuItems.tsx";
import WindowsMenuItems from "../components/desktop/download/WindowsMenuItems.tsx";

type DownloadButtonProps = {
	icon: string;
	desc: string;
	link: string;
	platform?: string;
	triggerEl?: JSX.Element;
	menuItems?: JSX.Element;
	onSelectHandler?: (value: string) => void;
};

const DownloadButton = ({ icon, desc, link, triggerEl, menuItems, onSelectHandler }: DownloadButtonProps) => {
	const isLowWidthMobile = useIsLowWidthMobile();

	if (triggerEl !== undefined && menuItems !== undefined && onSelectHandler !== undefined) {
		return (
			<OnpremiseInstallDownloadMenuMobile
				triggerEl={triggerEl}
				menuItems={menuItems}
				onSelectHandler={onSelectHandler}
			/>
		);
	}

	return (
		<styled.a
			w="100%"
			href={link}
			target="_blank"
			outline="none"
			WebkitTapHighlightColor="transparent"
			userSelect="none"
		>
			<HStack
				p="12px 16px 12px 12px"
				rounded="12px"
				bgColor="255255255.04"
				w="100%"
				justify="space-between"
				_active={{
					bgColor: "white",
				}}
			>
				<HStack gap={isLowWidthMobile ? "9px" : "12px"}>
					<Icon
						width={isLowWidthMobile ? "21px" : "27px"}
						height={isLowWidthMobile ? "21px" : "27px"}
						avatar={icon}
					/>
					<Text style={isLowWidthMobile ? "inter_14_21_600" : "inter_18_27_600"}>{desc}</Text>
				</HStack>
				<Center w={isLowWidthMobile ? "14px" : "18px"} h={isLowWidthMobile ? "21px" : "27px"}>
					<svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M7.03072 12.473L5.71396 11.169L9.65785 7.22514H0.0761719V5.32031H9.65785L5.71396 1.38281L7.03072 0.0724429L13.231 6.27273L7.03072 12.473Z"
							fill="#333E49"
						/>
					</svg>
				</Center>
			</HStack>
		</styled.a>
	);
};

export default function PageInstallMobile() {
	const langStringMobileLogoTitle = useLangString("install_page.mobile.logo.title");
	const langStringMobileLogoOnpremiseTitle = useLangString("install_page.mobile.logo.onpremise_title");
	const langStringPageTitle = useLangString("install_page.mobile.page.title");
	const langStringPageDesc = useLangString("install_page.mobile.page.desc");
	const langStringPageDownloadIos = useLangString("install_page.mobile.page.download_ios");
	const langStringPageDownloadAndroid = useLangString("install_page.mobile.page.download_android");
	const langStringPageDownloadHuawei = useLangString("install_page.mobile.page.download_huawei");
	const langStringPageDesktopFooter = useLangString("install_page.mobile.page.desktop_footer");
	const langStringPageDownloadMacos = useLangString("install_page.mobile.page.download_macos");
	const langStringPageDownloadComma = useLangString("install_page.mobile.page.download_comma");
	const langStringPageDownloadWindows = useLangString("install_page.mobile.page.download_windows");
	const langStringPageDownloadAnd = useLangString("install_page.mobile.page.download_and");
	const langStringPageDownloadLinux = useLangString("install_page.mobile.page.download_linux");
	const langStringPageDownloadDot = useLangString("install_page.mobile.page.download_dot");
	const langStringPageOnSuccessCopy = useLangString("install_page.mobile.page.on_success_copy");
	const langStringPageSupportBlockTitle = useLangString("install_page.mobile.page.support_block.title");
	const langStringPageSupportBlockDesc = useLangString("install_page.mobile.page.support_block.desc");
	const langStringPageSupportBlockTelegram = useLangString("install_page.mobile.page.support_block.telegram");
	const langStringPageSupportBlockMail = useLangString("install_page.mobile.page.support_block.mail");
	const isLowWidthMobile = useIsLowWidthMobile();
	const { getDownloadLink } = useDownloadLink();
	const downloadAppUrl = useAtomValue(downloadAppUrlState);

	const onSelectPlatform = useCallback((value: string) => {
		copyToClipboardInstall(getDownloadLink(value), langStringPageOnSuccessCopy);
	}, [downloadAppUrl]);

	return (
		<>
			<LandingBackground />
			<VStack w="100%" minHeight="100vh" gap="0" className="landing-page-layout-mobile-vh-only">
				<HStack w="100%" justifyContent="start" py="12px" px="16px" bgColor="255255255.03" userSelect="none">
					<HStack
						gap="10px"
						onClick={() => (window.location.href = "https://getcompass.ru/on-premise")}
						cursor="pointer"
						outline="none"
						WebkitTapHighlightColor="transparent"
					>
						<Icon width="32px" height="32px" avatar={CompassLogo32} />
						<HStack>
							<Text textTransform="uppercase" style="lato_14_17_700">
								{langStringMobileLogoTitle}
							</Text>
							<Box w="1px" h="14px" bgColor="2d343c" opacity="30%" />
							<Text style="lato_14_17_400">{langStringMobileLogoOnpremiseTitle}</Text>
						</HStack>
					</HStack>
				</HStack>
				<VStack w="100%" px="24px" mt="60px" gap="0">
					<Icon width="81px" height="80px" avatar={CompassLogo80} />
					<VStack pt="24px">
						<Text style="inter_30_36_700" letterSpacing="-1.5px" textAlign="center">
							{langStringPageTitle}
						</Text>
						<Text pt="16px" style="inter_18_25_400" textAlign="center">
							{langStringPageDesc.split("\n").map((line, index) => (
								<div key={index}>{line}</div>
							))}
						</Text>
					</VStack>
					<VStack w="100%" pt="24px" pb="24px">
						<DownloadButton
							icon={DownloadButtonAppStore}
							desc={langStringPageDownloadIos}
							link={getDownloadLink(MOBILE_PLATFORM_IOS)}
						/>
						<DownloadButton
							icon={DownloadButtonGooglePlay}
							desc={langStringPageDownloadAndroid}
							link={getDownloadLink(MOBILE_PLATFORM_ANDROID)}
						/>
						<DownloadButton
							icon={DownloadButtonAppGallery}
							desc={langStringPageDownloadHuawei}
							link={getDownloadLink(MOBILE_PLATFORM_HUAWEI)}
						/>
					</VStack>
					<Text color="677380" style="inter_14_20_400" textAlign="center">
						{langStringPageDesktopFooter}
						<DownloadButton
							icon=""
							desc={langStringPageDownloadMacos}
							link={getDownloadLink(DESKTOP_PLATFORM_MAC_OS_INTEL)}
							triggerEl={
								<styled.span>
									<styled.span
										textDecoration="underline"
										textDecorationSkipInk="none"
										textDecorationThickness="1px"
										textUnderlineOffset="4px"
										outline="none"
										WebkitTapHighlightColor="transparent"
										userSelect="none"
										cursor="pointer"
									>
										{langStringPageDownloadMacos}
									</styled.span>
									{langStringPageDownloadComma}
								</styled.span>
							}
							menuItems={<MacOsMenuItems isNeedAnotherPlatformItem = {false} />}
							onSelectHandler={onSelectPlatform}
						/>
						<DownloadButton
							icon=""
							desc={langStringPageDownloadWindows}
							link={getDownloadLink(DESKTOP_PLATFORM_WINDOWS_7_EXE)}
							triggerEl={
								<styled.span>
									<styled.span
										textDecoration="underline"
										textDecorationSkipInk="none"
										textDecorationThickness="1px"
										textUnderlineOffset="4px"
										outline="none"
										WebkitTapHighlightColor="transparent"
										userSelect="none"
										cursor="pointer"
									>
										{langStringPageDownloadWindows}
									</styled.span>
									{langStringPageDownloadAnd}
								</styled.span>
							}
							menuItems = {<WindowsMenuItems isNeedAnotherPlatformItem = {false} />}
							onSelectHandler={onSelectPlatform}
						/>
						<DownloadButton
							icon=""
							desc={langStringPageDownloadLinux}
							link={getDownloadLink(DESKTOP_PLATFORM_LINUX_DEB)}
							triggerEl={
								<styled.span>
									<styled.span
										textDecoration="underline"
										textDecorationSkipInk="none"
										textDecorationThickness="1px"
										textUnderlineOffset="4px"
										outline="none"
										WebkitTapHighlightColor="transparent"
										userSelect="none"
										cursor="pointer"
									>
										{langStringPageDownloadLinux}
									</styled.span>
									{langStringPageDownloadDot}
								</styled.span>
							}
							menuItems = {<LinuxMenuItems isNeedAnotherPlatformItem = {false} />}
							onSelectHandler={onSelectPlatform}
						/>
					</Text>
					<VStack w="100%" mt="64px" mb="32px" bgColor="255255255.04" gap="0px" rounded="16px" p="32px 24px">
						<HStack gap="0px">
							<Box
								w="72px"
								h="72px"
								bgPosition="center"
								bgSize="cover"
								bgRepeat="no-repeat"
								flexShrink="0"
								style={{
									backgroundImage: `url(${Avatar1})`,
								}}
							/>
							<Box
								w="72px"
								h="72px"
								bgPosition="center"
								bgSize="cover"
								bgRepeat="no-repeat"
								flexShrink="0"
								marginLeft="-20px"
								style={{
									backgroundImage: `url(${Avatar2})`,
								}}
							/>
							<Box
								w="72px"
								h="72px"
								bgPosition="center"
								bgSize="cover"
								bgRepeat="no-repeat"
								flexShrink="0"
								marginLeft="-20px"
								style={{
									backgroundImage: `url(${Avatar3})`,
								}}
							/>
						</HStack>
						<Text mt="10px" style="inter_30_36_700" textAlign="center">
							{langStringPageSupportBlockTitle}
						</Text>
						<Text mt="16px" style="inter_16_22_400" textAlign="center">
							{langStringPageSupportBlockDesc.split("\n").map((line, index) => (
								<div key={index}>{line}</div>
							))}
						</Text>
						<HStack w="100%" mt="24px" gap={isLowWidthMobile ? "8px" : "12px"}>
							<styled.a
								w="100%"
								href="https://t.me/getcompass"
								outline="none"
								WebkitTapHighlightColor="transparent"
								userSelect="none"
							>
								<HStack
									w="100%"
									className="group"
									cursor="pointer"
									justifyContent="center"
									gap="6px"
									border="1px solid rgba(103, 115, 128, 0.1)"
									rounded="8px"
									p={isLowWidthMobile ? "6px 12px" : "9px 21px"}
									_active={{ border: "1px solid rgba(103, 115, 128, 0.3)" }}
								>
									<Icon
										width={isLowWidthMobile ? "20px" : "22px"}
										height={isLowWidthMobile ? "20px" : "22px"}
										avatar={TelegramIcon22}
									/>
									<Text
										color="677380"
										style={isLowWidthMobile ? "inter_14_17_500" : "inter_16_19_500"}
										_groupActive={{ color: "333e49" }}
									>
										{langStringPageSupportBlockTelegram}
									</Text>
								</HStack>
							</styled.a>
						</HStack>
						<styled.a
							href={`mailto:${langStringPageSupportBlockMail}`}
							outline="none"
							WebkitTapHighlightColor="transparent"
							userSelect="none"
						>
							<HStack mt="16px" cursor="pointer" gap="8px">
								<Icon
									width={isLowWidthMobile ? "19px" : "21px"}
									height={isLowWidthMobile ? "19px" : "21px"}
									avatar={MailIcon21}
								/>
								<Text color="009fe6" style={isLowWidthMobile ? "inter_14_17_400" : "inter_16_19_400"}>
									{langStringPageSupportBlockMail}
								</Text>
							</HStack>
						</styled.a>
					</VStack>
				</VStack>
			</VStack>
		</>
	);
}
