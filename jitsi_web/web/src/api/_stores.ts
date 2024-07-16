import { atomWithImmer } from "jotai-immer";
import { APIConferenceData, Lang } from "./_types.ts";
import { atom, useAtomValue } from "jotai";
import { useMemo } from "react";
import { atomWithStorage } from "jotai/utils";

export const jsCssVersionState = atom(0);

export const langState = atomWithStorage<Lang>(
	"lang",
	JSON.parse(localStorage.getItem("lang") ?? "\"ru\"")
);

export const conferenceDataState = atom<APIConferenceData | null>(null);
export const conferenceDataErrorCodeState = atom<number>(0);
export const limitNextAttemptState = atom<number>(0);

export const toastConfigState = atomWithImmer<{
	[dialogId: string]: {
		message: string;
		type: string;
		size: string;
		isDialog: boolean;
		isVisible: boolean;
	};
}>({});

export const useToastConfig = (dialogId: string) =>
	useAtomValue(useMemo(() => atom((get) => get(toastConfigState)[dialogId]), [dialogId]));
