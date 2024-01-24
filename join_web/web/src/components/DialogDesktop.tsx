import {generateDialogId} from "./dialog.tsx";
import {useEffect, useMemo} from "react";
import {activeDialogIdState, useToastConfig} from "../api/_stores.ts";
import {useSetAtom} from "jotai";
import Toast from "../lib/Toast.tsx";
import {VStack} from "../../styled-system/jsx";
import {css} from "../../styled-system/css";

type DialogDesktopProps = {
	content: JSX.Element,
	overflow: "hidden" | "visible",
}

const DialogDesktop = ({content, overflow}: DialogDesktopProps) => {

	const dialogId = useMemo(() => generateDialogId(), []);
	const toastConfig = useToastConfig(dialogId);
	const setActiveDialogId = useSetAtom(activeDialogIdState);

	useEffect(() => setActiveDialogId(dialogId), [dialogId]);

	return (
		<VStack
			className={css({
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
					borderRadius: "8px",
					padding: "16px",
					display: "flex",
					alignItems: "center",
					justifyContent: "center",
					backgroundColor: "white",
					boxShadow: "0 0 0 3px rgba(0, 0, 0, 0.05)",
					outline: "none",
					position: "relative",
					width: "360px",
					overflow: overflow,
				})}
			>
				<Toast toastConfig={toastConfig}/>
				{content}
			</VStack>
		</VStack>
	);
}

export default DialogDesktop;