// @ts-ignore
export const PUBLIC_PATH_API = `${API_PATH}`;
// @ts-ignore
export const IS_NEED_INDEX_WEB = ${NEED_INDEX_WEB};

export function getPublicPathApi(): string {

	if (PUBLIC_PATH_API.length < 1) {
		return "";
	}

	return "/" + PUBLIC_PATH_API;
}