<?php

namespace AnalyticUtils\Domain\Counter\Entity;

/**
 * Класс сущности событий
 */
class Main {

	// тип сущности - в родительском классе это общее событие
	protected const _ENTITY_TYPE = "general";

	public const ACTION_INCREMENT = "inc"; // действие по инкременту счетчика
	public const ACTION_DECREMENT = "dec"; // действие по декременту счетчика
	public const ACTION_SET       = "set"; // действие по установке значения счетчика
}