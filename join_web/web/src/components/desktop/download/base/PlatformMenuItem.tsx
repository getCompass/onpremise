import { MenuItem } from "../../../menu.tsx";
import { HStack, styled } from "../../../../../styled-system/jsx";
import { Text } from "../../../text.tsx";
import useDownloadLink from "../../../../lib/useDownloadLink.ts";

type MenuItemProps = {
	id: string,
	icon: JSX.Element,
	platformText: string,
	versionText: string,
}

const PlatformMenuItem = ({ id, icon, platformText, versionText }: MenuItemProps) => {
	const { getDownloadLink } = useDownloadLink();

	return (
		<styled.a
			href = {getDownloadLink(id)}
			style = {{
				display: "block",
				textDecoration: "none",
				color: "inherit",
			}}
		>
			<MenuItem id = {id}>
				<HStack w = "100%" justifyContent = "space-between">
					<HStack gap = "8px">
						{icon}
						<Text style = "inter_16_24_400" color = "333e49">
							{platformText}
						</Text>
					</HStack>
					<Text px = "8px" py = "4px" bgColor = "103115128.01" rounded = "8px" color = "677380"
						  style = "inter_16_20_400">
						{versionText}
					</Text>
				</HStack>
			</MenuItem>
		</styled.a>
	);
};

export default PlatformMenuItem;