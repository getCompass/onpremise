import {useGetResponse} from "./_index.ts";
import {useMutation, useQuery, useQueryClient} from "@tanstack/react-query";
import {useNavigatePage} from "../components/hooks.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";

export function useApiAuthLogout() {

	const getResponse = useGetResponse("pivot");
	const queryClient = useQueryClient();
	const isJoinLink = useIsJoinLink();
	const {navigateToPage} = useNavigatePage();

	return useMutation({

		retry: false,
		networkMode: "always",
		mutationFn: async () => {

			const body = new URLSearchParams();
			return getResponse<object>("auth/logout", body);
		},
		async onSuccess() {

			await queryClient.invalidateQueries({queryKey: ["global/start"]});

			// только в случае если это join ссылка - перекидываем на welcome, иначе в GlobalStartProvider самообработается корректно
			if (isJoinLink) {

				await queryClient.invalidateQueries({queryKey: ["joinlink/prepare", window.location.href]});
				navigateToPage("welcome");
			}
		}
	});
}

export type ApiAuthGenerateToken = {
	authentication_token: string,
}

export function useApiAuthGenerateToken(join_link_uniq?: string) {

	const getResponse = useGetResponse("pivot");

	return useQuery({

		retry: false,
		refetchOnWindowFocus: false,
		networkMode: "always",
		queryKey: ["auth/generateToken"],
		queryFn: async () => {

			const body = new URLSearchParams();

			if (join_link_uniq !== undefined) {
				body.append("join_link_uniq", join_link_uniq);
			}

			return await getResponse<ApiAuthGenerateToken>("auth/generateToken", body);
		},
	});
}
