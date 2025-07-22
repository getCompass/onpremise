import { useLangString } from "../../../../lib/getLangString.ts";
import { MenuItem } from "../../../menu.tsx";
import { HStack, styled } from "../../../../../styled-system/jsx";
import DesktopIcon from "../../../../icons/DesktopIcon.tsx";
import { Text } from "../../../text.tsx";
import RightArrowIcon from "../../../../icons/RightArrowIcon.tsx";
import { COMPASS_DOWNLOAD_LINK_ANOTHER_PLATFORMS } from "../../../../private/custom.ts";
import { Size } from "../../../../api/_types.ts";

type MenuItemProps = {
	size: Size,
}

const AnotherPlatformsMenuItem = ({ size }: MenuItemProps) => {
	const langStringPageDesktopDownloadCompassBuildsAnotherPlatforms = useLangString("desktop.download_compass.builds.another_platforms");

	if (size === "small") {
		return (
			<styled.a
				href = {COMPASS_DOWNLOAD_LINK_ANOTHER_PLATFORMS}
				style = {{
					display: "block",
					textDecoration: "none",
					color: "inherit",
				}}
			>
				<MenuItem id = "another_platforms">
					<HStack w = "100%" justifyContent = "space-between">
						<HStack gap = "8px">
							<DesktopIcon size = {size} />
							<Text style = "inter_13_19_400" color = "333e49">
								{langStringPageDesktopDownloadCompassBuildsAnotherPlatforms}
							</Text>
						</HStack>
						<RightArrowIcon size = {size} />
					</HStack>
				</MenuItem>
			</styled.a>
		);
	}

	return (
		<styled.a
			href = {COMPASS_DOWNLOAD_LINK_ANOTHER_PLATFORMS}
			style = {{
				display: "block",
				textDecoration: "none",
				color: "inherit",
			}}
		>
			<MenuItem id = "another_platforms">
				<HStack w = "100%" justifyContent = "space-between">
					<HStack gap = "8px">
						<DesktopIcon size = {size} />
						<Text style = "inter_16_24_400" color = "333e49">
							{langStringPageDesktopDownloadCompassBuildsAnotherPlatforms}
						</Text>
					</HStack>
					<RightArrowIcon size = {size} />
				</HStack>
			</MenuItem>
		</styled.a>
	);
};

export default AnotherPlatformsMenuItem;