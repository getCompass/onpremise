import { atom, useAtomValue } from "jotai";
import { APIAuthInfo, APIJoinLinkInfo, Lang, PrepareJoinLinkErrorInfo } from "./_types.ts";
import { atomWithStorage } from "jotai/utils";
import { atomWithImmer } from "jotai-immer";
import { useMemo } from "react";

export const profileState = atomWithImmer<{
	is_authorized: boolean | null;
	need_fill_profile: boolean;
}>({
	is_authorized: null,
	need_fill_profile: false,
});

export const isLoadedState = atom(false);
export const isLoginCaptchaRenderedState = atom(false);
export const isPasswordChangedState = atom(false);

export const captchaPublicKeyState = atom("");

export const availableAuthMethodListState = atom<string[]>([]);

export const isRegistrationState = atom(false);
export const firstAuthState = atom(false);

export const loadingState = atom(true);

export const langState = atom<Lang>("ru");

export const toastConfigState = atomWithImmer<{
	[dialogId: string]: {
		message: string;
		type: string;
		size: string;
		isDialog: boolean;
		isMobile: boolean;
		isVisible: boolean;
	};
}>({});

export const useToastConfig = (dialogId: string) =>
	useAtomValue(useMemo(() => atom((get) => get(toastConfigState)[dialogId]), [dialogId]));

export const activeDialogIdState = atom("");
export const passwordInputState = atom("");
export const confirmPasswordState = atom("");
export const needShowForgotPasswordButtonState = atom(true);

export const confirmCodeState = atom<string[]>(Array(6).fill(""));

export const joinLinkState = atomWithImmer<APIJoinLinkInfo | null>(null);
export const prepareJoinLinkErrorState = atom<PrepareJoinLinkErrorInfo | null>(null);

export const authState = atomWithStorage<APIAuthInfo | null>(
	"a_state",
	JSON.parse(localStorage.getItem("a_state") ?? '""')
);

export const authInputState = atomWithStorage<string>(
	"auth_input",
	JSON.parse(localStorage.getItem("auth_input") ?? '""')
);

export const nameInputState = atomWithStorage<string>(
	"name_input",
	JSON.parse(localStorage.getItem("name_input") ?? '""')
);
