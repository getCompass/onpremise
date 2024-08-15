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
import WhatsappIcon24 from "../img/install_page/desktop/WhatsappIcon24.svg";
import TelegramIcon24 from "../img/install_page/desktop/TelegramIcon24.svg";
import MailIcon26 from "../img/install_page/desktop/MailIcon26.svg";
import { useCallback, useMemo } from "react";
import dayjs from "dayjs";
import { MenuItem, MenuItemGroup } from "../components/menu.tsx";
import LandingBackground from "../components/desktop/LandingBackground.tsx";
import OnpremiseInstallDownloadMenuDesktop from "../components/desktop/OnpremiseInstallDownloadMenuDesktop.tsx";
import {
	DESKTOP_PLATFORM_LINUX_DEB,
	DESKTOP_PLATFORM_LINUX_TAR,
	DESKTOP_PLATFORM_MAC_OS_ARM,
	DESKTOP_PLATFORM_MAC_OS_INTEL,
	DESKTOP_PLATFORM_WINDOWS,
	MOBILE_PLATFORM_ANDROID,
	MOBILE_PLATFORM_HUAWEI,
	MOBILE_PLATFORM_IOS,
} from "../api/_types.ts";
import useDownloadLink from "../lib/useDownloadLink.ts";

const Footer = () => {
	const currentYear = useMemo(() => dayjs.unix(dayjs().unix()).format("YYYY"), []);

	return (
		<HStack w="100%" mt="80px" p="32px" bgColor="393a4d" justify="space-between" userSelect="none">
			<HStack
				gap="10px"
				onClick={() => (window.location.href = "https://getcompass.ru/on-premise")}
				cursor="pointer"
			>
				<Icon width="24px" height="24px" avatar={CompassFooterLogo24} />
				<Text style="inter_14_17_400" letterSpacing="-0.5px" color="248248248.03">
					{"COMPASS © $CURRENT_YEAR".replace("$CURRENT_YEAR", currentYear)}
				</Text>
			</HStack>

			<Text style="inter_14_17_400" color="248248248.03">
				<styled.span>Сделано для вас&nbsp;</styled.span>
				<styled.span color="#c7544f">❤</styled.span>️
			</Text>
		</HStack>
	);
};

const MacOsIcon = () => {
	return (
		<Box w="22px" h="22px">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M15.4258 11.0566C15.4258 9.82812 15.9902 8.93164 17.0859 8.23438C16.4551 7.33789 15.5254 6.87305 14.2969 6.77344C13.1016 6.67383 11.8066 7.4375 11.3418 7.4375C10.8438 7.4375 9.71484 6.80664 8.81836 6.80664C6.95898 6.83984 5 8.26758 5 11.2227C5 12.0859 5.13281 12.9824 5.46484 13.9121C5.89648 15.1406 7.42383 18.1289 9.01758 18.0625C9.84766 18.0625 10.4453 17.4648 11.541 17.4648C12.6035 17.4648 13.1348 18.0625 14.0645 18.0625C15.6914 18.0625 17.0859 15.3398 17.4844 14.1113C15.3262 13.082 15.4258 11.123 15.4258 11.0566ZM13.5664 5.61133C14.4629 4.54883 14.3633 3.55273 14.3633 3.1875C13.5664 3.25391 12.6367 3.75195 12.1055 4.34961C11.5078 5.01367 11.1758 5.84375 11.2422 6.74023C12.1055 6.80664 12.9023 6.375 13.5664 5.61133Z"
					fill="#DCDCDC"
				/>
			</svg>
		</Box>
	);
};

const LinuxDebIcon = () => {
	return (
		<Box w="22px" h="22px">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M11.2344 3C6.68555 3 3 6.68555 3 11.2344C3 15.7832 6.68555 19.4688 11.2344 19.4688C15.7832 19.4688 19.4688 15.7832 19.4688 11.2344C19.4688 6.68555 15.7832 3 11.2344 3ZM12.9609 6.08789C13.2598 5.58984 13.9238 5.42383 14.4219 5.72266C14.9199 6.02148 15.0859 6.65234 14.7871 7.15039C14.5215 7.68164 13.8574 7.84766 13.3594 7.54883C12.8613 7.25 12.6621 6.61914 12.9609 6.08789ZM5.88867 12.2969C5.29102 12.2969 4.82617 11.832 4.82617 11.2344C4.82617 10.6699 5.29102 10.2051 5.88867 10.2051C6.48633 10.2051 6.95117 10.6699 6.95117 11.2344C6.95117 11.832 6.48633 12.2969 5.88867 12.2969ZM6.81836 12.3965C7.54883 11.832 7.54883 10.7031 6.81836 10.1055C7.11719 9.00977 7.78125 8.08008 8.71094 7.44922L9.47461 8.77734C7.78125 9.97266 7.78125 12.5293 9.47461 13.7246L8.71094 15.0195C7.78125 14.4219 7.11719 13.4922 6.81836 12.3965ZM14.4219 16.7793C13.8906 17.0781 13.2598 16.9121 12.9609 16.3809C12.6621 15.8828 12.8613 15.252 13.3594 14.9531C13.8574 14.6543 14.5215 14.8203 14.7871 15.3516C15.0859 15.8496 14.9199 16.4805 14.4219 16.7793ZM14.4219 14.4883C13.5254 14.123 12.5625 14.6875 12.4297 15.6504C12.2305 15.6836 10.8027 16.1152 9.20898 15.3184L9.93945 13.9902C11.832 14.8535 14.0566 13.5918 14.2227 11.5332H15.75C15.6836 12.6953 15.1855 13.7246 14.4219 14.4883ZM14.2227 10.9688C14.0566 8.91016 11.8652 7.61523 9.93945 8.51172L9.20898 7.18359C10.8027 6.38672 12.2305 6.81836 12.3965 6.85156C12.5625 7.81445 13.5254 8.37891 14.4219 8.01367C15.1855 8.77734 15.6836 9.80664 15.75 10.9688H14.2227Z"
					fill="#DCDCDC"
				/>
			</svg>
		</Box>
	);
};

