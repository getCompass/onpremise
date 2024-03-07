import {
	HStack,
	styled,
	VStack,
} from "../../styled-system/jsx";
import {useLangString} from "../lib/getLangString.ts";
import {Text} from "./text.tsx";
import {useEffect, useState} from "react";

type ConfirmDialogCloseProps = {
	isNeedShowConfirmCloseDialog: boolean,
	onConfirm: () => void,
	onCancel: () => void,
}

export default function ConfirmDialogClose({
											   isNeedShowConfirmCloseDialog,
											   onConfirm,
											   onCancel
										   }: ConfirmDialogCloseProps) {

	const langStringConfirmCloseDialogTitle = useLangString("confirm_close_dialog.title");
	const langStringConfirmCloseDialogDesc = useLangString("confirm_close_dialog.desc");
	const langStringConfirmCloseDialogConfirmButton = useLangString("confirm_close_dialog.confirm_button");
	const langStringConfirmCloseDialogCancelButton = useLangString("confirm_close_dialog.cancel_button");

	// локальное состояние для отслеживания, видно ли ConfirmDialogClose в данный момент
	// без этого при нажатии на Escape сразу будет срабатывать handleKeyDown
	const [isDialogVisible, setIsDialogVisible] = useState(false);

	useEffect(() => {
		setIsDialogVisible(isNeedShowConfirmCloseDialog);
	}, [isNeedShowConfirmCloseDialog]);

	useEffect(() => {

		const handleKeyDown = (event: KeyboardEvent) => {

			// подтверждаем закрытие попапа и !!потерю данных!!
			if (event.key === "Enter" && isDialogVisible) {

				event.preventDefault();
				onConfirm();
			}

			// закрываем попап подтверждения, данные не теряем
			if (event.key === "Escape" && isDialogVisible) {

				event.preventDefault();
				onCancel();
			}
		};

		window.addEventListener("keydown", handleKeyDown);

		// очищаем
		return () => {
			window.removeEventListener("keydown", handleKeyDown);
		};
	}, [isDialogVisible]);

	if (!isNeedShowConfirmCloseDialog) {
		return null;
	}

	return (
		<HStack
			w="100%"
			position="absolute"
			top="0"
			pt="8px"
			px="8px"
			zIndex="9000"
			onClick={() => onCancel()}
		>
			<HStack
				w="100%"
				py="12px"
				pr="12px"
				pl="16px"
				justify="space-between"
				bgColor="rgba(0, 0, 0, 0.9)"
				rounded="5px"
			>
				<VStack
					gap="0px"
					alignItems="start"
					userSelect="none"
				>
					<Text style="lato_14_20_700" color="f8f8f8" ls="-012">{langStringConfirmCloseDialogTitle}</Text>
					<Text opacity="50%" style="lato_13_18_400" color="f8f8f8">{langStringConfirmCloseDialogDesc}</Text>
				</VStack>
				<HStack
					gap="8px"
				>
					<styled.button
						px="16px"
						py="9px"
						rounded="10px"
						bgColor="rgba(255, 59, 48, 0.9)"
						color="white"
						cursor="pointer"
						outline="none"
						fontSize="15px"
						lineHeight="23px"
						fontFamily="lato_semibold"
						fontWeight="normal"
						_hover={{
							bgColor: "rgba(255, 18, 5, 0.9)",
						}}
						onClick={onConfirm}
					>
						{langStringConfirmCloseDialogConfirmButton}
					</styled.button>
					<styled.button
						px="16px"
						py="9px"
						rounded="10px"
						bgColor="rgba(0, 0, 0, 0.5)"
						color="white"
						cursor="pointer"
						outline="none"
						fontSize="15px"
						lineHeight="23px"
						fontFamily="lato_semibold"
						fontWeight="normal"
						_hover={{
							bgColor: "rgba(98, 98, 98, 0.2)",
						}}
						onClick={onCancel}
					>
						{langStringConfirmCloseDialogCancelButton}
					</styled.button>
				</HStack>
			</HStack>
		</HStack>
	);
}
