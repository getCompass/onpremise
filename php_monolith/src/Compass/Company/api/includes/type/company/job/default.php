<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Дефолтный класс для задач крона сендера для компаний.
 */
abstract class Type_Company_Job_Default {

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	abstract public static function provideJobExtra():array;

	/**
	 * Генерирует задачи, которые нужно взять на исполнение.
	 *
	 * @param array $extra экстра для генерации таска
	 */
	abstract public static function provideJobList(array $extra):array;

	/**
	 * Исполняет задачу.
	 */
	abstract public static function doWork(string $task_key, array $job):void;
}
