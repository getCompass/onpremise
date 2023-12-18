<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * абстрактный класс для работы с задачами мониторинга
 */
abstract class Domain_Call_Entity_MonitoringQueueDefault {

	/**
	 * Получает все записи
	 */
	abstract public static function getAll():array;

	/**
	 * Удаляет очереди для пользователей и определенного звонка
	 */
	abstract public static function deleteForUsers(array $user_id_list, string $call_map):void;

	/**
	 * Получаем пользователей кому нужно завершить звонок
	 */
	abstract public static function getUsersWhoNeedFinishCall(array $user_id_list, array $call_map_list):array;

}