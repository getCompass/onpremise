<?php

namespace Compass\Company;

/**
 * Класс для работы с аналитикой пользователя
 */
class Type_User_ActionAnalytics {

	protected const _EVENT_KEY = "user_action";

	public const ADD_CONVERSATION_MESSAGE = 1; // Отправил сообщение
	public const ADD_THREAD_MESSAGE       = 2; // Отправил комментарий
	public const ADD_VOICE                = 3; // Отправил голосовое сообщение
	public const ADD_REACTION             = 4; // Поставил реакцию
	public const ADD_CALL                 = 5; // Совершил звонок
	public const ADD_REMIND               = 6; // Установил напоминание
	public const ADD_FILE                 = 7; // Загрузил файл на сервер (любое фото/видео/документ)
	public const ADD_GROUP                = 8; // Создал группу
	public const ADD_SPACE                = 9; // Создал пространство
	public const ADD_JOIN_LINK            = 10; // Создал ссылку-приглашение
	public const DISMISSED_MEMBER         = 11; // Удалил пользователя из пространства (нажал уволить)
	public const DELETE_SPACE             = 12; // Удалил пространство
	public const DELETE_ACCOUNT           = 13; // Удалил аккаунт
	public const START_ANDROID_SESSION    = 14; // Начал сессию с Andorid
	public const START_IOS_SESSION        = 15; // Начал сессию с iOS
	public const START_ELECTRON           = 16; // Начал сессию с Electron
	public const END_ANDROID_SESSION      = 17; // Завершил сессию с Android
	public const END_IOS_SESSION          = 18; // Завершил сессию с iOS
	public const END_ELECTRON_SESSION     = 19; // Завершил сессию с Electron
	public const WRITE_TO_SUPPORT         = 20; // Написал в поддержку (после реализации intercom)
	public const ASSESSED_SUPPORT         = 21; // Поставил оценку поддержке (после реализации intercom)
	public const JOIN_GROUP               = 22; // Вступил в группу
	public const ADD_SINGLE               = 23; // Создал сингл-диалог

	/**
	 * Пишем аналитику по действиям пользователя
	 */
	public static function send(int $user_id, int $action):void {

		if (isTestServer() && !isBackendTest() && !isLocalServer()) {
			return;
		}

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"user_id"    => $user_id,
			"created_at" => time(),
			"action"     => $action,
		]);
	}
}