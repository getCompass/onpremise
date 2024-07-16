import useIsMobile from "../lib/useIsMobile.ts";
import { PropsWithChildren, useEffect } from "react";
import PageLayoutMobile from "./mobile/PageLayoutMobile.tsx";
import PageLayoutDesktop from "./desktop/PageLayoutDesktop.tsx";
import { useAtomValue } from "jotai/index";
import { langState } from "../api/_stores.ts";
import { useLangString } from "../lib/getLangString.ts";

export type PageLayoutProps = PropsWithChildren<{}>;

const PageLayout = ({ children }: PropsWithChildren) => {
	const langStringPreviewTitle = useLangString("preview.title");
	const langStringPreviewDescription = useLangString("preview.description");

	const isMobile = useIsMobile();
	const lang = useAtomValue(langState);

	useEffect(() => {
		const viewportOgTitle = document.querySelector('meta[property="og:title"]');
		const viewportTwitterTitle = document.querySelector('meta[name="twitter:title"]');
		const viewportDescription = document.querySelector('meta[name="description"]');
		const viewportOgDescription = document.querySelector('meta[property="og:description"]');
		const viewportTwitterDescription = document.querySelector('meta[name="twitter:description"]');
		const viewportOgLocale = document.querySelector('meta[property="og:locale"]');
		const viewportOgLocaleAlternate = document.querySelector('meta[property="og:locale:alternate"]');

		let locale;

		switch (lang) {
			case "en":
				locale = "en_US";
				break;
			case "de":
				locale = "de_DE";
				break;
			case "fr":
				locale = "fr_FR";
				break;
			case "es":
				locale = "es_ES";
				break;
			case "it":
				locale = "it_IT";
				break;
			case "ru":
			default:
				locale = "ru_RU";
				break;
		}

		viewportOgTitle?.setAttribute("content", langStringPreviewTitle);
		viewportTwitterTitle?.setAttribute("content", langStringPreviewTitle);
		viewportDescription?.setAttribute("content", langStringPreviewDescription);
		viewportOgDescription?.setAttribute("content", langStringPreviewDescription);
		viewportTwitterDescription?.setAttribute("content", langStringPreviewDescription);
		viewportOgLocale?.setAttribute("content", locale);
		viewportOgLocaleAlternate?.setAttribute("content", locale);
	}, [lang]);

	if (isMobile) {
		return <PageLayoutMobile children={children} />;
	}

	return <PageLayoutDesktop children={children} />;
};

export default PageLayout;
