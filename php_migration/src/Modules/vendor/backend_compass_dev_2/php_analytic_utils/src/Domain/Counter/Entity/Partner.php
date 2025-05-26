<?php

namespace AnalyticUtils\Domain\Counter\Entity;

/**
 * Класс сущности партнерских счетчиков
 */
class Partner extends Main {

	// тип сущности - в родительском классе это общее событие
	protected const _ENTITY_TYPE = "partner";

	public const PIVOT_REGISTERED                    = "row0"; // пользователь зарегистрировался в приложении
	public const COMPASS_COMMON_ACTION_DONE          = "row1"; // выполнено общее действие в приложении
	public const PIVOT_APP_RETURN                    = "row2"; // возврат пользователя в приложение
	public const PIVOT_INCOME                        = "row3"; // полученная выручка
	public const INVITE_LINK_UNIQUE_VISITORS         = "row4"; // количество уникальных посетителей для пригласительной ссылки
	public const PIVOT_REGISTERED_OWNER              = "row5"; // пользователь-собственник зарегистрировался в приложении
	public const PIVOT_REGISTERED_STAFF              = "row6"; // пользователь-сотрудник зарегистрировался в приложении
}