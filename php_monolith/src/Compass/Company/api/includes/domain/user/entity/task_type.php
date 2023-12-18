<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с типами задач для крона
 */
class Domain_User_Entity_TaskType {

	public const TYPE_EXIT = "exit"; // тип задачи увольнение

	public const TYPE_INT_TO_STRING = [
		1 => self::TYPE_EXIT,
	];
}
