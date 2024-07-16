import ConferenceNotFoundIcon84Svg from "../../img/desktop/ConferenceNotFoundIcon84.svg";
import { Icon } from "../../components/Icon.tsx";
import { Center, VStack } from "../../../styled-system/jsx";
import { useLangString } from "../../lib/getLangString.ts";
import { Text } from "../../components/text.tsx";

const PageContentDesktopConferenceNotFound = () => {
	const langStringDesktopConferenceNotFoundContentTitle = useLangString("desktop.conference_not_found_content.title");
	const langStringDesktopConferenceNotFoundContentDesc = useLangString("desktop.conference_not_found_content.desc");

	return (
		<VStack minW="942px" mt="178px" px="32px" py="32px" rounded="20px" bgColor="255255255.03" gap="0px">
			<Center w="100px" h="100px">
				<Icon width="84px" height="84px" avatar={ConferenceNotFoundIcon84Svg} />
			</Center>
			<Text style="inter_40_48_700" letterSpacing="-0.3px" mt="12px" textAlign="center">
				{langStringDesktopConferenceNotFoundContentTitle}
			</Text>
			<Text style="inter_20_28_400" mt="16px" textAlign="center">
				{langStringDesktopConferenceNotFoundContentDesc.split("\n").map((line, index) => (
					<div key={index}>{line}</div>
				))}
			</Text>
		</VStack>
	);
};

export default PageContentDesktopConferenceNotFound;
