import useIsMobile from "../lib/useIsMobile.ts";
import {Center, VStack} from "../../styled-system/jsx";
import LogoutButtonMobile from "../components/LogoutButtonMobile.tsx";
import NotFinishedLeavingIconMobile from "../components/NotFinishedLeavingIconMobile.tsx";
import {Text} from "../components/text.tsx";
import {useLangString} from "../lib/getLangString.ts";
import NotFinishedLeavingIconDesktop from "../components/NotFinishedLeavingIconDesktop.tsx";

const NeedFinishSpaceLeavingBeforeJoinDesktop = () => {

	const langStringNotFinishedSpaceLeavingDesc = useLangString("not_finished_space_leaving.desc");

	return (
		<Center
			gap="8px"
			maxWidth="560px"
			h="100vh"
			className="invisible-scrollbar"
			zIndex="9999"
		>
			<VStack
				w="366px"
				py="32px"
				px="16px"
				gap="20px"
				bgColor="434455"
				rounded="12px"
			>
				<NotFinishedLeavingIconDesktop/>
				<Text
					w="100%"
					textAlign="center"
					color="white"
					fs="14"
					lh="20"
					font="regular"
				>{langStringNotFinishedSpaceLeavingDesc}</Text>
			</VStack>
		</Center>
	);
}

const NeedFinishSpaceLeavingBeforeJoinMobile = () => {

	const langStringNotFinishedSpaceLeavingDesc = useLangString("not_finished_space_leaving.desc");

	return (
		<VStack
			gap="0px"
			py="16px"
			maxWidth="100vw"
			width="100%"
			className={"h100dvh invisible-scrollbar"}
			position="relative"
			zIndex="9999"
		>
			<VStack gap="0px" width="100%" alignItems="end" px="16px">
				<LogoutButtonMobile/>
			</VStack>
			<Center w="100%" h="100%" px="24px">
				<VStack
					w="100%"
					py="32px"
					px="16px"
					gap="20px"
					bgColor="434455"
					rounded="12px"
				>
					<NotFinishedLeavingIconMobile/>
					<Text
						w="100%"
						textAlign="center"
						color="white"
						fs="16"
						lh="22"
						font="regular"
					>{langStringNotFinishedSpaceLeavingDesc}</Text>
				</VStack>
			</Center>
		</VStack>
	);
}

const NeedFinishSpaceLeavingBeforeJoin = () => {

	const isMobile = useIsMobile();

	if (isMobile) {
		return <NeedFinishSpaceLeavingBeforeJoinMobile/>;
	}

	return <NeedFinishSpaceLeavingBeforeJoinDesktop/>;
}

export default NeedFinishSpaceLeavingBeforeJoin;