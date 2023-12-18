import UAParser from "ua-parser-js";

const parser = new UAParser(window.navigator.userAgent);

export function isMobile(): boolean {

	const device = parser.getDevice().type;
	return device === "mobile" || device === "tablet";
}

export function isMobileHuawei(): boolean {
	return isMobile() && parser.getDevice().vendor === "Huawei";
}

export function isMobileApple(): boolean {
	return isMobile() && parser.getDevice().vendor === "Apple";
}

export function isMobileAndroid(): boolean {
	return isMobile() && !isMobileHuawei() && parser.getOS().name === "Android";
}

export function isSafariDesktop(): boolean {
	return !isMobile() && parser.getBrowser()?.name === "Safari";
}

export function hasVendor(): boolean {
	return !!parser.getBrowser()?.name;
}
