import {useSetAtom} from "jotai";
import {toastConfigState} from "../api/_stores.ts";
import useIsMobile from "./useIsMobile.ts";

export const useShowToast = (dialogId: string) => {

    const setToastConfig = useSetAtom(toastConfigState);
    const isMobile = useIsMobile();

    return (message: string, type: string, size: string = "") => {

        const isDialog = !dialogId.startsWith("page_");
        setToastConfig((prev) => ({
            ...prev,
            [dialogId]: {
                isDialog: isDialog,
                message: message,
                type: type,
                size: isDialog ? "small" : size,
                isMobile: isMobile,
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
        isMobile: boolean;
        isVisible: boolean;
    },
}

export default function Toast({toastConfig}: ToastProps) {

    if (toastConfig === undefined || !toastConfig.isVisible) {
        return null;
    }

    return (
        <div
            className={`toast-container ${toastConfig.isDialog ? "popup" : ""} ${toastConfig.isMobile ? "mobile" : "desktop"} ${toastConfig.size}`}>
            <div
                className={`toast ${toastConfig.type} ${toastConfig.size} ${toastConfig.isMobile ? "mobile" : "desktop"}`}>
                {toastConfig.message}
            </div>
        </div>
    );
}