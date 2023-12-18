<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс, который исполняет задачи по периодической рассылке данных для компаний.
 * Рассылает статистику и данные о рейтинге отработанного времени.
 */
class Type_Company_Job_Main {

	/**
	 * Метод генерации и исполнения задач.
	 */
	public static function work():int {

		$config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME);

		// если у компании пока нет имени, значит пока без владельца, просто пропускаем
		// попробуем еще раз примерно через час
		if (!isset($config["value"])) {
			return time() + 3600 + random_int(0, 100);
		}

		// время, когда задачу нужно будет взять в работу в следующий раз
		$next_iteration_at_list = [];

		/** @var Type_Company_Job_Default $provider */
		foreach (self::_getJobProviderList() as $provider => $extra) {

			if (isset($extra["next_time"])) {
				$next_iteration_at_list[] = $extra["next_time"];
			}

			/** @var string $provider_class class name */
			$provider_class = $provider;

			// получаем экстру и генерируем задачи
			// технически их можно унести в базу, эту функцию переделать в продюьсер, а консьюм сделать в отдельном методе
			self::_processJobList($provider_class, $provider::provideJobList($extra));
		}

		// высчитываем следующую итерацию
		$next_iteration = min($next_iteration_at_list);

		// возвращаем время следующего тика + рандом, чтобы не ломились в одну секунду все компании сразу
		return $next_iteration + rand(0, 10);
	}

	# region protected

	/**
	 * Вызывает исполнение задач.
	 */
	protected static function _processJobList(string $provider, array $job_list):void {

		/** @var Type_Company_Job_Default $provider class name */

		foreach ($job_list as $job_key => $job) {
			$provider::doWork($job_key, $job);
		}
	}

	/**
	 * Возвращает список обработчиков, которые должны быть вызваны в этом кроне.
	 *
	 * @return array[]
	 */
	protected static function _getJobProviderList():array {

		return [
			Type_Company_Job_WorksheetWeek::class => Type_Company_Job_WorksheetWeek::provideJobExtra(),
			Type_Company_Job_RatingWeek::class    => Type_Company_Job_RatingWeek::provideJobExtra(),
			Type_Company_Job_RatingMonth::class   => Type_Company_Job_RatingMonth::provideJobExtra(),
		];
	}

	# endregion protected
}