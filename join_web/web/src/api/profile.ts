import { useGetResponse } from "./_index.ts";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useNavigatePage } from "../components/hooks.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import { useSetAtom } from "jotai/index";
import { isNeedShowCreateProfileDialogAfterSsoRegistrationState } from "./_stores.ts";

export type ApiProfileSetArgs = {
	name: string;
};

export function useApiProfileSet() {
	const getResponse = useGetResponse("pivot");
	const setIsNeedShowCreateProfileDialogAfterSsoRegistration = useSetAtom(
		isNeedShowCreateProfileDialogAfterSsoRegistrationState
	);
	const queryClient = useQueryClient();
	const isJoinLink = useIsJoinLink();
	const { navigateToPage } = useNavigatePage();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ name }: ApiProfileSetArgs) => {
			const body = new URLSearchParams({
				name: name,
			});

			return getResponse<object>("profile/set", body);
		},
		async onSuccess() {
			setIsNeedShowCreateProfileDialogAfterSsoRegistration(false);
			await queryClient.invalidateQueries({ queryKey: ["global/start"] });
			if (isJoinLink) {
				await queryClient.invalidateQueries({ queryKey: ["joinlink/prepare", window.location.href] });
			}

			navigateToPage("token");
		},
	});
}
