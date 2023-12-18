import {
	isMobile,
	isMobileHuawei,
	isMobileApple,
	isMobileAndroid,
	isSafariDesktop,
	hasVendor,
} from "./deviceDetect";

export default function useDeviceDetect() {

	return {
		isMobile: isMobile(),
		isMobileHuawei: isMobileHuawei(),
		isMobileApple: isMobileApple(),
		isMobileAndroid: isMobileAndroid(),
		isSafariDesktop: isSafariDesktop(),
		hasVendor: hasVendor(),
	}
}
