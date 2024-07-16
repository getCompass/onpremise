import ConferenceNotFoundIcon84Svg from "../../img/desktop/ConferenceNotFoundIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import { useMemo } from "react";
import { plural } from "../../lib/plural.ts";
import dayjs from "dayjs";
import { limitNextAttemptState } from "../../api/_stores.ts";
import { useAtomValue } from "jotai";

const PageContentDesktopConferenceLimit = () => {
	const langStringDesktopConferenceLimitContentTitle = useLangString("desktop.conference_limit.title");
	const langStringDesktopConferenceLimitContentDescOneMinute = useLangString(
		"desktop.conference_limit.desc_one_minute"
	);
	const langStringDesktopConferenceLimitContentDescTwoMinutes = useLangString(
		"desktop.conference_limit.desc_two_minutes"
	);
	const langStringDesktopConferenceLimitContentDescFiveMinutes = useLangString(
		"desktop.conference_limit.desc_five_minutes"
	);
	const langStringDesktopConferenceLimitContentDescOneHour = useLangString("desktop.conference_limit.desc_one_hour");
	const langStringDesktopConferenceLimitContentDescTwoHours = useLangString(
		"desktop.conference_limit.desc_two_hours"
	);
	const langStringDesktopConferenceLimitContentDescFiveHours = useLangString(
		"desktop.conference_limit.desc_five_hours"
	);

	const limitNextAttempt = useAtomValue(limitNextAttemptState);

	const desc = useMemo(() => {
		if (limitNextAttempt < 1) {
			return plural(
				1,
				langStringDesktopConferenceLimitContentDescOneHour,
				langStringDesktopConferenceLimitContentDescTwoHours,
				langStringDesktopConferenceLimitContentDescFiveHours
			).replace("$HOURS", String(1));
		}

		const remainingLimitMinutes = Math.ceil((limitNextAttempt - dayjs().unix()) / 60);
		const remainingLimitHours = Math.ceil((limitNextAttempt - dayjs().unix()) / 3600);
		if (remainingLimitMinutes >= 60) {
			return plural(
				remainingLimitHours,
				langStringDesktopConferenceLimitContentDescOneHour,
				langStringDesktopConferenceLimitContentDescTwoHours,
				langStringDesktopConferenceLimitContentDescFiveHours
			).replace("$HOURS", String(remainingLimitHours));
		}

		return plural(
			remainingLimitMinutes,
			langStringDesktopConferenceLimitContentDescOneMinute,
			langStringDesktopConferenceLimitContentDescTwoMinutes,
			langStringDesktopConferenceLimitContentDescFiveMinutes
		).replace("$MINUTES", String(remainingLimitMinutes));
	}, [limitNextAttempt]);

	return (
		<VStack minW="942px" mt="178px" px="32px" py="32px" rounded="20px" bgColor="255255255.03" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={ConferenceNotFoundIcon84Svg} />
			</Center>
			<Text style="inter_40_48_700" letterSpacing="-0.3px" mt="12px" textAlign="center">
				{langStringDesktopConferenceLimitContentTitle}
			</Text>
			<Text style="inter_20_28_400" mt="16px" textAlign="center">
				{desc.split("\n").map((line, index) => (
					<div key={index}>{line}</div>
				))}
			</Text>
		</VStack>
	);
};

export default PageContentDesktopConferenceLimit;
