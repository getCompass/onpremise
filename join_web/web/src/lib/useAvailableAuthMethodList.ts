import {useAtomValue} from "jotai/index";
import {availableAuthMethodListState} from "../api/_stores.ts";

export const AUTH_METHOD_PHONE_NUMBER = "phone_number";
export const AUTH_METHOD_MAIL = "mail";

const useAvailableAuthMethodList = () => {

	let availableAuthMethodList = useAtomValue(availableAuthMethodListState);

	// проверка на наличие метода аутентификации по номеру телефона
	const isAuthMethodPhoneNumberEnabled = (): boolean => {
		return availableAuthMethodList.includes(AUTH_METHOD_PHONE_NUMBER);
	}

	// проверка на наличие метода аутентификации по почте
	const isAuthMethodMailEnabled = (): boolean => {
		return availableAuthMethodList.includes(AUTH_METHOD_MAIL);
	}

	// проверка на наличие обоих методов аутентификации
	const isAuthMethodPhoneNumberMailEnabled = (): boolean => {
		return availableAuthMethodList.includes(AUTH_METHOD_PHONE_NUMBER) && availableAuthMethodList.includes(AUTH_METHOD_MAIL);
	}

	return {isAuthMethodPhoneNumberEnabled, isAuthMethodMailEnabled, isAuthMethodPhoneNumberMailEnabled};
};

export default useAvailableAuthMethodList;

