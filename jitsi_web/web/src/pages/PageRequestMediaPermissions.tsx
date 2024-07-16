import useIsMobile from "../lib/useIsMobile.ts";
import PageRequestPermissionsDesktop from "./desktop/PageRequestPermissionsDesktop.tsx";
import PageRequestPermissionsMobile from "./mobile/PageRequestPermissionsMobile.tsx";

const PageRequestMediaPermissions = () => {
	const isMobile = useIsMobile();

	if (isMobile) {
		return <PageRequestPermissionsMobile />;
	}

	return <PageRequestPermissionsDesktop />;
};

export default PageRequestMediaPermissions;
