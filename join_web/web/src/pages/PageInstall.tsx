import useIsMobile from "../lib/useIsMobile.ts";
import PageInstallMobile from "./PageInstallMobile.tsx";
import PageInstallDesktop from "./PageInstallDesktop.tsx";
import { useEffect } from "react";
import PageHeaderLeft from "../components/PageHeaderLeft.tsx";

const PageInstall = () => {
	const isMobile = useIsMobile();

	useEffect(() => {
		const backgroundColor = document.body.style.getPropertyValue("background-color");
		const hasInline = backgroundColor !== "";

		// удаляем background-color из style
		document.body.style.removeProperty("background-color");

		return () => {
			// если было inline - восстанавливаем, иначе оставляем удаленным
			if (hasInline) {
				document.body.style.setProperty("background-color", backgroundColor);
			} else {
				document.body.style.removeProperty("background-color");
			}
		};
	}, []);

	if (isMobile) {
		return <>
			<PageInstallMobile/>
			<PageHeaderLeft />
		</>
	}

	return <>
		<PageInstallDesktop/>
		<PageHeaderLeft />
	</>
};

export default PageInstall;
