import { useAtom, useAtomValue } from "jotai";
import ruFlag14 from "../../img/flags/ru14.png";
import enFlag14 from "../../img/flags/en14.png";
import deFlag14 from "../../img/flags/de14.png";
import frFlag14 from "../../img/flags/fr14.png";
import esFlag14 from "../../img/flags/es14.png";
import itFlag14 from "../../img/flags/it14.png";
import ruFlag20 from "../../img/flags/ru20.png";
import enFlag20 from "../../img/flags/en20.png";
import deFlag20 from "../../img/flags/de20.png";
import frFlag20 from "../../img/flags/fr20.png";
import esFlag20 from "../../img/flags/es20.png";
import itFlag20 from "../../img/flags/it20.png";
import {useEffect, useMemo} from "react";
import { Portal } from "@ark-ui/react";
import { langState } from "../../api/_stores.ts";
import { getLangFullName, Lang, LANG_CODES } from "../../api/_types.ts";
import {
	Menu,
	MenuArrow,
	MenuArrowTip,
	MenuContent,
	MenuItem,
	MenuItemGroup,
	MenuPositioner,
	MenuTrigger,
} from "../menu.tsx";
import { Box, HStack } from "../../../styled-system/jsx";
import { css } from "../../../styled-system/css";
import { Text } from "../text.tsx";

const getLangIcon = () => {
	const selectedLang = useAtomValue(langState);

	if (selectedLang === "ru") {
		return ruFlag14;
	}

	if (selectedLang === "en") {
		return enFlag14;
	}

	if (selectedLang === "de") {
		return deFlag14;
	}

	if (selectedLang === "fr") {
		return frFlag14;
	}

	if (selectedLang === "es") {
		return esFlag14;
	}

	if (selectedLang === "it") {
		return itFlag14;
	}

	return "";
};

type getImage20Props = {
	lang: Lang;
};

const getImage20 = ({ lang }: getImage20Props) => {
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
};

const LangMenuSelector = () => {
	const items = useMemo(() => {
		const items: { value: Lang; label: string }[] = [];
		LANG_CODES.map((langCode) => items.push({ value: langCode, label: getLangFullName(langCode) }));

		return items;
	}, []);

	const [selectedLang, setSelectedLang] = useAtom(langState);

	// TODO пока не показываем локализацию
	useEffect(() => setSelectedLang("ru"), []);
	return <></>;

	return (
		<Menu
			onSelect={({ value }) => setSelectedLang(value as Lang)}
			positioning={{ placement: "bottom-start", offset: { mainAxis: 7, crossAxis: 0 } }}
			type="lang_desktop"
			closeOnSelect={false}
		>
			{({ isOpen }) => (
				<>
					<MenuTrigger asChild>
						<HStack
							bgColor="hsla(0,0%,100%,.4)"
							minW="68px"
							gap="0px"
							p="9px"
							rounded="6px"
							cursor="pointer"
							_hover={{
								bgColor: "white",
							}}
						>
							<Box
								w="14px"
								h="14px"
								bgSize="cover"
								outline="none"
								userSelect="none"
								flexShrink="0"
								mr="6px"
								style={{
									backgroundImage: `url(${getLangIcon()})`,
								}}
							/>
							<Text style="lato_13_13_700" color="333e49" textTransform="uppercase">
								{selectedLang}
							</Text>
							<Box
								ml="4px"
								transform={isOpen ? "rotate(0deg)" : "rotate(180deg)"}
								transition="transform .2s ease-in-out"
							>
								<svg
									width="8"
									height="5"
									viewBox="0 0 8 5"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
									data-v-4a3f5d46=""
								>
									<path
										d="M4.4243 0.675736L6.97577 3.22721C7.35375 3.60519 7.08605 4.25147 6.55151 4.25147L1.44856 4.25147C0.914021 4.25147 0.646319 3.60519 1.0243 3.22721L3.57577 0.675736C3.81009 0.441422 4.18998 0.441421 4.4243 0.675736Z"
										fill="#333e49"
									></path>
								</svg>
							</Box>
						</HStack>
					</MenuTrigger>
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
											"--arrow-background": "white",
										})}
									/>
								</MenuArrow>
								<MenuItemGroup id="languages">
									{items.map((item) => {
										return (
											<MenuItem id={item.value} key={item.value}>
												<HStack w="100%" gap="8px" justify="space-between">
													<HStack gap="8px">
														<Box
															w="20px"
															h="20px"
															bgSize="cover"
															outline="none"
															userSelect="none"
															flexShrink="0"
															style={{
																backgroundImage: `url(${getImage20({
																	lang: item.value,
																})})`,
															}}
														/>
														<Text style="lato_14_20_500" color="333e49">
															{item.label}
														</Text>
													</HStack>
													{item.value === selectedLang && (
														<Box w="16px" h="16px">
															<svg
																width="16"
																height="16"
																viewBox="0 0 16 16"
																fill="none"
																xmlns="http://www.w3.org/2000/svg"
															>
																<path
																	fillRule="evenodd"
																	clipRule="evenodd"
																	d="M13.5286 3.86189C13.7889 3.60154 14.2111 3.60154 14.4714 3.86189C14.7318 4.12224 14.7318 4.54435 14.4714 4.8047L14 4.33329L13.5286 3.86189ZM6.66667 10.7238L13.5286 3.86189C13.5286 3.86185 13.5286 3.86189 14 4.33329C14.4714 4.8047 14.4714 4.80466 14.4714 4.8047L14.4709 4.80518L7.13807 12.138C6.87772 12.3984 6.45561 12.3984 6.19526 12.138L2.86193 8.8047C2.60158 8.54435 2.60158 8.12224 2.86193 7.86189C3.12228 7.60154 3.54439 7.60154 3.80474 7.86189L6.66667 10.7238Z"
																	fill="#009FE6"
																></path>
															</svg>
														</Box>
													)}
												</HStack>
											</MenuItem>
										);
									})}
								</MenuItemGroup>
							</MenuContent>
						</MenuPositioner>
					</Portal>
				</>
			)}
		</Menu>
	);
};

export default LangMenuSelector;
