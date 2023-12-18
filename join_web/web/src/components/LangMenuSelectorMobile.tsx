import {Box, HStack} from "../../styled-system/jsx";
import {useAtom} from "jotai";
import {langState} from "../api/_stores.ts";
import ruFlag20 from "../img/flags/ru20.png";
import enFlag20 from "../img/flags/en20.png";
import deFlag20 from "../img/flags/de20.png";
import frFlag20 from "../img/flags/fr20.png";
import esFlag20 from "../img/flags/es20.png";
import itFlag20 from "../img/flags/it20.png";
import {useMemo} from "react";
import {
	Select,
	SelectContent,
	SelectItem, SelectItemIndicator, SelectItemText,
	SelectPositioner,
} from "./select.tsx";
import {getLangFullName, Lang, LANG_CODES} from "../api/_types.ts";
import {Portal} from "@ark-ui/react";

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

type LangMenuSelectorProps = {
	selectTrigger: JSX.Element,
}

const LangMenuSelectorMobile = ({selectTrigger}: LangMenuSelectorProps) => {

	const items = useMemo(() => {

		const items: { value: Lang }[] = [];
		LANG_CODES.map(langCode => items.push({value: langCode}));

		return items;
	}, []);

	const [selectedLang, setSelectedLang] = useAtom(langState);

	return (
		<Select
			positioning={{placement: "bottom-end"}}
			width="100%"
			items={items}
			multiple={false}
			defaultValue={[`${selectedLang}`]}
			onChange={(items) => {
				setSelectedLang(items.value[0] as Lang)
			}}
		>
			{selectTrigger}
			<Portal>
				<SelectPositioner
					style={{
						position: "fixed",
						bottom: 0,
						left: 0,
						top: "auto",
						transform: "none",
						zIndex: "dropdown",
						width: "100%",
						userSelect: "none",
						outline: "none",
						WebkitTapHighlightColor: "transparent",
						// @ts-ignore
						"--x": "0px",
						"--y": "0px",
					}}
				>
					<SelectContent
						lazyMount
						unmountOnExit
					>
						{items.map((item) => (
							<SelectItem
								key={item.value}
								item={item}
							>
								<Box
									w="20px"
									h="20px"
									bgSize="cover"
									outline="none"
									userSelect="none"
									flexShrink="0"
									style={{
										backgroundImage: `url(${getImage20({lang: item.value as Lang})})`
									}}
								/>
								<HStack
									py="16px"
									w="100%"
									gap="16px"
									alignItems="center"
									justifyContent="space-between"
									borderBottom="1px solid #f0f0f0"
								>
									<SelectItemText>{getLangFullName(item.value)}</SelectItemText>
									<SelectItemIndicator>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
											 xmlns="http://www.w3.org/2000/svg">
											<path fillRule="evenodd" clipRule="evenodd"
												  d="M16.9109 4.82611C17.2363 4.50067 17.764 4.50067 18.0894 4.82611C18.4149 5.15155 18.4149 5.67918 18.0894 6.00462L17.5002 5.41536L16.9109 4.82611ZM8.3335 13.4035L16.9109 4.82611C16.911 4.82606 16.9109 4.82611 17.5002 5.41536C18.0894 6.00462 18.0895 6.00458 18.0894 6.00462L18.0888 6.00522L8.92275 15.1713C8.59731 15.4967 8.06968 15.4967 7.74424 15.1713L3.57757 11.0046C3.25214 10.6792 3.25214 10.1515 3.57757 9.82611C3.90301 9.50067 4.43065 9.50067 4.75609 9.82611L8.3335 13.4035Z"
												  fill="#009FE6"/>
										</svg>
									</SelectItemIndicator>
								</HStack>
							</SelectItem>
						))}
					</SelectContent>
				</SelectPositioner>
			</Portal>
		</Select>
	);
}

export default LangMenuSelectorMobile;