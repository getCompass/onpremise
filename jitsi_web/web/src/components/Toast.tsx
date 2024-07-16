import { useSetAtom } from "jotai";
import { toastConfigState } from "../api/_stores.ts";
import useIsMobile from "../lib/useIsMobile.ts";

export const useShowToast = (dialogId: string) => {
	const setToastConfig = useSetAtom(toastConfigState);

	return (message: string, type: string, size: string = "") => {
		const isDialog = !dialogId.startsWith("page_");
		setToastConfig((prev) => ({
			...prev,
			[dialogId]: {
				isDialog: isDialog,
				message: message,
				type: type,
				size: isDialog ? "small" : size,
				isVisible: true,
			},
		}));

		setTimeout(() => {
			setToastConfig((prev) => ({
				...prev,
				[dialogId]: {
					isVisible: false,
				},
			}));
		}, 3000);
	};
};

type ToastProps = {
	toastConfig: {
		message: string;
		type: string;
		size: string;
		isDialog: boolean;
		isVisible: boolean;
	};
};

export default function Toast({ toastConfig }: ToastProps) {
	const isMobile = useIsMobile();

	if (toastConfig === undefined || !toastConfig.isVisible) {
		return null;
	}

	return (
		<div
			className={`toast-container ${toastConfig.isDialog ? "popup" : ""} ${toastConfig.size} ${
				isMobile ? "mobile" : ""
			}`}
		>
			<div className={`toast ${toastConfig.type} ${toastConfig.size} ${isMobile ? "mobile" : ""}`}>
				{toastConfig.message}
			</div>
		</div>
	);
}
