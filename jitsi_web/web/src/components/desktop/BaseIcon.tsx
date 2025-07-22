import { Center } from "../../../styled-system/jsx";
import { Size } from "../../api/_types.ts";

export type IconProps = {
	size: Size;
}

type BaseIconProps = {
	size: Size;
	icon: JSX.Element;
}

const BaseIcon = ({ size, icon }: BaseIconProps) => {

	if (size === "small") {
		return (
			<Center w = "17px" h = "17px">
				{icon}
			</Center>
		);
	}

	return (
		<Center w = "22px" h = "22px">
			{icon}
		</Center>
	);
};

export default BaseIcon;