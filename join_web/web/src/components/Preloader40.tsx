import { Box } from "../../styled-system/jsx";

const Preloader40 = () => {

	return (
		<Box
			animation="spin500ms"
			w="40px"
			h="40px"
		>
			<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M20 2.35C20 1.45 20 0.95 20 0C14.65 0 9.65 2.1 5.85 5.85C2.1 9.65 0 14.65 0 20C0.8 20 1.55 20 2.35 20C2.35 10.25 10.25 2.35 20 2.35Z"
					fill="url(#paint0_linear_11_15639)" />
				<path
					d="M20 2.35C20 1.45 20 0.95 20 0C25.35 0 30.35 2.1 34.15 5.85C37.9 9.65 40 14.65 40 20C39.2 20 38.45 20 37.65 20C37.65 10.25 29.75 2.35 20 2.35Z"
					fill="url(#paint1_linear_11_15639)" />
				<path
					d="M37.65 20C37.65 29.7 29.7 37.65 20 37.65C10.25 37.65 2.35 29.75 2.35 20C1.55 20 0.8 20 0 20C0 25.35 2.1 30.35 5.85 34.15C9.65 37.9 14.65 40 20 40C25.35 40 30.35 37.9 34.15 34.15C37.9 30.35 40 25.35 40 20H37.65Z"
					fill="#007AFF" />
				<defs>
					<linearGradient id="paint0_linear_11_15639" x1="8.75" y1="5" x2="-3.75" y2="16"
									gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stop-color="#007AFF" stop-opacity="0" />
						<stop offset="0.364583" stop-color="#007AFF" stop-opacity="0.2567" />
						<stop offset="1" stop-color="#007AFF" />
					</linearGradient>
					<linearGradient id="paint1_linear_11_15639" x1="31.25" y1="5" x2="43.75" y2="16"
									gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stop-color="#007AFF" stop-opacity="0" />
						<stop offset="0.364583" stop-color="#007AFF" stop-opacity="0.2567" />
						<stop offset="1" stop-color="#007AFF" />
					</linearGradient>
				</defs>
			</svg>
		</Box>
	);
}

export default Preloader40;