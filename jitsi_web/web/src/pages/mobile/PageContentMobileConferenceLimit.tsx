import ConferenceNotFoundIcon84Svg from "../../img/mobile/ConferenceNotFoundIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";
import {useAtomValue} from "jotai/index";
import {limitNextAttemptState} from "../../api/_stores.ts";
import {useMemo} from "react";
import {plural} from "../../lib/plural.ts";
import dayjs from "dayjs";

const PageContentMobileConferenceLimit = () => {
	const langStringMobileConferenceLimitContentTitle = useLangString("mobile.conference_limit.title");
	const langStringMobileConferenceLimitContentDescOneMinute = useLangString(
		"mobile.conference_limit.desc_one_minute"
	);
	const langStringMobileConferenceLimitContentDescTwoMinutes = useLangString(
		"mobile.conference_limit.desc_two_minutes"
	);
	const langStringMobileConferenceLimitContentDescFiveMinutes = useLangString(
		"mobile.conference_limit.desc_five_minutes"
	);
	const langStringMobileConferenceLimitContentDescOneHour = useLangString("mobile.conference_limit.desc_one_hour");
	const langStringMobileConferenceLimitContentDescTwoHours = useLangString(
		"mobile.conference_limit.desc_two_hours"
	);
	const langStringMobileConferenceLimitContentDescFiveHours = useLangString(
		"mobile.conference_limit.desc_five_hours"
	);

	const limitNextAttempt = useAtomValue(limitNextAttemptState);

	const desc = useMemo(() => {
		if (limitNextAttempt < 1) {
			return plural(
				1,
				langStringMobileConferenceLimitContentDescOneHour,
				langStringMobileConferenceLimitContentDescTwoHours,
				langStringMobileConferenceLimitContentDescFiveHours
			).replace("$HOURS", String(1));
		}

		const remainingLimitMinutes = Math.ceil((limitNextAttempt - dayjs().unix()) / 60);
		const remainingLimitHours = Math.ceil((limitNextAttempt - dayjs().unix()) / 3600);
		if (remainingLimitMinutes >= 60) {
			return plural(
				remainingLimitHours,
				langStringMobileConferenceLimitContentDescOneHour,
				langStringMobileConferenceLimitContentDescTwoHours,
				langStringMobileConferenceLimitContentDescFiveHours
			).replace("$HOURS", String(remainingLimitHours));
		}

		return plural(
			remainingLimitMinutes,
			langStringMobileConferenceLimitContentDescOneMinute,
			langStringMobileConferenceLimitContentDescTwoMinutes,
			langStringMobileConferenceLimitContentDescFiveMinutes
		).replace("$MINUTES", String(remainingLimitMinutes));
	}, [limitNextAttempt]);

	return (
		<VStack w="100%" mt="163px" pt="32px" pb="24px" px="24px" rounded="16px" bgColor="255255255.04" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={ConferenceNotFoundIcon84Svg} />
			</Center>
			<Text style="inter_24_34_700" mt="16px" textAlign="center" color="333e49">
				{langStringMobileConferenceLimitContentTitle}
			</Text>
			<Text style="inter_18_25_400" mt="8px" textAlign="center" color="333e49">
				{desc}
			</Text>
		</VStack>
	);
};

export default PageContentMobileConferenceLimit;
