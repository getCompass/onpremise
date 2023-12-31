import {APIResponse} from "./_types";
import {FetchError, ofetch} from "ofetch";

export class ApiError extends Error {
	error_code: number;
	next_attempt: number;
	available_attempts: number;
	company_id: number;
	inviter_user_id: number;
	inviter_full_name: string;
	is_postmoderation: number;
	role: number;
	was_member_before: number;
	expires_at: number;

	constructor(message: string, error_code: number, next_attempt: number, available_attempts: number, company_id: number, inviter_user_id: number, inviter_full_name: string, is_postmoderation: number, role: number, was_member_before: number, expires_at: number) {

		super(message);
		this.name = "ApiError";
		this.error_code = error_code;
		this.next_attempt = next_attempt;
		this.available_attempts = available_attempts;
		this.company_id = company_id;
		this.inviter_user_id = inviter_user_id;
		this.inviter_full_name = inviter_full_name;
		this.is_postmoderation = is_postmoderation;
		this.role = role;
		this.was_member_before = was_member_before;
		this.expires_at = expires_at;
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

export function useGetResponse() {

	return async <T>(method: string, body: URLSearchParams) => {

		try {

			const result = await ofetch<APIResponse<T>>(`/api/onpremiseweb/${method}/`, {
				method: "POST",
				body,
			});

			if (result.status !== "ok") {

				// @ts-ignore
				throw new ApiError("status not ok", result.response.error_code ?? 0, result.response.next_attempt ?? 0,
					// @ts-ignore
					result.response.available_attempts ?? 0, result.response.company_id ?? 0,
					// @ts-ignore
					result.response.inviter_user_id ?? 0, result.response.inviter_full_name ?? "", result.response.is_post_moderation ?? 0,
					// @ts-ignore
					result.response.role ?? 0, result.response.was_member ?? 0, result.response.expires_at ?? 0
				)
			}

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
