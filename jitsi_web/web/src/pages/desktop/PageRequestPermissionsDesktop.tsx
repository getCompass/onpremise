import { Box, HStack, VStack } from "../../../styled-system/jsx";
import { Icon } from "../../components/Icon.tsx";
import CompassLogo32Svg from "../../img/desktop/CompassLogo32.svg";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import { useAtomValue } from "jotai";
import { langState } from "../../api/_stores.ts";

const PageRequestPermissionsDesktop = () => {
	const langStringDesktopLogoTitle = useLangString("desktop.logo.title");
	const langStringDesktopRequestPermissionsPageDesc = useLangString("desktop.request_permissions_page.desc");

	const [searchParams] = useSearchParams();
	const lang = useAtomValue(langState);

	useEffect(() => {
		async function getMediaPermissions() {
			const redirectLink = searchParams.get("redirect_link");

			const permissionsGranted = localStorage.getItem("mediaPermissionsGranted");
			if (permissionsGranted !== "true") {
				try {
					const constraints = { audio: true, video: true };
					await navigator.mediaDevices.getUserMedia(constraints);
					localStorage.setItem("mediaPermissionsGranted", "true");
				} catch (error) {}
			}

			setTimeout(() => {
				if (redirectLink !== null) {
					window.location.replace(`${redirectLink}&lang=${lang}`);
				}
			}, 1500);
		}

		getMediaPermissions();
	}, []);

	return (
		<>
			<Box bgColor="rgba(0, 0, 0, 0.1)" position="absolute" zIndex="9999" w="100vw" h="100vh" />
			<VStack w="100%" py="24px">
				<HStack w="100%" justifyContent="space-between">
					<HStack gap="12px">
						<Icon width="32px" height="32px" avatar={CompassLogo32Svg} />
						<Text textTransform="uppercase" style="lato_15_21_700">
							{langStringDesktopLogoTitle}
						</Text>
					</HStack>
				</HStack>
				<Box py="24px" px="27px" mt="540px" rounded="20px" bgColor="255255255.03" minW="584px">
					<Text style="inter_20_28_400" textAlign="center">
						{langStringDesktopRequestPermissionsPageDesc.split("\n").map((line, index) => (
							<div key={index}>{line}</div>
						))}
					</Text>
				</Box>
			</VStack>
		</>
	);
};

export default PageRequestPermissionsDesktop;
