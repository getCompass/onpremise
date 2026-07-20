import {useAtomValue} from "jotai";
import {langState} from "../api/_stores.ts";
import {Lang} from "../api/_types.ts";

type LangConfig = {
	[K in Lang]?: Record<string, any>;
};

const LANG_CONFIG: LangConfig = {
	ru: {
		desktop: {
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
			},
			leave_conference_content: {
				title: "Вы покинули конференцию",
				desc: "Для создания новой конференции скачайте Compass.",
				join_to_conference_again_button: "Вернуться в конференцию",
			},
			kicked_from_conference_content: {
				title: "Вы исключены из конференции",
				desc: "Для создания новой конференции скачайте Compass.",
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
			},
			leave_conference_content: {
				title: "Вы покинули конференцию",
				desc: "Для создания новой конференции скачайте Compass.",
				join_to_conference_again_button: "Вернуться в конференцию",
			},
			kicked_from_conference_content: {
				title: "Вы исключены из конференции",
				desc: "Для создания новой конференции скачайте Compass.",
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
		try_compass_button: "Скачать Compass",
		"loading": "Загружается",
	},
	en: {
		desktop: {
			"request_permissions_page": {
				"desc": "To join the Compass videoconference, please allow access to your camera and microphone."
			},
			"join_public_conference_content": {
				"title": "Join videoconference",
				"desc": "Join the videoconference in browser or in the Compass corporate messenger.",
				"join_button": "Join",
				"open_compass_button": "Open Compass"
			},
			"join_private_conference_content": {
				"title": "Private conference",
				"desc": "Please join the conference in the Compass workspace where it is being held, or ask the organizer to make it public and click \"Try again\".",
				"join_via_compass_button": "Join in Compass",
				"try_again_button": "Try again"
			},
			"conference_ended_content": {
				"title": "Videoconference was ended",
				"desc": "The videoconference was ended by the organizer. Please download Compass to create a new conference.",
			},
			"leave_conference_content": {
				"title": "You left the conference",
				"desc": "Please download Compass to create a new conference.",
				"join_to_conference_again_button": "Return to the conference"
			},
			"kicked_from_conference_content": {
				"title": "You have been removed from the conference",
				"desc": "Please download Compass to create a new conference."
			},
			"conference_not_found_content": {
				"title": "Videoconference not found",
				"desc": "Please check the link or contact the conference organizer."
			},
			"conference_limit": {
				"title": "You have reached the limit",
				"desc_one_minute": "You have reached the limit to join the conference via link. Please wait for $MINUTES minute and try again.",
				"desc_two_minutes": "You have reached the limit to join the conference via link. Please try again in $MINUTES minutes.",
				"desc_five_minutes": "You have reached the limit to join the conference via link. Please try again in $MINUTES minutes.",
				"desc_one_hour": "You have reached the limit to join the conference via link. Please try again in $HOURS hour.",
				"desc_two_hours": "You have reached the limit to join the conference via link. Please try again in $HOURS hours.",
				"desc_five_hours": "You have reached the limit to join the conference via link. Please try again in $HOURS hours."
			},
			"unsupported_browser": {
				"title": "Browser not supported",
				"desc": "Please update your browser to join the conference or continue in the Compass app.",
				"open_compass_button": "Open Compass",
				"supported_browsers_button": "Supported browsers",
				"supported_browsers_popover": {
					"title": "Supported browsers",
					"supported_browser_list": "Chrome version $SUPPORTED_DESKTOP_CHROME_VERSION or later Firefox version $SUPPORTED_DESKTOP_FIREFOX_VERSION or later Safari version $SUPPORTED_DESKTOP_SAFARI_VERSION or later Edge version $SUPPORTED_DESKTOP_EDGE_VERSION or later",
					"desc": "If you have trouble joining the conference, please contact us at"
				}
			},
			download_compass: {
				button: "Download Compass",
				builds: {
					"macos_download": "Download for MacOS",
					"intel_version": "Intel",
					"arm_version": "M1 or newer",
					"windows_download": "Download for Windows",
					"windows_10": "Windows 10 or later",
					"windows_7": "Windows 7 and 8",
					"exe_version": ".exe",
					"msi_version": ".msi",
					"linux_download": "Download for Linux",
					"linux_astra_download": "Download for Astra Linux",
					"deb_version": ".deb",
					"tar_version": ".tar",
					"rpm_version": ".rpm",
					"astra_version": ".deb",
					"another_platforms": "Other platforms"
				},
			},
		},
		mobile: {
			"request_permissions_page": {
				"desc": "To join the Compass videoconference, please allow access to your camera and microphone."
			},
			"join_public_conference_content": {
				"title": "Join videoconference",
				"desc": "Join the videoconference in browser or in the Compass corporate messenger.",
				"join_button": "Join",
				"open_compass_button": "Open Compass"
			},
			"join_private_conference_content": {
				"title": "Private conference",
				"desc": "Please join the conference in the Compass workspace where it is being held, or ask the organizer to make it public and click \"Try again\".",
				"join_via_compass_button": "Join in Compass",
				"try_again_button": "Try again"
			},
			"conference_ended_content": {
				"title": "Videoconference was ended",
				"desc": "The videoconference was ended by the organizer. Please download Compass to create a new conference.",
			},
			"leave_conference_content": {
				"title": "You left the conference",
				"desc": "Please download Compass to create a new conference.",
				"join_to_conference_again_button": "Return to the conference"
			},
			"kicked_from_conference_content": {
				"title": "You have been removed from the conference",
				"desc": "Please download Compass to create a new conference."
			},
			"conference_not_found_content": {
				"title": "Videoconference not found",
				"desc": "Please check the link or contact the conference organizer."
			},
			"conference_limit": {
				"title": "You have reached the limit",
				"desc_one_minute": "You have reached the limit to join the conference via link. Please wait for $MINUTES minute and try again.",
				"desc_two_minutes": "You have reached the limit to join the conference via link. Please try again in $MINUTES minutes.",
				"desc_five_minutes": "You have reached the limit to join the conference via link. Please try again in $MINUTES minutes.",
				"desc_one_hour": "You have reached the limit to join the conference via link. Please try again in $HOURS hour.",
				"desc_two_hours": "You have reached the limit to join the conference via link. Please try again in $HOURS hours.",
				"desc_five_hours": "You have reached the limit to join the conference via link. Please try again in $HOURS hours."
			},
			"unsupported_browser": {
				"title": "Browser not supported",
				"desc": "Please update your browser to join the conference or continue in the Compass app.",
				"open_compass_button": "Open Compass",
				"supported_browsers_button": "Supported browsers",
				"supported_browsers_popover": {
					"title": "Supported browsers",
					"supported_browser_list": "Chrome version $SUPPORTED_DESKTOP_CHROME_VERSION or later Firefox version $SUPPORTED_DESKTOP_FIREFOX_VERSION or later Safari version $SUPPORTED_DESKTOP_SAFARI_VERSION or later Edge version $SUPPORTED_DESKTOP_EDGE_VERSION or later",
					"ios_supported_browser_list": "Chrome version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION or later Firefox version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION or later Safari version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION or later Edge version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION or later",
					"android_supported_browser_list": "Chrome version $SUPPORTED_MOBILE_ANDROID_CHROME_VERSION or later Firefox version $SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION or later",
					"desc": "If you have trouble joining the conference, please contact us at"
				}
			},
			download_compass: {
				button: "Download Compass",
				stores: {
					"appstore": "Go to App Store",
					"google_play": "Go to Google Play",
					"app_gallery": "Go to AppGallery"
				},
			},
		},
		preview: {
			title: "Join Compass videoconference",
			description: "Join the videoconference via link or in the Compass corporate messenger",
		},
		"network_error": "No Internet connection",
		"server_error": "Connection error. Please try again.",
		"try_compass_button": "Download Compass",
		"loading": "Loading",
	},
	de: {
		desktop: {
			"request_permissions_page": {
				"desc": "Um an der Compass-Videokonferenz teilzunehmen, erlauben Sie bitte den Zugriff auf Ihre Kamera und Ihr Mikrofon."
			},
			"join_public_conference_content": {
				"title": "Videokonferenz betreten",
				"desc": "Nehmen Sie an der Videokonferenz teil, indem Sie den Link anklicken oder die Compass-App verwenden.",
				"join_button": "Betreten",
				"open_compass_button": "In Compass fortfahren"
			},
			"join_private_conference_content": {
				"title": "Private Konferenz",
				"desc": "Bitte treten Sie der Konferenz in dem Compass-Workspace bei, in dem sie stattfindet, oder bitten Sie den Organisator, sie öffentlich zu machen, und klicken Sie auf „Erneut versuchen“.",
				"join_via_compass_button": "Über den Browser betreten",
				"try_again_button": "Erneut versuchen"
			},
			"conference_ended_content": {
				"title": "Die Videokonferenz ist abgeschlossen",
				"desc": "Der Meeting-Veranstalter hat die Videokonferenz abgeschlossen. Laden Sie Compass herunter, um eine neue Konferenz zu erstellen.",
			},
			"leave_conference_content": {
				"title": "Sie haben die Konferenz verlassen",
				"desc": "Laden Sie Compass herunter, um eine neue Konferenz zu erstellen.",
				"join_to_conference_again_button": "Zurück zur Konferenz"
			},
			"kicked_from_conference_content": {
				"title": "Sie wurden aus der Konferenz ausgeschlossen",
				"desc": "Laden Sie Compass herunter, um eine neue Konferenz zu erstellen."
			},
			"conference_not_found_content": {
				"title": "Videokonferenz nicht gefunden",
				"desc": "Bitte überprüfen Sie den Link oder kontaktieren Sie die Person, die Sie eingeladen hat."
			},
			"conference_limit": {
				"title": "Limit erreicht",
				"desc_one_minute": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $MINUTES Minute erneut.",
				"desc_two_minutes": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $MINUTES Minuten erneut.",
				"desc_five_minutes": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $MINUTES Minuten erneut.",
				"desc_one_hour": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $HOURS Stunde erneut.",
				"desc_two_hours": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte warten Sie $HOURS Stunden und versuchen Sie es erneut.",
				"desc_five_hours": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $HOURS Stunden erneut."
			},
			"unsupported_browser": {
				"title": "Browser nicht unterstützt",
				"desc": "Bitte aktualisieren Sie Ihren Browser, um an der Videokonferenz teilzunehmen, oder setzen Sie sie in der Compass-App fort.",
				"open_compass_button": "In Compass fortfahren",
				"supported_browsers_button": "Unterstützte Browser",
				"supported_browsers_popover": {
					"title": "Unterstützte Browser",
					"supported_browser_list": "Chrome $SUPPORTED_DESKTOP_CHROME_VERSION und höher Firefox $SUPPORTED_DESKTOP_FIREFOX_VERSION und höher Safari $SUPPORTED_DESKTOP_SAFARI_VERSION und höher Edge $SUPPORTED_DESKTOP_EDGE_VERSION und höher",
					"desc": "Wenn Sie Probleme beim Verbinden mit der Konferenz haben, schreiben Sie uns an"
				}
			},
			download_compass: {
				button: "Compass herunterladen",
				builds: {
					"macos_download": "Für MacOS herunterladen",
					"intel_version": "Intel",
					"arm_version": "M1 oder neuer",
					"windows_download": "Für Windows herunterladen",
					"windows_10": "Windows 10 und höher",
					"windows_7": "Windows 7 und 8",
					"exe_version": ".exe",
					"msi_version": ".msi",
					"linux_download": "Für Linux herunterladen",
					"linux_astra_download": "Für Astra Linux herunterladen",
					"deb_version": ".deb",
					"tar_version": ".tar",
					"rpm_version": ".rpm",
					"astra_version": ".deb",
					"another_platforms": "Andere Plattformen"
				},
			},
		},
		mobile: {
			"request_permissions_page": {
				"desc": "Um an der Compass-Videokonferenz teilzunehmen, erlauben Sie bitte den Zugriff auf Ihre Kamera und Ihr Mikrofon."
			},
			"join_public_conference_content": {
				"title": "Videokonferenz betreten",
				"desc": "Nehmen Sie an der Videokonferenz teil, indem Sie den Link anklicken oder die Compass-App verwenden.",
				"join_button": "Betreten",
				"open_compass_button": "In Compass fortfahren"
			},
			"join_private_conference_content": {
				"title": "Private Konferenz",
				"desc": "Bitte treten Sie der Konferenz in dem Compass-Workspace bei, in dem sie stattfindet, oder bitten Sie den Organisator, sie öffentlich zu machen, und klicken Sie auf „Erneut versuchen“.",
				"join_via_compass_button": "Über den Browser betreten",
				"try_again_button": "Erneut versuchen"
			},
			"conference_ended_content": {
				"title": "Die Videokonferenz ist abgeschlossen",
				"desc": "Der Meeting-Veranstalter hat die Videokonferenz abgeschlossen. Laden Sie Compass herunter, um eine neue Konferenz zu erstellen.",
			},
			"leave_conference_content": {
				"title": "Sie haben die Konferenz verlassen",
				"desc": "Laden Sie Compass herunter, um eine neue Konferenz zu erstellen.",
				"join_to_conference_again_button": "Zurück zur Konferenz"
			},
			"kicked_from_conference_content": {
				"title": "Sie wurden aus der Konferenz ausgeschlossen",
				"desc": "Laden Sie Compass herunter, um eine neue Konferenz zu erstellen."
			},
			"conference_not_found_content": {
				"title": "Videokonferenz nicht gefunden",
				"desc": "Bitte überprüfen Sie den Link oder kontaktieren Sie die Person, die Sie eingeladen hat."
			},
			"conference_limit": {
				"title": "Limit erreicht",
				"desc_one_minute": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $MINUTES Minute erneut.",
				"desc_two_minutes": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $MINUTES Minuten erneut.",
				"desc_five_minutes": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $MINUTES Minuten erneut.",
				"desc_one_hour": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $HOURS Stunde erneut.",
				"desc_two_hours": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte warten Sie $HOURS Stunden und versuchen Sie es erneut.",
				"desc_five_hours": "Sie haben das Limit an Versuche für die Verbindung mit der Konferenz erreicht. Bitte versuchen Sie es in $HOURS Stunden erneut."
			},
			"unsupported_browser": {
				"title": "Browser nicht unterstützt",
				"desc": "Bitte aktualisieren Sie Ihren Browser, um an der Videokonferenz teilzunehmen, oder setzen Sie sie in der Compass-App fort.",
				"open_compass_button": "In Compass fortfahren",
				"supported_browsers_button": "Unterstützte Browser",
				"supported_browsers_popover": {
					"title": "Unterstützte Browser",
					"supported_browser_list": "Chrome $SUPPORTED_DESKTOP_CHROME_VERSION und höher Firefox $SUPPORTED_DESKTOP_FIREFOX_VERSION und höher Safari $SUPPORTED_DESKTOP_SAFARI_VERSION und höher Edge $SUPPORTED_DESKTOP_EDGE_VERSION und höher",
					"ios_supported_browser_list": "Chrome $SUPPORTED_MOBILE_IOS_SAFARI_VERSION und höher Firefox $SUPPORTED_MOBILE_IOS_SAFARI_VERSION und höher Safari $SUPPORTED_MOBILE_IOS_SAFARI_VERSION und höher Edge $SUPPORTED_MOBILE_IOS_SAFARI_VERSION und höher",
					"android_supported_browser_list": "Chrome $SUPPORTED_MOBILE_ANDROID_CHROME_VERSION und höher Firefox $SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION und höher",
					"desc": "Wenn Sie Probleme beim Verbinden mit der Konferenz haben, schreiben Sie uns an"
				}
			},
			download_compass: {
				button: "Compass herunterladen",
				stores: {
					"appstore": "Zum App Store gehen",
					"google_play": "Zu Google Play gehen",
					"app_gallery": "Zur AppGallery gehen"
				},
			},
		},
		"preview": {
			"title": "Videokonferenz betreten",
			"description": "Nehmen Sie indem Sie den Link anklicken oder in der Compass Unternehmens-Messenger verwenden"
		},
		"network_error": "Keine Internetverbindung",
		"server_error": "Verbindungsfehler. Bitte versuchen Sie es erneut.",
		"try_compass_button": "Compass herunterladen",
		"loading": "Wird geladen",

	},
	fr: {
		desktop: {
			"request_permissions_page": {
				"desc": "Pour rejoindre la vidéoconférence Compass, veuillez autoriser l’accès à votre caméra et à votre microphone."
			},
			"join_public_conference_content": {
				"title": "Rejoindre la visioconférence",
				"desc": "Rejoignez la visioconférence dans le navigateur ou dans l'application « Compass ».",
				"join_button": "Connecter",
				"open_compass_button": "Ouvrir Compass"
			},
			"join_private_conference_content": {
				"title": "Conférence privée",
				"desc": "Veuillez rejoindre la conférence dans l'espace de travail Compass où elle se déroule, ou demandez à l'organisateur de la rendre publique et cliquez sur « Réessayer ».",
				"join_via_compass_button": "Se connecter dans Compass",
				"try_again_button": "Réessayer"
			},
			"conference_ended_content": {
				"title": "La visioconférence est terminée",
				"desc": "L'organisateur de réunion a terminé la visioconférence. Téléchargez Compass pour créer une nouvelle conférence.",
			},
			"leave_conference_content": {
				"title": "Vous avez quitté la conférence",
				"desc": "Téléchargez Compass pour créer une nouvelle conférence.",
				"join_to_conference_again_button": "Revenir à la conférence"
			},
			"kicked_from_conference_content": {
				"title": "Vous avez été exclu de la conférence",
				"desc": "Téléchargez Compass pour créer une nouvelle conférence."
			},
			"conference_not_found_content": {
				"title": "Visioconférence non trouvé",
				"desc": "Veuillez vérifier le lien ou contacter l'organisateur de la conférence."
			},
			"conference_limit": {
				"title": "Limite atteinte",
				"desc_one_minute": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $MINUTES minute et réessayez.",
				"desc_two_minutes": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $MINUTES minutes et réessayez.",
				"desc_five_minutes": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $MINUTES minutes et réessayez.",
				"desc_one_hour": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $HOURS heure et réessayez.",
				"desc_two_hours": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $HOURS heures et réessayez.",
				"desc_five_hours": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $HOURS heures et réessayez."
			},
			"unsupported_browser": {
				"title": "Navigateur n'est pas pris en charge",
				"desc": "Veuillez mettre à jour votre navigateur pour rejoindre la visioconférence ou continuez dans l'application Compass.",
				"open_compass_button": "Ouvrir Compass",
				"supported_browsers_button": "Navigateurs pris en charge",
				"supported_browsers_popover": {
					"title": "Navigateurs pris en charge",
					"supported_browser_list": "Chrome version $SUPPORTED_DESKTOP_CHROME_VERSION ou ultérieur Firefox version $SUPPORTED_DESKTOP_FIREFOX_VERSION ou ultérieur Safari version $SUPPORTED_DESKTOP_SAFARI_VERSION ou ultérieur Edge version $SUPPORTED_DESKTOP_EDGE_VERSION ou ultérieur",
					"desc": "Si vous rencontrez un problème pour vous connecter à la conférence, écrivez-nous à"
				}
			},
			download_compass: {
				button: "Télécharger Compass",
				builds: {
					"macos_download": "Télécharger pour MacOS",
					"intel_version": "Intel",
					"arm_version": "M1 ou plus récents",
					"windows_download": "Télécharger pour Windows",
					"windows_10": "Windows 10 ou ultérieur",
					"windows_7": "Windows 7 et 8",
					"exe_version": ".exe",
					"msi_version": ".msi",
					"linux_download": "Télécharger pour Linux",
					"linux_astra_download": "Télécharger pour Astra Linux",
					"deb_version": ".deb",
					"tar_version": ".tar",
					"rpm_version": ".rpm",
					"astra_version": ".deb",
					"another_platforms": "Autres plateformes"
				},
			},
		},
		mobile: {
			"request_permissions_page": {
				"desc": "Pour rejoindre la vidéoconférence Compass, veuillez autoriser l’accès à votre caméra et à votre microphone."
			},
			"join_public_conference_content": {
				"title": "Rejoindre la visioconférence",
				"desc": "Rejoignez la visioconférence dans le navigateur ou dans l'application « Compass ».",
				"join_button": "Connecter",
				"open_compass_button": "Ouvrir Compass"
			},
			"join_private_conference_content": {
				"title": "Conférence privée",
				"desc": "Veuillez rejoindre la conférence dans l'espace de travail Compass où elle se déroule, ou demandez à l'organisateur de la rendre publique et cliquez sur « Réessayer ».",
				"join_via_compass_button": "Se connecter dans Compass",
				"try_again_button": "Réessayer"
			},
			"conference_ended_content": {
				"title": "La visioconférence est terminée",
				"desc": "L'organisateur de réunion a terminé la visioconférence. Téléchargez Compass pour créer une nouvelle conférence.",
			},
			"leave_conference_content": {
				"title": "Vous avez quitté la conférence",
				"desc": "Téléchargez Compass pour créer une nouvelle conférence.",
				"join_to_conference_again_button": "Revenir à la conférence"
			},
			"kicked_from_conference_content": {
				"title": "Vous avez été exclu de la conférence",
				"desc": "Téléchargez Compass pour créer une nouvelle conférence."
			},
			"conference_not_found_content": {
				"title": "Visioconférence non trouvé",
				"desc": "Veuillez vérifier le lien ou contacter l'organisateur de la conférence."
			},
			"conference_limit": {
				"title": "Limite atteinte",
				"desc_one_minute": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $MINUTES minute et réessayez.",
				"desc_two_minutes": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $MINUTES minutes et réessayez.",
				"desc_five_minutes": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $MINUTES minutes et réessayez.",
				"desc_one_hour": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $HOURS heure et réessayez.",
				"desc_two_hours": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $HOURS heures et réessayez.",
				"desc_five_hours": "Vous n'avez plus de tentatives de rejoindre la conférence par le lien. Veuillez attendre $HOURS heures et réessayez."
			},
			"unsupported_browser": {
				"title": "Navigateur n'est pas pris en charge",
				"desc": "Veuillez mettre à jour votre navigateur pour rejoindre la visioconférence ou continuez dans l'application Compass.",
				"open_compass_button": "Ouvrir Compass",
				"supported_browsers_button": "Navigateurs pris en charge",
				"supported_browsers_popover": {
					"title": "Navigateurs pris en charge",
					"supported_browser_list": "Chrome version $SUPPORTED_DESKTOP_CHROME_VERSION ou ultérieur Firefox version $SUPPORTED_DESKTOP_FIREFOX_VERSION ou ultérieur Safari version $SUPPORTED_DESKTOP_SAFARI_VERSION ou ultérieur Edge version $SUPPORTED_DESKTOP_EDGE_VERSION ou ultérieur",
					"ios_supported_browser_list": "Chrome version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION ou ultérieur Firefox version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION ou ultérieur Safari version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION ou ultérieur Edge version $SUPPORTED_MOBILE_IOS_SAFARI_VERSION ou ultérieur",
					"android_supported_browser_list": "Chrome version $SUPPORTED_MOBILE_ANDROID_CHROME_VERSION ou ultérieur Firefox version $SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION ou ultérieur",
					"desc": "Si vous rencontrez un problème pour vous connecter à la conférence, écrivez-nous à"
				}
			},
			download_compass: {
				button: "Télécharger Compass",
				stores: {
					"appstore": "Aller à l'Appstore",
					"google_play": "Aller sur Google Play",
					"app_gallery": "Aller à AppGallery"
				},
			},

		},
		"preview": {
			"title": "Rejoindre la visioconférence",
			"description": "Rejoignez par le lien ou dans le messager corporatif Compass"
		},
		"network_error": "Pas de connexion Internet",
		"server_error": "Erreur de connexion. Réessayez.",
		"try_compass_button": "Télécharger Compass",
		"loading": "Chargement",
	},
	es: {
		desktop: {
			"request_permissions_page": {
				"desc": "Para unirse a la videoconferencia de Compass, permita el acceso a su cámara y micrófono."
			},
			"join_public_conference_content": {
				"title": "Conectarse a la videoconferencia",
				"desc": "Únase a la videoconferencia haciendo clic aquí o en la aplicación Compass.",
				"join_button": "Conectarse",
				"open_compass_button": "Continuar en Compass"
			},
			"join_private_conference_content": {
				"title": "Conferencia privada",
				"desc": "Accede al espacio de trabajo de Compass donde se está llevando a cabo la conferencia, o pide al organizador que la haga pública y haz clic en \"Intentar de nuevo\".",
				"join_via_compass_button": "Conectarse en Compass",
				"try_again_button": "Intentar de nuevo"
			},
			"conference_ended_content": {
				"title": "Videoconferencia finalizada",
				"desc": "El organizador de encuentro dio por concluida la videoconferencia. Descarga Compass para crear una nueva conferencia.",
			},
			"leave_conference_content": {
				"title": "Ha abandonado la conferencia",
				"desc": "Descarga Compass para crear una nueva conferencia.",
				"join_to_conference_again_button": "Volver a la conferencia"
			},
			"kicked_from_conference_content": {
				"title": "Has sido eliminado de la conferencia",
				"desc": "Descarga Compass para crear una nueva conferencia."
			},
			"conference_not_found_content": {
				"title": "Videoconferencia no encontrado",
				"desc": "Consulta el enlace o contacta al organizador de la conferencia."
			},
			"conference_limit": {
				"title": "Límite alcanzado",
				"desc_one_minute": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $MINUTES minuto y vuelva a intentarlo.",
				"desc_two_minutes": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $MINUTES minutos y vuelva a intentarlo.",
				"desc_five_minutes": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $MINUTES minutos y vuelva a intentarlo.",
				"desc_one_hour": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $HOURS hora y vuelva a intentarlo.",
				"desc_two_hours": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $HOURS horas y vuelva a intentarlo.",
				"desc_five_hours": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $HOURS horas y vuelva a intentarlo."
			},
			"unsupported_browser": {
				"title": "Navegador no compatible",
				"desc": "Actualiza tu navegador para unirte a la videoconferencia o continúa en la aplicación Compass.",
				"open_compass_button": "Continuar en Compass",
				"supported_browsers_button": "Navegadores compatibles",
				"supported_browsers_popover": {
					"title": "Navegadores compatibles",
					"supported_browser_list": "Chrome versión $SUPPORTED_DESKTOP_CHROME_VERSION o posterior Firefox versión $SUPPORTED_DESKTOP_FIREFOX_VERSION o posterior Safari versión $SUPPORTED_DESKTOP_SAFARI_VERSION o posterior Edge versión $SUPPORTED_DESKTOP_EDGE_VERSION o posterior",
					"desc": "Si tienes algún problema al conectarte a la conferencia, escríbenos a"
				}
			},
			download_compass: {
				button: "Scarica Compass",
				builds: {
					"macos_download": "Descargar para MacOS",
					"intel_version": "Intel",
					"arm_version": "M1 o más reciente",
					"windows_download": "Descargar para Windows",
					"windows_10": "Windows 10 o posterior",
					"windows_7": "Windows 7 y 8",
					"exe_version": ".exe",
					"msi_version": ".msi",
					"linux_download": "Descarga para Linux",
					"linux_astra_download": "Descarga para Astra Linux",
					"deb_version": ".deb",
					"tar_version": ".tar",
					"rpm_version": ".rpm",
					"astra_version": ".deb",
					"another_platforms": "Otras plataformas"
				},
			},
		},
		mobile: {
			"request_permissions_page": {
				"desc": "Para unirse a la videoconferencia de Compass, permita el acceso a su cámara y micrófono."
			},
			"join_public_conference_content": {
				"title": "Conectarse a la videoconferencia",
				"desc": "Únase a la videoconferencia haciendo clic aquí o en la aplicación Compass.",
				"join_button": "Conectarse",
				"open_compass_button": "Continuar en Compass"
			},
			"join_private_conference_content": {
				"title": "Conferencia privada",
				"desc": "Accede al espacio de trabajo de Compass donde se está llevando a cabo la conferencia, o pide al organizador que la haga pública y haz clic en \"Intentar de nuevo\".",
				"join_via_compass_button": "Conectarse en Compass",
				"try_again_button": "Intentar de nuevo"
			},
			"conference_ended_content": {
				"title": "Videoconferencia finalizada",
				"desc": "El organizador de encuentro dio por concluida la videoconferencia. Descarga Compass para crear una nueva conferencia.",
			},
			"leave_conference_content": {
				"title": "Ha abandonado la conferencia",
				"desc": "Descarga Compass para crear una nueva conferencia.",
				"join_to_conference_again_button": "Volver a la conferencia"
			},
			"kicked_from_conference_content": {
				"title": "Has sido eliminado de la conferencia",
				"desc": "Descarga Compass para crear una nueva conferencia."
			},
			"conference_not_found_content": {
				"title": "Videoconferencia no encontrado",
				"desc": "Consulta el enlace o contacta al organizador de la conferencia."
			},
			"conference_limit": {
				"title": "Límite alcanzado",
				"desc_one_minute": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $MINUTES minuto y vuelva a intentarlo.",
				"desc_two_minutes": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $MINUTES minutos y vuelva a intentarlo.",
				"desc_five_minutes": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $MINUTES minutos y vuelva a intentarlo.",
				"desc_one_hour": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $HOURS hora y vuelva a intentarlo.",
				"desc_two_hours": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $HOURS horas y vuelva a intentarlo.",
				"desc_five_hours": "Ha agotado todos los intentos de conexión a la conferencia mediante el enlace. Por favor, espere $HOURS horas y vuelva a intentarlo."
			},
			"unsupported_browser": {
				"title": "Navegador no compatible",
				"desc": "Actualiza tu navegador para unirte a la videoconferencia o continúa en la aplicación Compass.",
				"open_compass_button": "Continuar en Compass",
				"supported_browsers_button": "Navegadores compatibles",
				"supported_browsers_popover": {
					"ios_supported_browser_list": "Chrome versión $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o posterior Firefox версии $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o posterior Safari versión $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o posterior Edge versión $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o posterior",
					"android_supported_browser_list": "Chrome versión $SUPPORTED_MOBILE_ANDROID_CHROME_VERSION o posterior Firefox versión $SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION o posterior",
					"desc": "Si tienes algún problema al conectarte a la conferencia, escríbenos a"
				}
			},
			download_compass: {
				button: "Scarica Compass",
				stores: {
					"appstore": "Ir a Appstore",
					"google_play": "Ir a Google Play",
					"app_gallery": "Ir a AppGallery"
				},
			},
		},
		"preview": {
			"title": "Conectarse a la videoconferencia",
			"description": "Únase el enlace o en la aplicación de mensajería corporativa Compass"
		},
		"network_error": "No hay conexión a Internet",
		"server_error": "Error de conexión. Vuelva a intentarlo.",
		"try_compass_button": "Descargar Compass",
		"loading": "Cargando",
	},
	it: {
		desktop: {
			"request_permissions_page": {
				"desc": "Per partecipare alla videoconferenza di Compass, consenti l’accesso alla tua fotocamera e al microfono."
			},
			"join_public_conference_content": {
				"title": "Partecipa a una videoconferenza",
				"desc": "Partecipa alla videoconferenza nel browser o nell'app Compass.",
				"join_button": "Partecipa",
				"open_compass_button": "Continua in Compass"
			},
			"join_private_conference_content": {
				"title": "Conferenza privata",
				"desc": "Accedi allo spazio di lavoro Compass in cui si svolge la conferenza, oppure chiedi all'organizzatore di renderla pubblica e clicca su \"Riprova\".",
				"join_via_compass_button": "Connettiti in Compass",
				"try_again_button": "Riprova"
			},
			"conference_ended_content": {
				"title": "Videoconferenza terminata",
				"desc": "L'organizzatore ha terminato la videoconferenza. Scarica Compass per creare una nuova conferenza.",
			},
			"leave_conference_content": {
				"title": "Hai abbandonato la conferenza",
				"desc": "Scarica Compass per creare una nuova conferenza.",
				"join_to_conference_again_button": "Torna alla conferenza"
			},
			"kicked_from_conference_content": {
				"title": "Sei stato rimosso dalla conferenza",
				"desc": "Scarica Compass per creare una nuova conferenza."
			},
			"conference_not_found_content": {
				"title": "Videoconferenza non trovato",
				"desc": "Consultare il link o contatta l'organizzatore della conferenza."
			},
			"conference_limit": {
				"title": "Limite raggiunto",
				"desc_one_minute": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi 1 minuto e riprova.",
				"desc_two_minutes": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $MINUTES minuti e riprova.",
				"desc_five_minutes": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $MINUTES minuti e riprova.",
				"desc_one_hour": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $HOURS ora e riprova.",
				"desc_two_hours": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $HOURS ore e riprova.",
				"desc_five_hours": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $HOURS ore e riprova."
			},
			"unsupported_browser": {
				"title": "Browser non supportato",
				"desc": "Aggiorna il browser per partecipare alla videoconferenza o continua nell'app Compass.",
				"open_compass_button": "Continua in Compass",
				"supported_browsers_button": "Browser supportati",
				"supported_browsers_popover": {
					"title": "Browser supportati",
					"supported_browser_list": "Chrome versione $SUPPORTED_DESKTOP_CHROME_VERSION o successive Firefox versione $SUPPORTED_DESKTOP_FIREFOX_VERSION o successive Safari versione $SUPPORTED_DESKTOP_SAFARI_VERSION o successive Edge versione $SUPPORTED_DESKTOP_EDGE_VERSION o successive",
					"desc": "Se riscontri problemi durante la connessione alla conferenza, scrivici a"
				}
			},
			download_compass: {
				button: "Descargar Compass",
				builds: {
					"macos_download": "Scarica per MacOS",
					"intel_version": "Intel",
					"arm_version": "M1 o più recente",
					"windows_download": "Scarica per Windows",
					"windows_10": "Windows 10 o successive",
					"windows_7": " Windows 7 and 8",
					"exe_version": ".exe",
					"msi_version": ".msi",
					"linux_download": "Scarica per Linux",
					"linux_astra_download": "Scarica per Astra Linux",
					"deb_version": ".deb",
					"tar_version": ".tar",
					"rpm_version": ".rpm",
					"astra_version": ".deb",
					"another_platforms": "Altre piattaforme"
				},
			},
		},
		mobile: {
			"request_permissions_page": {
				"desc": "Per partecipare alla videoconferenza di Compass, consenti l’accesso alla tua fotocamera e al microfono."
			},
			"join_public_conference_content": {
				"title": "Partecipa a una videoconferenza",
				"desc": "Partecipa alla videoconferenza nel browser o nell'app Compass.",
				"join_button": "Partecipa",
				"open_compass_button": "Continua in Compass"
			},
			"join_private_conference_content": {
				"title": "Conferenza privata",
				"desc": "Accedi allo spazio di lavoro Compass in cui si svolge la conferenza, oppure chiedi all'organizzatore di renderla pubblica e clicca su \"Riprova\".",
				"join_via_compass_button": "Connettiti in Compass",
				"try_again_button": "Riprova"
			},
			"conference_ended_content": {
				"title": "Videoconferenza terminata",
				"desc": "L'organizzatore ha terminato la videoconferenza. Scarica Compass per creare una nuova conferenza.",
			},
			"leave_conference_content": {
				"title": "Hai abbandonato la conferenza",
				"desc": "Scarica Compass per creare una nuova conferenza.",
				"join_to_conference_again_button": "Torna alla conferenza"
			},
			"kicked_from_conference_content": {
				"title": "Sei stato rimosso dalla conferenza",
				"desc": "Scarica Compass per creare una nuova conferenza."
			},
			"conference_not_found_content": {
				"title": "Videoconferenza non trovato",
				"desc": "Consultare il link o contatta l'organizzatore della conferenza."
			},
			"conference_limit": {
				"title": "Limite raggiunto",
				"desc_one_minute": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi 1 minuto e riprova.",
				"desc_two_minutes": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $MINUTES minuti e riprova.",
				"desc_five_minutes": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $MINUTES minuti e riprova.",
				"desc_one_hour": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $HOURS ora e riprova.",
				"desc_two_hours": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $HOURS ore e riprova.",
				"desc_five_hours": "Hai esaurito tutti i tentativi di connessione alla conferenza tramite il link. Attendi $HOURS ore e riprova."
			},
			"unsupported_browser": {
				"title": "Browser non supportato",
				"desc": "Aggiorna il browser per partecipare alla videoconferenza o continua nell'app Compass.",
				"open_compass_button": "Continua in Compass",
				"supported_browsers_button": "Browser supportati",
				"supported_browsers_popover": {
					"title": "Browser supportati",
					"supported_browser_list": "Chrome versione $SUPPORTED_DESKTOP_CHROME_VERSION o successive Firefox versione $SUPPORTED_DESKTOP_FIREFOX_VERSION o successive Safari versione $SUPPORTED_DESKTOP_SAFARI_VERSION o successive Edge versione $SUPPORTED_DESKTOP_EDGE_VERSION o successive",
					"ios_supported_browser_list": "Chrome versione $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o successive Firefox versione $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o successive Safari versione $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o successive Edge versione $SUPPORTED_MOBILE_IOS_SAFARI_VERSION o successive",
					"android_supported_browser_list": "Chrome versione $SUPPORTED_MOBILE_ANDROID_CHROME_VERSION o successive Firefox versione $SUPPORTED_MOBILE_ANDROID_FIREFOX_VERSION o successive",
					"desc": "Se riscontri problemi durante la connessione alla conferenza, scrivici a"
				}
			},
			download_compass: {
				button: "Descargar Compass",
				stores: {
					"appstore": "Vai all'Appstore",
					"google_play": "Vai a Google Play",
					"app_gallery": "Vai all'AppGallery"
				},
			},
		},
		"preview": {
			"title": "Partecipa a una videoconferenza",
			"description": "Partecipa il link o nell'app di messaggistica aziendale di Compass"
		},
		"network_error": "Nessuna connessione a Internet",
		"server_error": "Errore di connessione. Riprova.",
		"try_compass_button": "Scarica Compass",
		"loading": "Caricamento",
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
