import { useGetResponse } from "./_index.ts";
import { useMutation, useQuery } from "@tanstack/react-query";
import { APIJoinLinkInfo } from "./_types.ts";

export type ApiJoinLinkPrepare = {
	validation_result: APIJoinLinkInfo;
};

export function useApiJoinLinkPrepare(raw_join_link: string) {
	const getResponse = useGetResponse("pivot");

	return useQuery({
		retry: false,
		refetchOnWindowFocus: false,
		networkMode: "always",
		queryKey: ["joinlink/prepare", raw_join_link],
		queryFn: async () => {
			if (raw_join_link.length < 1) {
				return null;
			}

			const body = new URLSearchParams({
				raw_join_link: raw_join_link,
			});

			return await getResponse<ApiJoinLinkPrepare>("joinlink/prepare", body);
		},
	});
}

type ApiJoinLinkAcceptArgs = {
	join_link_uniq: string;
};

export function useApiJoinLinkAccept() {
	const getResponse = useGetResponse("pivot");

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ join_link_uniq }: ApiJoinLinkAcceptArgs) => {
			const body = new URLSearchParams({
				join_link_uniq: join_link_uniq,
			});
			return await getResponse<object>("joinlink/accept", body);
		},
	});
}
