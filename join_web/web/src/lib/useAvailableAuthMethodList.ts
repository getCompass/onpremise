import {useAtomValue} from "jotai/index";
import {availableAuthGuestMethodListState, availableAuthMethodListState} from "../api/_stores.ts";

export const AUTH_METHOD_PHONE_NUMBER = "phone_number";
export const AUTH_METHOD_MAIL = "mail";
export const AUTH_METHOD_SSO = "sso";

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

	// проверка на наличие метода аутентификации через SSO
	const isAuthMethodSsoEnabled = (): boolean => {
		return availableAuthMethodList.includes(AUTH_METHOD_SSO);
	}

	// проверка на наличие обоих методов аутентификации
	const isAuthMethodPhoneNumberMailEnabled = (): boolean => {
		return availableAuthMethodList.includes(AUTH_METHOD_PHONE_NUMBER) && availableAuthMethodList.includes(AUTH_METHOD_MAIL);
	}

	// получаем список методов авторизации
	const getMethodList = (): string[] => {
		return availableAuthMethodList;
	}

	return {
		isAuthMethodPhoneNumberEnabled,
		isAuthMethodMailEnabled,
		isAuthMethodSsoEnabled,
		isAuthMethodPhoneNumberMailEnabled,
		getMethodList
	};
};

export const useAvailableAuthGuestMethodList = () => {

	let availableAuthGuestMethodList = useAtomValue(availableAuthGuestMethodListState);

	// проверка на наличие метода аутентификации по номеру телефона
	const isAuthMethodPhoneNumberEnabled = (): boolean => {
		return availableAuthGuestMethodList.includes(AUTH_METHOD_PHONE_NUMBER);
	}

	// проверка на наличие метода аутентификации по почте
	const isAuthMethodMailEnabled = (): boolean => {
		return availableAuthGuestMethodList.includes(AUTH_METHOD_MAIL);
	}

	// проверка на наличие метода аутентификации через SSO
	const isAuthMethodSsoEnabled = (): boolean => {
		return availableAuthGuestMethodList.includes(AUTH_METHOD_SSO);
	}

	// проверка на наличие обоих методов аутентификации
	const isAuthMethodPhoneNumberMailEnabled = (): boolean => {
		return availableAuthGuestMethodList.includes(AUTH_METHOD_PHONE_NUMBER) && availableAuthGuestMethodList.includes(AUTH_METHOD_MAIL);
	}

	// получаем список методов авторизации
	const getMethodList = (): string[] => {
		return availableAuthGuestMethodList;
	}

	return {
		isAuthMethodPhoneNumberEnabled,
		isAuthMethodMailEnabled,
		isAuthMethodSsoEnabled,
		isAuthMethodPhoneNumberMailEnabled,
		getMethodList
	};
};

export default useAvailableAuthMethodList;

