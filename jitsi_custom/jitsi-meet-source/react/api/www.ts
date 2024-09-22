import { useMutation } from "@tanstack/react-query";
import { ofetch } from "ofetch";
import {APIResponse} from "./_types";

type ApiWwwGetCompassRequestConsultationArgs = {
	name: string;
	phone_number: string;
};

export type ApiWwwGetCompassRequestConsultation = {};

export function useApiWwwGetCompassRequestConsultation() {
	return useMutation({
		retry: false,
		networkMode: "always",
		mutationFn: async ({ name, phone_number }: ApiWwwGetCompassRequestConsultationArgs) => {
			const body = new URLSearchParams({
				name: name,
				phone_number: phone_number,
				source_id: "",
				pagetitle: "Демо ВКС",
                page_url: `${window.location.origin}/jitsi_demo/`,
				utm_tag: "",
				form_type: "request_consultation",
			});
			return await ofetch<APIResponse<ApiWwwGetCompassRequestConsultation>>(
				"/www/getcompass/requestConsultation",
				{
					method: "POST",
					body,
				}
			);
		},
	});
}
