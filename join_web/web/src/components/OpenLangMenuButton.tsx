import {Box} from "../../styled-system/jsx";
import {useAtomValue} from "jotai";
import {langState} from "../api/_stores.ts";
import ruFlag from "../img/flags/ru18.png";
import enFlag from "../img/flags/en18.png";
import deFlag from "../img/flags/de18.png";
import frFlag from "../img/flags/fr18.png";
import esFlag from "../img/flags/es18.png";
import itFlag from "../img/flags/it18.png";
import {useMemo} from "react";
import {SelectControl, SelectTrigger} from "./select.tsx";
import LangMenuSelectorMobile from "./LangMenuSelectorMobile.tsx";

const OpenLangMenuButton = () => {

	const selectedLang = useAtomValue(langState);
	const image = useMemo(() => {

		if (selectedLang === "ru") {
			return ruFlag;
		}

		if (selectedLang === "en") {
			return enFlag;
		}

		if (selectedLang === "de") {
			return deFlag;
		}

		if (selectedLang === "fr") {
			return frFlag;
		}

		if (selectedLang === "es") {
			return esFlag;
		}

		if (selectedLang === "it") {
			return itFlag;
		}

		return "";
	}, [selectedLang]);

	return (
		<Box
			bgColor="000000.005"
			p="7px"
			rounded="100%"
			position="absolute"
			top="8px"
			right="16px"
		>
			<LangMenuSelectorMobile
				selectTrigger={
					<SelectControl
						w="18px"
						h="18px"
					>
						<SelectTrigger>
							<Box
								w="18px"
								h="18px"
								bgSize="cover"
								outline="none"
								userSelect="none"
								style={{
									backgroundImage: `url(${image})`
								}}
							/>
						</SelectTrigger>
					</SelectControl>
				}
			/>
		</Box>
	);
}

export default OpenLangMenuButton;