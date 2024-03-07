import {useCallback} from "react";
import {atom, useAtom} from "jotai";

export type Dialog =
	| "auth_email_phone_number"
	| "auth_email_register"
	| "auth_email_login"
	| "auth_forgot_password"
	| "auth_create_new_password"
	| "auth_phone_number_confirm_code"
	| "auth_email_confirm_code"
	| "auth_create_profile"
	| "token_page";

const activeDialogState = atom<Dialog>("auth_email_phone_number");

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
