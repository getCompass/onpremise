import DialogMobile from "../components/DialogMobile.tsx";
import useIsMobile from "../lib/useIsMobile.ts";
import {Box, VStack} from "../../styled-system/jsx";
import {Text} from "../components/text.tsx";
import {useLangString} from "../lib/getLangString.ts";
import DialogDesktop from "../components/DialogDesktop.tsx";

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
					<g clipPath="url(#clip0_1426_4432)">
						<path
							d="M44 88C68.3005 88 88 68.3005 88 44C88 19.6995 68.3005 0 44 0C19.6995 0 0 19.6995 0 44C0 68.3005 19.6995 88 44 88Z"
							fill="url(#paint0_linear_1426_4432)"/>
						<path fillRule="evenodd" clipRule="evenodd"
							  d="M53.6526 18C56.6941 18.0062 59.5177 19.1924 61.6632 21.3378C63.8086 23.4833 64.9948 26.3069 65.0005 29.3481C65.0067 32.3897 63.8316 35.2085 61.6949 37.3454L52.0154 47.0247C50.6175 48.4226 48.8587 49.4195 46.9389 49.9359C44.4005 50.6926 40.9954 50.3751 38.2724 48.7378C37.451 48.236 36.7095 47.6543 36.0278 46.9726C35.3261 46.3107 34.7643 45.5494 34.2624 44.728L35.9998 42.9906C36.2195 42.7709 36.4193 42.6112 36.5393 42.5714C37.6185 41.9331 39.0395 42.116 40.0017 42.9985C40.3145 43.3113 41.4716 44.1817 41.905 44.1829C43.4268 44.9816 46.1887 44.8314 48.0056 43.0146L57.6851 33.3352C59.8617 31.1186 59.8744 27.5368 57.6691 25.3315C55.5236 23.1862 51.822 23.1987 49.6651 25.3154L43.121 31.8596C42.6939 31.6455 41.5654 31.2799 40.5582 31.1342C40.3563 30.9323 37.9558 30.791 35.9758 31.025C35.9958 31.0049 35.9758 30.9849 35.9758 30.9849L45.6553 21.3056C47.7922 19.1689 50.611 17.9938 53.6526 18ZM32.3355 58.6853L38.9198 52.101C41.3212 53.3626 44.6535 53.1143 45.7187 53.0349C45.8688 53.0237 45.9739 53.0159 46.025 53.0161L36.3455 62.6954C34.2088 64.8319 31.39 66.007 28.3484 66.0008C25.307 65.9946 22.4833 64.8086 20.3378 62.6632C18.1924 60.5177 17.0064 57.6939 17 54.6527C16.994 51.6112 18.1689 48.7923 20.3056 46.6556L29.9851 36.9763C32.7927 34.1687 37.1682 33.237 39.1231 33.7729C40.8331 33.7763 43.73 34.7854 45.9729 37.0283C46.6546 37.6701 47.2564 38.4314 47.7383 39.2729L46.0009 41.0103C44.9258 42.0854 43.1132 42.068 41.999 41.0022C39.7275 38.8704 36.2152 38.766 33.9951 40.9861L24.3156 50.6655C22.139 52.8821 22.1263 56.4639 24.3316 58.6692C26.477 60.8145 30.1788 60.8018 32.3355 58.6853ZM49 60.009C49 65.599 53.5478 70.1468 59.1379 70.1468C64.7279 70.1468 69.2757 65.599 69.2757 60.009C69.2757 54.4189 64.7279 49.8711 59.1379 49.8711C53.5478 49.8711 49 54.4189 49 60.009ZM63.2546 58.0429L61.2885 60.009L63.2546 61.975C63.8485 62.5689 63.8485 63.5317 63.2546 64.1255C62.6608 64.7194 61.698 64.7194 61.1041 64.1255L59.138 62.1595L57.172 64.1255C56.5781 64.7194 55.6153 64.7194 55.0215 64.1255C54.4276 63.5317 54.4276 62.5689 55.0215 61.975L56.9875 60.009L55.0215 58.0429C54.4276 57.4489 54.4276 56.486 55.0215 55.8922C55.6153 55.2984 56.5781 55.2984 57.172 55.8922L59.138 57.8583L61.1041 55.8922C61.698 55.2984 62.6608 55.2984 63.2546 55.8922C63.8485 56.4862 63.8485 57.4491 63.2546 58.0429Z"
							  fill="url(#paint1_linear_1426_4432)"/>
					</g>
					<defs>
						<linearGradient id="paint0_linear_1426_4432" x1="44" y1="88" x2="44" y2="0"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FFC6C6"/>
							<stop offset="1" stopColor="#FFF6F6"/>
						</linearGradient>
						<linearGradient id="paint1_linear_1426_4432" x1="41.0003" y1="66.0008" x2="41.0003" y2="18"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FF9C9C"/>
							<stop offset="1" stopColor="#FF5151"/>
						</linearGradient>
						<clipPath id="clip0_1426_4432">
							<rect width="88" height="88" fill="white"/>
						</clipPath>
					</defs>
				</svg>
			</Box>
		</Box>
	);
}

