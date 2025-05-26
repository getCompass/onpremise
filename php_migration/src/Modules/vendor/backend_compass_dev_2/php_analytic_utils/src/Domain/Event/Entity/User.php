<?php

namespace AnalyticUtils\Domain\Event\Entity;

use AnalyticUtils\Domain\Counter\Entity\Main as Counter;
use AnalyticUtils\Domain\Counter\Entity\User as UserCounter;

/**
 * Класс сущности пользовательских событий
 *
 * Warning!!! ПОСЛЕДНЕЕ ЗНАЧЕНИЕ: [160] - храним здесь последнее используемое значение для удобства
 */
class User extends Main {

	// тип сущности - в родительском классе это общее событие
	protected const _ENTITY_TYPE = "user";

	// общее
	public const PIVOT_APP_RETURN = 1; // возврат пользователя в приложение

	// регистрация и авторизация
	public const PIVOT_AVATAR_SET                              = 2; // установил аватар в процессе регистрации ???
	public const PIVOT_AVATAR_DELETED                          = 3; // удалил аватар в процессе регистрации ???
	public const PIVOT_REGISTERED                              = 4; // пользователь зарегистрировался
	public const PIVOT_LOGGED_IN                               = 5; // пользователь авторизовался
	public const COMPANY_AVATAR_SET                            = 6; // пользователь установил аватар компании
	public const COMPANY_AVATAR_DELETED                        = 7; // пользователь удалил аватар компании
	public const COMPANY_CREATED                               = 8; // создал компанию
	public const PIVOT_INVITE_LINK_CLICKED                     = 9; // перешел по ссылке приглашению ???
	public const PIVOT_INVITE_LINK_ENTERED                     = 10; // ввел ссылку-приглашению вручную ???
	public const COMPANY_HIRING_REQUEST_ADDED_WITH_COMMENT     = 11; // оставил заявку на вступление с комментарием
	public const COMPANY_HIRING_REQUEST_ADDED_WITHOUT_COMMENT  = 12; // оставил заявку на вступление без комментария
	public const PIVOT_INVITE_LINK_ACCEPTED_WITHOUT_MODERATION = 13; // принял приглашение по ссылке без модерации
	public const PIVOT_ACCOUNT_DELETION_STARTED                = 14; // начал процесс удаления аккаунта
	public const PIVOT_ACCOUNT_DELETION_CONFIRMED              = 15; // подтвердил удаление аккаунта
	public const PIVOT_LOGGED_OUT                              = 16; // вышел из аккаунта

	// меню компании
	public const COMPANY_NOTIFICATIONS_DISABLED              = 17; // отключил уведомления в компании
	public const COMPANY_NOTIFICATIONS_ENABLED               = 18; // включил уведомления в компании
	public const COMPANY_MEMBER_LIST_OPENED                  = 19; // открыл список сотрудников компании
	public const COMPANY_RATING_OPENED                       = 20; // открыл статистику компании
	public const COMPANY_USERBOT_CREATED                     = 21; // создан бот
	public const COMPANY_USERBOT_LIST_OPENED                 = 22; // список ботов открыт
	public const COMPANY_NAME_CHANGED                        = 23; // изменил название компании
	public const COMPANY_ROLE_SETTINGS_OPENED                = 24; // изменил настройки ролей компании
	public const COMPANY_OWNER_ADDED                         = 25; // добавили руководителя
	public const COMPANY_OWNER_DELETED                       = 26; // удалили из руководителей
	public const COMPANY_HR_ADDED                            = 27; // добавили пользователя в "Найм и увольнение"
	public const COMPANY_HR_DELETED                          = 28; // удалили пользователя из "Найм и увольнение"
	public const COMPANY_ADMIN_ADDED                         = 29; // добавили администратора
	public const COMPANY_ADMIN_DELETED                       = 30; // удалили администратора
	public const COMPANY_DEVELOPER_ADDED                     = 31; // добавили программиста
	public const COMPANY_DEVELOPER_DELETED                   = 32; // удалили программиста
	public const COMPANY_NOTIFICATIONS_TEXT_HIDING_ENABLED   = 33; // включили скрытие текста в уведомлениях
	public const COMPANY_NOTIFICATIONS_TEXT_HIDING_DISABLED  = 34; // отключили скрытие текста в уведомлениях
	public const COMPANY_DELETION_STARTED                    = 35; // начали процесс удаления компании
	public const COMPANY_DELETION_CONFIRMED                  = 36; // подтвердили процесс удаления компании
	public const COMPANY_SELF_DISMISSAL_STARTED              = 37; // начал процесс самоувольнения
	public const COMPANY_SELF_DISMISSAL_CONFIRMED            = 38; // подтвердил самоувольнение
	public const COMPANY_GENERAL_CHAT_NOTIFICATIONS_ENABLED  = 159; // включили оповещения в главный чат
	public const COMPANY_GENERAL_CHAT_NOTIFICATIONS_DISABLED = 160; // включили оповещения в главный чат