const LinuxTarIcon = () => {
	return (
		<Box w="22px" h="22px">
			<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M10.1226 6.19264C10.1826 6.17014 10.2651 6.16264 10.3176 6.19264C10.3326 6.20014 10.3476 6.21514 10.3401 6.23014V6.24514H10.3476C10.3326 6.29014 10.2576 6.28264 10.2201 6.29764C10.1826 6.31264 10.1526 6.35014 10.1151 6.35014C10.0776 6.35014 10.0176 6.33514 10.0101 6.29764C10.0026 6.24514 10.0776 6.19264 10.1226 6.19264ZM10.8876 6.29764C10.8501 6.28264 10.7751 6.29014 10.7676 6.24514V6.23014C10.7601 6.21514 10.7751 6.20014 10.7826 6.19264C10.8476 6.16637 10.9202 6.16637 10.9851 6.19264C11.0301 6.19264 11.1051 6.24514 11.0976 6.29764C11.0901 6.33514 11.0301 6.34264 10.9926 6.34264C10.9551 6.34264 10.9251 6.31264 10.8876 6.29764ZM9.56762 7.38514C9.79262 7.53514 10.0851 7.73764 10.4526 7.73764C10.8201 7.73764 11.2551 7.53514 11.5176 7.38514C11.6601 7.28764 11.8551 7.13764 12.0051 7.03264C12.1251 6.93514 12.1176 6.83764 12.2226 6.83764C12.3201 6.84514 12.2451 6.93514 12.1101 7.08514C11.9676 7.18264 11.7576 7.33264 11.5851 7.43764C11.2551 7.58764 10.8651 7.83514 10.4526 7.83514C10.0476 7.83514 9.71762 7.63264 9.48512 7.48264C9.36512 7.38514 9.27512 7.28764 9.20012 7.23514C9.08012 7.13764 9.09512 6.98764 9.14762 6.98764C9.23012 6.99514 9.24512 7.08514 9.29762 7.13764C9.37262 7.18264 9.46262 7.28764 9.56762 7.38514Z"
					fill="white"
				/>
				<path
					d="M11.0977 6.29755C11.0902 6.33505 11.0302 6.34255 10.9927 6.34255C10.9552 6.34255 10.9252 6.31255 10.8877 6.29755C10.8502 6.28255 10.7752 6.29005 10.7677 6.24505V6.23005C10.7602 6.21505 10.7752 6.20005 10.7827 6.19255C10.8476 6.16628 10.9202 6.16628 10.9852 6.19255C11.0302 6.19255 11.1052 6.24505 11.0977 6.29755Z"
					fill="#B4B4B4"
				/>
				<path
					d="M14.4726 10.235V10.925H14.0601C14.4346 11.6078 14.7027 12.3438 14.8551 13.1075C14.9676 13.1 15.1026 13.1225 15.2451 13.1525C15.3051 12.995 15.3426 12.8375 15.3651 12.68C15.5076 11.6375 14.8476 10.6175 14.4726 10.235ZM14.4726 10.235V10.925H14.0601C14.4346 11.6078 14.7027 12.3438 14.8551 13.1075C14.9676 13.1 15.1026 13.1225 15.2451 13.1525C15.3051 12.995 15.3426 12.8375 15.3651 12.68C15.5076 11.6375 14.8476 10.6175 14.4726 10.235ZM17.9601 16.2125V16.145L17.9526 16.1375C17.8251 15.9875 17.7651 15.74 17.6976 15.4475C17.6376 15.1475 17.5626 14.855 17.3226 14.66C17.2776 14.6225 17.2326 14.615 17.1801 14.5625C17.1374 14.5341 17.0886 14.5161 17.0376 14.51C17.2476 13.895 17.2701 13.2725 17.1651 12.68C17.1126 12.3575 17.0226 12.0425 16.9026 11.7425C16.4976 10.685 15.7926 9.7625 15.2526 9.1325C14.9751 8.78 14.6901 8.4275 14.4726 8.0525C14.2251 7.625 14.0601 7.1675 14.0676 6.605C14.0826 5.5625 14.1501 3.935 13.4151 2.9225C13.0251 2.375 12.3876 2 11.3826 2C11.2626 2 11.1426 2.0075 11.0151 2.015C10.0626 2.09 9.47011 2.435 9.10261 2.9225C8.24011 4.0625 8.64511 5.9525 8.61511 6.74C8.55511 7.5575 8.39011 8.2025 7.82011 9.005C7.58761 9.275 7.31761 9.605 7.05511 9.98C6.55261 10.685 6.05011 11.525 5.75761 12.395C5.57011 12.9575 5.46511 13.535 5.51761 14.09L5.54011 14.2625C5.51011 14.285 5.48011 14.315 5.45761 14.36C5.25511 14.5625 5.11261 14.81 4.95511 14.99C4.79761 15.14 4.58761 15.1925 4.34761 15.29C4.10761 15.395 3.84511 15.4925 3.69511 15.8C3.62011 15.9425 3.59011 16.1 3.59011 16.25C3.59011 16.4 3.61261 16.5575 3.63511 16.655C3.68011 16.955 3.72511 17.2025 3.66511 17.3825C3.47761 17.8925 3.45511 18.245 3.58261 18.5C3.71761 18.7475 3.98761 18.845 4.29511 18.95C4.91011 19.1 5.74261 19.0475 6.40261 19.4C7.10011 19.745 7.82011 19.9025 8.38261 19.7525C8.78761 19.6625 9.12511 19.4 9.30511 19.04C9.34261 19.04 9.37261 19.04 9.41011 19.0325C9.83011 19.01 10.2951 18.8375 11.0151 18.7925C11.2176 18.7775 11.4351 18.8 11.6751 18.83C12.0576 18.89 12.4926 18.9725 12.9726 18.9425C12.9876 19.04 13.0176 19.085 13.0551 19.19H13.0626C13.3551 19.775 13.9026 20.0375 14.4876 19.9925C15.0726 19.9475 15.6951 19.595 16.2051 19.0175C16.6776 18.44 17.4801 18.2 18.0051 17.885C18.2676 17.7425 18.4851 17.54 18.5001 17.2475C18.5151 16.9475 18.3501 16.64 17.9601 16.2125ZM12.1251 5.48C12.0951 5.3825 12.0501 5.33 11.9901 5.2325C11.9226 5.18 11.8626 5.1275 11.7876 5.1275H11.7726C11.7051 5.1275 11.6376 5.15 11.5776 5.2325C11.5026 5.3 11.4501 5.3825 11.4201 5.48C11.3826 5.5775 11.3601 5.675 11.3526 5.78V5.795C11.3526 5.8625 11.3601 5.93 11.3676 5.9975C11.2176 5.945 11.0376 5.8925 10.9101 5.84C10.9026 5.795 10.8951 5.7425 10.8951 5.6975V5.6825C10.8876 5.48 10.9251 5.285 11.0076 5.105C11.0676 4.94 11.1801 4.7975 11.3301 4.7C11.4651 4.6025 11.6226 4.55 11.7801 4.55H11.7951C11.9526 4.55 12.0951 4.6025 12.2376 4.7C12.3801 4.805 12.4851 4.9475 12.5676 5.105C12.6501 5.2925 12.6876 5.4425 12.6951 5.645C12.6951 5.63 12.7026 5.615 12.7026 5.6V5.675C12.6951 5.675 12.6951 5.6675 12.6951 5.66C12.6951 5.8325 12.6501 6.0125 12.5826 6.1775C12.5451 6.2675 12.4926 6.3575 12.4176 6.425C12.3951 6.41 12.3726 6.4025 12.3501 6.395C12.2751 6.3575 12.2001 6.3425 12.1326 6.2975C12.0804 6.27219 12.0248 6.25451 11.9676 6.245C12.0051 6.2 12.0801 6.1475 12.1101 6.095C12.1476 5.9975 12.1701 5.9 12.1776 5.795V5.78C12.1776 5.6825 12.1626 5.5775 12.1251 5.48ZM9.00511 5.15C9.04261 5 9.11761 4.865 9.22261 4.745C9.32011 4.6475 9.41761 4.595 9.53761 4.595H9.56011C9.67113 4.5966 9.77879 4.63336 9.86761 4.7C9.98011 4.7975 10.0701 4.9175 10.1301 5.045C10.1976 5.195 10.2351 5.345 10.2426 5.5475C10.2501 5.6 10.2501 5.6375 10.2501 5.675C10.2501 5.705 10.2501 5.7275 10.2426 5.75V5.81C10.2234 5.81765 10.2032 5.82269 10.1826 5.825C10.0626 5.87 9.97261 5.93 9.88261 5.975C9.89011 5.915 9.89011 5.8475 9.88261 5.78V5.765C9.87511 5.6675 9.85261 5.615 9.82261 5.5175C9.79759 5.44045 9.75377 5.37086 9.69511 5.315C9.67557 5.29835 9.65289 5.2858 9.62841 5.27807C9.60394 5.27034 9.57816 5.26759 9.55261 5.27H9.53761C9.48511 5.27 9.44011 5.3 9.40261 5.3675C9.35011 5.4275 9.32011 5.495 9.30511 5.57C9.29011 5.6525 9.28261 5.735 9.29011 5.8175V5.8325C9.29761 5.93 9.32011 5.9825 9.35011 6.08C9.38761 6.1775 9.42511 6.23 9.47761 6.2825C9.48511 6.29 9.49261 6.2975 9.50011 6.2975C9.44761 6.3425 9.41761 6.35 9.37261 6.4025C9.34175 6.42729 9.30595 6.44519 9.26761 6.455C9.19261 6.3575 9.12511 6.26 9.06511 6.1475C8.99011 5.99 8.95261 5.825 8.94511 5.6525C8.93011 5.48 8.95261 5.3075 9.00511 5.15ZM9.08761 6.7325C9.25261 6.6275 9.37261 6.5225 9.45511 6.4775C9.53011 6.425 9.56011 6.4025 9.58261 6.38H9.59011C9.71761 6.2225 9.92011 6.0275 10.2201 5.9225C10.3251 5.9 10.4451 5.8775 10.5801 5.8775C10.8276 5.8775 11.1351 5.9225 11.4951 6.1775C11.7201 6.3275 11.8926 6.38 12.2976 6.53C12.4926 6.6275 12.6051 6.725 12.6651 6.83V6.725C12.7176 6.8375 12.7176 6.965 12.6726 7.0775C12.5826 7.3175 12.2826 7.565 11.8701 7.715C11.6601 7.8125 11.4876 7.9625 11.2776 8.06C11.0676 8.165 10.8351 8.285 10.5126 8.2625C10.3975 8.27203 10.2818 8.25403 10.1751 8.21C10.0851 8.165 10.0026 8.12 9.92761 8.0675C9.77761 7.9625 9.65011 7.8125 9.46261 7.715C9.15511 7.5275 8.99011 7.325 8.93761 7.1825C8.88511 6.98 8.93761 6.83 9.08761 6.7325ZM9.20761 18.4775C9.16261 19.0325 8.84011 19.3325 8.35261 19.445C7.85761 19.5425 7.19761 19.445 6.53011 19.1C5.80261 18.695 4.92511 18.7475 4.37011 18.6425C4.08511 18.5975 3.90511 18.4925 3.81511 18.3425C3.73261 18.1925 3.73261 17.8925 3.91261 17.42C4.00261 17.165 3.93511 16.85 3.89011 16.58C3.85261 16.28 3.83011 16.0475 3.92761 15.875C4.04761 15.62 4.22761 15.575 4.44511 15.47C4.67011 15.3725 4.93261 15.32 5.14261 15.125V15.1175C5.33761 14.915 5.48011 14.6675 5.65261 14.4875C5.79511 14.3375 5.93761 14.24 6.15511 14.24H6.16261C6.20011 14.24 6.23761 14.24 6.28261 14.2475C6.56761 14.2925 6.81511 14.5025 7.05511 14.81L7.74511 16.0625H7.75261C7.93261 16.46 8.32261 16.865 8.65261 17.2925C8.98261 17.7425 9.23761 18.14 9.20761 18.47V18.4775ZM11.6751 17.9975C10.9326 18.17 10.1451 18.1175 9.41011 17.7125C9.32011 17.6675 9.23011 17.615 9.14761 17.555C9.06511 17.4125 8.96011 17.2775 8.84011 17.1575C8.7849 17.0635 8.71386 16.9798 8.63011 16.91C8.77261 16.91 8.89261 16.8875 8.99011 16.8575C9.09511 16.805 9.18511 16.715 9.22261 16.61C9.30511 16.4075 9.22261 16.085 8.96011 15.7325C8.69761 15.3875 8.25511 14.99 7.61011 14.5925C7.36261 14.435 7.17511 14.27 7.03261 14.09C6.89011 13.9175 6.79261 13.7375 6.73261 13.55C6.61261 13.145 6.62761 12.7325 6.72511 12.3125C6.83761 11.8175 7.06261 11.3375 7.28011 10.925C7.43011 10.6625 7.57261 10.43 7.69261 10.2425C7.76761 10.19 7.71511 10.3325 7.40011 10.925L7.37761 10.97C7.07761 11.5325 6.51511 12.845 7.28761 13.865C7.31761 13.1225 7.48261 12.3875 7.77511 11.705C7.87261 11.48 8.00011 11.2175 8.13511 10.925C8.57761 9.9875 9.11761 8.7725 9.17011 7.7525C9.20761 7.7825 9.33511 7.8575 9.38761 7.9025C9.55261 8.0075 9.68011 8.1575 9.83761 8.255C9.99511 8.405 10.1976 8.5025 10.5051 8.5025C10.5351 8.51 10.5576 8.51 10.5876 8.51C10.8951 8.51 11.1426 8.4125 11.3451 8.3075C11.5626 8.21 11.7351 8.06 11.9001 8.0075H11.9076C12.2601 7.91 12.5376 7.7075 12.7026 7.4825C12.9726 8.5475 13.6101 10.0925 14.0151 10.8425C14.4133 11.5483 14.6968 12.3128 14.8551 13.1075C14.9676 13.1 15.1026 13.1225 15.2451 13.1525C15.3051 12.995 15.3426 12.8375 15.3651 12.68C15.5076 11.6375 14.8476 10.6175 14.4726 10.235C14.4501 10.22 14.4276 10.1975 14.4126 10.1825C14.2476 10.0325 14.2401 9.9275 14.3226 9.9275C14.3751 9.9725 14.4201 10.0175 14.4726 10.07C14.8926 10.505 15.3801 11.2025 15.5676 11.9975C15.6276 12.215 15.6576 12.4475 15.6576 12.68C15.6576 12.8675 15.6351 13.0625 15.5901 13.25C15.6351 13.2725 15.6876 13.295 15.7401 13.3025C16.5276 13.7 16.8126 14 16.6776 14.45V14.42H16.5276C16.6401 14.0675 16.3926 13.7975 15.7176 13.505C15.0276 13.205 14.4726 13.25 14.3751 13.85C14.3676 13.88 14.3676 13.9025 14.3601 13.955C14.3076 13.97 14.2551 13.9925 14.2026 14C13.8801 14.2025 13.7001 14.5025 13.6026 14.8925C13.5051 15.29 13.4751 15.755 13.4451 16.295C13.4301 16.5425 13.3176 16.925 13.2051 17.3075C12.7476 17.63 12.2301 17.8775 11.6751 17.9975ZM17.9001 17.615C17.4276 17.915 16.5801 18.1475 16.0401 18.7925C15.5676 19.3475 14.9976 19.6475 14.4951 19.685C13.9926 19.7225 13.5576 19.535 13.3026 19.01H13.2951C13.1376 18.71 13.2051 18.2375 13.3401 17.7425C13.4751 17.24 13.6626 16.73 13.6926 16.3175C13.7151 15.785 13.7451 15.32 13.8351 14.96C13.9326 14.6075 14.0751 14.36 14.3226 14.2175L14.3601 14.2025C14.3826 14.6525 14.6151 15.1325 15.0276 15.2375C15.4701 15.335 16.1151 14.9825 16.3851 14.66L16.5426 14.6525C16.7826 14.645 16.9851 14.66 17.1876 14.855C17.3451 15.005 17.4201 15.2525 17.4876 15.515C17.5551 15.815 17.6076 16.1 17.7951 16.31C18.1701 16.7075 18.2901 16.9925 18.2826 17.165C18.2676 17.3675 18.1401 17.465 17.9001 17.615ZM14.4726 10.235V10.925H14.0601C14.4346 11.6078 14.7027 12.3438 14.8551 13.1075C14.9676 13.1 15.1026 13.1225 15.2451 13.1525C15.3051 12.995 15.3426 12.8375 15.3651 12.68C15.5076 11.6375 14.8476 10.6175 14.4726 10.235Z"
					fill="#B4B4B4"
				/>
				<path
					d="M12.6649 6.72494V6.82994C12.6049 6.72494 12.4924 6.62744 12.2974 6.52994C11.8924 6.37994 11.7199 6.32744 11.4949 6.17744C11.1349 5.92244 10.8274 5.87744 10.5799 5.87744C10.4449 5.87744 10.3249 5.89994 10.2199 5.92244C9.91988 6.02744 9.71738 6.22244 9.58988 6.37994H9.58238C9.55988 6.40244 9.52988 6.42494 9.45488 6.47744C9.37238 6.52244 9.25238 6.62744 9.08738 6.73244C8.93738 6.82994 8.88488 6.97994 8.93738 7.18244C8.98988 7.32494 9.15488 7.52744 9.46238 7.71494C9.64988 7.81244 9.77738 7.96244 9.92738 8.06744C10.0024 8.11994 10.0849 8.16494 10.1749 8.20994C10.2799 8.25494 10.3924 8.26994 10.5124 8.26244C10.8349 8.28494 11.0674 8.16494 11.2774 8.05994C11.4874 7.96244 11.6599 7.81244 11.8699 7.71494C12.2824 7.56494 12.5824 7.31744 12.6724 7.07744C12.7174 6.96494 12.7174 6.83744 12.6649 6.72494ZM10.7674 6.22994C10.7599 6.21494 10.7749 6.19994 10.7824 6.19244C10.8473 6.16617 10.9199 6.16617 10.9849 6.19244C11.0299 6.19244 11.1049 6.24494 11.0974 6.29744C11.0899 6.33494 11.0299 6.34244 10.9924 6.34244C10.9549 6.34244 10.9249 6.31244 10.8874 6.29744C10.8499 6.28244 10.7749 6.28994 10.7674 6.24494V6.22994ZM10.1224 6.19244C10.1824 6.16994 10.2649 6.16244 10.3174 6.19244C10.3324 6.19994 10.3474 6.21494 10.3399 6.22994V6.24494H10.3474C10.3324 6.28994 10.2574 6.28244 10.2199 6.29744C10.1824 6.31244 10.1524 6.34994 10.1149 6.34994C10.0774 6.34994 10.0174 6.33494 10.0099 6.29744C10.0024 6.24494 10.0774 6.19244 10.1224 6.19244ZM12.1099 7.08494C11.9674 7.18244 11.7574 7.33244 11.5849 7.43744C11.2549 7.58744 10.8649 7.83494 10.4524 7.83494C10.0474 7.83494 9.71738 7.63244 9.48488 7.48244C9.36488 7.38494 9.27488 7.28744 9.19988 7.23494C9.07988 7.13744 9.09488 6.98744 9.14738 6.98744C9.22988 6.99494 9.24488 7.08494 9.29738 7.13744C9.37238 7.18244 9.46238 7.28744 9.56738 7.38494C9.79238 7.53494 10.0849 7.73744 10.4524 7.73744C10.8199 7.73744 11.2549 7.53494 11.5174 7.38494C11.6599 7.28744 11.8549 7.13744 12.0049 7.03244C12.1249 6.93494 12.1174 6.83744 12.2224 6.83744C12.3199 6.84494 12.2449 6.93494 12.1099 7.08494ZM18.2824 17.1649C18.2674 17.3674 18.1399 17.4649 17.8999 17.6149C17.4274 17.9149 16.5799 18.1474 16.0399 18.7924C15.5674 19.3474 14.9974 19.6474 14.4949 19.6849C13.9924 19.7224 13.5574 19.5349 13.3024 19.0099H13.2949C13.1374 18.7099 13.2049 18.2374 13.3399 17.7424C13.4749 17.2399 13.6624 16.7299 13.6924 16.3174C13.7149 15.7849 13.7449 15.3199 13.8349 14.9599C13.9324 14.6074 14.0749 14.3599 14.3224 14.2174L14.3599 14.2024C14.3824 14.6524 14.6149 15.1324 15.0274 15.2374C15.4699 15.3349 16.1149 14.9824 16.3849 14.6599L16.5424 14.6524C16.7824 14.6449 16.9849 14.6599 17.1874 14.8549C17.3449 15.0049 17.4199 15.2524 17.4874 15.5149C17.5549 15.8149 17.6074 16.0999 17.7949 16.3099C18.1699 16.7074 18.2899 16.9924 18.2824 17.1649ZM9.20738 18.4699V18.4774C9.16238 19.0324 8.83988 19.3324 8.35238 19.4449C7.85738 19.5424 7.19738 19.4449 6.52988 19.0999C5.80238 18.6949 4.92488 18.7474 4.36988 18.6424C4.08488 18.5974 3.90488 18.4924 3.81488 18.3424C3.73238 18.1924 3.73238 17.8924 3.91238 17.4199C4.00238 17.1649 3.93488 16.8499 3.88988 16.5799C3.85238 16.2799 3.82988 16.0474 3.92738 15.8749C4.04738 15.6199 4.22738 15.5749 4.44488 15.4699C4.66988 15.3724 4.93238 15.3199 5.14238 15.1249V15.1174C5.33738 14.9149 5.47988 14.6674 5.65238 14.4874C5.79488 14.3374 5.93738 14.2399 6.15488 14.2399H6.16238C6.19988 14.2399 6.23738 14.2399 6.28238 14.2474C6.56738 14.2924 6.81488 14.5024 7.05488 14.8099L7.74488 16.0624H7.75238C7.93238 16.4599 8.32238 16.8649 8.65238 17.2924C8.98238 17.7424 9.23738 18.1399 9.20738 18.4699Z"
					fill="white"
				/>
				<path
					d="M10.3476 6.24514C10.3326 6.29014 10.2576 6.28264 10.2201 6.29764C10.1826 6.31264 10.1526 6.35014 10.1151 6.35014C10.0776 6.35014 10.0176 6.33514 10.0101 6.29764C10.0026 6.24514 10.0776 6.19264 10.1226 6.19264C10.1826 6.17014 10.2651 6.16264 10.3176 6.19264C10.3326 6.20014 10.3476 6.21514 10.3401 6.23014V6.24514H10.3476ZM12.1101 7.08514C11.9676 7.18264 11.7576 7.33264 11.5851 7.43764C11.2551 7.58764 10.8651 7.83514 10.4526 7.83514C10.0476 7.83514 9.71762 7.63264 9.48512 7.48264C9.36512 7.38514 9.27512 7.28764 9.20012 7.23514C9.08012 7.13764 9.09512 6.98764 9.14762 6.98764C9.23012 6.99514 9.24512 7.08514 9.29762 7.13764C9.37262 7.18264 9.46262 7.28764 9.56762 7.38514C9.79262 7.53514 10.0851 7.73764 10.4526 7.73764C10.8201 7.73764 11.2551 7.53514 11.5176 7.38514C11.6601 7.28764 11.8551 7.13764 12.0051 7.03264C12.1251 6.93514 12.1176 6.83764 12.2226 6.83764C12.3201 6.84514 12.2451 6.93514 12.1101 7.08514Z"
					fill="#B4B4B4"
				/>
			</svg>
		</Box>
	);
};

