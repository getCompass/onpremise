import { Center } from "../../styled-system/jsx";

const RightArrowIcon = () => {
	return (
		<Center w = "26px" h = "22px">
			<svg width = "26" height = "16" viewBox = "0 0 26 16" fill = "none" xmlns = "http://www.w3.org/2000/svg">
				<path
					d = "M18.8431 0.928933L25.2071 7.29289C25.5976 7.68342 25.5976 8.31658 25.2071 8.70711L18.8431 15.0711C18.4526 15.4616 17.8195 15.4616 17.4289 15.0711C17.0384 14.6805 17.0384 14.0474 17.4289 13.6569L22.0858 9H1C0.447715 9 0 8.55229 0 8C0 7.44772 0.447715 7 1 7H22.0858L17.4289 2.34315C17.0384 1.95262 17.0384 1.31946 17.4289 0.928933C17.8195 0.538409 18.4526 0.538409 18.8431 0.928933Z"
					fill = "#DCDCDC" />
			</svg>
		</Center>
	);
};

export default RightArrowIcon;