	// меню профиля
	public const COMPANY_EMPLOYEE_CARD_OPENED           = 39;  // открыл карточку сотрудника
	public const COMPANY_EMPLOYEE_MY_CARD_OPENED        = 152; // открыл свою карточку сотрудника
	public const PIVOT_NOTIFICATIONS_ENABLED            = 40;  // включил общие уведомления
	public const PIVOT_NOTIFICATIONS_DISABLED           = 41;  // отключил общие уведомления
	public const PIVOT_NOTIFICATION_DISABLED_FOR_GROUPS = 42;  // отключил уведомления для групп
	public const PIVOT_NOTIFICATIONS_DISABLE_TIME_ADDED = 43;  // добавил время на отключение уведомлений

	// пригласить в компанию
	public const COMPANY_JOIN_LINK_MAIN_OPENED    = 44; // открыт экран с основной ссылкой
	public const COMPANY_JOIN_LINK_LIST_OPENED    = 45; // открыт экран с ссылками
	public const COMPANY_MAIN_JOIN_LINK_GENERATED = 46; // сгенерирована новая основная ссылка
	public const COMPANY_JOIN_LINK_EDITED         = 47; // ссылка изменена
	public const COMPANY_JOIN_LINK_DELETED        = 48; // ссылка удалена
	public const COMPANY_JOIN_LINK_GENERATED      = 49; // сгенерирована новая основная ссылка

	// левое меню и чат
	public const PIVOT_COMPANY_ORDER_CHANGED                 = 50;  // изменил позицию компании в левом меню
	public const COMPANY_LEFT_MENU_UNREAD_SWITCHED           = 51;  // переключились на непрочитанные
	public const COMPANY_LEFT_MENU_ALL_SWITCHED              = 52;  // переключились на все чаты
	public const COMPANY_CONVERSATION_SINGLE_CREATED         = 53;  // создали сингл
	public const COMPANY_CONVERSATION_MARKED_AS_UNREAD       = 58;  // пометили чат как непрочитанный
	public const COMPANY_CONVERSATION_ADDED_TO_FAVORITES     = 59;  // добавили чат в избранное
	public const COMPANY_CONVERSATION_REMOVED_FROM_FAVORITES = 60;  // удалили чат из избранного
	public const COMPANY_CONVERSATION_NOTIFICATIONS_DISABLED = 61;  // отключил уведомления в чате
	public const COMPANY_CONVERSATION_NOTIFICATIONS_ENABLED  = 62;  // включил уведомления в чате
	public const COMPANY_GROUP_LEFT                          = 63;  // покинул группу
	public const COMPANY_CONVERSATION_SINGLE_LEFT            = 64;  // покинул сингл
	public const COMPANY_GROUP_GENERAL_OPENED                = 65;  // открыт "Главный чат"
	public const COMPANY_GROUP_HIRING_OPENED                 = 66;  // открыт чат найма и увольнения
	public const COMPANY_GROUP_NOTES_OPENED                  = 67;  // открыты заметки
	public const COMPANY_SINGLE_DEFAULT_OPENED               = 157; // открыт диалог
	public const COMPANY_GROUP_DEFAULT_OPENED                = 158; // открыта группа

