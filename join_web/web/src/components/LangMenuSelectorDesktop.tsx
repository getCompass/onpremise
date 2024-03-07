import {Box, HStack, VStack} from "../../styled-system/jsx";
import {useSetAtom} from "jotai";
import {langState} from "../api/_stores.ts";
import ruFlag18 from "../img/flags/ru18.png";
import enFlag18 from "../img/flags/en18.png";
import deFlag18 from "../img/flags/de18.png";
import frFlag18 from "../img/flags/fr18.png";
import esFlag18 from "../img/flags/es18.png";
import itFlag18 from "../img/flags/it18.png";
import ruFlag20 from "../img/flags/ru20.png";
import enFlag20 from "../img/flags/en20.png";
import deFlag20 from "../img/flags/de20.png";
import frFlag20 from "../img/flags/fr20.png";
import esFlag20 from "../img/flags/es20.png";
import itFlag20 from "../img/flags/it20.png";
import {useMemo} from "react";
import {getLangFullName, Lang, LANG_CODES} from "../api/_types.ts";
import {Portal} from "@ark-ui/react";
import {css} from "../../styled-system/css";
import {
	Menu,
	MenuArrow,
	MenuArrowTip,
	MenuContent,
	MenuItem,
	MenuItemGroup,
	MenuPositioner,
	MenuTrigger
} from "./menu.tsx";
import {Text} from "./text.tsx";
import {useAtomValue} from "jotai";

const getLangIcon = () => {

	const selectedLang = useAtomValue(langState);

	if (selectedLang === "ru") {
		return ruFlag18;
	}

	if (selectedLang === "en") {
		return enFlag18;
	}

	if (selectedLang === "de") {
		return deFlag18;
	}

	if (selectedLang === "fr") {
		return frFlag18;
	}

	if (selectedLang === "es") {
		return esFlag18;
	}

	if (selectedLang === "it") {
		return itFlag18;
	}

	return "";
}

type getImage20Props = {
	lang: Lang,
}

const getImage20 = ({lang}: getImage20Props) => {

	if (lang === "ru") {
		return ruFlag20;
	}

	if (lang === "en") {
		return enFlag20;
	}

	if (lang === "de") {
		return deFlag20;
	}

	if (lang === "fr") {
		return frFlag20;
	}

	if (lang === "es") {
		return esFlag20;
	}

	if (lang === "it") {
		return itFlag20;
	}

	return "";
}

const LangMenuSelectorDesktop = () => {

	const items = useMemo(() => {

		const items: { value: Lang, label: string }[] = [];
		LANG_CODES.map(langCode => items.push({value: langCode, label: getLangFullName(langCode)}));

		return items;
	}, []);

	const setSelectedLang = useSetAtom(langState);

	return (
		<Menu
			onSelect={({value}) => setSelectedLang(value as Lang)}
			positioning={{placement: "bottom-start", offset: {mainAxis: 7, crossAxis: 19}}}
			type="small_desktop"
		>
			<VStack
				gap="0px"
			>
				<MenuTrigger asChild>
					<Box
						bgColor="000000.005"
						p="7px"
						rounded="100%"
						cursor="pointer"
						_hover={{
							bgColor: "000000.005.hover"
						}}
					>
						<Box
							w="18px"
							h="18px"
							bgSize="cover"
							outline="none"
							userSelect="none"
							flexShrink="0"
							style={{
								backgroundImage: `url(${getLangIcon()})`
							}}
						/>
					</Box>
				</MenuTrigger>
			</VStack>
			<Portal>
				<MenuPositioner>
					<MenuContent>
						<MenuArrow
							className={css({
								"--arrow-size": "9px",
							})}
						>
							<MenuArrowTip
								className={css({
									"--arrow-background": "white"
								})}/>
						</MenuArrow>
						<MenuItemGroup id="languages">
							{items.map((item) => {

								return (
									<MenuItem id={item.value} key={item.value}>
										<HStack gap="12px">
											<Box
												w="20px"
												h="20px"
												bgSize="cover"
												outline="none"
												userSelect="none"
												flexShrink="0"
												style={{
													backgroundImage: `url(${getImage20({lang: item.value})})`
												}}
											/>
											<Text
												fs="15"
												lh="22"
												color="333e49"
												font="regular"
											>{item.label}</Text>
										</HStack>
									</MenuItem>
								);
							})}
						</MenuItemGroup>
					</MenuContent>
				</MenuPositioner>
			</Portal>
		</Menu>
	);
}

export default LangMenuSelectorDesktop;