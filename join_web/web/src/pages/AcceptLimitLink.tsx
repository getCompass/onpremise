import DialogMobile from "../components/DialogMobile.tsx";
import useIsMobile from "../lib/useIsMobile.ts";
import {Box, VStack} from "../../styled-system/jsx";
import {Text} from "../components/text.tsx";
import {useLangString} from "../lib/getLangString.ts";
import DialogDesktop from "../components/DialogDesktop.tsx";
import {useMemo} from "react";
import dayjs from "dayjs";
import {useAtomValue} from "jotai";
import {prepareJoinLinkErrorState} from "../api/_stores.ts";
import {PrepareJoinLinkErrorLimitData} from "../api/_types.ts";
import {formatTimeToText} from "../lib/formatTimeToText.ts";

const IconMobile = () => {

	return (
		<Box
			p="16px"
			position="absolute"
			mt="-76px"
			bgColor="white"
			rounded="100%"
		>
			<Box w="88px" h="88px">
				<svg width="88" height="88" viewBox="0 0 88 88" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clipPath="url(#clip0_1426_5102)">
						<path
							d="M44 88C68.3005 88 88 68.3005 88 44C88 19.6995 68.3005 0 44 0C19.6995 0 0 19.6995 0 44C0 68.3005 19.6995 88 44 88Z"
							fill="url(#paint0_linear_1426_5102)"/>
						<path
							d="M61.2475 31.9988L64.5418 28.7112C65.1394 28.1148 65.1394 27.1489 64.5418 26.5525C63.9442 25.956 62.9762 25.956 62.3788 26.5525L59.0844 29.8401C55.4075 26.6265 50.7045 24.5738 45.5297 24.2309V21.1002H47.0594C47.9048 21.1002 48.5891 20.4174 48.5891 19.5736C48.5891 18.7299 47.9048 18.0469 47.0594 18.0469H40.9406C40.0952 18.0469 39.4109 18.7297 39.4109 19.5735C39.4109 20.4172 40.0952 21.1001 40.9406 21.1001H42.4703V24.2308C37.2955 24.5737 32.5925 26.6264 28.9156 29.8399L25.6212 26.5523C25.0236 25.9559 24.0556 25.9559 23.4582 26.5523C22.8607 27.1487 22.8606 28.1146 23.4582 28.711L26.7525 31.9987C23.2165 36.0284 21.0547 41.288 21.0547 47.0532C21.0547 59.681 31.3472 69.9531 44 69.9531C56.6527 69.9531 66.9453 59.681 66.9453 47.0534C66.9453 41.2882 64.7833 36.0286 61.2475 31.9988ZM44 51.6333C41.4695 51.6333 39.4109 49.5789 39.4109 47.0534C39.4109 45.0656 40.6936 43.3869 42.4703 42.7546V34.8401C42.4703 33.9962 43.1544 33.3133 44 33.3133C44.8456 33.3133 45.5297 33.9962 45.5297 34.8401V42.7546C47.3064 43.3869 48.5891 45.0656 48.5891 47.0534C48.5891 49.5789 46.5305 51.6333 44 51.6333Z"
							fill="url(#paint1_linear_1426_5102)"/>
					</g>
					<defs>
						<linearGradient id="paint0_linear_1426_5102" x1="44" y1="88" x2="44" y2="0"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FFC6C6"/>
							<stop offset="1" stopColor="#FFF6F6"/>
						</linearGradient>
						<linearGradient id="paint1_linear_1426_5102" x1="44" y1="69.9531" x2="44" y2="18.0469"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FF9C9C"/>
							<stop offset="1" stopColor="#FF5151"/>
						</linearGradient>
						<clipPath id="clip0_1426_5102">
							<rect width="88" height="88" fill="white"/>
						</clipPath>
					</defs>
				</svg>
			</Box>
		</Box>
	);
}

const AcceptLimitLinkMobile = () => {

	const langStringAcceptLimitLinkTitle = useLangString("accept_limit_link.title");
	const langStringAcceptLimitLinkDesc = useLangString("accept_limit_link.desc");
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const oneHour = useLangString("one_hour");
	const twoHours = useLangString("two_hours");
	const fiveHours = useLangString("five_hours");
	const oneMinute = useLangString("one_minute");
	const twoMinutes = useLangString("two_minutes");
	const fiveMinutes = useLangString("five_minutes");

	const time = useMemo(() => {

		if (prepareJoinLinkError === null || prepareJoinLinkError.data === undefined) {
			return 0;
		}

		return formatTimeToText(
			(prepareJoinLinkError.data as PrepareJoinLinkErrorLimitData).expires_at - dayjs().unix(),
			{
				oneHour,
				twoHours,
				fiveHours,
				oneMinute,
				twoMinutes,
				fiveMinutes,
			}
		);
	}, []);

	return (
		<VStack gap="0px">
			<IconMobile/>
			<VStack gap="4px" pt="44px">
				<Text
					w="100%"
					fs="20"
					lh="28"
					font="bold"
					ls="-03"
					color="333e49"
					textAlign="center"
				>
					{langStringAcceptLimitLinkTitle}
				</Text>
				<Text
					w="100%"
					fs="16"
					lh="22"
					color="333e49"
					textAlign="center"
				>
					{langStringAcceptLimitLinkDesc.replace("$TIME", time.toString())}
				</Text>
			</VStack>
		</VStack>
	);
}

