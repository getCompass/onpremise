import {Box} from "../../styled-system/jsx";

const Preloader18Opacity30 = () => {

	return (
		<Box
			animation="spin500ms"
			w="18px"
			h="18px"
		>
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g opacity="0.3">
					<path
						d="M9 1.0575C9 0.6525 9 0.4275 9 0C6.5925 0 4.3425 0.945 2.6325 2.6325C0.945 4.3425 0 6.5925 0 9C0.36 9 0.6975 9 1.0575 9C1.0575 4.6125 4.6125 1.0575 9 1.0575Z"
						fill="url(#paint0_linear_1877_14714)"/>
					<path
						d="M9 1.0575C9 0.6525 9 0.4275 9 0C11.4075 0 13.6575 0.945 15.3675 2.6325C17.055 4.3425 18 6.5925 18 9C17.64 9 17.3025 9 16.9425 9C16.9425 4.6125 13.3875 1.0575 9 1.0575Z"
						fill="url(#paint1_linear_1877_14714)"/>
					<path
						d="M16.9425 9C16.9425 13.365 13.365 16.9425 9 16.9425C4.6125 16.9425 1.0575 13.3875 1.0575 9C0.6975 9 0.36 9 0 9C0 11.4075 0.945 13.6575 2.6325 15.3675C4.3425 17.055 6.5925 18 9 18C11.4075 18 13.6575 17.055 15.3675 15.3675C17.055 13.6575 18 11.4075 18 9H16.9425Z"
						fill="#B4B4B4"/>
				</g>
				<defs>
					<linearGradient id="paint0_linear_1877_14714" x1="3.9375" y1="2.25" x2="-1.6875" y2="7.2"
									gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stopColor="#B4B4B4" stopOpacity="0"/>
						<stop offset="0.364583" stopColor="#B4B4B4" stopOpacity="0.2567"/>
						<stop offset="1" stopColor="#B4B4B4"/>
					</linearGradient>
					<linearGradient id="paint1_linear_1877_14714" x1="14.0625" y1="2.25" x2="19.6875" y2="7.2"
									gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stopColor="#B4B4B4" stopOpacity="0"/>
						<stop offset="0.364583" stopColor="#B4B4B4" stopOpacity="0.2567"/>
						<stop offset="1" stopColor="#B4B4B4"/>
					</linearGradient>
				</defs>
			</svg>
		</Box>
	);
}

export default Preloader18Opacity30;