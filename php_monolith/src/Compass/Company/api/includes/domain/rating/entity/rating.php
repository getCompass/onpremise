<?php

namespace Compass\Company;

/**
 * Методы для работы с рейтингом
 */
class Domain_Rating_Entity_Rating {

	// типы ивентов, участвующие в рейтинге
	public const CONVERSATION_MESSAGE = "conversation_message";
	public const THREAD_MESSAGE       = "thread_message";
	public const REACTION             = "reaction";
	public const FILE                 = "file";
	public const CALL                 = "call";
	public const VOICE                = "voice";
	public const RESPECT              = "respect";
	public const EXACTINGNESS         = "exactingness";
	public const GENERAL              = "general";

	// дублируется в модуле php_conversation и микросервисе go_rating
	public const ALLOW_EVENTS = [
		self::CONVERSATION_MESSAGE,
		self::THREAD_MESSAGE,
		self::REACTION,
		self::FILE,
		self::CALL,
		self::VOICE,
		self::RESPECT,
		self::EXACTINGNESS,
		self::GENERAL,
	];

	public const PERIOD_WEEK_TYPE  = 1; // тип периода рейтинга (неделя)
	public const PERIOD_MONTH_TYPE = 2; // тип периода рейтинга (месяц)

	// разрешенные типы периода для рейтинга
	public const ALLOW_PERIOD_TYPES = [
		self::PERIOD_WEEK_TYPE,
		self::PERIOD_MONTH_TYPE,
	];

	// разрешенные для декремента ивенты
	protected const _ALLOW_DECREMENT_EVENTS = [
		self::RESPECT,
		self::EXACTINGNESS,
	];

	protected const _ALLOW_DECREMENT_TIME_LIMIT = 30 * DAY1; // лимит времени, после истечения которого рейтинг не декрементим

	public const MIN_RATING_EVENT_COUNT = 1000; // минимальное количество действий активности для отправки сообщения активности в компании

	/**
	 * получаем список типов, разрешенных для декременте
	 */
	public static function getAllowEventsForDecrement():array {

		return Domain_Rating_Entity_Rating::_ALLOW_DECREMENT_EVENTS;
	}

	/**
	 * получаем имит времени, после истечения которого рейтинг не декрементим
	 */
	public static function getAllowDecrementTimeLimit():int {

		return Domain_Rating_Entity_Rating::_ALLOW_DECREMENT_TIME_LIMIT;
	}
}
