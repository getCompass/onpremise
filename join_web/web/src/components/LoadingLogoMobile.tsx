import {Box} from "../../styled-system/jsx";
import logoMobile from "../img/logo_mobile.svg";

const LoadingLogoMobile = () => {

	return (
		<Box
			userSelect="none"
			bgPosition="center"
			w="214px"
			h="229px"
			style={{
				backgroundImage: `url(${logoMobile})`,
				zIndex: 99999,
			}}
		/>
	);
}

export default LoadingLogoMobile;