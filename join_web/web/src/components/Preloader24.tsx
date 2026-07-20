import { Box } from "../../styled-system/jsx";

const Preloader24 = () => {

	return (
		<Box
			animation="spin500ms"
			w="24px"
			h="24px"
		>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M12 1.41C12 0.87 12 0.57 12 0C8.79 0 5.79 1.26 3.51 3.51C1.26 5.79 0 8.79 0 12C0.48 12 0.93 12 1.41 12C1.41 6.15 6.15 1.41 12 1.41Z"
					fill="url(#paint0_linear_11_14481)" />
				<path
					d="M12 1.41C12 0.87 12 0.57 12 0C15.21 0 18.21 1.26 20.49 3.51C22.74 5.79 24 8.79 24 12C23.52 12 23.07 12 22.59 12C22.59 6.15 17.85 1.41 12 1.41Z"
					fill="url(#paint1_linear_11_14481)" />
				<path
					d="M22.59 12C22.59 17.82 17.82 22.59 12 22.59C6.15 22.59 1.41 17.85 1.41 12C0.93 12 0.48 12 0 12C0 15.21 1.26 18.21 3.51 20.49C5.79 22.74 8.79 24 12 24C15.21 24 18.21 22.74 20.49 20.49C22.74 18.21 24 15.21 24 12H22.59Z"
					fill="#007AFF" />
				<defs>
					<linearGradient id="paint0_linear_11_14481" x1="5.25" y1="3" x2="-2.25" y2="9.6"
									gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stop-color="#007AFF" stop-opacity="0" />
						<stop offset="0.364583" stop-color="#007AFF" stop-opacity="0.2567" />
						<stop offset="1" stop-color="#007AFF" />
					</linearGradient>
					<linearGradient id="paint1_linear_11_14481" x1="18.75" y1="3" x2="26.25" y2="9.6"
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

export default Preloader24;