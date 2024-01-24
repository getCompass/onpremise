export type Lang = "ru" | "en" | "de" | "fr" | "es" | "it";
export const LANG_CODES: Lang[] = ["ru", "en", "de", "fr", "es", "it"];

const LANG_MAP: Record<Lang, string> = {
	ru: "Русский",
	en: "English",
	de: "Deutsch",
	fr: "Français",
	es: "Español",
	it: "Italiano"
};

export function getLangFullName(langCode: Lang): string {
	return LANG_MAP[langCode] || "";
}

export const LIMIT_ERROR_CODE = 423;
export const NEED_FINISH_SPACE_LEAVING_BEFORE_JOIN = 1708011;
export const INCORRECT_LINK_ERROR_CODE = 1711001;
export const INACTIVE_LINK_ERROR_CODE = 1711002;
export const ALREADY_MEMBER_ERROR_CODE = 1711006;

export type APIAuthInfo = {
	auth_key: string;
	next_resend: number;
	available_attempts: number;
	expire_at: number;
	phone_mask: string;
	phone_number: string;
	type: number;
};

export type PrepareJoinLinkErrorInfo = {
	error_code: number,
	data?: PrepareJoinLinkErrorAlreadyMemberData | PrepareJoinLinkErrorLimitData,
}

export type PrepareJoinLinkErrorLimitData = {
	expires_at: number,
}

export type PrepareJoinLinkErrorAlreadyMemberData = {
	company_id: number,
	inviter_user_id: number,
	inviter_full_name: string,
	is_postmoderation: number,
	role: number,
	was_member_before: number,
}

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
	response: T,
	status: "ok" | "error";
};
