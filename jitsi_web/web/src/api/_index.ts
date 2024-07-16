import { useAtom } from "jotai";
import { jsCssVersionState } from "./_stores";
import { APIResponse } from "./_types";
import { FetchError, ofetch } from "ofetch";
import { getPublicPathApi } from "../private/custom.ts";

export function useUpdateJsCss() {
	const [jsCssVersion, setJsCssVersion] = useAtom(jsCssVersionState);

	return (value: number) => {
		if (jsCssVersion > 0 && jsCssVersion < value) {
			location.reload();
		}

		setJsCssVersion(value);
	};
}

export class ApiError extends Error {
	error_code: number;
	limit_expires_at: number;
	next_attempt: number;

	constructor(message: string, error_code: number, limit_expires_at: number, next_attempt: number) {
		super(message);
		this.name = "ApiError";
		this.error_code = error_code;
		this.limit_expires_at = limit_expires_at;
		this.next_attempt = next_attempt;
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
	const updateJsCss = useUpdateJsCss();

	return async <T>(method: string, body: URLSearchParams) => {
		try {
			const result = await ofetch<APIResponse<T>>(getPublicPathApi() + `/api/www/${method}/`, {
				method: "POST",
				body,
			});

			updateJsCss(result.jscss_version);

			if (result.status !== "ok") {
				throw new ApiError(
					"status not ok",
					// @ts-ignore
					result.response.error_code ?? 0,
					// @ts-ignore
					result.response.expires_at ?? 0,
					// @ts-ignore
					result.response.next_attempt ?? 0,
				);
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
