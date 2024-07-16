import UnsupportedBrowserIcon84Svg from "../../img/mobile/UnsupportedBrowserIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Box, Center, styled, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import { useAtomValue } from "jotai";
import { conferenceDataState } from "../../api/_stores.ts";
import { useCallback } from "react";
import { getDeeplink, isIos, openDeepLink } from "../../lib/functions.ts";
import {
	Popover,
	PopoverAnchor,
	PopoverArrow,
	PopoverArrowTip,
	PopoverContent,
	PopoverPositioner,
	PopoverTrigger,
} from "../../components/popover.tsx";
import { css } from "../../../styled-system/css";
import {
	SUPPORTED_MOBILE_ANDROID_CHROME_VERSION,
	SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION,
	SUPPORTED_MOBILE_IOS_SAFARI_VERSION,
} from "../../api/_types.ts";

const PageContentMobileUnsupportedBrowser = () => {
	const langStringMobileUnsupportedBrowserTitle = useLangString("mobile.unsupported_browser.title");
	const langStringMobileUnsupportedBrowserDesc = useLangString("mobile.unsupported_browser.desc");
	const langStringMobileUnsupportedBrowserOpenCompassButton = useLangString(
		"mobile.unsupported_browser.open_compass_button"
	);
	const langStringMobileUnsupportedBrowserSupportedBrowsersButton = useLangString(
		"mobile.unsupported_browser.supported_browsers_button"
	);
	const langStringMobileUnsupportedBrowserSupportedBrowsersPopoverTitle = useLangString(
		"mobile.unsupported_browser.supported_browsers_popover.title"
	);
	const langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverSupportedBrowserList = useLangString(
		isIos()
			? "mobile.unsupported_browser.supported_browsers_popover.ios_supported_browser_list"
			: "mobile.unsupported_browser.supported_browsers_popover.android_supported_browser_list"
	);
	const langStringMobileUnsupportedBrowserSupportedBrowsersPopoverDesc = useLangString(
		"mobile.unsupported_browser.supported_browsers_popover.desc"
	);

	const conferenceData = useAtomValue(conferenceDataState);

	const onOpenCompassClickHandler = useCallback(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(true, getDeeplink(conferenceData.link));
	}, [conferenceData]);

	return (
		<VStack w="100%" mt="98px" pt="32px" pb="24px" rounded="16px" bgColor="255255255.04" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={UnsupportedBrowserIcon84Svg} />
			</Center>
			<Text style="inter_24_34_700" mt="16px" textAlign="center" color="333e49" px="24px">
				{langStringMobileUnsupportedBrowserTitle}
			</Text>
			<Text style="inter_18_25_400" mt="8px" textAlign="center" color="333e49" px="20px">
				{langStringMobileUnsupportedBrowserDesc}
			</Text>
			<Popover positioning={{ placement: "top", offset: { mainAxis: 7 }, sameWidth: true }} type="mobile">
				<PopoverAnchor asChild>
					<Box w="100%" px="24px" mt="24px">
						<Button
							size="px24py12full"
							textSize="inter_18_27_600"
							rounded="12px"
							onClick={() => onOpenCompassClickHandler()}
						>
							{langStringMobileUnsupportedBrowserOpenCompassButton}
						</Button>
					</Box>
				</PopoverAnchor>
				<PopoverTrigger asChild>
					<Button mt="20px" textSize="inter_16_22_400" color="2574a9">
						{langStringMobileUnsupportedBrowserSupportedBrowsersButton}
					</Button>
				</PopoverTrigger>
				<PopoverPositioner px="24px">
					<PopoverContent>
						<PopoverArrow
							className={css({
								"--arrow-size": "7px",
							})}
						>
							<PopoverArrowTip
								className={css({
									"--arrow-background": "white",
								})}
							/>
						</PopoverArrow>
						<VStack alignItems="start" gap="20px">
							<Text style="lato_15_20_700">
								{langStringMobileUnsupportedBrowserSupportedBrowsersPopoverTitle}
							</Text>
							<Text style="lato_15_20_400">
								{langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverSupportedBrowserList
									.replace(
										"$SUPPORTED_MOBILE_IOS_SAFARI_VERSION",
										String(SUPPORTED_MOBILE_IOS_SAFARI_VERSION)
									)
									.replace(
										"$SUPPORTED_MOBILE_IOS_SAFARI_VERSION",
										String(SUPPORTED_MOBILE_IOS_SAFARI_VERSION)
									)
									.replace(
										"$SUPPORTED_MOBILE_IOS_SAFARI_VERSION",
										String(SUPPORTED_MOBILE_IOS_SAFARI_VERSION)
									)
									.replace(
										"$SUPPORTED_MOBILE_IOS_SAFARI_VERSION",
										String(SUPPORTED_MOBILE_IOS_SAFARI_VERSION)
									)
									.replace(
										"$SUPPORTED_MOBILE_ANDROID_CHROME_VERSION",
										String(SUPPORTED_MOBILE_ANDROID_CHROME_VERSION)
									)
									.replace(
										"$SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION",
										String(SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION)
									)
									.split("\n")
									.map((line, index) => (
										<styled.li
											className={css({
												listStyleType: "none",
												position: "relative",
												paddingLeft: "22px",
												"&::before": {
													content: '"•"',
													position: "absolute",
													left: "6px",
													fontSize: "16px",
													lineHeight: 1.25,
													color: "black",
												},
											})}
											key={index}
										>
											{line}
										</styled.li>
									))}
							</Text>
							<Text style="lato_15_20_400">
								{langStringMobileUnsupportedBrowserSupportedBrowsersPopoverDesc}&nbsp;
								<styled.a
									href="mailto:support@getcompass.ru"
									textDecoration="underline"
									textDecorationSkipInk="none"
									textDecorationThickness="1px"
									textUnderlineOffset="4px"
								>
									support@getcompass.ru
								</styled.a>
							</Text>
						</VStack>
					</PopoverContent>
				</PopoverPositioner>
			</Popover>
		</VStack>
	);
};

export default PageContentMobileUnsupportedBrowser;
