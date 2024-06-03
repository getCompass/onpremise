export type Lang = "ru" | "en" | "de" | "fr" | "es" | "it";
export const LANG_CODES: Lang[] = ["ru", "en", "de", "fr", "es", "it"];

export const AUTH_MAIL_SCENARIO_SHORT = "short_confirm";
export const AUTH_MAIL_SCENARIO_FULL = "full_confirm";

export type AUTH_MAIL_SCENARIO_SHORT = "short_confirm";
export type AUTH_MAIL_SCENARIO_FULL = "full_confirm";

export const AUTH_MAIL_STAGE_ENTERING_PASSWORD = "entering_password";
export const AUTH_MAIL_STAGE_ENTERING_CODE = "entering_code";
export const AUTH_MAIL_STAGE_FINISHED = "finished";

export type AUTH_MAIL_STAGE_ENTERING_PASSWORD = "entering_password";
export type AUTH_MAIL_STAGE_ENTERING_CODE = "entering_code";
export type AUTH_MAIL_STAGE_FINISHED = "finished";

const LANG_MAP: Record<Lang, string> = {
	ru: "Русский",
	en: "English",
	de: "Deutsch",
	fr: "Français",
	es: "Español",
	it: "Italiano",
};

export function getLangFullName(langCode: Lang): string {
	return LANG_MAP[langCode] || "";
}

// словарь с текстами, возвращаемый в api-методе /global/start/
export type ApiGlobalStartDictionaryData = {
	auth_sso_start_button_text: string,
}

// информация о пользователе, возвращаемая в различных api-методах
export type ApiUserInfoData = {
	user_id: number,
	full_name: string,
}

export const isValidEmail = (email: string): boolean => {
	// регулярное выражение для проверки электронной почты
	// это выражение проверяет, что email начинается с английских букв, цифр, точек, подчеркиваний или дефисов
	// затем следует символ "@", после которого идет имя домена, состоящее из английских букв, дефисов или точек
	// домен верхнего уровня должен состоять из английских букв, тире или цифр(привет punycode) и быть длиной от 2 до 30 символов
	const regex = /^[a-zA-ZА-Яа-яЁё0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]{2,30}$/;
	return regex.test(email);
};

export const LIMIT_ERROR_CODE = 423;
export const NEED_FINISH_SPACE_LEAVING_BEFORE_JOIN = 1708011;
export const INCORRECT_LINK_ERROR_CODE = 1711001;
export const INACTIVE_LINK_ERROR_CODE = 1711002;
export const ALREADY_MEMBER_ERROR_CODE = 1711006;

export const APIAuthTypeRegisterByPhoneNumber = 1;
export const APIAuthTypeLoginByPhoneNumber = 2;
export const APIAuthTypeRegisterByMail = 3;
export const APIAuthTypeLoginByMail = 4;
export const APIAuthTypeResetPasswordByMail = 5;

export type APIAuthTypeRegisterByPhoneNumber = 1;
export type APIAuthTypeLoginByPhoneNumber = 2;
export type APIAuthTypeRegisterByMail = 3;
export type APIAuthTypeLoginByMail = 4;
export type APIAuthTypeResetPasswordByMail = 5;

export type APIAuthInfoDataTypeRegisterLoginByPhoneNumber = {
	next_resend: number;
	available_attempts: number;
	expire_at: number;
	phone_mask: string;
	phone_number: string;
};
export type APIAuthInfoDataTypeRegisterLoginResetPasswordByMail = {
	next_resend: number;
	password_available_attempts: number;
	code_available_attempts: number;
	expire_at: number;
	mail: string;
	scenario: AUTH_MAIL_SCENARIO_SHORT | AUTH_MAIL_SCENARIO_FULL;
	stage: AUTH_MAIL_STAGE_ENTERING_PASSWORD | AUTH_MAIL_STAGE_ENTERING_CODE | AUTH_MAIL_STAGE_FINISHED;
};

export type APIAuthInfoType =
	| APIAuthTypeRegisterByPhoneNumber
	| APIAuthTypeLoginByPhoneNumber
	| APIAuthTypeRegisterByMail
	| APIAuthTypeLoginByMail
	| APIAuthTypeResetPasswordByMail;

export type APIAuthInfoData =
	| APIAuthInfoDataTypeRegisterLoginByPhoneNumber
	| APIAuthInfoDataTypeRegisterLoginResetPasswordByMail;

export type APIAuthInfo = {
	type: APIAuthInfoType;
	auth_key: string;
	data: APIAuthInfoData;
};

export type PrepareJoinLinkErrorInfo = {
	error_code: number;
	data?: PrepareJoinLinkErrorAlreadyMemberData | PrepareJoinLinkErrorLimitData;
};

export type PrepareJoinLinkErrorLimitData = {
	expires_at: number;
};

export type PrepareJoinLinkErrorAlreadyMemberData = {
	company_id: number;
	inviter_user_id: number;
	inviter_full_name: string;
	is_postmoderation: number;
	is_waiting_for_postmoderation: number;
	role: number;
	was_member_before: number;
	join_link_uniq: string;
};

export type APIJoinLinkInfo = {
	join_link_uniq: string;
	company_id: number;
	company_name: string;
	inviter_user_id: number;
	inviter_full_name: string;
	entry_option: number;
	role: "member" | "guest";
	is_postmoderation: number;
	is_waiting_for_postmoderation: number;
	was_member_before: number;
	is_exit_status_in_progress: number;
};

export type APIResponse<T> = {
	server_time: number;
	response: T;
	status: "ok" | "error";
};

export type AuthSsoInfo = {
	state: "none" | "in_progress",
	data: {
		link: string,
		sso_auth_token: string,
		signature: string,
	}
}