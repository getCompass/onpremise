<?php

namespace Compass\Premise;

/**
 * Класс для сущности команд в premise
 */
class Domain_Premise_Entity_Space {

	public const NOT_EXIST_SPACE_STATUS     = 0; // не присоединён ни к одной команде
	public const UNIQUE_MEMBER_SPACE_STATUS = 1; // уникальный участник
	public const UNIQUE_GUEST_SPACE_STATUS  = 2; // уникальный гость
	public const UNIQUE_BOT_SPACE_STATUS    = 3; // уникальный бот
}