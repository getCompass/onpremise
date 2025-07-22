import { useState, useEffect } from "react";
import UAParser from "ua-parser-js";

// Функция инициализации парсера
const useDeviceDetect = () => {
	const [deviceInfo, setDeviceInfo] = useState({
		isMobile: false,
		isAndroid: false,
		isIPad: false,
		isMobileHuawei: false,
		isMobileApple: false,
		isMobileAndroid: false,
		isSafariDesktop: false,
		os: "",
		isDesktopMacOs: false,
		isDesktopWindows: false,
		isDesktopLinux: false,
		isBrowserFirefox: false,
	});

	useEffect(() => {
		const parser = new UAParser(navigator.userAgent);

		const device = parser.getDevice();
		const os = parser.getOS()?.name?.toLowerCase() ?? "";
		const browserName = parser.getBrowser()?.name;

		const isMobile = device.type === "mobile" || device.type === "tablet";
		const isAndroid = os === "android";
		const isIPad = device.model?.includes("iPad") ?? false;
		const isMobileHuawei = isMobile && device.vendor === "Huawei";
		const isMobileApple = isMobile && device.vendor === "Apple";
		const isMobileAndroid = isMobile && !isMobileHuawei && isAndroid;
		const isSafariDesktop = !isMobile && browserName === "Safari";
		const isDesktopMacOs = !isMobile && os.includes("mac os");
		const isDesktopWindows = !isMobile && os.includes("window");
		const isDesktopLinux = !isMobile && !isDesktopWindows && !isDesktopMacOs;
		const isBrowserFirefox = browserName === "Firefox";

		setDeviceInfo({
			isMobile,
			isAndroid,
			isIPad,
			isMobileHuawei,
			isMobileApple,
			isMobileAndroid,
			isSafariDesktop,
			os,
			isDesktopMacOs,
			isDesktopWindows,
			isDesktopLinux,
			isBrowserFirefox,
		});
	}, []);

	return deviceInfo;
};

export default useDeviceDetect;
