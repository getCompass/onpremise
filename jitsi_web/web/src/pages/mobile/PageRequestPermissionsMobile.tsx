import { Box, HStack, VStack } from "../../../styled-system/jsx";
import { Icon } from "../../components/Icon.tsx";
import CompassLogo32Svg from "../../img/mobile/CompassLogo32.svg";
import { Text } from "../../components/text.tsx";
import { useLangString } from "../../lib/getLangString.ts";
import { useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import { useAtomValue } from "jotai/index";
import { langState } from "../../api/_stores.ts";

const PageRequestPermissionsMobile = () => {
	const langStringMobileLogoTitle = useLangString("mobile.logo.title");
	const langStringMobileRequestPermissionsPageDesc = useLangString("mobile.request_permissions_page.desc");

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
			<Box bgColor="rgba(4, 4, 10, 0.3)" position="absolute" zIndex="9999" w="100vw" h="100vh" />
			<VStack w="100%" py="24px" h="100vh" justify="space-between">
				<HStack w="100%" pb="12px" justifyContent="space-between">
					<HStack gap="10px">
						<Icon width="32px" height="32px" avatar={CompassLogo32Svg} />
						<Text textTransform="uppercase" style="lato_14_17_700" color="333e49" mt="-2px">
							{langStringMobileLogoTitle}
						</Text>
					</HStack>
				</HStack>
				<Box p="16px" rounded="20px" bgColor="255255255.03">
					<Text style="inter_18_25_400" color="333e49" textAlign="center">
						{langStringMobileRequestPermissionsPageDesc.split("\n").map((line, index) => (
							<div key={index}>{line}</div>
						))}
					</Text>
				</Box>
			</VStack>
		</>
	);
};

export default PageRequestPermissionsMobile;
