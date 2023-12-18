<?php

namespace Compass\Company;

// абстрактный класс для работы с задачами User_Observer
abstract class Type_User_Observer_Default {

	##########################################################
	# region PUBLIC METHODS
	##########################################################

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	abstract public static function provideJobExtra(int $user_id, int $job_type):array;

	/**
	 * Генерирует задачи, которые нужно взять на исполнение
	 *
	 * @param array $extra экстра для генерации таска
	 */
	abstract public static function provideJobList(array $observer_data, array $extra):array;

	/**
	 * Исполняет задачу
	 */
	abstract public static function doJob(array $job):int;

	/**
	 * Получаем время следующего выполнения
	 */
	abstract public static function getNextWorkTime(array $job_extra):int;

	# endregion
	##########################################################
}