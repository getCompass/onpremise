import {APICommandData, APIResponse} from "./_types";
import {FetchError, ofetch} from "ofetch";
// @ts-ignore
import {getPublicPathApi} from "../private/custom.ts";

export class ApiError extends Error {
	error_code: number;
	next_attempt: number;
	available_attempts: number;
	company_id: number;
	inviter_user_id: number;
	inviter_full_name: string;
	is_postmoderation: number;
	is_waiting_for_postmoderation: number;
	role: number;
	was_member_before: number;
	expires_at: number;
	join_link_uniq: string;
	sso_protocol: string;
	mail_allowed_domains: string[]

	constructor(
		message: string,
		error_code: number,
		next_attempt: number,
		available_attempts: number,
		company_id: number,
		inviter_user_id: number,
		inviter_full_name: string,
		is_postmoderation: number,
		is_waiting_for_postmoderation: number,
		role: number,
		was_member_before: number,
		expires_at: number,
		join_link_uniq: string,
		sso_protocol: string,
		mail_allowed_domains: string[]
	) {
		super(message);
		this.name = "ApiError";
		this.error_code = error_code;
		this.next_attempt = next_attempt;
		this.available_attempts = available_attempts;
		this.company_id = company_id;
		this.inviter_user_id = inviter_user_id;
		this.inviter_full_name = inviter_full_name;
		this.is_postmoderation = is_postmoderation;
		this.is_waiting_for_postmoderation = is_waiting_for_postmoderation;
		this.role = role;
		this.was_member_before = was_member_before;
		this.expires_at = expires_at;
		this.join_link_uniq = join_link_uniq;
		this.sso_protocol = sso_protocol;
		this.mail_allowed_domains = mail_allowed_domains;
	}
}

export class ApiCommand {
	type: string;
	data: APICommandData;

	constructor(
		type: string,
		data: APICommandData,
	) {
		this.type = type;
		this.data = data;
	}
}

export class NetworkError extends Error {
	constructor(message: string) {
		super(message);
		this.name = "NetworkError";
	}
}

export class ServerError extends Error {
	constructor(message: string) {
		super(message);
		this.name = "ServerError";
	}
}

export class LimitError extends Error {
	constructor(message: string) {
		super(message);
		this.name = "LimitError";
	}
}

export type GET_RESPONSE_MODULE = "pivot" | "federation";

interface CommandResponse {
	command: {
		type: string;
		data: object;
	};
}

function isCommandResponse<T>(r: APIResponse<T> | CommandResponse): r is CommandResponse {
	return (r as any).command !== undefined;
}

export function useGetResponse(module: GET_RESPONSE_MODULE) {
	return async <T>(method: string, body: URLSearchParams, headerList: Record<string, string> = {}) => {
		try {
			const result = await ofetch<APIResponse<T>>(getPublicPathApi() + `/${module}/api/onpremiseweb/${method}/`, {
				method: "POST",
				body,
				headers: {
					...headerList,
				},
			});

			if (isCommandResponse(result)) {
				throw new ApiCommand(result.command.type, result.command.data as APICommandData);
			}

			if (result.status !== "ok") {
				// @ts-ignore
				throw new ApiError(
					"status not ok",
					// @ts-ignore
					result.response.error_code ?? 0,
					// @ts-ignore
					result.response.next_attempt ?? 0,
					// @ts-ignore
					result.response.available_attempts ?? 0,
					// @ts-ignore
					result.response.company_id ?? 0,
					// @ts-ignore
					result.response.inviter_user_id ?? 0,
					// @ts-ignore
					result.response.inviter_full_name ?? "",
					// @ts-ignore
					result.response.is_post_moderation ?? 0,
					// @ts-ignore
					result.response.is_waiting_for_postmoderation ?? 0,
					// @ts-ignore
					result.response.role ?? 0,
					// @ts-ignore
					result.response.was_member ?? 0,
					// @ts-ignore
					result.response.expires_at ?? 0,
					// @ts-ignore
					result.response.join_link_uniq ?? "",
					// @ts-ignore
					result.response.sso_protocol ?? "",
					// @ts-ignore
					result.response.mail_allowed_domains ?? []
				);
			}

			// @ts-ignore добавляем в ответ
			result.response.server_time = result.server_time
			return result.response;
		} catch (error) {
			if (error instanceof FetchError) {
				if (error.statusCode === 500) {
					throw new ServerError("Server 500");
				}

				if (error.statusCode === 423) {
					throw new LimitError("Exceeded limit");
				}

				throw new NetworkError("No internet connection");
			}

			throw error;
		}
	};
}
