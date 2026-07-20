import { Box } from "../../styled-system/jsx";

const Preloader14 = () => {

	return (
		<Box
			animation="spin500ms"
			w="14px"
			h="14px"
		>
			<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M7 0.8225C7 0.5075 7 0.3325 7 0C5.1275 0 3.3775 0.735 2.0475 2.0475C0.735 3.3775 0 5.1275 0 7C0.28 7 0.5425 7 0.8225 7C0.8225 3.5875 3.5875 0.8225 7 0.8225Z"
					fill="url(#paint0_linear_116_5948)" />
				<path
					d="M7 0.8225C7 0.5075 7 0.3325 7 0C8.8725 0 10.6225 0.735 11.9525 2.0475C13.265 3.3775 14 5.1275 14 7C13.72 7 13.4575 7 13.1775 7C13.1775 3.5875 10.4125 0.8225 7 0.8225Z"
					fill="url(#paint1_linear_116_5948)" />
				<path
					d="M13.1775 7C13.1775 10.395 10.395 13.1775 7 13.1775C3.5875 13.1775 0.8225 10.4125 0.8225 7C0.5425 7 0.28 7 0 7C0 8.8725 0.735 10.6225 2.0475 11.9525C3.3775 13.265 5.1275 14 7 14C8.8725 14 10.6225 13.265 11.9525 11.9525C13.265 10.6225 14 8.8725 14 7H13.1775Z"
					fill="#007AFF" />
				<defs>
					<linearGradient id="paint0_linear_116_5948" x1="3.0625" y1="1.75" x2="-1.3125" y2="5.6"
									gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stop-color="#007AFF" stop-opacity="0" />
						<stop offset="0.364583" stop-color="#007AFF" stop-opacity="0.2567" />
						<stop offset="1" stop-color="#007AFF" />
					</linearGradient>
					<linearGradient id="paint1_linear_116_5948" x1="10.9375" y1="1.75" x2="15.3125" y2="5.6"
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

export default Preloader14;