	// чат (общие действия)
	public const COMPANY_CONVERSATION_HELLO_MESSAGE_ADDED                     = 68; // отправил "Привет 👋"
	public const COMPANY_CONVERSATION_TEXT_MESSAGED_ADDED                     = 69; // отправил текстовое сообщение
	public const COMPANY_CONVERSATION_MENTION_MESSAGED_ADDED                  = 70; // отправил текстовое сообщение с меншеном
	public const COMPANY_CONVERSATION_IMAGE_MESSAGE_ADDED                     = 71; // отправил картинку
	public const COMPANY_CONVERSATION_VIDEO_MESSAGE_ADDED                     = 72; // отправил видео
	public const COMPANY_CONVERSATION_AUDIO_MESSAGE_ADDED                     = 73; // отправил аудио
	public const COMPANY_CONVERSATION_FILE_MESSAGE_ADDED                      = 74; // отправил файл
	public const COMPANY_CONVERSATION_DOCUMENT_MESSAGE_ADDED                  = 75; // отправил документ
	public const COMPANY_CONVERSATION_VOICE_MESSAGE_ADDED                     = 76; // отправил голосовое
	public const COMPANY_CONVERSATION_REACTION_ADDED                          = 77; // отправил реакцию
	public const COMPANY_CONVERSATION_REACTION_DELETED                        = 78; // удалил реакцию
	public const COMPANY_THREAD_MESSAGED_ADDED                                = 79; // добавили сообщение в тред
	public const COMPANY_CONVERSATION_MESSAGE_QUOTED_WITHOUT_COMMENT          = 80; // процитировал сообщение без комментария
	public const COMPANY_CONVERSATION_MESSAGE_QUOTED_WITH_COMMENT             = 81; // процитировал сообщение с комментарием
	public const COMPANY_CONVERSATION_MESSAGE_DELETED                         = 82; // удалил сообщение
	public const COMPANY_CONVERSATION_MESSAGE_HIDDEN                          = 83; // скрыл сообщение
	public const COMPANY_CONVERSATION_MESSAGE_REPORTED                        = 84; // пожаловался на сообщение
	public const COMPANY_CONVERSATION_MESSAGE_REPOSTED_WITHOUT_COMMENT        = 85; // репост без комментария
	public const COMPANY_CONVERSATION_MESSAGE_REPOSTED_WITH_COMMENT           = 86; // репост с комментарием
	public const COMPANY_CONVERSATION_HISTORY_CLEARED_MYSELF                  = 87; // история сообщения очищена у себя
	public const COMPANY_CONVERSATION_HISTORY_CLEARED                         = 88; // история сообщения очищена
	public const COMPANY_CONVERSATION_THREAD_MESSAGE_REPOSTED_WITHOUT_COMMENT = 115; // репостнул сообщение треда без комментария
	public const COMPANY_CONVERSATION_THREAD_MESSAGE_REPOSTED_WITH_COMMENT    = 116; // репостнул сообщения треда с комментарием

	// настройки профиля
	public const PIVOT_PROFILE_NAME_CHANGED                   = 89;  // изменил имя профиля
	public const COMPANY_PROFILE_STATUS_SET                   = 90;  // установил статус в профиле
	public const PIVOT_PROFILE_PHONE_CHANGE                   = 91;  // сменил номер телефона
	public const COMPANY_PROFILE_BADGE_COLOR_CHANGED          = 92;  // сменил цвет бейджа
	public const COMPANY_PROFILE_MY_BADGE_COLOR_CHANGED       = 153; // сменил цвет своего бейджа
	public const COMPANY_PROFILE_BADGE_DESCRIPTION_CHANGED    = 93;  // сменил описание бейджа
	public const COMPANY_PROFILE_MY_BADGE_DESCRIPTION_CHANGED = 154; // сменил описание своего бейджа
	public const COMPANY_PROFILE_BADGE_DELETED                = 94;  // удалил данные бейджа
	public const COMPANY_PROFILE_MY_BADGE_DELETED             = 155; // удалил данные своего бейджа
	public const COMPANY_PROFILE_JOIN_TIME_CHANGED            = 95;  // изменил дату вступления
	public const COMPANY_PROFILE_MY_JOIN_TIME_CHANGED         = 156; // изменил свою дату вступления

