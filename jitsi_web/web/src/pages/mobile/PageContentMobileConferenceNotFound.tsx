import ConferenceNotFoundIcon84Svg from "../../img/mobile/ConferenceNotFoundIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";

const PageContentMobileConferenceNotFound = () => {
	const langStringMobileConferenceNotFoundContentTitle = useLangString("mobile.conference_not_found_content.title");
	const langStringMobileConferenceNotFoundContentDesc = useLangString("mobile.conference_not_found_content.desc");

	return (
		<VStack w="100%" mt="163px" pt="32px" pb="24px" px="24px" rounded="16px" bgColor="255255255.04" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={ConferenceNotFoundIcon84Svg} />
			</Center>
			<Text style="inter_24_34_700" mt="16px" textAlign="center" color="333e49">
				{langStringMobileConferenceNotFoundContentTitle}
			</Text>
			<Text style="inter_18_25_400" mt="8px" textAlign="center" color="333e49">
				{langStringMobileConferenceNotFoundContentDesc}
			</Text>
		</VStack>
	);
};

export default PageContentMobileConferenceNotFound;
