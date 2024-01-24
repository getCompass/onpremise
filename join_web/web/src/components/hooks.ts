import {useCallback} from "react";
import {atom, useAtom} from "jotai";

export type Dialog =
    | "auth_phone_number"
    | "auth_confirm_code"
    | "auth_create_profile"
    | "token_page";

const activeDialogState = atom<Dialog>("auth_phone_number");

export function useNavigateDialog() {

    const [activeDialog, setActiveDialog] = useAtom(activeDialogState);

    const navigateToDialog = useCallback(
        (dialog: Dialog) => {

            setActiveDialog(dialog);
        },
        [activeDialog]
    );

    return {activeDialog, navigateToDialog};
}

export type Page =
    | "welcome"
    | "auth"
    | "token";

const activePageState = atom<Page>("welcome");

export function useNavigatePage() {

    const [activePage, setActivePage] = useAtom(activePageState);

    const navigateToPage = useCallback(
        (dialog: Page) => {

            setActivePage(dialog);
        }, [activePage]
    );

    return {activePage, navigateToPage};
}
