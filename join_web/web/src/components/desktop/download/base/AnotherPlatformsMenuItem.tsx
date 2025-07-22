import { useLangString } from "../../../../lib/getLangString.ts";
import { MenuItem } from "../../../menu.tsx";
import { HStack, styled } from "../../../../../styled-system/jsx";
import DesktopIcon from "../../../../icons/DesktopIcon.tsx";
import { Text } from "../../../text.tsx";
import RightArrowIcon from "../../../../icons/RightArrowIcon.tsx";

const AnotherPlatformsMenuItem = () => {
	const langStringDownloadCompassDesktopBuildsAnotherPlatforms = useLangString("download_compass.desktop_builds.another_platforms");

	return (
		<styled.a
			href = "/install"
			style = {{
				display: "block",
				textDecoration: "none",
				color: "inherit",
			}}
		>
			<MenuItem id = "another_platforms">
				<HStack w = "100%" justifyContent = "space-between">
					<HStack gap = "8px">
						<DesktopIcon />
						<Text style = "inter_16_24_400" color = "333e49">
							{langStringDownloadCompassDesktopBuildsAnotherPlatforms}
						</Text>
					</HStack>
					<RightArrowIcon />
				</HStack>
			</MenuItem>
		</styled.a>
	);
};

export default AnotherPlatformsMenuItem;