	// группа
	public const COMPANY_GROUP_AVATAR_ADDED                      = 54; // добавили аватар при создании группы
	public const COMPANY_GROUP_AVATAR_DELETED                    = 55; // удалили аватар при создании группы ???
	public const COMPANY_GROUP_CREATED                           = 56; // создали группу
	public const COMPANY_GROUP_MEMBERS_INVITED                   = 57; // добавили в группу пользователей
	public const COMPANY_GROUP_EDITED                            = 96; // отредактировал группу
	public const COMPANY_GROUP_MEMBER_DELETED                    = 97; // удалил из группы пользователя
	public const COMPANY_GROUP_MEMBER_INVITED                    = 98; // добавил в группу пользователя
	public const COMPANY_GROUP_INVITE_DECLINED                   = 99; // отклонил приглашение в группу
	public const COMPANY_GROUP_LOWERED_TO_MEMBER                 = 100; // понизил администратора до участника
	public const COMPANY_GROUP_PROMOTED_TO_ADMIN                 = 101; // повысил участника до админа
	public const COMPANY_GROUP_SELF_LOWERED_TO_MEMBER            = 102; // понизил себя до участника
	public const COMPANY_GROUP_SELF_PROMOTED_TO_ADMIN            = 103; // самоназначился до админа
	public const COMPANY_GROUP_COPIED                            = 104; // продублировал группу
	public const COMPANY_GROUP_SHOW_HISTORY_OPTION_ENABLED       = 105; // включил опцию "Показывать историю сообщений"
	public const COMPANY_GROUP_SHOW_HISTORY_OPTION_DISABLED      = 106; // отключил опцию "Показывать историю сообщений"
	public const COMPANY_GROUP_JOIN_NOTIFICATION_OPTION_ENABLED  = 107; // включил опцию "Уведомление о вступлении"
	public const COMPANY_GROUP_JOIN_NOTIFICATION_OPTION_DISABLED = 108; // отключил опцию "Уведомление о вступлении"
	public const COMPANY_GROUP_EXIT_NOTIFICATION_OPTION_ENABLED  = 109; // включил опцию "Уведомление о выходе"
	public const COMPANY_GROUP_EXIT_NOTIFICATION_OPTION_DISABLED = 110; // отключил опцию "Уведомление о выходе"

