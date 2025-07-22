import { useGetResponse } from "./_index.ts";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useNavigateDialog, useNavigatePage } from "../components/hooks.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import { useSetAtom } from "jotai";
import {
	authenticationTokenExpiresAtState,
	authenticationTokenState,
	authenticationTokenTimeLeftState
} from "./_stores.ts";
import dayjs from "dayjs";

export function useApiAuthLogout() {
	const getResponse = useGetResponse("pivot");
	const queryClient = useQueryClient();
	const isJoinLink = useIsJoinLink();
	const { navigateToPage } = useNavigatePage();
	const { navigateToDialog } = useNavigateDialog();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async () => {
			const body = new URLSearchParams();
			return getResponse<object>("auth/logout", body);
		},
		async onSuccess() {
			await queryClient.invalidateQueries({ queryKey: ["global/start"] });

			// только в случае если это join ссылка - перекидываем на welcome, иначе в GlobalStartProvider самообработается корректно
			if (isJoinLink) {
				await queryClient.invalidateQueries({ queryKey: ["joinlink/prepare", window.location.href] });
				navigateToPage("welcome");
				navigateToDialog("auth_email_phone_number");
			}
		},
	});
}

export type ApiAuthGenerateTokenAcceptArgs = {
	join_link_uniq: undefined | string;
	login_type: undefined | number;
};

export type ApiAuthGenerateToken = {
	authentication_token: string;
	expires_at: number;
};

export function useApiAuthGenerateToken() {
	const getResponse = useGetResponse("pivot");
	const setAuthenticationToken = useSetAtom(authenticationTokenState);
	const setExpiresAt = useSetAtom(authenticationTokenExpiresAtState);
	const setTimeLeft = useSetAtom(authenticationTokenTimeLeftState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ join_link_uniq, login_type }: ApiAuthGenerateTokenAcceptArgs) => {
			const body = new URLSearchParams();

			if (join_link_uniq !== undefined) {
				body.append("join_link_uniq", join_link_uniq);
			}
			if (login_type !== undefined) {
				body.append("login_type", login_type.toString());
			}

			const response = await getResponse<ApiAuthGenerateToken>("auth/generateToken", body);
			setAuthenticationToken(response.authentication_token);
			setExpiresAt(response.expires_at);
			setTimeLeft(response.expires_at - dayjs().unix());

			return response;
		},
	});
}
