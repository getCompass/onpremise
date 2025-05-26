<?php

namespace AnalyticUtils\Domain\Counter\Entity;

/**
 * Класс сущности пользовательских счетчиков
 */
class User extends Main {

	// тип сущности - в родительском классе это общее событие
	protected const _ENTITY_TYPE = "user";

	public const PIVOT_APP_RETURN              = "row0"; // возврат пользователя в приложение
	public const PIVOT_USER_INVITED            = "row1"; // пригласил пользователя
	public const COMPANY_CREATED               = "row2"; // создал компанию
	public const PIVOT_TOTAL_ONLINE_TIME       = "row3"; // зафиксировали онлайн пользователя
	public const PIVOT_TOTAL_ACTIONS_COMPLETED = "row4"; // общее количество выполненных действий

}