	// все комментарии
	public const COMPANY_THREAD_MESSAGE_DELETED                  = 111; // удалил комментарий
	public const COMPANY_THREAD_MESSAGE_HIDDEN                   = 112; // скрыл комментарий
	public const COMPANY_THREAD_MESSAGE_QUOTED_WITHOUT_COMMENT   = 113; // процитировал комментарий в треде (без добавления комментария)
	public const COMPANY_THREAD_MESSAGE_QUOTED_WITH_COMMENT      = 114; // процитировал комментарий в треде (с добавлением комментария)
	public const COMPANY_THREAD_MESSAGE_REPOSTED_WITHOUT_COMMENT = 117; // переслал сообщение в тред без комментария
	public const COMPANY_THREAD_MESSAGE_REPOSTED_WITH_COMMENT    = 118; // переслал сообщение в тред c комментарием
	public const COMPANY_THREAD_ADDED_TO_FAVORITES               = 119; // добавил тред в избранное
	public const COMPANY_THREAD_REMOVED_FROM_FAVORITES           = 120; // убрал тред из избранного
	public const COMPANY_THREAD_MARK_AS_UNREAD                   = 121; // пометил тред непрочитанным
	public const COMPANY_THREAD_UNFOLLOWED                       = 122; // отписался от треда
	public const COMPANY_THREAD_FOLLOWED                         = 123; // подписался на тред
	public const COMPANY_THREAD_REACTION_ADDED                   = 124; // добавил реакцию на комментарий
	public const COMPANY_THREAD_REACTION_REMOVED                 = 125; // удалил реакцию с комментария
	public const COMPANY_THREAD_TEXT_MESSAGED_ADDED              = 144; // отправил текстовое сообщение
	public const COMPANY_THREAD_MENTION_MESSAGED_ADDED           = 145; // отправил текстовое сообщение с меншеном
	public const COMPANY_THREAD_IMAGE_MESSAGE_ADDED              = 146; // отправил картинку
	public const COMPANY_THREAD_VIDEO_MESSAGE_ADDED              = 147; // отправил видео
	public const COMPANY_THREAD_AUDIO_MESSAGE_ADDED              = 148; // отправил аудио
	public const COMPANY_THREAD_FILE_MESSAGE_ADDED               = 149; // отправил файл
	public const COMPANY_THREAD_DOCUMENT_MESSAGE_ADDED           = 150; // отправил документ
	public const COMPANY_THREAD_VOICE_MESSAGE_ADDED              = 151; // отправил голосовое

	// файлы
	public const COMPANY_CONVERSATION_FILE_LIST_OPENED = 126; // открыл список файлов чата

	// наймы и увольнения
	public const COMPANY_HIRING_REQUEST_CONFIRMED   = 127; // одобрил вступление сотрудника в компанию
	public const COMPANY_HIRING_REQUEST_DECLINED    = 128; // отклонил вступление сотрудника в компанию
	public const COMPANY_DISMISSAL_REQUEST_DECLINED = 129; // одобрил увольнение сотрудника в компанию

	// бот
	public const COMPANY_USERBOT_EDITED                 = 130; // отредактировал чат-бота
	public const COMPANY_USERBOT_DISABLED               = 131; // выключил чат-бота
	public const COMPANY_USERBOT_ENABLED                = 132; // включил чат-бота
	public const COMPANY_USERBOT_DELETED                = 133; // удалил чат-бота
	public const COMPANY_USERBOT_SECRET_REFRESHED       = 134; // перегенерировал ключ чат-бота
	public const COMPANY_USERBOT_REACT_COMMAND_ENABLED  = 135; // включил реагирование на команды
	public const COMPANY_USERBOT_REACT_COMMAND_DISABLED = 136; // выключил реагирование на команды
	public const COMPANY_USERBOT_ADDED_TO_GROUP         = 137; // добавили бота в группу
	public const COMPANY_USERBOT_REMOVED_FROM_GROUP     = 138; // удалили бота из группы
	public const COMPANY_USERBOT_COMMAND_SET            = 139; // нажал "отправить команду"

	// звонки
	public const COMPANY_CALL_ACCEPTED      = 140; // принял входящий вызов
	public const COMPANY_CALL_INIT          = 141; // совершил исходящий вызов
	public const COMPANY_CALL_MEMBER_ADDED  = 142; // добавил участника в звонок
	public const COMPANY_CALL_MEMBER_KICKED = 143; // удалил участника из звонка

