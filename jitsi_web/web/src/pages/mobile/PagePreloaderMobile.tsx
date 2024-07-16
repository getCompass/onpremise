import { Box, Center, VStack } from "../../../styled-system/jsx";
import Background from "../../components/mobile/Background.tsx";
import { css } from "../../../styled-system/css";

const PagePreloaderMobile = () => {
	return (
		<Center
			h="100vh"
			mdDown={{ h: "100vh" }}
			className={css({
				["@media screen and (max-width: 600px)"]: {
					background:
						"linear-gradient( 180deg, rgba(255, 255, 255, 0.1) 0%, rgba(145, 155, 234, 0.3) 22.6%, rgba(145, 155, 234, 0.3) 52.6%, rgba(145, 155, 234, 0.3) 52.6%, rgba(145, 155, 234, 0.3) 52.6% )",
					backgroundAttachment: "fixed",
				},
			})}
		>
			<Background />
			<VStack>
				<Box animation="spin500ms" w="30px" h="30px">
					<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M22.0711 7.92893C23.4696 9.32746 24.422 11.1093 24.8079 13.0491C25.1937 14.9889 24.9957 16.9996 24.2388 18.8268C23.4819 20.6541 22.2002 22.2159 20.5557 23.3147C18.9112 24.4135 16.9778 25 15 25C13.0222 25 11.0888 24.4135 9.4443 23.3147C7.7998 22.2159 6.51808 20.6541 5.7612 18.8268C5.00433 16.9996 4.80629 14.9889 5.19215 13.0491C5.578 11.1093 6.53041 9.32745 7.92893 7.92893L9.20224 9.20223C8.05555 10.3489 7.27464 11.8099 6.95827 13.4004C6.6419 14.9909 6.80427 16.6395 7.42485 18.1377C8.04544 19.6359 9.09636 20.9165 10.4447 21.8174C11.7931 22.7184 13.3783 23.1993 15 23.1993C16.6217 23.1993 18.2069 22.7184 19.5553 21.8175C20.9036 20.9165 21.9546 19.636 22.5751 18.1377C23.1957 16.6395 23.3581 14.9909 23.0417 13.4004C22.7254 11.8099 21.9445 10.3489 20.7978 9.20223L22.0711 7.92893Z"
							fill="#BFBFC1"
						/>
					</svg>
				</Box>
				<Box>Загрузка</Box>
			</VStack>
		</Center>
	);
};

export default PagePreloaderMobile;
