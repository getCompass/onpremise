import { Box } from "../../styled-system/jsx";
import { Property } from "../../styled-system/types/csstype";

type AvatarProps = {
	width: Property.Width;
	height: Property.Height;
	avatar: string;
};

export default function Avatar({ width, height, avatar }: AvatarProps) {
	return (
		<Box
			w={width}
			h={height}
			bgPosition="center"
			bgSize="cover"
			bgRepeat="no-repeat"
			rounded="100px"
			flexShrink="0"
			cursor="default"
			style={{
				backgroundImage: `url(${avatar})`,
			}}
		/>
	);
}
