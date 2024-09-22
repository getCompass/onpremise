export type APIAction = {
	type: string;
	data: APIActionProfile;
};

export type APIActionProfile = {
	logged_in: boolean;
	manager_id: number;
};

export type APIResponse<T> = {
	jscss_version: number;
	response: T;
	status: "ok" | "error";
	actions?: APIAction[];
};