const MenuItemMacOsIntel = () => {
	const langStringPageBuildsMacosDownload = useLangString("install_page.desktop.page.builds.macos_download");
	const langStringPageBuildsIntelVersion = useLangString("install_page.desktop.page.builds.intel_version");

	return (
		<MenuItem id={DESKTOP_PLATFORM_MAC_OS_INTEL}>
			<HStack w="100%" justifyContent="space-between">
				<HStack gap="8px">
					<MacOsIcon />
					<Text style="inter_18_27_400" color="333e49">
						{langStringPageBuildsMacosDownload}
					</Text>
				</HStack>
				<Text px="8px" py="4px" bgColor="103115128.01" rounded="8px" color="677380" style="inter_18_22_400">
					{langStringPageBuildsIntelVersion}
				</Text>
			</HStack>
		</MenuItem>
	);
};

const MenuItemMacOsM1M2 = () => {
	const langStringPageBuildsMacosDownload = useLangString("install_page.desktop.page.builds.macos_download");
	const langStringPageBuildsM1M2Version = useLangString("install_page.desktop.page.builds.m1m2_version");

	return (
		<MenuItem id={DESKTOP_PLATFORM_MAC_OS_ARM}>
			<HStack w="100%" justifyContent="space-between">
				<HStack gap="8px">
					<MacOsIcon />
					<Text style="inter_18_27_400" color="333e49">
						{langStringPageBuildsMacosDownload}
					</Text>
				</HStack>
				<Text px="8px" py="4px" bgColor="103115128.01" rounded="8px" color="677380" style="inter_18_22_400">
					{langStringPageBuildsM1M2Version}
				</Text>
			</HStack>
		</MenuItem>
	);
};

