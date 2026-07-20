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
	| "auth_sso_ldap"
	| "auth_ldap_2fa_attach_mail"
	| "auth_ldap_2fa_setup_totp"
	| "auth_ldap_2fa_confirm_totp"
	| "auth_create_profile"
	| "token_page";

const prevDialogState = atom<Dialog>("auth_email_phone_number");
const activeDialogState = atom<Dialog>("auth_email_phone_number");

export function useNavigateDialog() {

	const [prevDialog, setPrevDialog] = useAtom(prevDialogState);
	const [activeDialog, setActiveDialog] = useAtom(activeDialogState);

	const navigateToDialog = useCallback(
		(dialog: Dialog) => {
			setPrevDialog(activeDialog);
			setActiveDialog(dialog);
		},
		[activeDialog]
	);

	return {activeDialog, prevDialog, navigateToDialog};
}

export type Page =
	| "welcome"
	| "auth"
	| "token"
	| "install";

const prevPageState = atom<Page>("welcome");
const activePageState = atom<Page>("welcome");

export function useNavigatePage() {

	const [prevPage, setPrevPage] = useAtom(prevPageState);
	const [activePage, setActivePage] = useAtom(activePageState);

	const navigateToPage = useCallback(
		(page: Page) => {

			setPrevPage(activePage);
			setActivePage(page);
		}, [activePage]
	);

	return {activePage, prevPage, navigateToPage};
}
