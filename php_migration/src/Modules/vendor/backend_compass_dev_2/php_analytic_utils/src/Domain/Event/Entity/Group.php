<?php

namespace AnalyticUtils\Domain\Event\Entity;

/**
 * Класс сущности группы пользовательских событий
 */
class Group {

	public const GENERAL                    = 1; // общие
	public const REGISTRATION_AUTHORIZATION = 2; // регистрация и авторизация
	public const COMPANY_MENU               = 3; // меню компании
	public const PROFILE_MENU               = 4; // меню профиля
	public const COMPANY_INVITE             = 5; // пригласить в компанию
	public const LEFT_MENU_AND_CONVERSATION = 6; // левое меню и чаты
	public const CONVERSATION_COMMON        = 7; // общие действия в чате
	public const PROFILE_SETTINGS           = 8; // настройки профиля
	public const CONVERSATION_GROUP         = 9; // группы
	public const THREAD_MENU                = 10; // все комментарии
	public const FILE                       = 11; // файлы
	public const HIRING                     = 12; // наймы и увольнения
	public const USERBOT                    = 13; // чат-бот
	public const CALL                       = 14; // звонки

}