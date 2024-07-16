import useIsMobile from "../lib/useIsMobile.ts";
import PageInstallMobile from "./PageInstallMobile.tsx";
import PageInstallDesktop from "./PageInstallDesktop.tsx";

const PageInstall = () => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return <PageInstallMobile />;
	}

	return <PageInstallDesktop />;
};

export default PageInstall;
