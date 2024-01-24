import {Box} from "../../styled-system/jsx";
import logoDesktop from "../img/logo_desktop.svg";

const LoadingLogoDesktop = () => {

	return (
		<Box
			userSelect="none"
			bgPosition="center"
			w="256px"
			h="275px"
			style={{
				backgroundImage: `url(${logoDesktop})`,
				zIndex: 99999,
			}}
		/>
	);
}

export default LoadingLogoDesktop;