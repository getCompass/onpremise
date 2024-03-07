import { useAtomValue } from "jotai";
import { langState } from "../api/_stores.ts";
import { Lang } from "../api/_types.ts";

type LangConfig = {
	[K in Lang]?: Record<string, any>;
};

const LANG_CONFIG: LangConfig = {
	ru: {
		welcome_dialog: {
			title: "Приглашение",
			desc_mobile:
				" приглашает вас в корпоративный мессенджер Compass. Пожалуйста, авторизуйтесь для начала работы.",
			desc_desktop:
				" приглашает вас в корпоративный мессенджер Compass. Пожалуйста, авторизуйтесь для начала работы.",
			confirm_button: "Продолжить",
		},
		email_phone_number_dialog: {
			title: "Привет",
			desc_email: "Для авторизации введите email:",
			desc_phone_number: "Для авторизации введите номер телефона:",
			desc_email_phone_number: "Для авторизации введите email или номер телефона:",
			input_placeholder_email: "Email",
			input_placeholder_phone_number: "Телефон",
			input_placeholder_email_phone_number: "Email или телефон",
			confirm_button: "Продолжить",
			prohibited_symbols_tooltip: "Эмодзи, пробелы и специальные символы не поддерживаются",
		},
		email_register_dialog: {
			title: "Compass",
			desc: "Придумайте пароль для авторизации через ",
			password_input_placeholder: "Пароль (минимум 8 символов)",
			confirm_password_input_placeholder: "Повторите пароль",
			back_button: "← Назад",
			register_button: "Зарегистрироваться",
			passwords_not_match_error: "Пароли не совпадают",
			password_less_than_min_symbols_error: "Пожалуйста, введите минимум 8 символов",
		},
		email_login_dialog: {
			title: "Compass",
			desc: "Введите пароль для авторизации через ",
			password_input_placeholder: "Введите пароль",
			back_button: "← Назад",
			login_button: "Продолжить",
			forgot_password_button: "Забыли пароль?",
			auth_blocked: "Вы израсходовали все попытки ввода пароля. Пожалуйста, повторите через $MINUTES.",
		},
		forgot_password_dialog: {
			title: "Compass",
			desc: "Пройдите проверку для перехода к сбросу пароля",
		},
		create_new_password_dialog: {
			title: "Создать новый пароль",
			desc: "Пароль будет запрашиваться при авторизации через почту ",
			password_input_placeholder: "Пароль (минимум 8 символов)",
			confirm_password_input_placeholder: "Повторите пароль",
			cancel_button: "Отмена",
			confirm_button: "Создать",
			success_tooltip_message: "Пароль изменён",
			passwords_not_match_error: "Пароли не совпадают",
			password_less_than_min_symbols_error: "Пожалуйста, введите минимум 8 символов",
		},
		confirm_code_phone_number_dialog: {
			title: "Compass",
			desc: "Код из SMS ",
			back_button: "Назад",
			resend_button: "Повторить отправку",
			resend_after: "Повторить через ",
			auth_blocked: "Вы израсходовали все попытки подтверждения по SMS. Пожалуйста, повторите через $MINUTES.",
		},
		confirm_code_email_dialog: {
			title_desktop: "Код подтверждения",
			title_mobile: "Подтвердите почту",
			desc: "Пожалуйста, введите код, отправленный на ",
			back_button: "Назад",
			resend_button: "Повторить отправку",
			resend_after: "Повторить через ",
			auth_blocked: "Вы израсходовали все попытки подтверждения по коду. Пожалуйста, повторите через $MINUTES.",
		},
		create_profile_dialog: {
			title: "Создать профиль",
			desc: "У каждого пользователя в команде есть профиль, который видят собеседники. Создайте свой профиль.",
			input_placeholder: "Имя Фамилия",
			cancel_button: "Отмена",
			confirm_button: "Далее",
			incorrect_name_tooltip:
				"Чтобы участникам команды было удобно с вами общаться, пожалуйста, напишите имя и фамилию на вашем языке.",
			not_saved_symbols_tooltip: "Эмодзи и специальные символы не будут сохранены",
			confirm_cancel_mobile: {
				title: "Отменить регистрацию?",
				short_title: "Отменить?",
				confirm_button: "Да",
				cancel_button: "Нет",
			},
			confirm_cancel_desktop: {
				title: "Завершить регистрацию?",
				desc: "Процесс регистрации будет остановлен. В любой момент вы можете начать его сначала.",
				confirm_button: "Завершить",
				cancel_button: "Отмена",
			},
		},
		page_token: {
			title: "Почти готово",
			desc: "Чтобы начать общение внутри команды, пожалуйста, сделайте два простых шага:",
			step_1: {
				register_desc_pt1: "Скопируйте секретный код",
				register_desc_pt2: " и вставьте его в приложении.",
				register_button: "Скопировать",
				login_desc_pt1: "Откройте приложение Compass",
				login_desc_pt2: " и при необходимости введите секретный код.",
				login_button: "Открыть Compass",
			},
			step_2: {
				desc_pt1_mobile: "Установите приложение Compass,",
				desc_pt1_desktop: "Установите приложение Compass,",
				desc_pt2_mobile: " если его нет на вашем телефоне.",
				desc_pt2_desktop: " если его нет на вашем компьютере.",
				button_mobile: "Установить",
				button_desktop: "Скачать",
			},
			mobile_stores: {
				appstore: "Перейти в App Store",
				google_play: "Перейти в Google Play",
				app_gallery: "Перейти в AppGallery",
			},
			desktop_builds: {
				macos_download: "Скачать для MacOS",
				intel_version: "Intel",
				m1m2_version: "M1/M2",
				windows_download: "Скачать для Windows",
				linux_download: "Скачать для Linux",
				deb_version: ".deb",
				tar_version: ".tar",
			},
		},
		page_invite: {
			title: "Поздравляем!",
			desc: "Вы присоединились к команде.",
			or: "или",
			open_compass_mobile: {
				title_pt1: "Откройте приложение Compass",
				title_pt2: "и начните общение внутри команды.",
				button: "Открыть Compass",
			},
			open_compass_wait_post_moderation_mobile: {
				title_pt1: "Откройте приложение Compass",
				title_pt2: "и подождите одобрения заявки.",
				button: "Открыть Compass",
			},
			open_compass_desktop: {
				title_pt1: "Откройте приложение Compass",
				title_pt2: "и при необходимости введите секретный код.",
				button: "Открыть Compass",
			},
			copy_token_mobile: {
				title_pt1: "Скопируйте секретный код для входа",
				title_pt2: " и вставьте его в приложении.",
				button: "Скопировать",
			},
			install_app_desktop: {
				title_pt1: "Установите приложение Compass,",
				title_pt2: "если его нет на вашем компьютере.",
				button: "Скачать",
			},
			already_member: {
				title: "Поздравляем!",
				desc: "Вы уже состоите в этой команде.",
			},
			waiting_for_postmoderation: {
				title: "Заявка в команду отправлена",
				desc: "Нужно немного подождать, пока руководитель одобрит вашу заявку.",
			},
			join_as_guest: {
				title: "Поздравляем!",
				desc: "Вы вступили в команду в качестве гостя.",
			},
		},
		settings: {
			change_lang: "Сменить язык",
			logout: "Выйти",
		},
		logout_dialog: {
			title: "Хотите выйти?",
			desc: "Чтобы вернуться к авторизации, нужно будет войти.",
			cancel_button: "Отмена",
			confirm_button: "Выйти",
		},
		inactive_link: {
			title: "Ссылка неактивна",
			desc: "Пожалуйста, свяжитесь с человеком, который вас пригласил, и попросите отправить новую ссылку.",
		},
		invalid_link: {
			title: "Некорректная ссылка",
			desc: "Пожалуйста, проверьте ссылку или свяжитесь с человеком, который вас пригласил.",
		},
		accept_limit_link: {
			title: "Достигнут лимит",
			desc: "Вы израсходовали все попытки вступления в команду по ссылке. Пожалуйста, подождите $TIME и попробуйте снова.",
		},
		not_finished_space_leaving: {
			desc: "Процесс вашего удаления из этой команды ещё не был завершён. Попробуйте вступить через 2 минуты.",
		},
		confirm_close_dialog: {
			title: "Хотите закрыть?",
			desc: "Данные не сохранятся.",
			confirm_button: "Да",
			cancel_button: "Нет",
		},
		errors: {
			network_error: "Нет подключения к интернету",
			server_error: "Ошибка соединения. Попробуйте ещё раз.",
			incorrect_captcha: "Не удалось пройти капчу, попробуйте снова",
			email_limit_error: "Достигнут лимит ввода email. Попробуйте через $MINUTES.",
			phone_number_limit_error: "Достигнут лимит ввода номера. Попробуйте через $MINUTES.",
			phone_number_email_limit_error: "Достигнут лимит ввода номера или email. Попробуйте через $MINUTES.",
			phone_number_email_incorrect_phone_email_error: "Введён некорректный номер телефона или email",
			phone_number_incorrect_phone_error: "Введён некорректный номер телефона",
			email_incorrect_email_error: "Введён некорректный email",
			auth_incorrect_password_error: "Введён неверный пароль. $REMAINING_ATTEMPT_COUNTS.",
			confirm_code_limit_error: "Достигнут лимит повторной отправки кода. Попробуйте через $MINUTES.",
			confirm_code_incorrect_code_error: "Некорректный код. Пожалуйста, попробуйте ещё раз.",
			confirm_code_incorrect_code_one_left: "Осталась",
			confirm_code_incorrect_code_two_lefts: "Осталось",
			confirm_code_incorrect_code_five_lefts: "Осталось",
			confirm_code_incorrect_code_one_attempt: " попытка",
			confirm_code_incorrect_code_two_attempts: " попытки",
			confirm_code_incorrect_code_five_attempts: " попыток",
			create_profile_incorrect_name_error: "Некорректное имя.",
		},
		one_hour: " час",
		two_hours: " часа",
		five_hours: " часов",
		one_minute: " минуту",
		two_minutes: " минуты",
		five_minutes: " минут",
	},
	en: {},
	de: {},
	fr: {},
	es: {},
	it: {},
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
