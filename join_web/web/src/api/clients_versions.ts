import {useQuery} from "@tanstack/react-query";
import {useAtomValue, useSetAtom} from "jotai/index";
import {downloadAppUrlState, electronVersionState} from "./_stores.ts";
import {ELECTRON_VERSION_22, ELECTRON_VERSION_30} from "./_types.ts";

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
	const downloadAppUrl = useAtomValue(downloadAppUrlState);

	return useQuery({
		retry: false,
		networkMode: "offlineFirst",
		queryKey: ["backendVersion", backendVersion],
		queryFn: async () => {
			if (backendVersion.length < 1) {
				return {};
			}

			const electron_version_list = [ELECTRON_VERSION_22, ELECTRON_VERSION_30];
			const result: Record<string, any> = {};

			for (const electron_version of electron_version_list) {
				try {
					const data = await fetchVersionInfo(`${downloadAppUrl}${electron_version}/electron_versions.json`);
					result[electron_version] = data;

					if (data.hasOwnProperty(backendVersion) && data[backendVersion]) {
						setElectronVersion(prev => ({
							...prev,
							[electron_version]: data[backendVersion],
						}));
					}
				} catch (error) {
					result[electron_version] = {};
				}
			}

			return result;
		},
	});
}