const MenuItemLinuxDeb = () => {
	const langStringPageBuildsLinuxDownload = useLangString("install_page.desktop.page.builds.linux_download");
	const langStringPageBuildsDebVersion = useLangString("install_page.desktop.page.builds.deb_version");

	return (
		<MenuItem id={DESKTOP_PLATFORM_LINUX_DEB}>
			<HStack w="100%" justifyContent="space-between">
				<HStack gap="8px">
					<LinuxDebIcon />
					<Text style="inter_18_27_400" color="333e49">
						{langStringPageBuildsLinuxDownload}
					</Text>
				</HStack>
				<Text px="8px" py="4px" bgColor="103115128.01" rounded="8px" color="677380" style="inter_18_22_400">
					{langStringPageBuildsDebVersion}
				</Text>
			</HStack>
		</MenuItem>
	);
};

const MenuItemLinuxTar = () => {
	const langStringPageBuildsLinuxDownload = useLangString("install_page.desktop.page.builds.linux_download");
	const langStringPageBuildsTarVersion = useLangString("install_page.desktop.page.builds.tar_version");

	return (
		<MenuItem id={DESKTOP_PLATFORM_LINUX_TAR}>
			<HStack w="100%" justifyContent="space-between">
				<HStack gap="8px">
					<LinuxTarIcon />
					<Text style="inter_18_27_400" color="333e49">
						{langStringPageBuildsLinuxDownload}
					</Text>
				</HStack>
				<Text px="8px" py="4px" bgColor="103115128.01" rounded="8px" color="677380" style="inter_18_22_400">
					{langStringPageBuildsTarVersion}
				</Text>
			</HStack>
		</MenuItem>
	);
};

