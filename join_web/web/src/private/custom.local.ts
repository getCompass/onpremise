// @ts-ignore
export const PUBLIC_PATH_API = `${API_PATH}`;

export function getPublicPathApi(): string {

	if (PUBLIC_PATH_API.length < 1) {
		return "";
	}

	return "/" + PUBLIC_PATH_API;
}