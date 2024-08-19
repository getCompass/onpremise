import { useQuery } from "@tanstack/react-query";
import { useSetAtom } from "jotai/index";
import { electronVersionState } from "./_stores.ts";

async function fetchVersionInfo(url: string) {
	const response = await fetch(url, {
		cache: "no-cache", // без этого он будет доставать json из кэша
	});
	if (!response.ok) {
		throw new Error("Network response was not ok");
	}
	return response.json();
}

export default function useElectronVersions(backendVersion: string) {
	const setElectronVersion = useSetAtom(electronVersionState);

	return useQuery({
		retry: false,
		networkMode: "offlineFirst",
		queryKey: ["backendVersion", backendVersion],
		queryFn: async () => {
			if (backendVersion.length < 1) {
				return {};
			}

			const data = await fetchVersionInfo("https://update-onpremise.getcompass.ru/electron_versions.json");
			if (!data.hasOwnProperty(backendVersion)) {
				return data;
			}
			if (!data[backendVersion]) {
				return data;
			}

			setElectronVersion(data[backendVersion]);

			return data;
		},
	});
}