	// известные события с типом
	public const EVENT_SETTINGS_LIST = [
		self::PIVOT_APP_RETURN                                             => [
			"counter" => [
				"id"     => UserCounter::PIVOT_APP_RETURN,
				"action" => Counter::ACTION_INCREMENT,
			],
			"group"   => Group::GENERAL,
		],

		// регистрация и авторизация
		self::PIVOT_AVATAR_SET                                             => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_AVATAR_DELETED                                         => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_REGISTERED                                             => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_LOGGED_IN                                              => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::COMPANY_AVATAR_SET                                           => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::COMPANY_AVATAR_DELETED                                       => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::COMPANY_CREATED                                              => [
			"counter" => [
				"id"     => UserCounter::COMPANY_CREATED,
				"action" => Counter::ACTION_INCREMENT,
			],
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_INVITE_LINK_CLICKED                                    => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_INVITE_LINK_ENTERED                                    => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::COMPANY_HIRING_REQUEST_ADDED_WITH_COMMENT                    => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::COMPANY_HIRING_REQUEST_ADDED_WITHOUT_COMMENT                 => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_INVITE_LINK_ACCEPTED_WITHOUT_MODERATION                => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_ACCOUNT_DELETION_STARTED                               => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_ACCOUNT_DELETION_CONFIRMED                             => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],
		self::PIVOT_LOGGED_OUT                                             => [
			"counter" => null,
			"group"   => Group::REGISTRATION_AUTHORIZATION,
		],

		// меню компании
		self::COMPANY_NOTIFICATIONS_DISABLED                               => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_NOTIFICATIONS_ENABLED                                => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_MEMBER_LIST_OPENED                                   => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_RATING_OPENED                                        => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_USERBOT_CREATED                                      => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_USERBOT_LIST_OPENED                                  => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_NAME_CHANGED                                         => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_ROLE_SETTINGS_OPENED                                 => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_OWNER_ADDED                                          => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_OWNER_DELETED                                        => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_HR_ADDED                                             => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_HR_DELETED                                           => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_ADMIN_ADDED                                          => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_ADMIN_DELETED                                        => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_DEVELOPER_ADDED                                      => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_DEVELOPER_DELETED                                    => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_NOTIFICATIONS_TEXT_HIDING_ENABLED                    => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_NOTIFICATIONS_TEXT_HIDING_DISABLED                   => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_DELETION_STARTED                                     => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_DELETION_CONFIRMED                                   => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_SELF_DISMISSAL_STARTED                               => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_SELF_DISMISSAL_CONFIRMED                             => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_GENERAL_CHAT_NOTIFICATIONS_ENABLED                   => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],
		self::COMPANY_GENERAL_CHAT_NOTIFICATIONS_DISABLED                  => [
			"counter" => null,
			"group"   => Group::COMPANY_MENU,
		],

		// меню профиля
		self::COMPANY_EMPLOYEE_CARD_OPENED                                 => [
			"counter" => null,
			"group"   => Group::PROFILE_MENU,
		],
		self::COMPANY_EMPLOYEE_MY_CARD_OPENED                              => [
			"counter" => null,
			"group"   => Group::PROFILE_MENU,
		],
		self::PIVOT_NOTIFICATIONS_ENABLED                                  => [
			"counter" => null,
			"group"   => Group::PROFILE_MENU,
		],
		self::PIVOT_NOTIFICATIONS_DISABLED                                 => [
			"counter" => null,
			"group"   => Group::PROFILE_MENU,
		],
		self::PIVOT_NOTIFICATION_DISABLED_FOR_GROUPS                       => [
			"counter" => null,
			"group"   => Group::PROFILE_MENU,
		],
		self::PIVOT_NOTIFICATIONS_DISABLE_TIME_ADDED                       => [
			"counter" => null,
			"group"   => Group::PROFILE_MENU,
		],

		// пригласить в компанию
		self::COMPANY_JOIN_LINK_MAIN_OPENED                                => [
			"counter" => null,
			"group"   => Group::COMPANY_INVITE,
		],
		self::COMPANY_JOIN_LINK_LIST_OPENED                                => [
			"counter" => null,
			"group"   => Group::COMPANY_INVITE,
		],
		self::COMPANY_MAIN_JOIN_LINK_GENERATED                             => [
			"counter" => null,
			"group"   => Group::COMPANY_INVITE,
		],
		self::COMPANY_JOIN_LINK_EDITED                                     => [
			"counter" => null,
			"group"   => Group::COMPANY_INVITE,
		],
		self::COMPANY_JOIN_LINK_DELETED                                    => [
			"counter" => null,
			"group"   => Group::COMPANY_INVITE,
		],
		self::COMPANY_JOIN_LINK_GENERATED                                  => [
			"counter" => null,
			"group"   => Group::COMPANY_INVITE,
		],

