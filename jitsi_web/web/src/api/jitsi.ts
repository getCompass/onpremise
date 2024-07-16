import { useMutation } from "@tanstack/react-query";
import { useGetResponse } from "./_index.ts";
import { APIConferenceData } from "./_types.ts";
import { conferenceDataState } from "./_stores.ts";
import { useSetAtom } from "jotai";

type ApiJitsiGetConferenceDataArgs = {
	link: string;
};

export type ApiJitsiGetConferenceData = {
	conference_data: APIConferenceData;
};

export function useApiJitsiGetConferenceData() {
	const getResponse = useGetResponse();
	const setConferenceData = useSetAtom(conferenceDataState);

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ link }: ApiJitsiGetConferenceDataArgs) => {
			const body = new URLSearchParams({
				link: link,
			});
			if (window.location.href.includes("requestMediaPermissions")) {
				return null;
			}

			const response = await getResponse<ApiJitsiGetConferenceData>("jitsi/getConferenceData", body);
			setConferenceData(response.conference_data);

			return response;
		},
	});
}

type ApiJitsiJoinConferenceArgs = {
	link: string;
};

export type ApiJitsiJoinConference = {
	jitsi_conference_link: string;
	request_media_permissions_link: string;
};

export function useApiJitsiJoinConference() {
	const getResponse = useGetResponse();

	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ link }: ApiJitsiJoinConferenceArgs) => {
			const body = new URLSearchParams({
				link: link,
			});
			return await getResponse<ApiJitsiJoinConference>("jitsi/joinConference", body);
		},
	});
}
