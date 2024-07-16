import { Box } from "../../styled-system/jsx";
import { Property } from "../../styled-system/types/csstype";

type IconProps = {
	width: Property.Width;
	height: Property.Height;
	avatar: string;
};

export const Icon = ({ width, height, avatar }: IconProps) => {
	return (
		<Box
			w={width}
			h={height}
			bgPosition="center"
			bgSize="cover"
			bgRepeat="no-repeat"
			flexShrink="0"
			style={{
				backgroundImage: `url(${avatar})`,
			}}
		/>
	);
};