		// левое меню и чаты
		self::PIVOT_COMPANY_ORDER_CHANGED                                  => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_LEFT_MENU_UNREAD_SWITCHED                            => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_LEFT_MENU_ALL_SWITCHED                               => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_CONVERSATION_SINGLE_CREATED                          => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_CONVERSATION_MARKED_AS_UNREAD                        => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_CONVERSATION_ADDED_TO_FAVORITES                      => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_CONVERSATION_REMOVED_FROM_FAVORITES                  => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_CONVERSATION_NOTIFICATIONS_DISABLED                  => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_CONVERSATION_NOTIFICATIONS_ENABLED                   => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_GROUP_LEFT                                           => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_CONVERSATION_SINGLE_LEFT                             => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_GROUP_GENERAL_OPENED                                 => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_GROUP_HIRING_OPENED                                  => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_GROUP_NOTES_OPENED                                   => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_SINGLE_DEFAULT_OPENED                                => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],
		self::COMPANY_GROUP_DEFAULT_OPENED                                 => [
			"counter" => null,
			"group"   => Group::LEFT_MENU_AND_CONVERSATION,
		],

		// общие действия с чатом
		self::COMPANY_CONVERSATION_HELLO_MESSAGE_ADDED                     => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_TEXT_MESSAGED_ADDED                     => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MENTION_MESSAGED_ADDED                  => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_IMAGE_MESSAGE_ADDED                     => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_VIDEO_MESSAGE_ADDED                     => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_AUDIO_MESSAGE_ADDED                     => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_FILE_MESSAGE_ADDED                      => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_DOCUMENT_MESSAGE_ADDED                  => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_VOICE_MESSAGE_ADDED                     => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_REACTION_ADDED                          => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_REACTION_DELETED                        => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_THREAD_MESSAGED_ADDED                                => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MESSAGE_QUOTED_WITHOUT_COMMENT          => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MESSAGE_QUOTED_WITH_COMMENT             => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MESSAGE_DELETED                         => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MESSAGE_HIDDEN                          => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MESSAGE_REPORTED                        => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MESSAGE_REPOSTED_WITHOUT_COMMENT        => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_MESSAGE_REPOSTED_WITH_COMMENT           => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_HISTORY_CLEARED_MYSELF                  => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_HISTORY_CLEARED                         => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_THREAD_MESSAGE_REPOSTED_WITHOUT_COMMENT => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],
		self::COMPANY_CONVERSATION_THREAD_MESSAGE_REPOSTED_WITH_COMMENT    => [
			"counter" => null,
			"group"   => Group::CONVERSATION_COMMON,
		],

		// настройки профиля
		self::PIVOT_PROFILE_NAME_CHANGED                                   => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_STATUS_SET                                   => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::PIVOT_PROFILE_PHONE_CHANGE                                   => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_BADGE_COLOR_CHANGED                          => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_MY_BADGE_COLOR_CHANGED                       => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_BADGE_DESCRIPTION_CHANGED                    => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_MY_BADGE_DESCRIPTION_CHANGED                 => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_BADGE_DELETED                                => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_MY_BADGE_DELETED                             => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_JOIN_TIME_CHANGED                            => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],
		self::COMPANY_PROFILE_MY_JOIN_TIME_CHANGED                         => [
			"counter" => null,
			"group"   => Group::PROFILE_SETTINGS,
		],

		// группы
		self::COMPANY_GROUP_AVATAR_ADDED                                   => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_AVATAR_DELETED                                 => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_CREATED                                        => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_MEMBERS_INVITED                                => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_EDITED                                         => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_MEMBER_DELETED                                 => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_MEMBER_INVITED                                 => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_INVITE_DECLINED                                => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_LOWERED_TO_MEMBER                              => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_PROMOTED_TO_ADMIN                              => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_SELF_LOWERED_TO_MEMBER                         => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_SELF_PROMOTED_TO_ADMIN                         => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_COPIED                                         => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_SHOW_HISTORY_OPTION_ENABLED                    => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_SHOW_HISTORY_OPTION_DISABLED                   => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_JOIN_NOTIFICATION_OPTION_ENABLED               => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_JOIN_NOTIFICATION_OPTION_DISABLED              => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_EXIT_NOTIFICATION_OPTION_ENABLED               => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],
		self::COMPANY_GROUP_EXIT_NOTIFICATION_OPTION_DISABLED              => [
			"counter" => null,
			"group"   => Group::CONVERSATION_GROUP,
		],

		// все комментарии
		self::COMPANY_THREAD_MESSAGE_DELETED                               => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_MESSAGE_HIDDEN                                => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_MESSAGE_QUOTED_WITHOUT_COMMENT                => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_MESSAGE_QUOTED_WITH_COMMENT                   => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_MESSAGE_REPOSTED_WITHOUT_COMMENT              => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_MESSAGE_REPOSTED_WITH_COMMENT                 => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_ADDED_TO_FAVORITES                            => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_REMOVED_FROM_FAVORITES                        => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_MARK_AS_UNREAD                                => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_UNFOLLOWED                                    => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_FOLLOWED                                      => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_REACTION_ADDED                                => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_REACTION_REMOVED                              => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_TEXT_MESSAGED_ADDED                           => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_MENTION_MESSAGED_ADDED                        => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_IMAGE_MESSAGE_ADDED                           => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_VIDEO_MESSAGE_ADDED                           => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_AUDIO_MESSAGE_ADDED                           => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_FILE_MESSAGE_ADDED                            => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_DOCUMENT_MESSAGE_ADDED                        => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],
		self::COMPANY_THREAD_VOICE_MESSAGE_ADDED                           => [
			"counter" => null,
			"group"   => Group::THREAD_MENU,
		],

		// файлы
		self::COMPANY_CONVERSATION_FILE_LIST_OPENED                        => [
			"counter" => null,
			"group"   => Group::FILE,
		],

		// наймы и увольнения
		self::COMPANY_HIRING_REQUEST_CONFIRMED                             => [
			"counter" => null,
			"group"   => Group::HIRING,
		],
		self::COMPANY_HIRING_REQUEST_DECLINED                              => [
			"counter" => null,
			"group"   => Group::HIRING,
		],
		self::COMPANY_DISMISSAL_REQUEST_DECLINED                           => [
			"counter" => null,
			"group"   => Group::HIRING,
		],

		// чат-бот
		self::COMPANY_USERBOT_EDITED                                       => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_DISABLED                                     => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_ENABLED                                      => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_DELETED                                      => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_SECRET_REFRESHED                             => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_REACT_COMMAND_ENABLED                        => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_REACT_COMMAND_DISABLED                       => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_ADDED_TO_GROUP                               => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_REMOVED_FROM_GROUP                           => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],
		self::COMPANY_USERBOT_COMMAND_SET                                  => [
			"counter" => null,
			"group"   => Group::USERBOT,
		],

		// звонки
		self::COMPANY_CALL_ACCEPTED                                        => [
			"counter" => null,
			"group"   => Group::CALL,
		],
		self::COMPANY_CALL_INIT                                            => [
			"counter" => null,
			"group"   => Group::CALL,
		],
		self::COMPANY_CALL_MEMBER_ADDED                                    => [
			"counter" => null,
			"group"   => Group::CALL,
		],
		self::COMPANY_CALL_MEMBER_KICKED                                   => [
			"counter" => null,
			"group"   => Group::CALL,
		],

	];
}