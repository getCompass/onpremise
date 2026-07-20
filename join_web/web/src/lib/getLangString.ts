import {useAtomValue} from "jotai";
import {langState} from "../api/_stores.ts";
import {Lang} from "../api/_types.ts";

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
			desc_email_guest: "Для авторизации в гостевой аккаунт введите email:",
			desc_phone_number: "Для авторизации введите номер телефона:",
			desc_phone_number_guest: "Для авторизации в гостевой аккаунт введите номер телефона:",
			desc_email_phone_number: "Для авторизации введите email или номер телефона:",
			desc_email_phone_number_guest: "Для авторизации в гостевой аккаунт введите email или номер телефона:",
			desc_sso: "Для авторизации нажмите\nкнопку ниже:",
			desc_sso_guest: "Для авторизации в гостевой аккаунт нажмите кнопку ниже:",
			input_placeholder_email: "Email",
			input_placeholder_phone_number: "Телефон",
			input_placeholder_email_phone_number: "Email или телефон",
			confirm_button: "Продолжить",
			sso_button: "Войти через корп. портал (SSO LDAP)",
			open_guest_auth_methods_button: "Войти в гостевой аккаунт",
			prohibited_symbols_tooltip: "Эмодзи, пробелы и специальные символы не поддерживаются",
		},
		email_register_dialog: {
			title: "Compass",
			desc: "Придумайте пароль для авторизации через $EMAIL",
			password_input_placeholder: "Пароль (минимум 8 символов)",
			confirm_password_input_placeholder: "Повторите пароль",
			back_button: "← Назад",
			register_button: "Зарегистрироваться",
			passwords_not_match_error: "Пароли не совпадают",
			password_less_than_min_symbols_error: "Пожалуйста, введите минимум 8 символов",
		},
		email_login_dialog: {
			title: "Compass",
			desc: "Введите пароль для авторизации через $EMAIL",
			password_input_placeholder: "Введите пароль",
			back_button: "← Назад",
			login_button: "Продолжить",
			forgot_password_button: "Забыли пароль?",
			auth_blocked: "Вы израсходовали все попытки ввода пароля. Пожалуйста, повторите через $MINUTES.",
		},
		ldap_login_dialog: {
			title: "Compass",
			desc: "Для авторизации введите username и пароль от вашей корпоративной учётной записи LDAP:",
			username_input_placeholder: "Username",
			password_input_placeholder: "Пароль",
			back_button: "← Назад",
			login_button: "Продолжить",
			unknown_error:
				"Авторизация через LDAP временно недоступна. Обратитесь  к руководителю или системному администратору.",
			incorrect_credentials_error: "Введён неверный username или пароль",
			auth_blocked: "Достигнут лимит входа через LDAP. Попробуйте через $MINUTES.",
			incorrect_config_user_search_filter: "Ошибка в синтаксисе фильтра для авторизации через LDAP. Обратитесь к руководителю или системному администратору.",
			incorrect_2fa_mail_config_attribute: "Не удалось получить почту для двухфакторной аутентификации или другие данные из LDAP. Обратитесь к руководителю или системному администратору.",
		},
		ldap_2fa_add_mail_dialog: {
			title: "Двухфакторная аутентификация",
			desc: "Укажите почту, на которую будет приходить код подтверждения для доступа к аккаунту.",
			desc_change_mail: "Укажите новую почту, на которую будет приходить код подтверждения для доступа к аккаунту.",
			mail_input_placeholder: "Email",
			confirm_button: "Продолжить",
			change_mail_toast_success: "Почта для двухфакторной аутентификации изменена",
		},
		ldap_2fa_setup_totp_dialog: {
			title: "Двухфакторная аутентификация",
			desc: "Отсканируйте QR в мобильном приложении для одноразовых кодов.",
			confirm_button: "Продолжить",
			cant_scan_qr_button: "Не удается отсканировать QR-код?",
		},
		ldap_2fa_setup_totp_cant_scan_qr_dialog: {
			title: "Настройка без QR",
			first_step_desc_desktop: "В мобильном приложении для одноразовых кодов выберите ручной ввод ключа настройки.",
			first_step_desc_mobile: "В мобильном приложении для одноразовых кодов выберите ручной ввод ключа настройки.",
			second_step_desc: "Укажите ваш ключ:",
			third_step_desc: "Убедитесь, что выбран параметр «По времени». Подтвердите добавление ключа.",
		},
		ldap_2fa_confirm_totp_dialog: {
			title: "Код подтверждения",
			desc: "Пожалуйста, введите код из мобильного приложения для одноразовых кодов.",
			input_placeholder: "Код",
			confirm_button: "Продолжить",
			cant_get_code_button: "Не удается получить код?",
			cant_get_code_tooltip: "Обратитесь к руководителю или системному администратору, чтобы получить новый код.",
		},
		forgot_password_dialog: {
			title: "Compass",
			desc: "Пройдите проверку для перехода к сбросу пароля",
		},
		create_new_password_dialog: {
			title: "Создать новый пароль",
			desc: "Пароль будет запрашиваться при авторизации через почту $EMAIL",
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
			title: "Код подтверждения",
			title_ldap_change_mail_confirm_current: "Подтвердите текущую почту",
			title_ldap_change_mail_confirm_new: "Подтвердите новую почту",
			desc: "Пожалуйста, введите код, отправленный на $EMAIL",
			desc_ldap_change_mail_confirm_current: "Для перехода к смене почты введите код, отправленный на $EMAIL",
			back_button: "Назад",
			resend_button: "Повторить отправку",
			resend_after: "Повторить через ",
			auth_blocked: "Вы израсходовали все попытки подтверждения по коду. Пожалуйста, повторите через $MINUTES.",
			change_mail_button: "Изменить почту",
			ldap_2fa_change_mail_limit_error: "Достигнут лимит изменения почты. Попробуйте через 24 часа.",
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
				update_token: "Обновить код.",
				login_desc_pt1: "Откройте приложение Compass",
				login_desc_pt2_desktop: " и при необходимости введите секретный код.",
				login_desc_pt2_mobile: " и введите секретный код.",
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
			sso_error: "Авторизация через SSO недоступна. Обратитесь к руководителю или системному администратору.",
			incorrect_captcha: "Не удалось пройти капчу, попробуйте снова",
			email_limit_error: "Достигнут лимит ввода email. Попробуйте через $MINUTES.",
			phone_number_limit_error: "Достигнут лимит ввода номера. Попробуйте через $MINUTES.",
			phone_number_email_limit_error: "Достигнут лимит ввода номера или email. Попробуйте через $MINUTES.",
			sso_limit_error: "Достигнут лимит входа через SSO. Попробуйте через $MINUTES.",
			phone_number_email_incorrect_phone_email_error: "Введён некорректный номер телефона или email",
			phone_number_incorrect_phone_error: "Введён некорректный номер телефона",
			email_incorrect_email_error: "Введён некорректный email",
			auth_incorrect_password_error: "Введён неверный пароль. $REMAINING_ATTEMPT_COUNTS.",
			confirm_code_limit_error: "Достигнут лимит повторной отправки кода. Попробуйте через $MINUTES.",
			confirm_code_confirm_is_expired_error: "Время попытки авторизации истекло, пожалуйста, попробуйте ещё раз",
			confirm_code_2fa_is_disabled_error: "Двухфакторная аутентификация отключена на сервере. Пожалуйста, повторите авторизацию.",
			add_mail_2fa_ldap_not_allowed_domain_error: "Введена некорректная почта. Используйте почту с доменом «@$DOMAIN» для авторизации на сервере.",
			add_mail_2fa_ldap_not_allowed_domains_error: "Введена некорректная почта. Используйте почту с доменами $DOMAINS для авторизации на сервере.",
			confirm_code_incorrect_code_error: "Некорректный код. Пожалуйста, попробуйте ещё раз.",
			confirm_code_incorrect_code_one_left: "Осталась",
			confirm_code_incorrect_code_two_lefts: "Осталось",
			confirm_code_incorrect_code_five_lefts: "Осталось",
			confirm_code_incorrect_code_one_attempt: " попытка",
			confirm_code_incorrect_code_two_attempts: " попытки",
			confirm_code_incorrect_code_five_attempts: " попыток",
			create_profile_incorrect_name_error: "Некорректное имя.",
			auth_method_disabled:
				"Авторизация через SSO недоступна. Обратитесь к руководителю или системному администратору.",
			sso_registration_without_invite:
				"Регистрация через SSO недоступна. Обратитесь к руководителю или системному администратору для получения ссылки-приглашения.",
			auth_ldap_method_disabled:
				"Авторизация через LDAP недоступна. Обратитесь к руководителю или системному администратору.",
			ldap_registration_without_invite:
				"Регистрация через LDAP недоступна. Обратитесь к руководителю или системному администратору для получения ссылки-приглашения.",
			auth_sso_full_name_incorrect:
				"Не удалось получить Имя Фамилию или другие данные из $SSO_PROVIDER_NAME. Обратитесь к руководителю или системному администратору.",
			auth_sso_totp_code_incorrect: "Некорректный код. Пожалуйста, попробуйте ещё раз.",
		},
		token_life_time_desktop: "Код действителен в течение $TIME",
		token_life_time_mobile: "Код действителен $TIME",
		token_life_time_expired: "Время действия кода истекло.",
		one_hour: " час",
		two_hours: " часа",
		five_hours: " часов",
		one_minute: " минуту",
		two_minutes: " минуты",
		five_minutes: " минут",
		download_compass: {
			desktop_builds: {
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
			mobile_stores: {
				appstore: "Перейти в App Store",
				google_play: "Перейти в Google Play",
				app_gallery: "Перейти в AppGallery",
			},
		},
		install_page: {
			"footer": "Сделано для вас",
			desktop: {
				logo: {
					title: "COMPASS",
					onpremise_title: "On-premise",
				},
				page: {
					title: "Скачайте приложение Compass On-premise",
					desc: "Актуальные версии приложения для любого устройства.",
					download_ios: {
						desc: "Установить на iPhone",
						platform_app_store: "App Store",
					},
					download_android: {
						desc: "Установить на Android",
						platform_google_play: "Google Play",
					},
					download_huawei: {
						desc: "Установить на Huawei",
						platform_app_gallery: "AppGallery",
					},
					support_block: {
						title: "Возникли проблемы с установкой?",
						desc: "Напишите нам в Telegram или на почту.\nПоможем с установкой и настройкой.",
						telegram: "Telegram",
						mail: "support@getcompass.ru",
					},
				},
			},
			mobile: {
				logo: {
					title: "COMPASS",
					onpremise_title: "On-premise",
				},
				page: {
					title: "Установите приложение Compass On-premise",
					desc: "Актуальные версии приложения для любого устройства.",
					download_ios: "Перейти в App Store",
					download_android: "Перейти в Google Play",
					download_huawei: "Перейти в AppGallery",
					desktop_footer: "Есть версии для ",
					download_macos: "MacOS",
					download_comma: ", ",
					download_windows: "Windows",
					download_and: " и ",
					download_linux: "Linux",
					download_dot: ".",
					on_success_copy: "Ссылка на скачивание Compass для компьютера скопирована",
					support_block: {
						title: "Возникли проблемы с установкой?",
						desc: "Напишите нам в Telegram или на почту. Поможем с установкой и настройкой.",
						telegram: "Telegram",
						mail: "support@getcompass.ru",
					},
				},
			},
		},
	},
	en: {
		"welcome_dialog": {
			"title": "Invitation",
			"desc_mobile": " has invited you to join the Compass corporate messenger. Please log in to get started.",
			"desc_desktop": " has invited you to join the Compass corporate messenger. Please log in to get started.",
			"confirm_button": "Continue"
		},
		"email_phone_number_dialog": {
			"title": "Hi",
			"desc_email": "To log in, please enter your email address:",
			"desc_email_guest": "To log in to the guest account, please enter your email address:",
			"desc_phone_number": "To log in, please enter your phone number:",
			"desc_phone_number_guest": "To log in to the guest account, please enter your phone number:",
			"desc_email_phone_number": "To log in, please enter your email address or phone number:",
			"desc_email_phone_number_guest": "To log in to the guest account, please enter your email address or phone number:",
			"desc_sso": "To log in, please click the button below:",
			"desc_sso_guest": "To log in to the guest account, please click the button below:",
			"input_placeholder_email": "Email",
			"input_placeholder_phone_number": "Phone",
			"input_placeholder_email_phone_number": "Email or phone number",
			"confirm_button": "Continue",
			sso_button: "Log in via the corp. portal (SSO LDAP)",
			"open_guest_auth_methods_button": "Log in to the guest account",
			"prohibited_symbols_tooltip": "Emojis, spaces, and special characters are not supported"
		},
		"email_register_dialog": {
			"title": "Compass",
			"desc": "Create a password to log in via $EMAIL",
			"password_input_placeholder": "Password (minimum 8 characters)",
			"confirm_password_input_placeholder": "Confirm password",
			"back_button": "Back",
			"register_button": "Sign up",
			"passwords_not_match_error": "Passwords do not match",
			"password_less_than_min_symbols_error": "Please enter at least 8 characters"
		},
		"email_login_dialog": {
			"title": "Compass",
			"desc": "Enter your password to log in via $EMAIL",
			"password_input_placeholder": "Enter password",
			"back_button": "Back",
			"login_button": "Continue",
			"forgot_password_button": "Forgot password?",
			"auth_blocked": "You have reached the limit of attempts to enter password. Please try again in $MINUTES."
		},
		"ldap_login_dialog": {
			"title": "Compass",
			"desc": "To log in, please enter the username and password for your corporate LDAP account:",
			"username_input_placeholder": "Username",
			"password_input_placeholder": "Password / + code",
			"back_button": "Back",
			"login_button": "Continue",
			"unknown_error": "LDAP authentication temporarily unavailable. Please contact your manager or system administrator.",
			"incorrect_credentials_error": "Incorrect username or password entered",
			"auth_blocked": "You have reached the limit to log in via LDAP. Please try again in $MINUTES.",
			"incorrect_config_user_search_filter": "LDAP filter syntax error. Please contact your manager or system administrator.",
			"incorrect_2fa_mail_config_attribute": "Unable to get the required email for two-factor authentication or other data from LDAP. Please contact your manager or system administrator."
		},
		"ldap_2fa_add_mail_dialog": {
			"title": "Two-factor authentication",
			"desc": "Please enter the email address for receiving the verification code to access your account.",
			"desc_change_mail": "Please enter the new email address for receiving the verification code to access your account.",
			"mail_input_placeholder": "Email",
			"confirm_button": "Continue",
			"change_mail_toast_success": "Email address for two-factor authentication has been changed"
		},
		"ldap_2fa_setup_totp_dialog": {
			"title": "Two-factor authentication",
			"desc": "Scan the QR code in a mobile authenticator app.",
			"confirm_button": "Continue",
			"cant_scan_qr_button": "Having trouble scanning the QR code?"
		},
		"ldap_2fa_setup_totp_cant_scan_qr_dialog": {
			"title": "Setup without QR",
			"first_step_desc_desktop": "In your mobile authenticator app, choose manual entry for the setup key.",
			"first_step_desc_mobile": "In your mobile authenticator app, choose manual entry for the setup key.",
			"second_step_desc": "Enter your key:",
			"third_step_desc": "Ensure the \"Time-based\" option is selected. Confirm adding the key."
		},
		"ldap_2fa_confirm_totp_dialog": {
			"title": "Verification code",
			"desc": "Please enter the code from your mobile authenticator app.",
			"input_placeholder": "Code",
			"confirm_button": "Continue",
			"cant_get_code_button": "Having trouble getting a code?",
			"cant_get_code_tooltip": "Please contact your manager or system administrator to get a new code."
		},
		"forgot_password_dialog": {
			"title": "Compass",
			"desc": "Complete the verification to proceed with password reset"
		},
		"create_new_password_dialog": {
			"title": "Create new password",
			"desc": "Password will be requested when logging in via $EMAIL",
			"password_input_placeholder": "Password (minimum 8 characters)",
			"confirm_password_input_placeholder": "Confirm password",
			"cancel_button": "Cancel",
			"confirm_button": "Create",
			"success_tooltip_message": "Password changed",
			"passwords_not_match_error": "Passwords do not match",
			"password_less_than_min_symbols_error": "Please enter at least 8 characters"
		},
		"confirm_code_phone_number_dialog": {
			"title": "Compass",
			"desc": "SMS code ",
			"back_button": "Back",
			"resend_button": "Resend",
			"resend_after": "Try again in ",
			"auth_blocked": "You have reached the limit of the SMS confirmation attempts. Please try again in $MINUTES."
		},
		"confirm_code_email_dialog": {
			"title": "Verification code",
			"title_ldap_change_mail_confirm_current": "Please confirm your email address",
			"title_ldap_change_mail_confirm_new": "Confirm your new email address",
			"desc": "Please enter the code sent to $EMAIL",
			"desc_ldap_change_mail_confirm_current": "To proceed with changing your email address, please enter the code sent to $EMAIL",
			"back_button": "Back",
			"resend_button": "Resend",
			"resend_after": "Try again in ",
			"auth_blocked": "You have reached the limit of code verification attempts. Please try again in $MINUTES.",
			"change_mail_button": "Change email",
			"ldap_2fa_change_mail_limit_error": "You have reached the limit to change your email. Please try again in 24 hours."
		},
		"create_profile_dialog": {
			"title": "Create profile",
			"desc": "Each user in the team has a profile visible to coworkers. Create your profile.",
			"input_placeholder": "Enter your name",
			"cancel_button": "Cancel",
			"confirm_button": "Next",
			"incorrect_name_tooltip": "Please type the name in your language, to make it easier for the team members to communicate with you.",
			"not_saved_symbols_tooltip": "Emoji and special characters will not be saved",
			"confirm_cancel_mobile": {
				"title": "Cancel registration?",
				"short_title": "Cancel?",
				"confirm_button": "Yes",
				"cancel_button": "No"
			},
			"confirm_cancel_desktop": {
				"title": "Complete registration?",
				"desc": "The registration will stop. You can start it again at any time later.",
				"confirm_button": "Finish",
				"cancel_button": "Cancel"
			}
		},
		"page_token": {
			"title": "Almost done",
			"desc": "To start communicating with the team, please follow two simple steps:",
			"step_1": {
				"register_desc_pt1": "Copy the verification code",
				"register_desc_pt2": " and paste it in the application.",
				"register_button": "Copy",
				"update_token": "Refresh code.",
				"login_desc_pt1": "Open the Compass app",
				"login_desc_pt2_desktop": " and enter verification code if prompted.",
				"login_desc_pt2_mobile": " and enter verification code",
				"login_button": "Open Compass"
			},
			"step_2": {
				"desc_pt1_mobile": "Install Compass",
				"desc_pt1_desktop": "Install Compass",
				"desc_pt2_mobile": " if it is not on your smartphone.",
				"desc_pt2_desktop": " if it is not on your computer.",
				"button_mobile": "Install",
				"button_desktop": "Download"
			}
		},
		"page_invite": {
			"title": "Congratulations!",
			"desc": "You've joined the team.",
			"or": "or",
			"open_compass_mobile": {
				"title_pt1": "Open the Compass app",
				"title_pt2": "and start communicating within your team.",
				"button": "Open Compass"
			},
			"open_compass_wait_post_moderation_mobile": {
				"title_pt1": "Open the Compass app",
				"title_pt2": "and wait for your request to be approved.",
				"button": "Open Compass"
			},
			"open_compass_desktop": {
				"title_pt1": "Open the Compass app",
				"title_pt2": "and enter verification code if prompted.",
				"button": "Open Compass"
			},
			"copy_token_mobile": {
				"title_pt1": "Copy the verification code",
				"title_pt2": " and paste it in the application.",
				"button": "Copy"
			},
			"install_app_desktop": {
				"title_pt1": "Install Compass",
				"title_pt2": "if it is not on your computer.",
				"button": "Download"
			},
			"already_member": {
				"title": "Congratulations!",
				"desc": "You're already a member of this team."
			},
			"waiting_for_postmoderation": {
				"title": "Request to join the team sent",
				"desc": "Please wait for the team owner to approve your request."
			},
			"join_as_guest": {
				"title": "Congratulations!",
				"desc": "You've joined the team as a guest."
			}
		},
		"settings": {
			"change_lang": "Change language",
			"logout": "Log out"
		},
		"logout_dialog": {
			"title": "Log out?",
			"desc": "To return to the verification page, you will need to log in.",
			"cancel_button": "Cancel",
			"confirm_button": "Log out"
		},
		"inactive_link": {
			"title": "Link is inactive",
			"desc": "Please contact the person who invited you and ask them to send you a new link."
		},
		"invalid_link": {
			"title": "Invalid link",
			"desc": "Please check the link or contact the person who invited you."
		},
		"accept_limit_link": {
			"title": "You have reached the limit",
			"desc": "You have reached the limit to join the team using the link. Please try again in $TIME."
		},
		"not_finished_space_leaving": {
			"desc": "Your removal from this team is still in progress. Please try to join in 2 minutes."
		},
		"confirm_close_dialog": {
			"title": "Do you want to close?",
			"desc": "The data will not be saved.",
			"confirm_button": "Yes",
			"cancel_button": "No"
		},
		"errors": {
			"network_error": "No Internet connection",
			"server_error": "Connection error. Please try again.",
			"sso_error": "SSO login unavailable. Please contact your manager or system administrator.",
			"incorrect_captcha": "Captcha verification failed, please try again",
			"email_limit_error": "You have reached the limit for entering an email. Please try again in $MINUTES.",
			"phone_number_limit_error": "You have reached the limit for entering a number. Please try again in $MINUTES.",
			"phone_number_email_limit_error": "You have reached the limit for entering a number or email. Please try again in $MINUTES.",
			"sso_limit_error": "You have reached the limit to log in via SSO. Please try again in $MINUTES.",
			"phone_number_email_incorrect_phone_email_error": "Incorrect phone number or email entered",
			"phone_number_incorrect_phone_error": "Incorrect phone number entered",
			"email_incorrect_email_error": "Incorrect email entered",
			"auth_incorrect_password_error": "Incorrect password entered.",
			"confirm_code_limit_error": "You have reached the limit for resending the code. Please try again in $MINUTES.",
			"confirm_code_confirm_is_expired_error": "Login attempt timed out, please try again",
			"confirm_code_2fa_is_disabled_error": "Two-factor authentication is disabled on the server. Please authenticate again.",
			"add_mail_2fa_ldap_not_allowed_domain_error": "Incorrect email entered. Use your \"@$DOMAIN\" email address to log in to the server.",
			"add_mail_2fa_ldap_not_allowed_domains_error": "Incorrect email entered. Use an email address with the domain $DOMAINS to log into the server.",
			"confirm_code_incorrect_code_error": "Incorrect code. Please try again.",
			"confirm_code_incorrect_code_one_left": "left",
			"confirm_code_incorrect_code_two_lefts": "left",
			"confirm_code_incorrect_code_five_lefts": "left",
			"confirm_code_incorrect_code_one_attempt": " attempt",
			"confirm_code_incorrect_code_two_attempts": " attempts",
			"confirm_code_incorrect_code_five_attempts": " attempts",
			"create_profile_incorrect_name_error": "Incorrect name.",
			"auth_method_disabled": "SSO login unavailable. Please contact your manager or system administrator.",
			"sso_registration_without_invite": "Registration via SSO not available. Please contact your manager or system administrator to get an invitation link.",
			"auth_ldap_method_disabled": "LDAP login unavailable. Please contact your manager or system administrator.",
			"ldap_registration_without_invite": "Registration via LDAP not available. Please contact your manager or system administrator to get an invitation link.",
			"auth_sso_full_name_incorrect": "Unable to get the required Full Name or other data from $SSO_PROVIDER_NAME. Please contact your manager or system administrator.",
			"auth_sso_totp_code_incorrect": "Incorrect code. Please try again."
		},
		"token_life_time_desktop": "Code valid for $TIME",
		"token_life_time_mobile": "Code valid $TIME",
		"token_life_time_expired": "Code expired.",
		"one_hour": " hour",
		"two_hours": " hours",
		"five_hours": " hours",
		"one_minute": " minute",
		"two_minutes": " minutes",
		"five_minutes": " minutes",
		"download_compass": {
			"desktop_builds": {
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
			"mobile_stores": {
				"appstore": "Go to App Store",
				"google_play": "Go to Google Play",
				"app_gallery": "Go to AppGallery"
			}
		},
		"install_page": {
			"footer": "Made for you",
			"desktop": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Download the Compass On-premise app",
					"desc": "Latest versions of the app for any device.",
					"download_ios": {
						"desc": "Install on iPhone",
						"platform_app_store": "App Store"
					},
					"download_android": {
						"desc": "Install on Android",
						"platform_google_play": "Google Play"
					},
					"download_huawei": {
						"desc": "Install on Huawei",
						"platform_app_gallery": "AppGallery"
					},
					"support_block": {
						"title": "Having trouble installing?",
						"desc": "Contact us via Telegram or email. We'll help you with installation and setup.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			},
			"mobile": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Install the Compass On-premise app",
					"desc": "Latest versions of the app for any device.",
					"download_ios": "Go to App Store",
					"download_android": "Go to Google Play",
					"download_huawei": "Go to AppGallery",
					"desktop_footer": "Versions for ",
					"download_macos": "MacOS",
					"download_comma": ", ",
					"download_windows": "Windows",
					"download_and": " and ",
					"download_linux": "Linux",
					"download_dot": ".",
					"on_success_copy": "Download link for Compass for PC copied",
					"support_block": {
						"title": "Having trouble installing?",
						"desc": "Contact us via Telegram or email. We'll help you with installation and setup.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			}
		}
	},
	de: {
		"welcome_dialog": {
			"title": "Einladung",
			"desc_mobile": " lädt Sie zum Compass Unternehmens-Messenger ein. Bitte melden Sie sich an, um mit der Arbeit zu beginnen.",
			"desc_desktop": " lädt Sie zum Compass Unternehmens-Messenger ein. Bitte melden Sie sich an, um mit der Arbeit zu beginnen.",
			"confirm_button": "Weiter"
		},
		"email_phone_number_dialog": {
			"title": "Hallo",
			"desc_email": "Zur Autorisierung geben Sie bitte Ihre E-Mail-Adresse ein:",
			"desc_email_guest": "Zur Autorisierung im Gastkonto geben Sie bitte Ihre E-Mail-Adresse ein:",
			"desc_phone_number": "Geben Sie zur Autorisierung Ihre Telefonnummer ein:",
			"desc_phone_number_guest": "Geben Sie zur Autorisierung im Gastkonto Ihre Telefonnummer ein:",
			"desc_email_phone_number": "Geben Sie zur Autorisierung Ihre E-Mail-Adresse oder Telefonnummer ein:",
			"desc_email_phone_number_guest": "Geben Sie zur Autorisierung im Gastkonto Ihre E-Mail-Adresse oder Telefonnummer ein:",
			"desc_sso": "Zur Autorisierung klicken Sie bitte auf die Schaltfläche unten:",
			"desc_sso_guest": "Zur Autorisierung im Gastkonto klicken Sie bitte auf die Schaltfläche unten:",
			"input_placeholder_email": "E-Mail",
			"input_placeholder_phone_number": "Telefon",
			"input_placeholder_email_phone_number": "E-Mail oder Telefon",
			"confirm_button": "Weiter",
			sso_button: "Unternehmenszugang (SSO LDAP)",
			"open_guest_auth_methods_button": "Als Gast anmelden",
			"prohibited_symbols_tooltip": "Emojis, Leerzeichen und Sonderzeichen werden nicht unterstützt"
		},
		"email_register_dialog": {
			"title": "Compass",
			"desc": "Legen Sie ein Passwort für die Autorisierung über $EMAIL fest",
			"password_input_placeholder": "Passwort (mindestens 8 Zeichen)",
			"confirm_password_input_placeholder": "Wiederholen Sie das Passwort",
			"back_button": "Zurück",
			"register_button": "Registrieren",
			"passwords_not_match_error": "Die Passwörter stimmen nicht überein",
			"password_less_than_min_symbols_error": "Bitte geben Sie mindestens 8 Zeichen ein"
		},
		"email_login_dialog": {
			"title": "Compass",
			"desc": "Geben Sie das Passwort für die Autorisierung per $EMAIL ein",
			"password_input_placeholder": "Geben Sie das Passwort ein",
			"back_button": "Zurück",
			"login_button": "Weiter",
			"forgot_password_button": "Passwort vergessen?",
			"auth_blocked": "Sie haben alle Versuche zur Eingabe des Passworts ausgeschöpft. Bitte versuchen Sie es $MINUTES erneut."
		},
		"ldap_login_dialog": {
			"title": "Compass",
			"desc": "Geben Sie zur Autorisierung den Benutzernamen und das Passwort Ihres LDAP-Unternehmenskontos ein:",
			"username_input_placeholder": "Benutzernamen",
			"password_input_placeholder": "Passwort / + Einmalcode",
			"back_button": "Zurück",
			"login_button": "Weiter",
			"unknown_error": "Die Autorisierung über LDAP ist vorübergehend nicht verfügbar. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator.",
			"incorrect_credentials_error": "Falscher Benutzername oder falsches Passwort",
			"auth_blocked": "Das Limit für die Anmeldung über LDAP wurde erreicht. Sie es erneut in $MINUTES.",
			"incorrect_config_user_search_filter": "Syntaxfehler im Filter für die Autorisierung über LDAP. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator.",
			"incorrect_2fa_mail_config_attribute": "Die E-Mail-Adresse für die Zwei-Faktor-Authentifizierung oder andere Daten konnten nicht von LDAP abgerufen werden. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator."
		},
		"ldap_2fa_add_mail_dialog": {
			"title": "Zwei-Faktor-Authentifizierung",
			"desc": "Geben Sie die E-Mail-Adresse an, an die der Bestätigungscode für den Zugriff auf Ihr Konto gesendet werden soll.",
			"desc_change_mail": "Geben Sie die E-Mail-Adresse an, an die der Bestätigungscode für den Zugriff auf Ihr Konto gesendet werden soll.",
			"mail_input_placeholder": "E-Mail",
			"confirm_button": "Weiter",
			"change_mail_toast_success": "E-Mail für Zwei-Faktor-Authentifizierung geändert"
		},
		"ldap_2fa_setup_totp_dialog": {
			"title": "Zwei-Faktor-Authentifizierung",
			"desc": "Scannen Sie den QR-Code in einer Authentifikator-App auf Ihrem Smartphone.",
			"confirm_button": "Weiter",
			"cant_scan_qr_button": "Probleme beim Scannen des QR-Codes?"
		},
		"ldap_2fa_setup_totp_cant_scan_qr_dialog": {
			"title": "Einrichtung ohne QR",
			"first_step_desc_desktop": "Wählen Sie in Ihrer Authentifikator-App die manuelle Eingabe des Einrichtungsschlüssels aus.",
			"first_step_desc_mobile": "Wählen Sie in Ihrer Authentifikator-App die manuelle Eingabe des Einrichtungsschlüssels aus.",
			"second_step_desc": "Geben Sie Ihren Schlüssel ein:",
			"third_step_desc": "Stellen Sie sicher, dass die Option „Zeitbasiert“ ausgewählt ist. Bestätigen Sie das Hinzufügen des Schlüssels."
		},
		"ldap_2fa_confirm_totp_dialog": {
			"title": "Bestätigungscode",
			"desc": "Bitte geben Sie den Code aus Ihrer Authentifikator-App ein.",
			"input_placeholder": "Code",
			"confirm_button": "Weiter",
			"cant_get_code_button": "Probleme mit dem Code?",
			"cant_get_code_tooltip": "Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator, um einen neuen Code zu erhalten."
		},
		"forgot_password_dialog": {
			"title": "Compass",
			"desc": "Bestehen Sie die Prüfung, um das Passwort zurückzusetzen"
		},
		"create_new_password_dialog": {
			"title": "Neues Passwort erstellen",
			"desc": "Das Passwort wird bei der Autorisierung per E-Mail $EMAIL abgefragt ",
			"password_input_placeholder": "Passwort (mindestens 8 Zeichen)",
			"confirm_password_input_placeholder": "Wiederholen Sie das Passwort",
			"cancel_button": "Abbrechen",
			"confirm_button": "Erstellen",
			"success_tooltip_message": "Passwort geändert",
			"passwords_not_match_error": "Die Passwörter stimmen nicht überein",
			"password_less_than_min_symbols_error": "Bitte geben Sie mindestens 8 Zeichen ein"
		},
		"confirm_code_phone_number_dialog": {
			"title": "Compass",
			"desc": "SMS-Code ",
			"back_button": "Zurück",
			"resend_button": "Wiedersenden",
			"resend_after": "Wiederholen in ",
			"auth_blocked": "Leider haben Sie das Limit der SMS-Bestätigungsversuche erreicht. Bitte versuchen Sie es $MINUTES erneut."
		},
		"confirm_code_email_dialog": {
			"title": "Bestätigungscode",
			"title_ldap_change_mail_confirm_current": "Bestätigen Sie Ihre aktuelle E-Mail-Adresse",
			"title_ldap_change_mail_confirm_new": "Bestätigen Sie Ihre neue E-Mail-Adresse",
			"desc": "Bitte geben Sie den Code ein, der an $EMAIL gesendet wurde. ",
			"desc_ldap_change_mail_confirm_current": "Um zur Änderung der E-Mail-Adresse zu gelangen, geben Sie bitte den Code ein, der an $EMAIL gesendet wurde ",
			"back_button": "Zurück",
			"resend_button": "Wiedersenden",
			"resend_after": "Wiederholen in ",
			"auth_blocked": "Sie haben alle Versuche zur Bestätigung per Code aufgebraucht. Bitte versuchen Sie es $MINUTES erneut.",
			"change_mail_button": "E-Mail ändern",
			"ldap_2fa_change_mail_limit_error": "Sie haben das Limit für die Änderung der E-Mail-Adresse erreicht. Bitte versuchen Sie es in 24 Stunden erneut."
		},
		"create_profile_dialog": {
			"title": "Profil erstellen",
			"desc": "Jeder Benutzer im Team hat ein Profil, das für andere Teammitglieder sichtbar ist. Erstellen Sie Ihr Profil.",
			"input_placeholder": "Vorname Nachname",
			"cancel_button": "Abbrechen",
			"confirm_button": "Weiter",
			"incorrect_name_tooltip": "Bitte geben Sie den Namen in Ihrer Sprache ein, um anderen Mitgliedern die Kommunikation mit Ihnen zu erleichtern.",
			"not_saved_symbols_tooltip": "Emojis und Sonderzeichen werden nicht gespeichert",
			"confirm_cancel_mobile": {
				"title": "Die Registrierung abbrechen?",
				"short_title": "Abbrechen?",
				"confirm_button": "Ja",
				"cancel_button": "Nein"
			},
			"confirm_cancel_desktop": {
				"title": "Registrierung abschließen?",
				"desc": "Registrierung wird abgebrochen. Sie können sie zu einem späteren Zeitpunkt erneut starten.",
				"confirm_button": "Abschließen",
				"cancel_button": "Abbrechen"
			}
		},
		"page_token": {
			"title": "Fast fertig",
			"desc": "Um die Kommunikation innerhalb des Teams zu starten, gehen Sie bitte zwei einfache Schritte durch:",
			"step_1": {
				"register_desc_pt1": "Kopieren Sie den Verifizierungscode",
				"register_desc_pt2": " und fügen Sie ihn in die App ein.",
				"register_button": "Kopieren",
				"update_token": "Code aktualisieren.",
				"login_desc_pt1": "Öffnen Sie die Compass-Anwendung",
				"login_desc_pt2_desktop": " und geben Sie gegebenenfalls Ihren Geheimcode ein.",
				"login_desc_pt2_mobile": " und geben Sie Ihren Geheimcode ein.",
				"login_button": "Compass öffnen"
			},
			"step_2": {
				"desc_pt1_mobile": "Installieren Sie Compass,",
				"desc_pt1_desktop": "Installieren Sie Compass,",
				"desc_pt2_mobile": "wenn es noch nicht auf Ihrem Smartphone ist.",
				"desc_pt2_desktop": "wenn es noch nicht auf Ihrem Computer ist.",
				"button_mobile": "Installieren",
				"button_desktop": "Herunterladen"
			}
		},
		"page_invite": {
			"title": "Herzliche Glückwünsche!",
			"desc": "Sie sind dem Team beigetreten.",
			"or": "oder",
			"open_compass_mobile": {
				"title_pt1": "Öffnen Sie die Compass-Anwendung",
				"title_pt2": "und beginnen Sie mit der Kommunikation im Team.",
				"button": "Compass öffnen"
			},
			"open_compass_wait_post_moderation_mobile": {
				"title_pt1": "Öffnen Sie die Compass-Anwendung",
				"title_pt2": "und warten Sie auf die Genehmigung Ihres Antrags.",
				"button": "Compass öffnen"
			},
			"open_compass_desktop": {
				"title_pt1": "Öffnen Sie die Compass-Anwendung",
				"title_pt2": "und geben Sie gegebenenfalls Ihren Geheimcode ein.",
				"button": "Compass öffnen"
			},
			"copy_token_mobile": {
				"title_pt1": "Kopieren Sie den Verifizierungscode",
				"title_pt2": " und fügen Sie ihn in die App ein.",
				"button": "Kopieren"
			},
			"install_app_desktop": {
				"title_pt1": "Installieren Sie Compass,",
				"title_pt2": "wenn es noch nicht auf Ihrem Computer ist.",
				"button": "Herunterladen"
			},
			"already_member": {
				"title": "Herzliche Glückwünsche!",
				"desc": "Sie sind bereits Mitglied dieses Teams."
			},
			"waiting_for_postmoderation": {
				"title": "Ihr Beitrittsantrag gesendet",
				"desc": "Sie müssen noch ein bisschen warten, bis der Teambesitzer Ihren Antrag genehmigt."
			},
			"join_as_guest": {
				"title": "Herzliche Glückwünsche!",
				"desc": "Sie sind dem Team als Gast beigetreten."
			}
		},
		"settings": {
			"change_lang": "Sprache ändern",
			"logout": "Beenden"
		},
		"logout_dialog": {
			"title": "Möchten Sie abmelden?",
			"desc": "Um zur Autorisierung zurückzukehren, müssen Sie sich anmelden.",
			"cancel_button": "Abbrechen",
			"confirm_button": "Beenden"
		},
		"inactive_link": {
			"title": "Link nicht aktiv",
			"desc": "Bitte kontaktieren Sie die Person, die Sie eingeladen hat, und bitten Sie sie, einen neuen Link zu schicken."
		},
		"invalid_link": {
			"title": "Ungültiger Link",
			"desc": "Bitte überprüfen Sie den Link oder kontaktieren Sie die Person, die Sie eingeladen hat."
		},
		"accept_limit_link": {
			"title": "Limit erreicht",
			"desc": "Sie haben keine Versuche mehr, dem Team mit einem Link beizutreten. Bitte warten Sie $TIME und versuchen Sie es erneut."
		},
		"not_finished_space_leaving": {
			"desc": "Ihre Entfernung aus diesem Team ist noch nicht abgeschlossen. Bitte versuchen Sie, dem Unternehmen beizutreten in 2 Minuten."
		},
		"confirm_close_dialog": {
			"title": "Möchten Sie schließen?",
			"desc": "Die Daten werden nicht gespeichert.",
			"confirm_button": "Ja",
			"cancel_button": "Nein"
		},
		"errors": {
			"network_error": "Keine Internetverbindung",
			"server_error": "Verbindungsfehler. Bitte versuchen Sie es erneut.",
			"sso_error": "Die Anmeldung über SSO ist nicht verfügbar. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator.",
			"incorrect_captcha": "Das Captcha konnte nicht überprüft werden. Bitte versuchen Sie es erneut.",
			"email_limit_error": "Das Limit für die Eingabe von E-Mail-Adressen wurde erreicht. Sie es erneut in $MINUTES.",
			"phone_number_limit_error": "Das Limit für die Eingabe von Telefonnummern wurde erreicht. Sie es erneut in $MINUTES.",
			"phone_number_email_limit_error": "Das Limit für die Eingabe von Telefonnummern oder E-Mail wurde erreicht. Sie es erneut in $MINUTES.",
			"sso_limit_error": "Das Limit für die Anmeldung über SSO wurde erreicht. Sie es erneut in $MINUTES.",
			"phone_number_email_incorrect_phone_email_error": "Die eingegebene Telefonnummer oder E-Mail-Adresse ist ungültig",
			"phone_number_incorrect_phone_error": "Die eingegebene Telefonnummer ist ungültig",
			"email_incorrect_email_error": "Ungültige E-Mail-Adresse eingegeben",
			"auth_incorrect_password_error": "Falscher falsches Passwort.",
			"confirm_code_limit_error": "Das Limit für das erneute Senden des Codes wurde erreicht. Sie es erneut in $MINUTES.",
			"confirm_code_confirm_is_expired_error": "Das Zeitlimit für den Autorisierungsversuch ist abgelaufen. Bitte versuchen Sie es erneut.",
			"confirm_code_2fa_is_disabled_error": "Die Zwei-Faktor-Authentifizierung ist auf dem Server deaktiviert. Bitte authentifizieren Sie sich erneut.",
			"add_mail_2fa_ldap_not_allowed_domain_error": "Eine ungültige E-Mail-Adresse wurde eingegeben. Verwenden Sie eine E-Mail-Adresse mit der Domain „@$DOMAIN“ für die Autorisierung auf dem Server.",
			"add_mail_2fa_ldap_not_allowed_domains_error": "Eine ungültige E-Mail-Adresse wurde eingegeben. Verwenden Sie eine E-Mail-Adresse mit der Domain $DOMAINS\u2028für die Autorisierung auf dem Server.",
			"confirm_code_incorrect_code_error": "Ungültiger Code. Bitte versuchen Sie es erneut.",
			"confirm_code_incorrect_code_one_left": "Noch",
			"confirm_code_incorrect_code_two_lefts": "Noch",
			"confirm_code_incorrect_code_five_lefts": "Noch",
			"confirm_code_incorrect_code_one_attempt": " Versuche",
			"confirm_code_incorrect_code_two_attempts": " Versuche",
			"confirm_code_incorrect_code_five_attempts": " Versuche",
			"create_profile_incorrect_name_error": "Ungültiger Name",
			"auth_method_disabled": "Die Anmeldung über SSO ist nicht verfügbar. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator.",
			"sso_registration_without_invite": "Die Registrierung über SSO ist nicht verfügbar. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator, um einen Einladungslink zu erhalten.",
			"auth_ldap_method_disabled": "Die Autorisierung über LDAP ist nicht verfügbar. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator.",
			"ldap_registration_without_invite": "Die Registrierung über LDAP ist nicht verfügbar. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator, um einen Einladungslink zu erhalten.",
			"auth_sso_full_name_incorrect": "Vor- und Nachname oder andere Daten konnten nicht vom $SSO_PROVIDER_NAME abgerufen werden. Wenden Sie sich an Ihren Vorgesetzten oder Systemadministrator.",
			"auth_sso_totp_code_incorrect": "Ungültiger Code. Bitte versuchen Sie es erneut."
		},
		"token_life_time_desktop": "Der Code ist $TIME lang gültig ",
		"token_life_time_mobile": "Der Code ist $TIME lang gültig ",
		"token_life_time_expired": "Der Code ist abgelaufen.",
		"one_hour": " Stunde",
		"two_hours": " Stunden",
		"five_hours": " Stunden",
		"one_minute": " Minute",
		"two_minutes": " Minuten",
		"five_minutes": " Minuten",
		"download_compass": {
			"desktop_builds": {
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
			"mobile_stores": {
				"appstore": "Zum App Store gehen",
				"google_play": "Zu Google Play gehen",
				"app_gallery": "Zur AppGallery gehen"
			}
		},
		"install_page": {
			"footer": "Für Sie gemacht",
			"desktop": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Compass On-Premise herunterladen",
					"desc": "Aktuelle Versionen der App für jedes Gerät.",
					"download_ios": {
						"desc": "Installieren Sie es auf iPhone",
						"platform_app_store": "App Store"
					},
					"download_android": {
						"desc": "Installieren Sie es auf Android",
						"platform_google_play": "Google Play"
					},
					"download_huawei": {
						"desc": "Installieren Sie es auf Huawei",
						"platform_app_gallery": "AppGallery"
					},
					"support_block": {
						"title": "Probleme bei der Installation?",
						"desc": "Schreiben Sie uns auf Telegram oder per E-Mail. Wir helfen Ihnen bei der Installation und Konfiguration.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			},
			"mobile": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Installieren Sie Compass On-premise",
					"desc": "Aktuelle Versionen der App für jedes Gerät.",
					"download_ios": "Zum App Store gehen",
					"download_android": "Zu Google Play gehen",
					"download_huawei": "Zur AppGallery gehen",
					"desktop_footer": "Verfügbar für ",
					"download_macos": "MacOS",
					"download_comma": ", ",
					"download_windows": "Windows",
					"download_and": " und ",
					"download_linux": "Linux",
					"download_dot": ".",
					"on_success_copy": "Der Download-Link für Compass für den Computer wurde kopiert",
					"support_block": {
						"title": "Probleme bei der Installation?",
						"desc": "Schreiben Sie uns auf Telegram oder per E-Mail. Wir helfen Ihnen bei der Installation und Konfiguration.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			}
		}
	},
	fr: {
		"welcome_dialog": {
			"title": "Invitation",
			"desc_mobile": " vous invite dans la messagerie d'entreprise Compass. Veuillez vous connecter pour commencer.",
			"desc_desktop": " vous invite dans la messagerie d'entreprise Compass. Veuillez vous connecter pour commencer.",
			"confirm_button": "Continuer"
		},
		"email_phone_number_dialog": {
			"title": "Bonjour",
			"desc_email": "Pour vous connecter, entrez votre e-mail :",
			"desc_email_guest": "Pour vous connecter au compte invité, entrez votre e-mail :",
			"desc_phone_number": "Pour vous connecter, entrez le numéro de téléphone :",
			"desc_phone_number_guest": "Pour vous connecter au compte invité, entrez le numéro de téléphone :",
			"desc_email_phone_number": "Pour vous connecter, entrez votre e-mail ou votre numéro de téléphone :",
			"desc_email_phone_number_guest": "Pour vous connecter au compte invité, entrez votre e-mail ou votre numéro de téléphone :",
			"desc_sso": "Pour vous connecter, cliquez sur le bouton ci-dessous :",
			"desc_sso_guest": "Pour vous connecter au compte invité, cliquez sur le bouton ci-dessous :",
			"input_placeholder_email": "E-mail",
			"input_placeholder_phone_number": "Téléphone",
			"input_placeholder_email_phone_number": "E-mail ou numéro de téléphone",
			"confirm_button": "Continuer",
			sso_button: "Se connecter via portail corp. (SSO LDAP)",
			"open_guest_auth_methods_button": "Accès au compte invité",
			"prohibited_symbols_tooltip": "Les émojis, les espaces et les caractères spéciaux ne sont pas pris en charge"
		},
		"email_register_dialog": {
			"title": "Compass",
			"desc": "Créez un mot de passe pour l'autorisation par $EMAIL",
			"password_input_placeholder": "Mot de passe (8 caractères minimum)",
			"confirm_password_input_placeholder": "Entrez le mot de passe encore une fois",
			"back_button": "Retour",
			"register_button": "S'inscrire",
			"passwords_not_match_error": "Les mots de passe ne correspondent pas",
			"password_less_than_min_symbols_error": "Veuillez saisir au moins 8 caractères"
		},
		"email_login_dialog": {
			"title": "Compass",
			"desc": "Entrez le mot de passe pour l'autorisation par $EMAIL",
			"password_input_placeholder": "Saisissez le mot de passe",
			"back_button": "Retour",
			"login_button": "Continuer",
			"forgot_password_button": "Mot de passe oublié ?",
			"auth_blocked": "Vous avez épuisé toutes les tentatives de saisie du mot de passe. Veuillez réessayer dans $MINUTES."
		},
		"ldap_login_dialog": {
			"title": "Compass",
			"desc": "Pour vous connecter, entrez votre nom d'utilisateur et votre mot de passe de compte corporatif LDAP :",
			"username_input_placeholder": "Nom d'utilisateur",
			"password_input_placeholder": "Mot de passe / + code",
			"back_button": "Retour",
			"login_button": "Continuer",
			"unknown_error": "Autorisation par LDAP temporairement non disponible. Veuillez contacter votre responsable ou l'administrateur système.",
			"incorrect_credentials_error": "Nom d'utilisateur ou mot de passe incorrect",
			"auth_blocked": "Limite d'entrée par LDAP atteinte. Réessayez dans $MINUTES.",
			"incorrect_config_user_search_filter": "Erreur de syntaxe de filtre d'autorisation par LDAP. Veuillez contacter votre responsable ou l'administrateur système.",
			"incorrect_2fa_mail_config_attribute": "Impossible de recevoir l'email pour l'authentification à deux facteurs ou d'autres données depuis LDAP. Veuillez contacter votre responsable ou l'administrateur système."
		},
		"ldap_2fa_add_mail_dialog": {
			"title": "Authentification à deux facteurs",
			"desc": "Indiquez l’adresse e-mail qui recevra le code de confirmation pour accéder à votre compte.",
			"desc_change_mail": "Indiquez l’adresse e-mail qui recevra le code de confirmation pour accéder à votre compte.",
			"mail_input_placeholder": "E-mail",
			"confirm_button": "Continuer",
			"change_mail_toast_success": "L’adresse e-mail pour l’authentification à deux facteurs a été changée"
		},
		"ldap_2fa_setup_totp_dialog": {
			"title": "Authentification à deux facteurs",
			"desc": "Scannez le code QR dans votre application d’authentification mobile.",
			"confirm_button": "Continuer",
			"cant_scan_qr_button": "Impossible de scanner le code QR ?"
		},
		"ldap_2fa_setup_totp_cant_scan_qr_dialog": {
			"title": "Configuration sans QR",
			"first_step_desc_desktop": "Dans votre application d’authentification mobile, sélectionnez la saisie manuelle de la clé de configuration.",
			"first_step_desc_mobile": "Dans votre application d’authentification mobile, sélectionnez la saisie manuelle de la clé de configuration.",
			"second_step_desc": "Saisissez votre clé :",
			"third_step_desc": "Assurez-vous que l’option « Basé sur le temps » est sélectionnée. Confirmez l’ajout de la clé."
		},
		"ldap_2fa_confirm_totp_dialog": {
			"title": "Code de confirmation",
			"desc": "Veuillez saisir le code de votre application d’authentification mobile.",
			"input_placeholder": "Code",
			"confirm_button": "Continuer",
			"cant_get_code_button": "Impossible d’obtenir le code ?",
			"cant_get_code_tooltip": "Veuillez contacter votre responsable ou l'administrateur système pour obtenir un nouveau code."
		},
		"forgot_password_dialog": {
			"title": "Compass",
			"desc": "Effectuez une vérification pour réinitialiser le mot de passe"
		},
		"create_new_password_dialog": {
			"title": "Créer un mot de passe",
			"desc": "Le mot de passe vous sera demandé lors de l'autorisation par $EMAIL",
			"password_input_placeholder": "Mot de passe (8 caractères minimum)",
			"confirm_password_input_placeholder": "Entrez le mot de passe encore une fois",
			"cancel_button": "Annuler",
			"confirm_button": "Créer",
			"success_tooltip_message": "Mot de passe modifié",
			"passwords_not_match_error": "Les mots de passe ne correspondent pas",
			"password_less_than_min_symbols_error": "Veuillez saisir au moins 8 caractères"
		},
		"confirm_code_phone_number_dialog": {
			"title": "Compass",
			"desc": "Code de message ",
			"back_button": "Retour",
			"resend_button": "Renvoyer",
			"resend_after": "Réessayer dans ",
			"auth_blocked": "Vous avez épuisé toutes vos tentatives de vérification par SMS. Veuillez réessayer dans $MINUTES."
		},
		"confirm_code_email_dialog": {
			"title": "Code de confirmation",
			"title_ldap_change_mail_confirm_current": "Confirmez l’adresse e-mail actuelle",
			"title_ldap_change_mail_confirm_new": "Confirmez la nouvelle adresse e-mail",
			"desc": "Veuillez entrer le code envoyé à $EMAIL",
			"desc_ldap_change_mail_confirm_current": "Pour changer d’adresse e-mail, entrez le code envoyé à $EMAIL",
			"back_button": "Retour",
			"resend_button": "Renvoyer",
			"resend_after": "Réessayer dans ",
			"auth_blocked": "Vous avez épuisé toutes vos tentatives de vérification avec le code. Veuillez réessayer dans $MINUTES.",
			"change_mail_button": "Modifier l'email",
			"ldap_2fa_change_mail_limit_error": "La limite de changement d’adresse e-mail est atteinte. Réessayez dans 24 heures."
		},
		"create_profile_dialog": {
			"title": "Créer le profil",
			"desc": "Chaque utilisateur dans l'équipe a le profil que ses interlocuteurs voient. Créez votre profil.",
			"input_placeholder": "Prénom Nom",
			"cancel_button": "Annuler",
			"confirm_button": "Suivant",
			"incorrect_name_tooltip": "Pour que les membres de l'équipe soient à l'aise en communiquant avec vous, veuillez écrire votre nom et prénom en votre langue.",
			"not_saved_symbols_tooltip": "Les emoji et les caractères spéciaux ne seront pas enregistrés",
			"confirm_cancel_mobile": {
				"title": "Terminer l'inscription ?",
				"short_title": "Annuler ?",
				"confirm_button": "Oui",
				"cancel_button": "Non"
			},
			"confirm_cancel_desktop": {
				"title": "Terminer l'inscription ?",
				"desc": "Le processus d'inscription sera arrêté. Vous pouvez le relancer à tout moment.",
				"confirm_button": "Terminer",
				"cancel_button": "Annuler"
			}
		},
		"page_token": {
			"title": "Presque terminé",
			"desc": "Pour commencer la communication dans l'équipe, veuillez passer deux étapes simples :",
			"step_1": {
				"register_desc_pt1": "Copiez le code de vérification",
				"register_desc_pt2": " et collez-le dans l’application.",
				"register_button": "Copier",
				"update_token": "Actualiser le code.",
				"login_desc_pt1": "Ouvrez l'application Compass",
				"login_desc_pt2_desktop": " et entrez le code de vérification si nécessaire.",
				"login_desc_pt2_mobile": " et entrez le code de vérification.",
				"login_button": "Ouvrir Compass"
			},
			"step_2": {
				"desc_pt1_mobile": "Installez l'application Compass,",
				"desc_pt1_desktop": "Installez l'application Compass,",
				"desc_pt2_mobile": " si vous ne l'avez pas sur votre téléphone.",
				"desc_pt2_desktop": " si vous ne l'avez pas sur votre ordinateur.",
				"button_mobile": "Installer",
				"button_desktop": "Télécharger"
			}
		},
		"page_invite": {
			"title": "Félicitations !",
			"desc": "Vous avez rejoint l'équipe.",
			"or": "ou",
			"open_compass_mobile": {
				"title_pt1": "Ouvrez l'application Compass",
				"title_pt2": "et communiquez avec votre équipe.",
				"button": "Ouvrir Compass"
			},
			"open_compass_wait_post_moderation_mobile": {
				"title_pt1": "Ouvrez l'application Compass",
				"title_pt2": "et attendez l'approbation de votre demande.",
				"button": "Ouvrir Compass"
			},
			"open_compass_desktop": {
				"title_pt1": "Ouvrez l'application Compass",
				"title_pt2": "et entrez le code de vérification si nécessaire.",
				"button": "Ouvrir Compass"
			},
			"copy_token_mobile": {
				"title_pt1": "Copiez le code de vérification",
				"title_pt2": " et collez-le dans l’application.",
				"button": "Copier"
			},
			"install_app_desktop": {
				"title_pt1": "Installez l'application Compass,",
				"title_pt2": "si vous ne l'avez pas sur votre ordinateur.",
				"button": "Télécharger"
			},
			"already_member": {
				"title": "Félicitations !",
				"desc": "Vous êtes déjà membre de cette équipe."
			},
			"waiting_for_postmoderation": {
				"title": "La demande d'adhésion à l'équipe est envoyée",
				"desc": "Patientez un peu jusqu'à ce que le chef approuve votre demande."
			},
			"join_as_guest": {
				"title": "Félicitations !",
				"desc": "Vous avez rejoint l'équipe comme visiteur."
			}
		},
		"settings": {
			"change_lang": "Changer de langue",
			"logout": "Sortir"
		},
		"logout_dialog": {
			"title": "Quitter ?",
			"desc": "Pour retourner à l'autorisation, il faudra se connecter.",
			"cancel_button": "Annuler",
			"confirm_button": "Sortir"
		},
		"inactive_link": {
			"title": "Lien inactif",
			"desc": "Veuillez contacter la personne qui vous a invité et demandez-lui de vous envoyer un nouveau lien."
		},
		"invalid_link": {
			"title": "Lien incorrect",
			"desc": "Veuillez consulter le lien ou contacter la personne qui vous a invité."
		},
		"accept_limit_link": {
			"title": "Limite atteinte",
			"desc": "Toutes les tentatives de rejoindre l'équipe par le lien sont épuisées. Veuillez attendre $TIME et réessayez."
		},
		"not_finished_space_leaving": {
			"desc": "Le processus de votre suppression de cette équipe n'est pas encore terminé. Essayez de rejoindre la société dans 2 minutes."
		},
		"confirm_close_dialog": {
			"title": "Fermer ?",
			"desc": "Sans sauvegarde.",
			"confirm_button": "Oui",
			"cancel_button": "Non"
		},
		"errors": {
			"network_error": "Pas de connexion Internet",
			"server_error": "Erreur de connexion. Réessayez.",
			"sso_error": "Autorisation par SSO non disponible. Veuillez contacter votre responsable ou l'administrateur système.",
			"incorrect_captcha": "Échec de captcha, veuillez réessayer",
			"email_limit_error": "Limite de saisie d'adresse e-mail atteinte. Réessayez dans $MINUTES.",
			"phone_number_limit_error": "Limite de saisie de numéro atteinte. Réessayez dans $MINUTES.",
			"phone_number_email_limit_error": "Limite de saisie du numéro ou de l'email atteinte. Réessayez dans $MINUTES.",
			"sso_limit_error": "Limite d'entrée par SSO atteinte. Réessayez dans $MINUTES.",
			"phone_number_email_incorrect_phone_email_error": "Le numéro de téléphone ou l'email saisi est incorrect",
			"phone_number_incorrect_phone_error": "Numéro de téléphone incorrect",
			"email_incorrect_email_error": "Adresse e-mail incorrecte",
			"auth_incorrect_password_error": "Mot de passe entré incorrect.",
			"confirm_code_limit_error": "Vous avez atteint la limite de renvoi du code. Réessayez dans $MINUTES.",
			"confirm_code_confirm_is_expired_error": "Temps de tentative d'autorisation expiré, veuillez réessayer",
			"confirm_code_2fa_is_disabled_error": "L'authentification à deux facteurs est désactivée sur le serveur. Veuillez vous authentifier à nouveau.",
			"add_mail_2fa_ldap_not_allowed_domain_error": "Adresse e-mail incorrecte. Pour vous connecter au serveur, utilisez une adresse e-mail du domaine « @$DOMAIN ».",
			"add_mail_2fa_ldap_not_allowed_domains_error": "Adresse e-mail incorrecte. Pour vous connecter au serveur, utilisez une adresse e-mail appartenant aux domaines $DOMAINS.",
			"confirm_code_incorrect_code_error": "Code incorrect. Veuillez réessayer.",
			"confirm_code_incorrect_code_one_left": "restée",
			"confirm_code_incorrect_code_two_lefts": "restées",
			"confirm_code_incorrect_code_five_lefts": "restées",
			"confirm_code_incorrect_code_one_attempt": " tentative",
			"confirm_code_incorrect_code_two_attempts": " tentatives",
			"confirm_code_incorrect_code_five_attempts": " tentatives",
			"create_profile_incorrect_name_error": "Prénom incorrect.",
			"auth_method_disabled": "Autorisation par SSO non disponible. Veuillez contacter votre responsable ou l'administrateur système.",
			"sso_registration_without_invite": "Inscription par SSO non disponible. Veuillez contacter votre responsable ou l'administrateur système pour recevoir le lien d'invitation.",
			"auth_ldap_method_disabled": "Autorisation par LDAP non disponible. Veuillez contacter votre responsable ou l'administrateur système.",
			"ldap_registration_without_invite": "Inscription par LDAP non disponible. Veuillez contacter votre responsable ou l'administrateur système pour recevoir le lien d'invitation.",
			"auth_sso_full_name_incorrect": "Impossible de recevoir le Nom et les autres données de $SSO_PROVIDER_NAME. Veuillez contacter votre responsable ou l'administrateur système.",
			"auth_sso_totp_code_incorrect": "Code incorrect. Veuillez réessayer."
		},
		"token_life_time_desktop": "Le code sera actif pendant $TIME",
		"token_life_time_mobile": "Le code sera actif pendant $TIME",
		"token_life_time_expired": "Le temps de validité du code a expiré.",
		"one_hour": " heure",
		"two_hours": " heures",
		"five_hours": " heures",
		"one_minute": " minute",
		"two_minutes": " minutes",
		"five_minutes": " minutes",
		"download_compass": {
			"desktop_builds": {
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
			"mobile_stores": {
				"appstore": "Aller à l'Appstore",
				"google_play": "Aller sur Google Play",
				"app_gallery": "Aller à AppGallery"
			}
		},
		"install_page": {
			"footer": " Fait pour vous",
			"desktop": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Téléchargez l'application Compass On-premise",
					"desc": "Les versions actuelles de l'application pour tout appareil.",
					"download_ios": {
						"desc": "Installez sur iPhone",
						"platform_app_store": "App Store"
					},
					"download_android": {
						"desc": "Installez sur Android",
						"platform_google_play": "Google Play"
					},
					"download_huawei": {
						"desc": "Installez sur Huawei",
						"platform_app_gallery": "AppGallery"
					},
					"support_block": {
						"title": "Problèmes d'installation ?",
						"desc": "Contactez-nous sur Telegram ou par e-mail. Nous vous aiderons à installer et configurer l'app.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			},
			"mobile": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Installer l'application Compass On-premise",
					"desc": "Les versions actuelles de l'application pour tout appareil.",
					"download_ios": "Aller à l'Appstore",
					"download_android": "Aller sur Google Play",
					"download_huawei": "Aller à AppGallery",
					"desktop_footer": "Versions disponibles pour ",
					"download_macos": "MacOS",
					"download_comma": ", ",
					"download_windows": "Windows",
					"download_and": " et ",
					"download_linux": "Linux",
					"download_dot": ".",
					"on_success_copy": "Le lien pour télécharger Compass sur PC a été copié",
					"support_block": {
						"title": "Problèmes d'installation ?",
						"desc": "Contactez-nous sur Telegram ou par e-mail. Nous vous aiderons à installer et configurer l'app.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			}
		}
	},
	es: {
		"welcome_dialog": {
			"title": "Invitación",
			"desc_mobile": " le invita a unirse al servicio de mensajero corporativo Compass. Por favor, identifíquese para empezar a trabajar.",
			"desc_desktop": " le invita a unirse al servicio de mensajero corporativo Compass. Por favor, identifíquese para empezar a trabajar.",
			"confirm_button": "Continuar"
		},
		"email_phone_number_dialog": {
			"title": "Hola",
			"desc_email": "Para obtener autorización, introduzca su correo electrónico:",
			"desc_email_guest": "Para obtener autorización para la cuenta de invitado, introduzca su correo electrónico:",
			"desc_phone_number": "Para obtener autorización, introduzca su número de teléfono:",
			"desc_phone_number_guest": "Para obtener autorización para la cuenta de invitado, introduzca su número de teléfono:",
			"desc_email_phone_number": "Para obtener autorización, introduzca su correo electrónico o número de teléfono:",
			"desc_email_phone_number_guest": "Para obtener autorización para la cuenta de invitado, introduzca su correo electrónico o número de teléfono:",
			"desc_sso": "Para obtener autorización, oprima el botón a continuación:",
			"desc_sso_guest": "Para obtener autorización para la cuenta de invitado, oprima el botón a continuación:",
			"input_placeholder_email": "Correo electrónico",
			"input_placeholder_phone_number": "Teléfono",
			"input_placeholder_email_phone_number": "Correo electrónico o número de teléfono",
			"confirm_button": "Continuar",
			sso_button: "Iniciar sesión vía portal corp. (SSO LDAP)",
			"open_guest_auth_methods_button": "Iniciar sesión como invitado",
			"prohibited_symbols_tooltip": "No se admiten emojis, espacios en blanco, ni caracteres especiales"
		},
		"email_register_dialog": {
			"title": "Compass",
			"desc": "Cree una contraseña para la autorización a través de $EMAIL",
			"password_input_placeholder": "Contraseña (mínimo 8 caracteres)",
			"confirm_password_input_placeholder": "Repita la contraseña",
			"back_button": "Atrás",
			"register_button": "Registrarse",
			"passwords_not_match_error": "Las contraseñas no coinciden",
			"password_less_than_min_symbols_error": "Por favor, introduzca al menos 8 caracteres"
		},
		"email_login_dialog": {
			"title": "Compass",
			"desc": "Introduzca la contraseña para la autorización a través de $EMAIL",
			"password_input_placeholder": "Introduzca la contraseña",
			"back_button": "Atrás",
			"login_button": "Continuar",
			"forgot_password_button": "¿Olvidó su contraseña?",
			"auth_blocked": "Ha agotado todos los intentos de introducir la contraseña. Por favor, vuelva a intentarlo en $MINUTES."
		},
		"ldap_login_dialog": {
			"title": "Compass",
			"desc": "Para autorizarse, introduzca el nombre de usuario y la contraseña de su cuenta corporativa LDAP:",
			"username_input_placeholder": "Nombre de usuario",
			"password_input_placeholder": "Contraseña / + código único",
			"back_button": "Atrás",
			"login_button": "Continuar",
			"unknown_error": "La autorización a través de LDAP no está disponible temporalmente. Póngase en contacto con el responsable o el administrador del sistema.",
			"incorrect_credentials_error": "Fue introducido un nombre de usuario o contraseña incorrectos",
			"auth_blocked": "Fue alcanzado el límite de intentos de acceso a través de LDAP. Intente en $MINUTES.",
			"incorrect_config_user_search_filter": "Error en la sintaxis del filtro para la autorización a través de LDAP. Póngase en contacto con el responsable o el administrador del sistema.",
			"incorrect_2fa_mail_config_attribute": "No se pudo obtener el correo para la autenticación de dos factores u otros datos de LDAP. Póngase en contacto con el responsable o el administrador del sistema."
		},
		"ldap_2fa_add_mail_dialog": {
			"title": "Autenticación de dos factores",
			"desc": "Indique la dirección de correo electrónico a la que se enviará el código de confirmación para acceder a la cuenta.",
			"desc_change_mail": "Indique la dirección de correo electrónico a la que se enviará el código de confirmación para acceder a la cuenta.",
			"mail_input_placeholder": "Correo electrónico",
			"confirm_button": "Continuar",
			"change_mail_toast_success": "Fue cambiado el correo electrónico para la autenticación de dos factores"
		},
		"ldap_2fa_setup_totp_dialog": {
			"title": "Autenticación de dos factores",
			"desc": "Escanee el código QR en una aplicación de autenticación móvil.",
			"confirm_button": "Continuar",
			"cant_scan_qr_button": "¿No puede escanear el código QR?"
		},
		"ldap_2fa_setup_totp_cant_scan_qr_dialog": {
			"title": "Configuración sin QR",
			"first_step_desc_desktop": "En su aplicación de autenticación móvil, seleccione la opción de introducir la clave manualmente.",
			"first_step_desc_mobile": "En su aplicación de autenticación móvil, seleccione la opción de introducir la clave manualmente.",
			"second_step_desc": "Introduzca su clave:",
			"third_step_desc": "Asegúrese de que la opción \"Basado en tiempo\" esté seleccionada. Confirme que ha agregado la clave."
		},
		"ldap_2fa_confirm_totp_dialog": {
			"title": "Código de confirmación",
			"desc": "Introduzca el código de su aplicación de autenticación móvil.",
			"input_placeholder": "Código",
			"confirm_button": "Continuar",
			"cant_get_code_button": "¿No puede obtener el código?",
			"cant_get_code_tooltip": "Póngase en contacto con su responsable o administrador del sistema para obtener un nuevo código."
		},
		"forgot_password_dialog": {
			"title": "Compass",
			"desc": "Complete la verificación para restablecer la contraseña"
		},
		"create_new_password_dialog": {
			"title": "Crear una contraseña nueva",
			"desc": "Se le solicitará la contraseña al autorizarse a través del correo electrónico $EMAIL",
			"password_input_placeholder": "Contraseña (mínimo 8 caracteres)",
			"confirm_password_input_placeholder": "Repita la contraseña",
			"cancel_button": "Cancelar",
			"confirm_button": "Crear",
			"success_tooltip_message": "Contraseña cambiada",
			"passwords_not_match_error": "Las contraseñas no coinciden",
			"password_less_than_min_symbols_error": "Por favor, introduzca al menos 8 caracteres"
		},
		"confirm_code_phone_number_dialog": {
			"title": "Compass",
			"desc": "Código del SMS ",
			"back_button": "Atrás",
			"resend_button": "Repetir el envío",
			"resend_after": "Intentar dentro de ",
			"auth_blocked": "Ha agotado todos los intentos de confirmación por SMS. Por favor, vuelva a intentarlo en $MINUTES."
		},
		"confirm_code_email_dialog": {
			"title": "Código de confirmación",
			"title_ldap_change_mail_confirm_current": "Confirme su correo electrónico actual",
			"title_ldap_change_mail_confirm_new": "Confirme su nueva dirección de correo electrónico",
			"desc": "Por favor, introduzca el código enviado a $EMAIL",
			"desc_ldap_change_mail_confirm_current": "Para cambiar de correo electrónico, introduzca el código enviado a $EMAIL",
			"back_button": "Atrás",
			"resend_button": "Repetir el envío",
			"resend_after": "Intentar dentro de ",
			"auth_blocked": "Ha agotado todos los intentos de confirmación mediante el código. Por favor, vuelva a intentarlo en $MINUTES.",
			"change_mail_button": "Cambiar correo electrónico",
			"ldap_2fa_change_mail_limit_error": "Fue alcanzado el límite de modificación del correo. Vuelva a intentarlo en 24 horas."
		},
		"create_profile_dialog": {
			"title": "Crear un perfil",
			"desc": "Cada usuario del equipo tiene un perfil que le es visible a sus interlocutores. Cree su perfil.",
			"input_placeholder": "Nombre Apellido",
			"cancel_button": "Cancelar",
			"confirm_button": "Seguir",
			"incorrect_name_tooltip": "Para facilitar la comunicación con los miembros de este equipo, escriba su nombre y apellidos en su propio idioma.",
			"not_saved_symbols_tooltip": "Los emoji y los símbolos especiales no se guardarán",
			"confirm_cancel_mobile": {
				"title": "¿Cancelar el registro?",
				"short_title": "¿Cancelar?",
				"confirm_button": "Sí",
				"cancel_button": "No"
			},
			"confirm_cancel_desktop": {
				"title": "¿Terminar el registro?",
				"desc": "El proceso de registro se detendrá. Puede empezar de nuevo en cualquier momento.",
				"confirm_button": "Terminar",
				"cancel_button": "Cancelar"
			}
		},
		"page_token": {
			"title": "Casi está listo",
			"desc": "Para comenzar la comunicación dentro del equipo, por favor, siga estos dos sencillos pasos:",
			"step_1": {
				"register_desc_pt1": "Copie el código de verificación",
				"register_desc_pt2": " y péguelo en la aplicación.",
				"register_button": "Copiar",
				"update_token": "Actualizar el código.",
				"login_desc_pt1": "Abra la aplicación Compass",
				"login_desc_pt2_desktop": " e introduzca el código de verificación si es necesario.",
				"login_desc_pt2_mobile": " e introduzca el código de verificación.",
				"login_button": "Abrir Compass"
			},
			"step_2": {
				"desc_pt1_mobile": "Instale la aplicación Compass",
				"desc_pt1_desktop": "Instale la aplicación Compass",
				"desc_pt2_mobile": " si no está en su móvil.",
				"desc_pt2_desktop": " si no está en su ordenador.",
				"button_mobile": "Instalar",
				"button_desktop": "Descargar"
			}
		},
		"page_invite": {
			"title": "¡Felicitaciones!",
			"desc": "Se ha unido al equipo.",
			"or": "o",
			"open_compass_mobile": {
				"title_pt1": "Abra la aplicación Compass",
				"title_pt2": "y comience a comunicarse dentro del equipo.",
				"button": "Abrir Compass"
			},
			"open_compass_wait_post_moderation_mobile": {
				"title_pt1": "Abra la aplicación Compass",
				"title_pt2": "y espere la aprobación de la solicitud.",
				"button": "Abrir Compass"
			},
			"open_compass_desktop": {
				"title_pt1": "Abra la aplicación Compass",
				"title_pt2": "e introduzca el código de verificación si es necesario.",
				"button": "Abrir Compass"
			},
			"copy_token_mobile": {
				"title_pt1": "Copie el código de verificación",
				"title_pt2": " y péguelo en la aplicación.",
				"button": "Copiar"
			},
			"install_app_desktop": {
				"title_pt1": "Instale la aplicación Compass",
				"title_pt2": " si no está en su ordenador.",
				"button": "Descargar"
			},
			"already_member": {
				"title": "¡Felicitaciones!",
				"desc": "Usted ya es miembro de este equipo."
			},
			"waiting_for_postmoderation": {
				"title": "La solicitud al equipo fue enviada",
				"desc": "Deberá esperar un poco para que el responsable apruebe su solicitud."
			},
			"join_as_guest": {
				"title": "¡Felicitaciones!",
				"desc": "Usted se unió al equipo como invitado."
			}
		},
		"settings": {
			"change_lang": "Cambiar idioma",
			"logout": "Salir"
		},
		"logout_dialog": {
			"title": "¿Desea salir?",
			"desc": "Para volver al proceso de autorización, deberá iniciar sesión.",
			"cancel_button": "Cancelar",
			"confirm_button": "Salir"
		},
		"inactive_link": {
			"title": "Enlace inactivo",
			"desc": "Por favor, comuníquese con la persona que le invitó y pídale que le envíe un nuevo enlace."
		},
		"invalid_link": {
			"title": "Enlace inválido",
			"desc": "Consulta el enlace o comuníquese con la persona que le invitó."
		},
		"accept_limit_link": {
			"title": "Límite alcanzado",
			"desc": "Ha agotado todos los intentos de unirse al equipo a través del enlace. Por favor, espere $TIME y vuelva a intentarlo."
		},
		"not_finished_space_leaving": {
			"desc": "Su proceso de eliminación de este equipo aún no se ha completado. Intente ingresar en 2 minutos."
		},
		"confirm_close_dialog": {
			"title": "¿Quiere cerrar?",
			"desc": "Los datos no se guardarán.",
			"confirm_button": "Sí",
			"cancel_button": "No"
		},
		"errors": {
			"network_error": "No hay conexión a Internet",
			"server_error": "Error de conexión. Vuelva a intentarlo.",
			"sso_error": "La autorización a través de SSO no está disponible. Póngase en contacto con el responsable\u2028o el administrador del sistema.",
			"incorrect_captcha": "No se pudo completar la verificación Captcha. Inténtelo de nuevo.",
			"email_limit_error": "Fue alcanzado el límite de introducción del correo electrónico. Intente en $MINUTES.",
			"phone_number_limit_error": "Fue alcanzado el límite de introducción del número. Intente en $MINUTES.",
			"phone_number_email_limit_error": "Fue alcanzado el límite de introducción del número o correo electrónico. Intente en $MINUTES.",
			"sso_limit_error": "Fue alcanzado el límite de intentos de acceso a través de SSO. Intente en $MINUTES.",
			"phone_number_email_incorrect_phone_email_error": "Fue introducido un número de teléfono o correo electrónico incorrecto.",
			"phone_number_incorrect_phone_error": "Fue introducido un número de teléfono incorrecto.",
			"email_incorrect_email_error": "Correo electrónico incorrecto",
			"auth_incorrect_password_error": "Contraseña inválido.",
			"confirm_code_limit_error": "Se ha alcanzado el límite de reenvío del código. Intente en $MINUTES.",
			"confirm_code_confirm_is_expired_error": "El tiempo de intento de autorización ha expirado, por favor, inténtelo de nuevo",
			"confirm_code_2fa_is_disabled_error": "La autenticación de dos factores está desactivada en el servidor Por favor, vuelva a autenticarse.",
			"add_mail_2fa_ldap_not_allowed_domain_error": "Correo electrónico incorrecto. Utilice el correo electrónico con el dominio \"@$DOMAIN\" para autenticarse en el servidor.",
			"add_mail_2fa_ldap_not_allowed_domains_error": "Correo electrónico incorrecto. Utilice el correo electrónico con los dominios $DOMAINS para autenticarse en el servidor.",
			"confirm_code_incorrect_code_error": "Código incorrecto. Por favor, vuelva a intentarlo.",
			"confirm_code_incorrect_code_one_left": "Queda",
			"confirm_code_incorrect_code_two_lefts": "Quedan",
			"confirm_code_incorrect_code_five_lefts": "Quedan",
			"confirm_code_incorrect_code_one_attempt": " intento",
			"confirm_code_incorrect_code_two_attempts": " intentos",
			"confirm_code_incorrect_code_five_attempts": " intentos",
			"create_profile_incorrect_name_error": "Nombre no válido.",
			"auth_method_disabled": "La autorización a través de SSO no está disponible. Póngase en contacto con el responsable\u2028o el administrador del sistema.",
			"sso_registration_without_invite": "El registro a través de SSO no está disponible. Póngase en contacto con el responsable o administrador del sistema para obtener un enlace de invitación.",
			"auth_ldap_method_disabled": "La autorización a través de LDAP no está disponible. Póngase en contacto con el responsable\u2028o el administrador del sistema.",
			"ldap_registration_without_invite": "El registro a través de LDAP no está disponible. Póngase en contacto con el responsable o administrador del sistema para obtener un enlace de invitación.",
			"auth_sso_full_name_incorrect": "No se pudo obtener el Nombre u otros datos del $SSO_PROVIDER_NAME. Póngase en contacto con el responsable o el administrador del sistema.",
			"auth_sso_totp_code_incorrect": "Código incorrecto. Por favor, vuelva a intentarlo."
		},
		"token_life_time_desktop": "El código es válido durante $TIME",
		"token_life_time_mobile": "El código es válido durante $TIME",
		"token_life_time_expired": "El código ha caducado.",
		"one_hour": " hora",
		"two_hours": " horas",
		"five_hours": " horas",
		"one_minute": " minuto",
		"two_minutes": " minutos",
		"five_minutes": " minutos",
		"download_compass": {
			"desktop_builds": {
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
			"mobile_stores": {
				"appstore": "Ir a Appstore",
				"google_play": "Ir a Google Play",
				"app_gallery": "Ir a AppGallery"
			}
		},
		"install_page": {
			"footer": "Hecho para usted",
			"desktop": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Descargue la aplicación Compass On-premise",
					"desc": "Versiones actualizadas de la aplicación para cualquier dispositivo.",
					"download_ios": {
						"desc": "Instálelo en su iPhone",
						"platform_app_store": "App Store"
					},
					"download_android": {
						"desc": "Instálelo en su Android",
						"platform_google_play": "Google Play"
					},
					"download_huawei": {
						"desc": "Instálelo en su Android",
						"platform_app_gallery": "AppGallery"
					},
					"support_block": {
						"title": "¿Tiene problemas con la instalación?",
						"desc": "Escríbanos a Telegram o por correo electrónico. Le ayudaremos con la instalación y configuración.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			},
			"mobile": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Instale la aplicación Compass On-premise",
					"desc": "Versiones actualizadas de la aplicación para cualquier dispositivo.",
					"download_ios": "Ir a Appstore",
					"download_android": "Ir a Google Play",
					"download_huawei": "Ir a AppGallery",
					"desktop_footer": "Disponible para ",
					"download_macos": "MacOS",
					"download_comma": ", ",
					"download_windows": "Windows",
					"download_and": " y ",
					"download_linux": "Linux",
					"download_dot": ".",
					"on_success_copy": "El enlace de descarga de Compass para computadora ha sido copiado",
					"support_block": {
						"title": "¿Tiene problemas con la instalación?",
						"desc": "Escríbanos a Telegram o por correo electrónico. Le ayudaremos con la instalación y configuración.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			}
		}
	},
	it: {
		"welcome_dialog": {
			"title": "Invito",
			"desc_mobile": " ti sta invitando nel messenger aziendale Compass. Effettua l'autorizzazione per iniziare.",
			"desc_desktop": " ti sta invitando nel messenger aziendale Compass. Effettua l'autorizzazione per iniziare.",
			"confirm_button": "Avanti"
		},
		"email_phone_number_dialog": {
			"title": "Ciao",
			"desc_email": "Per effettuare l'accesso, inserisci l'email:",
			"desc_email_guest": "Per effettuare l'accesso all'account ospite, inserisci l'email:",
			"desc_phone_number": "Per effettuare l'accesso, inserisci il numero di telefono:",
			"desc_phone_number_guest": "Per effettuare l'accesso all'account ospite, inserisci il numero di telefono:",
			"desc_email_phone_number": "Per effettuare l'accesso, inserisci l'email o il numero di telefono:",
			"desc_email_phone_number_guest": "Per effettuare l'accesso all'account ospite, inserisci l'email o il numero di telefono:",
			"desc_sso": "Per effettuare l'accesso, clicca sul pulsante qui sotto:",
			"desc_sso_guest": "Per effettuare l'accesso all'account ospite, clicca sul pulsante qui sotto:",
			"input_placeholder_email": "Email",
			"input_placeholder_phone_number": "Telefono",
			"input_placeholder_email_phone_number": "Email o telefono",
			"confirm_button": "Avanti",
			sso_button: "Accedi al portale aziend. (SSO LDAP)",
			"open_guest_auth_methods_button": "Accedi come ospite",
			"prohibited_symbols_tooltip": "Non sono supportati emoji, spazi e caratteri speciali"
		},
		"email_register_dialog": {
			"title": "Compass",
			"desc": "Crea una password per l'autorizzazione tramite $EMAIL",
			"password_input_placeholder": "Password (minimo 8 caratteri)",
			"confirm_password_input_placeholder": "Ripeti la password",
			"back_button": "Indietro",
			"register_button": "Registrati",
			"passwords_not_match_error": "La password non corrisponde",
			"password_less_than_min_symbols_error": "Inserisci minimo 8 caratteri"
		},
		"email_login_dialog": {
			"title": "Compass",
			"desc": "Inserisci la password per l'autorizzazione tramite $EMAIL",
			"password_input_placeholder": "Inserisci la password",
			"back_button": "Indietro",
			"login_button": "Avanti",
			"forgot_password_button": "Hai dimenticato la password?",
			"auth_blocked": "Hai esaurito tutti i tentativi di inserimento della password. Riprova tra $MINUTES."
		},
		"ldap_login_dialog": {
			"title": "Compass",
			"desc": "Per effettuare l'accesso, immenti il nome utente e la password del tuo account LDAP aziendale:",
			"username_input_placeholder": "Il nome utente",
			"password_input_placeholder": "Password / + codice monouso",
			"back_button": "Indietro",
			"login_button": "Avanti",
			"unknown_error": "L'autenticazione tramite LDAP è momentaneamente non disponibile. Contatta il tuo responsabile o l'amministratore di sistema.",
			"incorrect_credentials_error": "Il nome utente o la password inseriti non sono corretti",
			"auth_blocked": "È stato raggiunto il limite per l'accesso tramite LDAP. Riprova tra $MINUTES.",
			"incorrect_config_user_search_filter": "Errore nella sintassi del filtro per l'autorizzazione LDAP. Contatta il tuo responsabile o l'amministratore di sistema.",
			"incorrect_2fa_mail_config_attribute": "Impossibile ricevere l'email per l'autenticazione a due fattori o altri dati da LDAP. Contatta il tuo responsabile o l'amministratore di sistema."
		},
		"ldap_2fa_add_mail_dialog": {
			"title": "Autenticazione a due fattori",
			"desc": "Specifica l'indirizzo email a cui verrà inviato il codice di conferma per accedere al tuo account.",
			"desc_change_mail": "Specifica l'indirizzo email a cui verrà inviato il codice di conferma per accedere al tuo account.",
			"mail_input_placeholder": "Email",
			"confirm_button": "Avanti",
			"change_mail_toast_success": "L'indirizzo email per l'autenticazione a due fattori è stato modificato"
		},
		"ldap_2fa_setup_totp_dialog": {
			"title": "Autenticazione a due fattori",
			"desc": "Scansiona il codice QR in un'app di autenticazione mobile.",
			"confirm_button": "Avanti",
			"cant_scan_qr_button": "Non riesci a scansionare il codice QR?"
		},
		"ldap_2fa_setup_totp_cant_scan_qr_dialog": {
			"title": "Configurazione senza QR",
			"first_step_desc_desktop": "Nell'app di autenticazione mobile, seleziona l'inserimento manuale della chiave di configurazione.",
			"first_step_desc_mobile": "Nell'app di autenticazione mobile, seleziona l'inserimento manuale della chiave di configurazione.",
			"second_step_desc": "Inserisci la tua chiave:",
			"third_step_desc": "Assicurati che sia selezionata l'opzione \"Basato sul tempo\". Conferma l'aggiunta della chiave."
		},
		"ldap_2fa_confirm_totp_dialog": {
			"title": "Codice di conferma",
			"desc": "Inserisci il codice dall'app di autenticazione mobile.",
			"input_placeholder": "Codice",
			"confirm_button": "Avanti",
			"cant_get_code_button": "Non riesci a ottenere il codice?",
			"cant_get_code_tooltip": "Contatta il tuo responsabile o l'amministratore di sistema pervricevere un nuovo codice."
		},
		"forgot_password_dialog": {
			"title": "Compass",
			"desc": "Completa la verifica per procedere con il ripristino della password"
		},
		"create_new_password_dialog": {
			"title": "Crea una nuova password",
			"desc": "Verrà richiesta una password durante l'autorizzazione tramite $EMAIL",
			"password_input_placeholder": "Password (minimo 8 caratteri)",
			"confirm_password_input_placeholder": "Ripeti la password",
			"cancel_button": "Annulla",
			"confirm_button": "Crea",
			"success_tooltip_message": "Password cambiata",
			"passwords_not_match_error": "La password non corrisponde",
			"password_less_than_min_symbols_error": "Inserisci minimo 8 caratteri"
		},
		"confirm_code_phone_number_dialog": {
			"title": "Compass",
			"desc": "Codice dell'SMS ",
			"back_button": "Indietro",
			"resend_button": "Invia di nuovo",
			"resend_after": "Riprova tra ",
			"auth_blocked": "Hai esaurito i tentativi di conferma tramite SMS. Riprova tra $MINUTES."
		},
		"confirm_code_email_dialog": {
			"title": "Codice di conferma",
			"title_ldap_change_mail_confirm_current": "Conferma la tua email attuale",
			"title_ldap_change_mail_confirm_new": "Conferma la nuova email",
			"desc": "Inserisci il codice inviato a $EMAIL",
			"desc_ldap_change_mail_confirm_current": "Per procedere alla modifica della tua email, inserisci il codice inviato a $EMAIL",
			"back_button": "Indietro",
			"resend_button": "Invia di nuovo",
			"resend_after": "Riprova tra ",
			"auth_blocked": "Hai esaurito i tentativi per l'autenticazione tramite codice. Riprova tra $MINUTES.",
			"change_mail_button": "Modifica email",
			"ldap_2fa_change_mail_limit_error": "È stato raggiunto il limite per la modifica dell'email. Riprova tra 24 ore."
		},
		"create_profile_dialog": {
			"title": "Crea profilo",
			"desc": "Ciascun utente del team ha il suo profilo visibile dagli interlocutori. Crea il tuo profilo.",
			"input_placeholder": "Nome e cognome",
			"cancel_button": "Annulla",
			"confirm_button": "Avanti",
			"incorrect_name_tooltip": "Per rendere più comodo per i membri del team comunicare con te, scrivi il tuo nome e cognome nella tua lingua.",
			"not_saved_symbols_tooltip": "Emoji e caratteri speciali non verranno salvati",
			"confirm_cancel_mobile": {
				"title": "Annullare la registrazione?",
				"short_title": "Annullare?",
				"confirm_button": "Sì",
				"cancel_button": "No"
			},
			"confirm_cancel_desktop": {
				"title": "Terminare la registrazione?",
				"desc": "Il processo di registrazione verrà sospeso. È possibile ricominciare da capo in qualsiasi momento.",
				"confirm_button": "Termina",
				"cancel_button": "Annulla"
			}
		},
		"page_token": {
			"title": "Ci siamo quasi",
			"desc": "Per iniziare a comunicare con il team, effettua due passaggi semplici:",
			"step_1": {
				"register_desc_pt1": "Copia il codice di verifica",
				"register_desc_pt2": " e incollalo nell'app.",
				"register_button": "Copia",
				"update_token": "Aggiorna il codice.",
				"login_desc_pt1": "Apri l'app Compass",
				"login_desc_pt2_desktop": " e, se necessario, immetti il codice di verifica.",
				"login_desc_pt2_mobile": " e immetti il codice di verifica.",
				"login_button": "Apri Compass"
			},
			"step_2": {
				"desc_pt1_mobile": "Installa l'app Compass,",
				"desc_pt1_desktop": "Installa l'app Compass,",
				"desc_pt2_mobile": " se non è presente nel tuo telefono.",
				"desc_pt2_desktop": " se non è presente nel tuo computer.",
				"button_mobile": "Installa",
				"button_desktop": "Scarica"
			}
		},
		"page_invite": {
			"title": "Congratulazioni!",
			"desc": "Ti sei unito al team.",
			"or": "o",
			"open_compass_mobile": {
				"title_pt1": "Apri l'app Compass",
				"title_pt2": "e inizia a comunicare all'interno del team.",
				"button": "Apri Compass"
			},
			"open_compass_wait_post_moderation_mobile": {
				"title_pt1": "Apri l'app Compass",
				"title_pt2": "e attendi l'approvazione della richiesta.",
				"button": "Apri Compass"
			},
			"open_compass_desktop": {
				"title_pt1": "Apri l'app Compass",
				"title_pt2": "e, se necessario, immetti il codice di verifica.",
				"button": "Apri Compass"
			},
			"copy_token_mobile": {
				"title_pt1": "Copia il codice di verifica",
				"title_pt2": " e incollalo nell'app.",
				"button": "Copia"
			},
			"install_app_desktop": {
				"title_pt1": "Installa l'app Compass,",
				"title_pt2": "se non è presente nel tuo computer.",
				"button": "Scarica"
			},
			"already_member": {
				"title": "Congratulazioni!",
				"desc": "Sei già membro di in questo team."
			},
			"waiting_for_postmoderation": {
				"title": "La richiesta di ammissione al team è stata inviata",
				"desc": "Dovrai attendere che il responsabile del team approvi la tua richiesta."
			},
			"join_as_guest": {
				"title": "Congratulazioni!",
				"desc": "Sei entrato nel team in qualità di ospite."
			}
		},
		"settings": {
			"change_lang": "Cambia lingua",
			"logout": "Esci"
		},
		"logout_dialog": {
			"title": "Uscire?",
			"desc": "Per tornare all'autorizzazione è necessario effettuare l'accesso.",
			"cancel_button": "Annulla",
			"confirm_button": "Esci"
		},
		"inactive_link": {
			"title": "Il link non è attivo",
			"desc": "Contatta la persona che ti ha invitato e chiedile di inviarti un nuovo link."
		},
		"invalid_link": {
			"title": "Link non valido",
			"desc": "Consultare il link o contatta la persona che ti ha invitato."
		},
		"accept_limit_link": {
			"title": "Limite raggiunto",
			"desc": "Hai esaurito tutti i tentativi per entrare nel team tramite il link. Attendi $TIME e riprova."
		},
		"not_finished_space_leaving": {
			"desc": "Il processo di eliminazione da questo team non è ancora stato completato. Prova a entrare tra 2 minuti."
		},
		"confirm_close_dialog": {
			"title": "Chiudere?",
			"desc": "I dati non verranno salvati.",
			"confirm_button": "Sì",
			"cancel_button": "No"
		},
		"errors": {
			"network_error": "Nessuna connessione a Internet",
			"server_error": "Errore di connessione. Riprova.",
			"sso_error": "L'accesso tramite SSO non è disponibile. Contatta il tuo responsabile o l'amministratore di sistema.",
			"incorrect_captcha": "Captcha non superato, riprova",
			"email_limit_error": "È stato raggiunto il limite per l'inserimento dell'email. Riprova tra $MINUTES.",
			"phone_number_limit_error": "È stato raggiunto il limite per l'inserimento del numero. Riprova tra $MINUTES.",
			"phone_number_email_limit_error": "È stato raggiunto il limite per l'inserimento del numero o e-mail. Riprova tra $MINUTES.",
			"sso_limit_error": "È stato raggiunto il limite per l'accesso tramite SSO. Riprova tra $MINUTES.",
			"phone_number_email_incorrect_phone_email_error": "Numero di telefono o l'email non corretto",
			"phone_number_incorrect_phone_error": "Numero di telefono non corretto",
			"email_incorrect_email_error": "Indirizzo email non valido",
			"auth_incorrect_password_error": "La password inserita non è corretta.",
			"confirm_code_limit_error": "È stato raggiunto il limite per il reinvio del codice. Riprova tra $MINUTES.",
			"confirm_code_confirm_is_expired_error": "Il tentativo di autorizzazione è scaduto, riprovare",
			"confirm_code_2fa_is_disabled_error": "L'autenticazione a due fattori è disabilitata sul server. Ripeti l’autenticazione.",
			"add_mail_2fa_ldap_not_allowed_domain_error": "Indirizzo email non valido. Utilizza un'email con il dominio \"@$DOMAIN\" per autenticarti sul server.",
			"add_mail_2fa_ldap_not_allowed_domains_error": "Indirizzo email non valido. Utilizza un'email con il dominio $DOMAINS per autenticarti sul server.",
			"confirm_code_incorrect_code_error": "Codice non valido. Riprova.",
			"confirm_code_incorrect_code_one_left": "Sono rimasti",
			"confirm_code_incorrect_code_two_lefts": "Sono rimasti",
			"confirm_code_incorrect_code_five_lefts": "Sono rimasti",
			"confirm_code_incorrect_code_one_attempt": " tentativo",
			"confirm_code_incorrect_code_two_attempts": " tentativi",
			"confirm_code_incorrect_code_five_attempts": " tentativi",
			"create_profile_incorrect_name_error": "Nome non valido.",
			"auth_method_disabled": "L'accesso tramite SSO non è disponibile. Contatta il tuo responsabile o l'amministratore di sistema.",
			"sso_registration_without_invite": "La registrazione tramite SSO non è disponibile. Contatta il tuo responsabile o l'amministratore di sistema per ottenere un link di invito.",
			"auth_ldap_method_disabled": "L'accesso tramite LDAP non è disponibile. Contatta il tuo responsabile o l'amministratore di sistema.",
			"ldap_registration_without_invite": "La registrazione tramite LDAP non è disponibile. Contatta il tuo responsabile o l'amministratore di sistema per ottenere un link di invito.",
			"auth_sso_full_name_incorrect": "Impossibile recuperare il Nome o altri dati da $SSO_PROVIDER_NAME. Contatta il tuo responsabile o l'amministratore di sistema.",
			"auth_sso_totp_code_incorrect": "Codice non valido. Riprova."
		},
		"token_life_time_desktop": "Il codice è valido per $TIME",
		"token_life_time_mobile": "Il codice è valido per $TIME",
		"token_life_time_expired": "Il codice è scaduto.",
		"one_hour": " ora",
		"two_hours": " ore",
		"five_hours": " ore",
		"one_minute": " minuto",
		"two_minutes": " minuti",
		"five_minutes": " minuti",
		"download_compass": {
			"desktop_builds": {
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
			"mobile_stores": {
				"appstore": "Vai all'Appstore",
				"google_play": "Vai a Google Play",
				"app_gallery": "Vai all'AppGallery"
			}
		},
		"install_page": {
			"footer": "Fatto per Lei",
			"desktop": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Scarica l'app Compass On-premise",
					"desc": "Versioni aggiornate dell'app per qualsiasi dispositivo.",
					"download_ios": {
						"desc": "Installalo sul iPhone",
						"platform_app_store": "App Store"
					},
					"download_android": {
						"desc": "Installalo sul Android",
						"platform_google_play": "Google Play"
					},
					"download_huawei": {
						"desc": "Installalo sul Huawei",
						"platform_app_gallery": "AppGallery"
					},
					"support_block": {
						"title": "Problemi con l'installazione?",
						"desc": "Scrivici nella Telegram o via email Ti aiuteremo con l'installazione e la configurazione.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			},
			"mobile": {
				"logo": {
					"title": "COMPASS",
					"onpremise_title": "On-premise"
				},
				"page": {
					"title": "Installa l'app Compass On-premise",
					"desc": "Versioni aggiornate dell'app per qualsiasi dispositivo.",
					"download_ios": "Vai all'Appstore",
					"download_android": "Vai a Google Play",
					"download_huawei": "Vai all'AppGallery",
					"desktop_footer": "Disponibile per ",
					"download_macos": "MacOS",
					"download_comma": ", ",
					"download_windows": "Windows",
					"download_and": " e ",
					"download_linux": "Linux",
					"download_dot": ".",
					"on_success_copy": "Il link per scaricare Compass per computer è stato copiato",
					"support_block": {
						"title": "Problemi con l'installazione?",
						"desc": "Scrivici nella Telegram o via email Ti aiuteremo con l'installazione e la configurazione.",
						"telegram": "Telegram",
						"mail": "support@getcompass.ru"
					}
				}
			}
		}
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