const IconDesktop = () => {

	return (
		<Box
			p="12px"
			position="absolute"
			mt="-67px"
			bgColor="white"
			rounded="100%"
		>
			<Box w="76px" h="76px">
				<svg width="76" height="76" viewBox="0 0 76 76" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clipPath="url(#clip0_1426_11085)">
						<path
							d="M38 76C58.9868 76 76 58.9868 76 38C76 17.0132 58.9868 0 38 0C17.0132 0 0 17.0132 0 38C0 58.9868 17.0132 76 38 76Z"
							fill="url(#paint0_linear_1426_11085)"/>
						<path
							d="M52.8956 27.6354L55.7407 24.796C56.2568 24.281 56.2568 23.4467 55.7407 22.9317C55.2245 22.4166 54.3885 22.4166 53.8726 22.9317L51.0275 25.771C47.8519 22.9956 43.7902 21.2229 39.3211 20.9267V18.2229H40.6422C41.3724 18.2229 41.9633 17.6332 41.9633 16.9045C41.9633 16.1758 41.3724 15.5859 40.6422 15.5859H35.3578C34.6276 15.5859 34.0367 16.1757 34.0367 16.9044C34.0367 17.633 34.6276 18.2228 35.3578 18.2228H36.6789V20.9266C32.2098 21.2227 28.1481 22.9955 24.9725 25.7708L22.1274 22.9315C21.6113 22.4164 20.7753 22.4164 20.2593 22.9315C19.7434 23.4466 19.7432 24.2808 20.2593 24.7959L23.1044 27.6352C20.0506 31.1155 18.1836 35.6578 18.1836 40.6368C18.1836 51.5427 27.0726 60.4141 38 60.4141C48.9274 60.4141 57.8164 51.5427 57.8164 40.637C57.8164 35.658 55.9492 31.1156 52.8956 27.6354ZM38 44.5924C35.8146 44.5924 34.0367 42.8181 34.0367 40.637C34.0367 38.9203 35.1445 37.4705 36.6789 36.9244V30.0892C36.6789 29.3603 37.2697 28.7706 38 28.7706C38.7303 28.7706 39.3211 29.3603 39.3211 30.0892V36.9244C40.8555 37.4705 41.9633 38.9203 41.9633 40.637C41.9633 42.8181 40.1854 44.5924 38 44.5924Z"
							fill="url(#paint1_linear_1426_11085)"/>
					</g>
					<defs>
						<linearGradient id="paint0_linear_1426_11085" x1="38" y1="76" x2="38" y2="0"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FFC6C6"/>
							<stop offset="1" stopColor="#FFF6F6"/>
						</linearGradient>
						<linearGradient id="paint1_linear_1426_11085" x1="38" y1="60.4141" x2="38" y2="15.5859"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FF9C9C"/>
							<stop offset="1" stopColor="#FF5151"/>
						</linearGradient>
						<clipPath id="clip0_1426_11085">
							<rect width="76" height="76" fill="white"/>
						</clipPath>
					</defs>
				</svg>
			</Box>
		</Box>
	);
}

const AcceptLimitLinkDesktop = () => {

	const langStringAcceptLimitLinkTitle = useLangString("accept_limit_link.title");
	const langStringAcceptLimitLinkDesc = useLangString("accept_limit_link.desc");
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const oneHour = useLangString("one_hour");
	const twoHours = useLangString("two_hours");
	const fiveHours = useLangString("five_hours");
	const oneMinute = useLangString("one_minute");
	const twoMinutes = useLangString("two_minutes");
	const fiveMinutes = useLangString("five_minutes");

	const time = useMemo(() => {

		if (prepareJoinLinkError === null || prepareJoinLinkError.data === undefined) {
			return 0;
		}

		return formatTimeToText(
			(prepareJoinLinkError.data as PrepareJoinLinkErrorLimitData).expires_at - dayjs().unix(),
			{
				oneHour,
				twoHours,
				fiveHours,
				oneMinute,
				twoMinutes,
				fiveMinutes,
			}
		);
	}, []);

	return (
		<VStack gap="0px">
			<IconDesktop/>
			<VStack gap="8px" pt="30px" px="4px" pb="8px">
				<Text
					w="100%"
					fs="18"
					lh="24"
					font="bold900"
					ls="-02"
					color="333e49"
					textAlign="center"
				>
					{langStringAcceptLimitLinkTitle}
				</Text>
				<Text
					w="100%"
					fs="14"
					lh="20"
					color="333e49"
					textAlign="center"
				>
					{langStringAcceptLimitLinkDesc.replace("$TIME", time.toString())}
				</Text>
			</VStack>
		</VStack>
	);
}

const AcceptLimitLink = () => {

	const isMobile = useIsMobile();

	if (isMobile) {
		return <DialogMobile content={<AcceptLimitLinkMobile/>} overflow="visible" isNeedExtraPaddingBottom={true}/>;
	}

	return <DialogDesktop content={<AcceptLimitLinkDesktop/>} overflow="visible"/>;
}

export default AcceptLimitLink;