import { MenuItem } from "../../../menu.tsx";
import { HStack, styled } from "../../../../../styled-system/jsx";
import { Text } from "../../../text.tsx";
import useDownloadLink from "../../../../lib/useDownloadLink.ts";
import { Size } from "../../../../api/_types.ts";

type MenuItemProps = {
	id: string,
	size: Size,
	icon: JSX.Element,
	platformText: string,
	versionText: string,
}

const PlatformMenuItem = ({ id, size, icon, platformText, versionText }: MenuItemProps) => {
	const { getDownloadLink } = useDownloadLink();

	if (size === "small") {
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
							<Text style = "inter_13_19_400" color = "333e49">
								{platformText}
							</Text>
						</HStack>
						<Text px = "6px" py = "4px" bgColor = "103115128.01" rounded = "8px" color = "677380"
							  style = "inter_12_14_400">
							{versionText}
						</Text>
					</HStack>
				</MenuItem>
			</styled.a>
		);
	}

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