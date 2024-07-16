import { getDeeplinkUrlScheme } from "../private/custom.ts";
import UAParser from "ua-parser-js";
import {
	SUPPORTED_DESKTOP_CHROME_VERSION,
	SUPPORTED_DESKTOP_EDGE_VERSION,
	SUPPORTED_DESKTOP_FIREFOX_VERSION,
	SUPPORTED_DESKTOP_SAFARI_VERSION,
	SUPPORTED_MOBILE_ANDROID_CHROME_VERSION,
	SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION,
	SUPPORTED_MOBILE_IOS_SAFARI_VERSION,
} from "../api/_types.ts";

export function getDeeplink(link: string): string {
	return `${getDeeplinkUrlScheme()}action?type=joinConference&link=${link}`;
}

export function openDeepLink(isMobile: boolean, deepLinkUrl: string) {
	const parser = new UAParser(navigator.userAgent);
	const browser = parser.getBrowser();
	const isSafari = browser.name?.includes("Safari");

	if (isMobile) {
		window.location.assign(deepLinkUrl);
		return;
	}

	if (isSafari) {
		let iframe = document.createElement("iframe");
		iframe.src = deepLinkUrl;
		iframe.style.display = "none";
		document.body.appendChild(iframe);
		setTimeout(() => document.body.removeChild(iframe), 100);
		return;
	}

	window.location.assign(deepLinkUrl);
}

export function isReceivedCodecsSupported(): boolean {
	// на firefox <112 падает ошибка undefined function
	if (typeof RTCRtpReceiver.getCapabilities === "undefined" || typeof RTCRtpReceiver.getCapabilities !== "function") {
		return true;
	}

	const video = RTCRtpReceiver.getCapabilities("video");
	if (video === null) {
		return false;
	}

	let isSupported = false;
	const codecs = video.codecs;
	codecs.forEach((codec) => {
		if (
			codec.mimeType.toLowerCase() === "video/H264".toLowerCase() ||
			codec.mimeType.toLowerCase() === "video/VP9".toLowerCase()
		) {
			isSupported = true;
		}
	});

	return isSupported;
}

export function isSenderCodecsSupported(): boolean {
	// на firefox <112 падает ошибка undefined function
	if (typeof RTCRtpSender.getCapabilities === "undefined" || typeof RTCRtpSender.getCapabilities !== "function") {
		return true;
	}

	const video = RTCRtpSender.getCapabilities("video");
	if (video === null) {
		return false;
	}

	let isSupported = false;
	const codecs = video.codecs;
	codecs.forEach((codec) => {
		if (
			codec.mimeType.toLowerCase() === "video/H264".toLowerCase() ||
			codec.mimeType.toLowerCase() === "video/VP9".toLowerCase()
		) {
			isSupported = true;
		}
	});

	return isSupported;
}

export function isUnsupportedDesktopBrowser(): boolean {
	const userAgent = navigator.userAgent;
	const isChrome = /Chrome/.test(userAgent) && /Google Inc/.test(navigator.vendor);
	const isFirefox = /Firefox/.test(userAgent);
	const isSafari = /Safari/.test(userAgent) && /Apple Computer/.test(navigator.vendor);
	const isEdge = /Edg/.test(userAgent);

	const getVersion = (pattern: RegExp) => {
		const match = userAgent.match(pattern);
		return match ? parseInt(match[1], 10) : 0;
	};

	const chromeVersion = isChrome ? getVersion(/Chrome\/(\d+)/) : 0;
	const firefoxVersion = isFirefox ? getVersion(/Firefox\/(\d+)/) : 0;
	const safariVersion = isSafari ? getVersion(/Version\/(\d+)/) : 0;
	const edgeVersion = isEdge ? getVersion(/Edg\/(\d+)/) : 0;

	let isSupported =
		(isChrome && chromeVersion >= SUPPORTED_DESKTOP_CHROME_VERSION) ||
		(isFirefox && firefoxVersion >= SUPPORTED_DESKTOP_FIREFOX_VERSION) ||
		(isSafari && safariVersion >= SUPPORTED_DESKTOP_SAFARI_VERSION) ||
		(isEdge && edgeVersion >= SUPPORTED_DESKTOP_EDGE_VERSION);

	if (isSupported) {
		if (!isReceivedCodecsSupported() || !isSenderCodecsSupported()) {
			isSupported = false;
		}
	}

	return !isSupported;
}

export function isIos(): boolean {
	const userAgent = navigator.userAgent;
	return /iPhone|iPad|iPod/.test(userAgent);
}

export function isUnsupportedMobileBrowser(): boolean {
	const userAgent = navigator.userAgent;
	const isIOS = isIos();
	const isAndroid = /Android/.test(userAgent);

	const isChrome =
		(/Chrome/.test(userAgent) && /Google Inc/.test(navigator.vendor)) || (isIOS && /CriOS\/(\d+)/.test(userAgent));
	const isFirefox = /Firefox/.test(userAgent);
	const isSafari = /Safari/.test(userAgent);
	const isEdge = /Edg/.test(userAgent);

	const getVersion = (pattern: RegExp) => {
		const match = userAgent.match(pattern);
		return match ? parseInt(match[1], 10) : 0;
	};

	const getIosVersion = () => {
		const iosVersionPattern = /iPhone OS (\d+_\d+(_\d+)?)/;
		const chromeVersionPattern = /CriOS\/(\d+)/;
		const safariVersionPattern = /Version\/(\d+)/;
		const fireFoxVersionPattern = /FxiOS\/(\d+)/;

		const iosMatch = userAgent.match(iosVersionPattern);
		const chromeMatch = userAgent.match(chromeVersionPattern);
		const safariMatch = userAgent.match(safariVersionPattern);
		const fireFoxMatch = userAgent.match(fireFoxVersionPattern);

		if (iosMatch !== null && (chromeMatch || safariMatch || fireFoxMatch)) {
			return parseFloat(iosMatch[1].replace("_", "."));
		} else {
			return 0;
		}
	};

	let isSupported = false;

	if (isIOS) {
		let iosVersion = getIosVersion();
		if (iosVersion < 1) {
			iosVersion = getVersion(/Version\/(\d+(\.\d+)?)/);
		}
		isSupported =
			(isChrome && iosVersion >= SUPPORTED_MOBILE_IOS_SAFARI_VERSION) ||
			(isFirefox && iosVersion >= SUPPORTED_MOBILE_IOS_SAFARI_VERSION) ||
			(isSafari && iosVersion >= SUPPORTED_MOBILE_IOS_SAFARI_VERSION) ||
			(isEdge && iosVersion >= SUPPORTED_MOBILE_IOS_SAFARI_VERSION);
	} else if (isAndroid) {
		const chromeVersion = isChrome ? getVersion(/Chrome\/(\d+)/) : 0;
		const firefoxVersion = isFirefox ? getVersion(/Firefox\/(\d+)/) : 0;

		isSupported =
			(isChrome && chromeVersion >= SUPPORTED_MOBILE_ANDROID_CHROME_VERSION) ||
			(isFirefox && firefoxVersion >= SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION);
	}

	if (isSupported) {
		if (!isReceivedCodecsSupported() || !isSenderCodecsSupported()) {
			isSupported = false;
		}
	}

	return !isSupported && isUnsupportedDesktopBrowser();
}
