import {useAtomValue} from "jotai";
import {langState} from "../api/_stores.ts";
import {Lang} from "../api/_types.ts";

type LangConfig = {
	[K in Lang]?: Record<string, any>;
};

const LANG_CONFIG: LangConfig = {
	ru: {
		desktop: {
			logo: {
				title: "COMPASS",
			},
			request_permissions_page: {
				desc: "Для подключения к видеоконференции Compass\nразрешите доступ к вашей камере и микрофону.",
			},
			download_compass: {
				button: "Скачать Compass",
				builds: {
					macos_download: "Скачать для MacOS",
					intel_version: "Intel",
					arm_version: "M1 и новее",
					windows_download: "Скачать для Windows",
					windows_10: "Windows 10 и выше",
					windows_7: "Windows 7 и 8",
					exe_version: ".exe",
					msi_version: ".msi",
					linux_download: "Скачать для Linux",
					linux_astra_download: "Скачать для Astra Linux",
					deb_version: ".deb",
					tar_version: ".tar",
					rpm_version: ".rpm",
					astra_version: ".deb",
					another_platforms: "Другие платформы",
				},
			},
			join_public_conference_content: {
				title: "Подключиться к видеоконференции",
				desc: "Подключитесь к видеоконференции в браузере\nили продолжите в приложении Compass.",
				join_button: "Подключиться",
				open_compass_button: "Продолжить в Compass",
			},
			join_private_conference_content: {
				title: "Закрытая конференция",
				desc: "Войдите в пространство Compass, где проходит конференция, или попросите\nорганизатора сделать конференцию открытой и нажмите «Попробовать снова».",
				join_via_compass_button: "Подключиться в Compass",
				try_again_button: "Попробовать снова",
			},
			conference_ended_content: {
				title: "Видеоконференция завершена",
				desc: "Организатор встречи завершил видеоконференцию. Для создания новой\nконференции скачайте Compass.",
				try_compass_button: "Скачать Compass",
			},
			leave_conference_content: {
				title: "Вы покинули конференцию",
				desc: "Для создания новой конференции скачайте Compass.",
				try_compass_button: "Скачать Compass",
				join_to_conference_again_button: "Вернуться в конференцию",
			},
			conference_not_found_content: {
				title: "Видеоконференция не найдена",
				desc: "Пожалуйста, проверьте ссылку или свяжитесь\nс организатором конференции.",
			},
			conference_limit: {
				title: "Достигнут лимит",
				desc_one_minute:
					"Вы израсходовали все попытки подключения к конференции по ссылке.\nПожалуйста, подождите $MINUTES минуту и попробуйте снова.",
				desc_two_minutes:
					"Вы израсходовали все попытки подключения к конференции по ссылке.\nПожалуйста, подождите $MINUTES минуты и попробуйте снова.",
				desc_five_minutes:
					"Вы израсходовали все попытки подключения к конференции по ссылке.\nПожалуйста, подождите $MINUTES минут и попробуйте снова.",
				desc_one_hour:
					"Вы израсходовали все попытки подключения к конференции по ссылке.\nПожалуйста, подождите $HOURS час и попробуйте снова.",
				desc_two_hours:
					"Вы израсходовали все попытки подключения к конференции по ссылке.\nПожалуйста, подождите $HOURS часа и попробуйте снова.",
				desc_five_hours:
					"Вы израсходовали все попытки подключения к конференции по ссылке.\nПожалуйста, подождите $HOURS часов и попробуйте снова.",
			},
			unsupported_browser: {
				title: "Браузер не поддерживается",
				desc: "Обновите браузер, чтобы подключиться к видеоконференции\nили продолжите в приложении Compass.",
				open_compass_button: "Продолжить в Compass",
				supported_browsers_button: "Поддерживаемые браузеры",
				supported_browsers_popover: {
					title: "Поддерживаемые браузеры",
					supported_browser_list:
						"Chrome версии $SUPPORTED_DESKTOP_CHROME_VERSION и выше\nFirefox версии $SUPPORTED_DESKTOP_FIREFOX_VERSION и выше\nSafari версии $SUPPORTED_DESKTOP_SAFARI_VERSION и выше\nEdge версии $SUPPORTED_DESKTOP_EDGE_VERSION и выше",
					desc: "Если при подключении к конференции у вас возникла проблема, напишите нам на",
				},
			},
		},
		mobile: {
			logo: {
				title: "COMPASS",
			},
			request_permissions_page: {
				desc: "Для подключения к видеоконференции Compass разрешите доступ к вашей камере и микрофону.",
			},
			download_compass: {
				button: "Установить Compass",
				stores: {
					appstore: "Перейти в App Store",
					google_play: "Перейти в Google Play",
					app_gallery: "Перейти в AppGallery",
				},
			},
			join_public_conference_content: {
				title: "Подключиться к видеоконференции",
				desc: "Подключитесь к видеоконференции в браузере или продолжите в приложении Compass.",
				join_button: "Подключиться",
				open_compass_button: "Продолжить в Compass",
			},
			join_private_conference_content: {
				title: "Закрытая конференция",
				desc: "Войдите в пространство Compass, где проходит конференция, или попросите организатора сделать конференцию открытой и нажмите «Попробовать снова».",
				join_via_compass_button: "Подключиться в Compass",
				try_again_button: "Попробовать снова",
			},
			conference_ended_content: {
				title: "Видеоконференция завершена",
				desc: "Организатор встречи завершил видеоконференцию. Для создания новой конференции скачайте Compass.",
				try_compass_button: "Скачать Compass",
			},
			leave_conference_content: {
				title: "Вы покинули конференцию",
				desc: "Для создания новой конференции скачайте Compass.",
				try_compass_button: "Скачать Compass",
				join_to_conference_again_button: "Вернуться в конференцию",
			},
			conference_not_found_content: {
				title: "Видеоконференция не найдена",
				desc: "Пожалуйста, проверьте ссылку или свяжитесь с организатором конференции.",
			},
			conference_limit: {
				title: "Достигнут лимит",
				desc_one_minute:
					"Вы израсходовали все попытки подключения к конференции по ссылке. Пожалуйста, подождите $MINUTES минуту и попробуйте снова.",
				desc_two_minutes:
					"Вы израсходовали все попытки подключения к конференции по ссылке. Пожалуйста, подождите $MINUTES минуты и попробуйте снова.",
				desc_five_minutes:
					"Вы израсходовали все попытки подключения к конференции по ссылке. Пожалуйста, подождите $MINUTES минут и попробуйте снова.",
				desc_one_hour:
					"Вы израсходовали все попытки подключения к конференции по ссылке. Пожалуйста, подождите $HOURS час и попробуйте снова.",
				desc_two_hours:
					"Вы израсходовали все попытки подключения к конференции по ссылке. Пожалуйста, подождите $HOURS часа и попробуйте снова.",
				desc_five_hours:
					"Вы израсходовали все попытки подключения к конференции по ссылке. Пожалуйста, подождите $HOURS часов и попробуйте снова.",
			},
			unsupported_browser: {
				title: "Браузер не поддерживается",
				desc: "Обновите браузер, чтобы подключиться к видеоконференции или продолжите в приложении Compass.",
				open_compass_button: "Продолжить в Compass",
				supported_browsers_button: "Поддерживаемые браузеры",
				supported_browsers_popover: {
					title: "Поддерживаемые браузеры",
					ios_supported_browser_list:
						"Chrome версии $SUPPORTED_MOBILE_IOS_SAFARI_VERSION и выше\nFirefox версии $SUPPORTED_MOBILE_IOS_SAFARI_VERSION и выше\nSafari версии $SUPPORTED_MOBILE_IOS_SAFARI_VERSION и выше\nEdge версии $SUPPORTED_MOBILE_IOS_SAFARI_VERSION и выше",
					android_supported_browser_list:
						"Chrome версии $SUPPORTED_MOBILE_ANDROID_CHROME_VERSION и выше\nFirefox версии $SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION и выше",
					desc: "Если при подключении к конференции у вас возникла проблема, напишите нам на",
				},
			},
		},
		preview: {
			title: "Подключиться к видеоконференции",
			description: "Присоединяйтесь по ссылке или в приложении корпоративного мессенджера Compass",
		},
		network_error: "Нет подключения к интернету",
		server_error: "Ошибка соединения. Попробуйте ещё раз.",
	},
	en: {
		preview: {
			title: "Join Compass videoconference",
			description: "Join the videoconference via link or in the Compass corporate messenger",
		},
	},
	de: {
		preview: {
			title: "Compass-Videokonferenz betreten",
			description:
				"Nehmen Sie an der Videokonferenz teil, indem Sie den Link anklicken oder in der Compass Unternehmens-Messenger verwenden",
		},
	},
	fr: {
		preview: {
			title: "Rejoindre la visioconférence Compass",
			description: "Rejoignez la vidéoconférence par le lien ou dans le messager corporatif Compass",
		},
	},
	es: {
		preview: {
			title: "Conectarse a una videoconferencia de Compass",
			description:
				"Únase a la videoconferencia haciendo clic aquí o en la aplicación de mensajería corporativa Compass",
		},
	},
	it: {
		preview: {
			title: "Unisciti alla videoconferenza in Compass",
			description:
				"Partecipa alla videoconferenza utilizzando il link o nell'app di messaggistica aziendale di Compass",
		},
	},
};

export function useLangString(key: string): string {
	let lang = useAtomValue(langState);

	const getValueFromConfig = (keys: string[], config: any): string | undefined => {
		if (!keys.length || typeof config !== "object") {
			return config;
		}

		const nextKey = keys.shift()!;
		return getValueFromConfig(keys, config[nextKey]);
	};

	let result = getValueFromConfig(key.split("."), LANG_CONFIG[lang]);
	if (result === undefined) {
		result = getValueFromConfig(key.split("."), LANG_CONFIG["ru"]);
	}

	return result || "";
}
