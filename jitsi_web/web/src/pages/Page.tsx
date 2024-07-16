import useIsMobile from "../lib/useIsMobile.ts";
import PageHeaderDesktop from "./desktop/PageHeaderDesktop.tsx";
import PageContentDesktop from "./desktop/PageContentDesktop.tsx";
import PageHeaderMobile from "./mobile/PageHeaderMobile.tsx";
import PageContentMobile from "./mobile/PageContentMobile.tsx";
import { Box } from "../../styled-system/jsx";

const PageDesktop = () => {
	return (
		<>
			<PageHeaderDesktop />
			<Box pb="24px">
				<PageContentDesktop />
			</Box>
		</>
	);
};

const PageMobile = () => {
	return (
		<>
			<PageHeaderMobile />
			<Box pb="12px">
				<PageContentMobile />
			</Box>
		</>
	);
};

export const Page = () => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return <PageMobile />;
	}

	return <PageDesktop />;
};
