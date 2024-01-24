import {Box} from "../../styled-system/jsx";

const Preloader28 = () => {

	return (
		<Box
			animation="spin500ms"
			w="28px"
			h="28px"
		>
			<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g opacity="0.3">
					<path d="M14 1.645C14 1.015 14 0.665 14 0C10.255 0 6.755 1.47 4.095 4.095C1.47 6.755 0 10.255 0 14C0.56 14 1.085 14 1.645 14C1.645 7.175 7.175 1.645 14 1.645Z" fill="url(#paint0_linear_19_39096)"/>
					<path d="M14 1.645C14 1.015 14 0.665 14 0C17.745 0 21.245 1.47 23.905 4.095C26.53 6.755 28 10.255 28 14C27.44 14 26.915 14 26.355 14C26.355 7.175 20.825 1.645 14 1.645Z" fill="url(#paint1_linear_19_39096)"/>
					<path d="M26.355 14C26.355 20.79 20.79 26.355 14 26.355C7.175 26.355 1.645 20.825 1.645 14C1.085 14 0.56 14 0 14C0 17.745 1.47 21.245 4.095 23.905C6.755 26.53 10.255 28 14 28C17.745 28 21.245 26.53 23.905 23.905C26.53 21.245 28 17.745 28 14H26.355Z" fill="#B4B4B4"/>
				</g>
				<defs>
					<linearGradient id="paint0_linear_19_39096" x1="6.125" y1="3.5" x2="-2.625" y2="11.2" gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stopColor="#B4B4B4" stopOpacity="0"/>
						<stop offset="0.364583" stopColor="#B4B4B4" stopOpacity="0.2567"/>
						<stop offset="1" stopColor="#B4B4B4"/>
					</linearGradient>
					<linearGradient id="paint1_linear_19_39096" x1="21.875" y1="3.5" x2="30.625" y2="11.2" gradientUnits="userSpaceOnUse">
						<stop offset="0.046875" stopColor="#B4B4B4" stopOpacity="0"/>
						<stop offset="0.364583" stopColor="#B4B4B4" stopOpacity="0.2567"/>
						<stop offset="1" stopColor="#B4B4B4"/>
					</linearGradient>
				</defs>
			</svg>
		</Box>
	);
}

export default Preloader28;