type DownloadButtonProps = {
	icon: string;
	desc: string;
	platform: string;
	secondPlatform?: string;
	downloadLink: string;
	menuItems?: JSX.Element;
	onSelectHandler?: (value: string) => void;
};

const DownloadButton = ({
	icon,
	desc,
	platform,
	secondPlatform,
	downloadLink,
	menuItems,
	onSelectHandler,
}: DownloadButtonProps) => {
	if (secondPlatform !== undefined && menuItems !== undefined && onSelectHandler !== undefined) {
		return (
			<OnpremiseInstallDownloadMenuDesktop
				triggerEl={
					<Center
						w="100%"
						h="206px"
						bgColor="rgba(255, 255, 255, 0.4)"
						cursor="pointer"
						flexDirection="column"
						rounded="14px"
						_hover={{
							bgColor: "rgba(255, 255, 255, 0.8)",
						}}
					>
						<Icon width="76px" height="76px" avatar={icon} />
						<Text mt="12px" style="inter_18_25_400" textAlign="center">
							{desc}
						</Text>
						<HStack gap="8px">
							<Text
								mt="15px"
								bgColor="rgba(103, 115, 128, 0.1)"
								padding="4px 8px"
								style="inter_18_22_400"
								color="677380"
								rounded="8px"
							>
								{platform}
							</Text>
							<Text
								mt="15px"
								bgColor="rgba(103, 115, 128, 0.1)"
								padding="4px 8px"
								style="inter_18_22_400"
								color="677380"
								rounded="8px"
							>
								{secondPlatform}
							</Text>
						</HStack>
					</Center>
				}
				menuItems={menuItems}
				onSelectHandler={onSelectHandler}
			/>
		);
	}

	return (
		<Center
			w="100%"
			h="206px"
			bgColor="rgba(255, 255, 255, 0.4)"
			cursor="pointer"
			flexDirection="column"
			rounded="14px"
			onClick={() => (window.location.href = downloadLink)}
			_hover={{
				bgColor: "rgba(255, 255, 255, 0.8)",
			}}
		>
			<Icon width="76px" height="76px" avatar={icon} />
			<Text mt="12px" style="inter_18_25_400" textAlign="center">
				{desc}
			</Text>
			<Text
				mt="15px"
				bgColor="rgba(103, 115, 128, 0.1)"
				padding="4px 8px"
				style="inter_18_22_400"
				color="677380"
				rounded="8px"
			>
				{platform}
			</Text>
		</Center>
	);
};