const InactiveLinkMobile = () => {

	const langStringInactiveLinkTitle = useLangString("inactive_link.title");
	const langStringInactiveLinkDesc = useLangString("inactive_link.desc");

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
					{langStringInactiveLinkTitle}
				</Text>
				<Text
					w="100%"
					fs="16"
					lh="22"
					color="333e49"
					textAlign="center"
					font="regular"
				>
					{langStringInactiveLinkDesc}
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
					<g clipPath="url(#clip0_486_44276)">
						<path
							d="M38 76C58.9868 76 76 58.9868 76 38C76 17.0132 58.9868 0 38 0C17.0132 0 0 17.0132 0 38C0 58.9868 17.0132 76 38 76Z"
							fill="url(#paint0_linear_486_44276)"/>
						<path fillRule="evenodd" clipRule="evenodd"
							  d="M46.3371 15.5446C48.9639 15.5499 51.4025 16.5744 53.2553 18.4272C55.1082 20.2801 56.1327 22.7187 56.1376 25.3452C56.1429 27.972 55.1281 30.4065 53.2828 32.2519L44.9232 40.6113C43.7159 41.8186 42.1969 42.6796 40.539 43.1256C38.3467 43.7791 35.4059 43.5048 33.0542 42.0908C32.3448 41.6575 31.7045 41.1551 31.1157 40.5663C30.5097 39.9947 30.0246 39.3372 29.5911 38.6278L31.0915 37.1273C31.2813 36.9376 31.4538 36.7997 31.5574 36.7653C32.4895 36.2141 33.7167 36.3721 34.5477 37.1342C34.8179 37.4043 35.8172 38.156 36.1915 38.1571C37.5057 38.8469 39.8911 38.7172 41.4602 37.148L49.8198 28.7886C51.6995 26.8742 51.7105 23.7809 49.8059 21.8763C47.953 20.0235 44.7562 20.0344 42.8934 21.8624L37.2416 27.5142C36.8728 27.3293 35.8982 27.0136 35.0283 26.8877C34.854 26.7134 32.7808 26.5913 31.0708 26.7934C31.0881 26.7761 31.0708 26.7588 31.0708 26.7588L39.4304 18.3994C41.2758 16.5541 43.7103 15.5392 46.3371 15.5446ZM27.9269 50.6818L33.6133 44.9955C35.6873 46.085 38.5652 45.8705 39.4852 45.8019C39.6148 45.7923 39.7055 45.7855 39.7497 45.7857L31.3901 54.1451C29.5448 55.9903 27.1103 57.0052 24.4835 56.9998C21.8569 56.9944 19.4182 55.9701 17.5653 54.1173C15.7124 52.2644 14.6881 49.8257 14.6826 47.1992C14.6774 44.5724 15.6921 42.1379 17.5374 40.2926L25.897 31.9332C28.3217 29.5084 32.1006 28.7038 33.7889 29.1666C35.2658 29.1695 37.7676 30.0411 39.7047 31.9781C40.2934 32.5324 40.8131 33.1899 41.2293 33.9166L39.7288 35.4171C38.8003 36.3456 37.2349 36.3306 36.2726 35.4101C34.3109 33.569 31.2775 33.4788 29.3602 35.3962L21.0006 43.7556C19.1208 45.67 19.1099 48.7634 21.0145 50.668C22.8673 52.5207 26.0643 52.5097 27.9269 50.6818ZM42.3193 51.825C42.3193 56.6528 46.247 60.5805 51.0748 60.5805C55.9026 60.5805 59.8302 56.6528 59.8302 51.825C59.8302 46.9972 55.9026 43.0696 51.0748 43.0696C46.247 43.0696 42.3193 46.9972 42.3193 51.825ZM54.6301 50.127L52.9322 51.825L54.6301 53.523C55.143 54.0358 55.143 54.8674 54.6301 55.3802C54.1173 55.8931 53.2857 55.8931 52.7729 55.3802L51.0749 53.6823L49.3769 55.3802C48.8641 55.8931 48.0325 55.8931 47.5197 55.3802C47.0068 54.8674 47.0068 54.0358 47.5197 53.523L49.2177 51.825L47.5197 50.127C47.0068 49.614 47.0068 48.7825 47.5197 48.2696C48.0325 47.7568 48.8641 47.7568 49.3769 48.2696L51.0749 49.9676L52.7729 48.2696C53.2857 47.7568 54.1173 47.7568 54.6301 48.2696C55.143 48.7826 55.143 49.6142 54.6301 50.127Z"
							  fill="url(#paint1_linear_486_44276)"/>
					</g>
					<defs>
						<linearGradient id="paint0_linear_486_44276" x1="38" y1="76" x2="38" y2="0"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FFC6C6"/>
							<stop offset="1" stopColor="#FFF6F6"/>
						</linearGradient>
						<linearGradient id="paint1_linear_486_44276" x1="35.4103" y1="56.9998" x2="35.4103" y2="15.5446"
										gradientUnits="userSpaceOnUse">
							<stop stopColor="#FF9C9C"/>
							<stop offset="1" stopColor="#FF5151"/>
						</linearGradient>
						<clipPath id="clip0_486_44276">
							<rect width="76" height="76" fill="white"/>
						</clipPath>
					</defs>
				</svg>
			</Box>
		</Box>
	);
}

const InactiveLinkDesktop = () => {

	const langStringInactiveLinkTitle = useLangString("inactive_link.title");
	const langStringInactiveLinkDesc = useLangString("inactive_link.desc");

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
					{langStringInactiveLinkTitle}
				</Text>
				<Text
					w="100%"
					fs="14"
					lh="20"
					color="333e49"
					textAlign="center"
					font="regular"
				>
					{langStringInactiveLinkDesc}
				</Text>
			</VStack>
		</VStack>
	);
}

const InactiveLink = () => {

	const isMobile = useIsMobile();

	if (isMobile) {
		return <DialogMobile content={<InactiveLinkMobile/>} overflow="visible" isNeedExtraPaddingBottom={true}/>;
	}

	return <DialogDesktop content={<InactiveLinkDesktop/>} overflow="visible"/>;
}

export default InactiveLink;