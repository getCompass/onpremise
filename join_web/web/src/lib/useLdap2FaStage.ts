import {useNavigateDialog} from "../components/hooks.ts";
import {
	API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL,
	API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CURRENT_MAIL,
	API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL,
	API_COMMAND_SCENARIO_DATA_STAGE_ENTER_NEW_MAIL, API_COMMAND_TYPE_NEED_SETUP_TOTP, API_COMMAND_TYPE_NEED_TOTP_CODE,
	APICommandData, APINeedSetupTotpCommandData,
	LdapAuthCredentials
} from "../api/_types.ts";
import {useSetAtom} from "jotai/index";
import {authLdapCredentialsState, authLdapState, authLdapTotpState} from "../api/_stores.ts";
import {ApiCommand} from "../api/_index.ts";

const useLdap2FaStage = () => {
	const {navigateToDialog} = useNavigateDialog();
	const setAuthLdap = useSetAtom(authLdapState);
	const setAuthLdapCredentials = useSetAtom(authLdapCredentialsState);
	const setAuthLdapTotp = useSetAtom(authLdapTotpState);

	function navigateByStage(
		ldapData: APICommandData,
		setIsLoading?: (value: boolean) => void,
		setIsError?: (value: boolean) => void,
		ldapCredentials?: LdapAuthCredentials,
	): void {

		if (ldapCredentials !== undefined) {
			setAuthLdapCredentials(ldapCredentials);
		}

		switch (ldapData.scenario_data.stage) {
			case API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CURRENT_MAIL:
			case API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_CHANGING_MAIL:
			case API_COMMAND_SCENARIO_DATA_STAGE_CONFIRM_NEW_MAIL:

				setAuthLdap(ldapData);
				navigateToDialog("auth_email_confirm_code");
				break;
			case API_COMMAND_SCENARIO_DATA_STAGE_ENTER_NEW_MAIL:

				setAuthLdap(ldapData);
				navigateToDialog("auth_ldap_2fa_attach_mail");
				break;
			default:
				if (setIsLoading !== undefined) {
					setIsLoading(false);
				}
				if (setIsError !== undefined) {
					setIsError(true);
				}
				return;
		}
	}

	function navigateTotp(
		command: ApiCommand,
		setIsLoading?: (value: boolean) => void,
		setIsError?: (value: boolean) => void,
		ldapCredentials?: LdapAuthCredentials,
	): void {

		if (ldapCredentials !== undefined) {
			setAuthLdapCredentials(ldapCredentials);
		}

		switch (command.type) {
			case API_COMMAND_TYPE_NEED_TOTP_CODE:

				navigateToDialog("auth_ldap_2fa_confirm_totp");
				break;
			case API_COMMAND_TYPE_NEED_SETUP_TOTP:

				setAuthLdapTotp(command.data as unknown as APINeedSetupTotpCommandData);
				navigateToDialog("auth_ldap_2fa_setup_totp");
				break;
			default:
				if (setIsLoading !== undefined) {
					setIsLoading(false);
				}
				if (setIsError !== undefined) {
					setIsError(true);
				}
				return;
		}
	}

	return {
		navigateByStage,
		navigateTotp
	};
};

export default useLdap2FaStage;