export default function PageInstallDesktop() {
	const langStringDesktopLogoTitle = useLangString("install_page.desktop.logo.title");
	const langStringDesktopLogoOnpremiseTitle = useLangString("install_page.desktop.logo.onpremise_title");
	const langStringPageTitle = useLangString("install_page.desktop.page.title");
	const langStringPageDesc = useLangString("install_page.desktop.page.desc");
	const langStringPageDownloadMacDesc = useLangString("install_page.desktop.page.download_mac.desc");
	const langStringPageDownloadMacPlatformIntel = useLangString(
		"install_page.desktop.page.download_mac.platform_intel"
	);
	const langStringPageDownloadMacPlatformApple = useLangString(
		"install_page.desktop.page.download_mac.platform_apple"
	);
	const langStringPageDownloadWinDesc = useLangString("install_page.desktop.page.download_win.desc");
	const langStringPageDownloadWinPlatformExe = useLangString("install_page.desktop.page.download_win.platform_exe");
	const langStringPageDownloadLinuxDesc = useLangString("install_page.desktop.page.download_linux.desc");
	const langStringPageDownloadLinuxPlatformDeb = useLangString(
		"install_page.desktop.page.download_linux.platform_deb"
	);
	const langStringPageDownloadLinuxPlatformTar = useLangString(
		"install_page.desktop.page.download_linux.platform_tar"
	);
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
	const langStringPageSupportBlockWhatsapp = useLangString("install_page.desktop.page.support_block.whatsapp");
	const langStringPageSupportBlockTelegram = useLangString("install_page.desktop.page.support_block.telegram");
	const langStringPageSupportBlockMail = useLangString("install_page.desktop.page.support_block.mail");
	const { getDownloadLink } = useDownloadLink();

	const onSelectPlatform = useCallback((value: string) => {
		window.location.href = getDownloadLink(value);
	}, []);

	return (
		<>
			<LandingBackground />
			<VStack w="100%" minHeight="100vh" gap="0">
				<HStack w="100%" justifyContent="start" pt="24px" px="32px">
					<HStack
						gap="12px"
						onClick={() => (window.location.href = "https://getcompass.ru/on-premise")}
						cursor="pointer"
					>
						<Icon width="32px" height="32px" avatar={CompassLogo32} />
						<HStack>
							<Text textTransform="uppercase" style="lato_15_21_700">
								{langStringDesktopLogoTitle}
							</Text>
							<Box w="1px" h="14px" bgColor="2d343c" opacity="30%" />
							<Text style="inter_15_21_400">{langStringDesktopLogoOnpremiseTitle}</Text>
						</HStack>
					</HStack>
				</HStack>
				<VStack mt="50px" w="100%" gap="0" px="32px">
					<Icon width="80px" height="80px" avatar={CompassLogo80} />
					<VStack pt="24px" gap="0">
						<Text style="inter_40_48_700" letterSpacing="-0.5px" textAlign="center">
							{langStringPageTitle}
						</Text>
						<Text pt="16px" style="inter_20_28_400" textAlign="center">
							{langStringPageDesc.split("\n").map((line, index) => (
								<div key={index}>{line}</div>
							))}
						</Text>
					</VStack>
					<HStack w="100%" maxWidth="1008px" justify="center" mt="54px" gap="16px" userSelect="none">
						<DownloadButton
							icon={IconDownloadButtonApple}
							desc={langStringPageDownloadMacDesc}
							platform={langStringPageDownloadMacPlatformIntel}
							secondPlatform={langStringPageDownloadMacPlatformApple}
							downloadLink={getDownloadLink(DESKTOP_PLATFORM_MAC_OS_INTEL)}
							menuItems={
								<MenuItemGroup id="macos_builds">
									<MenuItemMacOsIntel />
									<MenuItemMacOsM1M2 />
								</MenuItemGroup>
							}
							onSelectHandler={onSelectPlatform}
						/>
						<DownloadButton
							icon={IconDownloadButtonWin}
							desc={langStringPageDownloadWinDesc}
							platform={langStringPageDownloadWinPlatformExe}
							downloadLink={getDownloadLink(DESKTOP_PLATFORM_WINDOWS)}
						/>
						<DownloadButton
							icon={IconDownloadButtonLinux}
							desc={langStringPageDownloadLinuxDesc}
							platform={langStringPageDownloadLinuxPlatformDeb}
							secondPlatform={langStringPageDownloadLinuxPlatformTar}
							downloadLink={getDownloadLink(DESKTOP_PLATFORM_LINUX_DEB)}
							menuItems={
								<MenuItemGroup id="linux_builds">
									<MenuItemLinuxDeb />
									<MenuItemLinuxTar />
								</MenuItemGroup>
							}
							onSelectHandler={onSelectPlatform}
						/>
					</HStack>
					<HStack w="100%" justify="center" mt="16px" gap="16px" userSelect="none" maxWidth="1008px">
						<DownloadButton
							icon={IconDownloadButtonAppStore}
							desc={langStringPageDownloadIosDesc}
							platform={langStringPageDownloadIosPlatformAppStore}
							downloadLink={getDownloadLink(MOBILE_PLATFORM_IOS)}
						/>
						<DownloadButton
							icon={IconDownloadButtonGooglePlay}
							desc={langStringPageDownloadAndroidDesc}
							platform={langStringPageDownloadAndroidPlatformGooglePlay}
							downloadLink={getDownloadLink(MOBILE_PLATFORM_ANDROID)}
						/>
						<DownloadButton
							icon={IconDownloadButtonAppGallery}
							desc={langStringPageDownloadHuaweiDesc}
							platform={langStringPageDownloadHuaweiPlatformAppGallery}
							downloadLink={getDownloadLink(MOBILE_PLATFORM_HUAWEI)}
						/>
					</HStack>
					<VStack
						width="100%"
						maxWidth="1008px"
						mt="54px"
						padding="64px 64px 54px 64px"
						gap="0"
						rounded="40px"
						bgColor="255255255.03"
					>
						<HStack gap="0px">
							<Box
								w="90px"
								h="90px"
								bgPosition="center"
								bgSize="cover"
								bgRepeat="no-repeat"
								flexShrink="0"
								style={{
									backgroundImage: `url(${Avatar1})`,
								}}
							/>
							<Box
								w="90px"
								h="90px"
								bgPosition="center"
								bgSize="cover"
								bgRepeat="no-repeat"
								flexShrink="0"
								marginLeft="-24px"
								style={{
									backgroundImage: `url(${Avatar2})`,
								}}
							/>
							<Box
								w="90px"
								h="90px"
								bgPosition="center"
								bgSize="cover"
								bgRepeat="no-repeat"
								flexShrink="0"
								marginLeft="-24px"
								style={{
									backgroundImage: `url(${Avatar3})`,
								}}
							/>
						</HStack>
						<Text mt="24px" style="inter_40_48_700" letterSpacing="-0.5px" textAlign="center">
							{langStringPageSupportBlockTitle}
						</Text>
						<Text mt="16px" style="inter_20_28_400" textAlign="center">
							{langStringPageSupportBlockDesc.split("\n").map((line, index) => (
								<div key={index}>{line}</div>
							))}
						</Text>
						<HStack mt="24px" gap="16px">
							<styled.a href="https://wa.me/message/CJINDDW52XJYM1">
								<HStack
									className="group"
									cursor="pointer"
									gap="6px"
									border="1px solid rgba(103, 115, 128, 0.3)"
									rounded="12px"
									p="15px 31px"
									_hover={{ border: "1px solid rgba(103, 115, 128, 0.6)" }}
								>
									<Icon width="24px" height="24px" avatar={WhatsappIcon24} />
									<Text color="677380" style="inter_20_24_500" _groupHover={{ color: "333e49" }}>
										{langStringPageSupportBlockWhatsapp}
									</Text>
								</HStack>
							</styled.a>
							<styled.a href="https://t.me/getcompass">
								<HStack
									className="group"
									cursor="pointer"
									gap="6px"
									border="1px solid rgba(103, 115, 128, 0.3)"
									rounded="12px"
									p="15px 31px"
									_hover={{ border: "1px solid rgba(103, 115, 128, 0.6)" }}
								>
									<Icon width="24px" height="24px" avatar={TelegramIcon24} />
									<Text color="677380" style="inter_20_24_500" _groupHover={{ color: "333e49" }}>
										{langStringPageSupportBlockTelegram}
									</Text>
								</HStack>
							</styled.a>
						</HStack>
						<styled.a href={`mailto:${langStringPageSupportBlockMail}`}>
							<HStack mt="24px" cursor="pointer" gap="10px">
								<Icon width="26px" height="26px" avatar={MailIcon26} />
								<Text color="009fe6" style="inter_20_24_500">
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
