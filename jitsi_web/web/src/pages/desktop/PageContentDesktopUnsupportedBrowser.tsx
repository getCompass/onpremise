import UnsupportedBrowserIcon84Svg from "../../img/desktop/UnsupportedBrowserIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack, styled } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { Button } from "../../components/button.tsx";
import { useCallback, useEffect } from "react";
import { conferenceDataState } from "../../api/_stores.ts";
import { useAtomValue } from "jotai";
import { getDeeplink, openDeepLink } from "../../lib/functions.ts";
import {
	Popover,
	PopoverArrow,
	PopoverArrowTip,
	PopoverContent,
	PopoverPositioner,
	PopoverTrigger,
} from "../../components/popover.tsx";
import { css } from "../../../styled-system/css";
import {
	SUPPORTED_DESKTOP_CHROME_VERSION,
	SUPPORTED_DESKTOP_EDGE_VERSION,
	SUPPORTED_DESKTOP_FIREFOX_VERSION,
	SUPPORTED_DESKTOP_SAFARI_VERSION,
} from "../../api/_types.ts";

const PageContentDesktopUnsupportedBrowser = () => {
	const langStringDesktopUnsupportedBrowserTitle = useLangString("desktop.unsupported_browser.title");
	const langStringDesktopUnsupportedBrowserDesc = useLangString("desktop.unsupported_browser.desc");
	const langStringDesktopUnsupportedBrowserOpenCompassButton = useLangString(
		"desktop.unsupported_browser.open_compass_button"
	);
	const langStringDesktopUnsupportedBrowserSupportedBrowsersButton = useLangString(
		"desktop.unsupported_browser.supported_browsers_button"
	);
	const langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverTitle = useLangString(
		"desktop.unsupported_browser.supported_browsers_popover.title"
	);
	const langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverSupportedBrowserList = useLangString(
		"desktop.unsupported_browser.supported_browsers_popover.supported_browser_list"
	);
	const langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverDesc = useLangString(
		"desktop.unsupported_browser.supported_browsers_popover.desc"
	);

	const conferenceData = useAtomValue(conferenceDataState);

	// если у пользователя установлено приложение Compass – поверх страницы сразу появляется системное браузерное окно с предложением открыть ссылку в приложении.
	useEffect(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(false, getDeeplink(conferenceData.link));
	}, []);

	const onOpenCompassClickHandler = useCallback(() => {
		if (conferenceData === null) {
			return;
		}

		openDeepLink(false, getDeeplink(conferenceData.link));
	}, [conferenceData?.link]);

	return (
		<VStack minW="942px" mt="122px" px="32px" py="32px" rounded="20px" bgColor="255255255.03" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={UnsupportedBrowserIcon84Svg} />
			</Center>
			<Text style="inter_40_48_700" letterSpacing="-0.3px" mt="12px" textAlign="center">
				{langStringDesktopUnsupportedBrowserTitle}
			</Text>
			<Text style="inter_20_28_400" mt="16px" textAlign="center">
				{langStringDesktopUnsupportedBrowserDesc.split("\n").map((line, index) => (
					<div key={index}>{line}</div>
				))}
			</Text>
			<Button
				minW="370px"
				size="px32py16"
				textSize="inter_20_24_500"
				mt="32px"
				rounded="12px"
				onClick={() => onOpenCompassClickHandler()}
			>
				{langStringDesktopUnsupportedBrowserOpenCompassButton}
			</Button>
			<Popover positioning={{ placement: "top", offset: { mainAxis: 7 } }} type="desktop">
				<PopoverTrigger asChild>
					<Button textSize="inter_16_22_400" mt="20px" color="2574a9">
						{langStringDesktopUnsupportedBrowserSupportedBrowsersButton}
					</Button>
				</PopoverTrigger>
				<PopoverPositioner>
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
								{langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverTitle}
							</Text>
							<Text style="lato_15_20_400">
								{langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverSupportedBrowserList
									.replace(
										"$SUPPORTED_DESKTOP_CHROME_VERSION",
										String(SUPPORTED_DESKTOP_CHROME_VERSION)
									)
									.replace(
										"$SUPPORTED_DESKTOP_FIREFOX_VERSION",
										String(SUPPORTED_DESKTOP_FIREFOX_VERSION)
									)
									.replace(
										"$SUPPORTED_DESKTOP_SAFARI_VERSION",
										String(SUPPORTED_DESKTOP_SAFARI_VERSION)
									)
									.replace("$SUPPORTED_DESKTOP_EDGE_VERSION", String(SUPPORTED_DESKTOP_EDGE_VERSION))
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
								{langStringDesktopUnsupportedBrowserSupportedBrowsersPopoverDesc}&nbsp;
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

export default PageContentDesktopUnsupportedBrowser;
