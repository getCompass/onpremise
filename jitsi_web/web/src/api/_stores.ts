import { atomWithImmer } from "jotai-immer";
import {APIConferenceData, Lang, LANG_CODES} from "./_types.ts";
import { atom, useAtomValue } from "jotai";
import { useMemo } from "react";

export const jsCssVersionState = atom(0);

const KEY_OF_LANGUAGE_ON_LOCAL_STORAGE = "language";
export const DEFAULT_LANG_CODE: Lang = 'en';

export function currentLanguage() {
	const changed_locale = localStorage.getItem(KEY_OF_LANGUAGE_ON_LOCAL_STORAGE) as Lang;
	if (changed_locale) {
		return LANG_CODES.includes(changed_locale) ? changed_locale : DEFAULT_LANG_CODE;
	}

	const lang_list = navigator?.languages ?? [];

	const language_code_list = lang_list.map((lang) => lang.split('-')[0]) as Array<Lang>;
	const uniq_language_code_list: Array<Lang> = Array.from(new Set(language_code_list));
	const support_language = uniq_language_code_list.find((code) => LANG_CODES.includes(code));

	return (support_language ?? DEFAULT_LANG_CODE) as Lang;
}

export function setLocale(locale: Lang) {
	localStorage.setItem(KEY_OF_LANGUAGE_ON_LOCAL_STORAGE, locale);
}

export const baseLangAtom = atom<Lang>(currentLanguage());
export const langState = atom(
	(get) => get(baseLangAtom),
	(get, set, newValue: Lang) => {
		const prevValue = get(baseLangAtom);

		if (prevValue === newValue) return;

		setLocale(newValue);

		set(baseLangAtom, newValue);
	}
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
