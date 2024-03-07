import useIsMobile from "../lib/useIsMobile.ts";
import PageTokenMobile from "./PageTokenMobile.tsx";
import PageTokenDesktop from "./PageTokenDesktop.tsx";

const PageToken = () => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return <PageTokenMobile />;
	}

	return <PageTokenDesktop />;
};

export default PageToken;
