import {generateDialogId} from "./dialog.tsx";
import {useEffect, useMemo} from "react";
import Toast from "../lib/Toast.tsx";
import {activeDialogIdState, useToastConfig} from "../api/_stores.ts";
import {useSetAtom} from "jotai";
import {VStack} from "../../styled-system/jsx";
import {css} from "../../styled-system/css";

type DialogMobileProps = {
	content: JSX.Element,
	overflow: "hidden" | "visible",
	isNeedExtraPaddingBottom: boolean,
}

const DialogMobile = ({content, overflow, isNeedExtraPaddingBottom}: DialogMobileProps) => {

	const dialogId = useMemo(() => generateDialogId(), []);
	const toastConfig = useToastConfig(dialogId);
	const setActiveDialogId = useSetAtom(activeDialogIdState);

	useEffect(() => setActiveDialogId(dialogId), [dialogId]);

	return (
		<VStack
			className={css({
				width: "100%",
				inset: "0",
				zIndex: "modal",
				display: "flex",
				alignItems: "center",
				justifyContent: "center",
				padding: "16px",
			})}
		>
			<VStack
				className={css({
					px: "16px",
					pt: "16px",
					pb: isNeedExtraPaddingBottom ? "24px" : "16px",
					display: "flex",
					alignItems: "center",
					justifyContent: "center",
					bgColor: "white",
					boxShadow: "0 0 0 3px rgba(0, 0, 0, 0.05)",
					outline: "none",
					position: "relative",
					borderRadius: "15px",
					maxWidth: "382px",
					width: "100%",
					overflow: overflow,
				})}
			>
				<Toast toastConfig={toastConfig}/>
				{content}
			</VStack>
		</VStack>
	);
}

export default DialogMobile;