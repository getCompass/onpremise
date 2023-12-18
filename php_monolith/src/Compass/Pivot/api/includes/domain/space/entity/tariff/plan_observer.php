<?php

namespace Compass\Pivot;

/**
 * Класс сущности "обсервер тарифных планов"
 */
class Domain_Space_Entity_Tariff_PlanObserver {

	protected const _COMPANY_GET_LIMIT = 1000;

	protected const _OBSERVE_PERIOD = HOUR1; // обсервер раз в час пробегает по пространствам

	/**
	 * Добавить пространство в обсервер
	 *
	 * @param int $company_id
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function add(int $company_id):void {

		$tariff_plan_observe = new Struct_Db_PivotCompany_TariffPlanObserve(
			$company_id,
			time() + self::_OBSERVE_PERIOD,
			0,
			"",
			time(),
			0
		);

		Gateway_Db_PivotCompany_TariffPlanObserve::insert($tariff_plan_observe);
	}

	/**
	 * Провести обсерв компаний
	 *
	 * @param string $sharding_key
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @long
	 */
	public static function observe(string $sharding_key):void {

		$offset                        = 0;
		$full_tariff_plan_observe_list = [];
		$company_id_list_for_delete    = [];
		$full_company_list             = [];

		// забираем все пространства, для которых пришло время, на обсерв
		do {

			$tariff_plan_observe_list = Gateway_Db_PivotCompany_TariffPlanObserve::getForObserve($sharding_key, time(), self::_COMPANY_GET_LIMIT, $offset);
			$space_id_list            = array_column($tariff_plan_observe_list, "space_id");
			$company_list             = Gateway_Db_PivotCompany_CompanyList::getList($space_id_list);

			foreach ($company_list as $company) {

				// если компания удалена - удаляем из обсервера
				if ($company->is_deleted) {

					$company_id_list_for_delete[] = $company->company_id;
					continue;
				}

				// если компания неактивна - пропускаем
				if ($company->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
					continue;
				}

				$full_tariff_plan_observe_list[$company->company_id] = $tariff_plan_observe_list[$company->company_id];
				$full_company_list[$company->company_id]             = $company;
			}

			// сразу ставим на час вперед проверку
			$set = [
				"observe_at" => time() + self::_OBSERVE_PERIOD,
			];
			Gateway_Db_PivotCompany_TariffPlanObserve::setList($space_id_list, $set);

			// удаляем компании из обсерва, которые уже удалены
			Gateway_Db_PivotCompany_TariffPlanObserve::deleteList($company_id_list_for_delete);
		} while (count($tariff_plan_observe_list) == self::_COMPANY_GET_LIMIT);

		// работаем с каждым обсервером
		foreach ($full_tariff_plan_observe_list as $company_id => $tariff_plan_observe) {
			self::_work($full_company_list[$company_id], $tariff_plan_observe);
		}
	}

	/**
	 * Работаем с тарифом
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _work(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe):void {

		// если report_after > 0 - значит в пространстве есть ошибка, которую еще не исправили инженеры
		if ($tariff_plan_observe->report_after > 0) {
			return;
		}

		// устанавливаем время, после которого нужно отрепортить об ошибке
		$report_after = time() + 10;
		Gateway_Db_PivotCompany_TariffPlanObserve::set($tariff_plan_observe->space_id, [
			"report_after" => $report_after,
		]);

		try {

			// генерим таски для тарифа
			self::_observePlans($company, $tariff_plan_observe);

			// обсерв завершился без ошибок - возвращаем report_after = 0
			Gateway_Db_PivotCompany_TariffPlanObserve::set($tariff_plan_observe->space_id, [
				"report_after" => 0,
			]);
		} catch (\Exception $e) {

			// если поймали хотя бы какой-то экзепшен - сразу пишем об этом лог и пропускаем пространство
			$log_text = (new \BaseFrame\System\Log())->addText(
				"ОШИБКА в пространстве $tariff_plan_observe->space_id" . PHP_EOL .
				$e->getMessage() . PHP_EOL .
				$e->getTraceAsString())
				->close()->text;

			Gateway_Db_PivotCompany_TariffPlanObserve::set($tariff_plan_observe->space_id, [
				"last_error_logs" => $log_text,
			]);
		}
	}

	/**
	 * Генерируем таски для компании, если это нужно
	 *
	 * @param Struct_Db_PivotCompany_Company           $company
	 * @param Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe
	 *
	 * @return void
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Space_Exception_Tariff_DifferenceFound
	 * @throws Domain_Space_Exception_Tariff_IsNotAllowedObserverTask
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \queryException
	 */
	protected static function _observePlans(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe):void {

		$tariff_rows = Gateway_Db_PivotCompany_TariffPlan::getBySpace($company->company_id);
		$tariff      = Domain_SpaceTariff_Tariff::load($tariff_rows);

		self::_observeMemberCount($company, $tariff, $tariff_plan_observe, count($tariff_rows) > 0);
	}

	/**
	 * Добавляем таски, связанные с тарифом мест в пространстве
	 *
	 * @param Struct_Db_PivotCompany_Company           $company
	 * @param Domain_SpaceTariff_Tariff                $tariff
	 * @param Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe
	 * @param bool                                     $db_tariff_exists
	 *
	 * @return void
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Space_Exception_Tariff_DifferenceFound
	 * @throws Domain_Space_Exception_Tariff_IsNotAllowedObserverTask
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 */
	protected static function _observeMemberCount(Struct_Db_PivotCompany_Company           $company,
								    Domain_SpaceTariff_Tariff                $tariff,
								    Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe, bool $db_tariff_exists):void {

		// если пространство уже заблокировано - пропускаем
		if ($tariff->memberCount()->isRestricted(time())) {
			return;
		}

		$space_config  = Domain_Domino_Entity_Config::get($company);
		$config_tariff = $space_config->tariff?->plan_info->member_count;

		// если тариф в конфиге и в базе отличаются кидаем экзепшн
		if (is_null($config_tariff)) {

			if ($db_tariff_exists) {
				throw new Domain_Space_Exception_Tariff_DifferenceFound("difference between db and config found");
			}

			// если нет ни конфига ни базы - завершаем выполнение, значит бесплатный тариф с доступным триалом (хотя как он попал в обсервер тогда?)
			Domain_SpaceTariff_Entity_Alert::send(
				":exclamation: Пространство $company->company_id с бесплатным тарифом и доступным триалом попало в обсервер");
			return;
		}

		$config_tariff =
			\Tariff\Plan\MemberCount\Default\Plan::fromData($config_tariff->active_till, $config_tariff->free_active_till, $config_tariff->option_list);

		$config_tariff = toJson($config_tariff->getData());
		$db_tariff     = toJson($tariff->memberCount()->getData());

		if ($config_tariff !== $db_tariff) {
			throw new Domain_Space_Exception_Tariff_DifferenceFound("difference between db and config found");
		}

		self::_makeNotifyTasks($tariff, $tariff_plan_observe);
	}

	/**
	 * Создаем таски для оповещения по оплате
	 *
	 * @param Domain_SpaceTariff_Tariff                $tariff
	 * @param Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe
	 *
	 * @return void
	 * @throws Domain_Space_Exception_Tariff_IsNotAllowedObserverTask
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 * @long
	 */
	protected static function _makeNotifyTasks(Domain_SpaceTariff_Tariff $tariff, Struct_Db_PivotCompany_TariffPlanObserve $tariff_plan_observe):void {

		$current_time  = time();
		$tariff_config = getConfig("TARIFF")["member_count"];

		$payment_notify_time     = $tariff->memberCount()->getActiveTill() - $tariff_config["payment_period"];
		$need_payment_notify     = !$tariff->memberCount()->isFree(time()) || $tariff->memberCount()->isTrial(time());
		$need_postpayment_notify = !$tariff->memberCount()->isActive(time()) && $tariff->memberCount()->getActiveTill() < time();

		// если хотя бы какой-то анонс пора отправлять, проверяем, не установлены ли они уже
		if ($need_payment_notify && $payment_notify_time < $current_time || $need_postpayment_notify) {

			// если пришло время публиковать какой-то анонс, проверяем, что он еще не опубликован
			$existing_type_list = Gateway_Announcement_Main::getExistingTypeList($tariff_plan_observe->space_id, [
				\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRED,
				\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRATION,
			]);

			$existing_type_list = array_flip($existing_type_list);
		}

		// если пришло время оплаты и анонса не существует
		if ($need_postpayment_notify) {

			if (!isset($existing_type_list[\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRED])) {

				Domain_Space_Entity_Tariff_PlanTask::schedule(
					Domain_Space_Entity_Tariff_PlanTask::TASK_TYPE_POSTPAYMENT_NOTIFY, time(), $tariff_plan_observe->space_id);

				// создаём задачу для оповещения блокировки
				Domain_Space_Entity_Tariff_PlanTask::schedule(
					Domain_Space_Entity_Tariff_PlanTask::TASK_TYPE_BLOCK_NOTIFY, $tariff->memberCount()->getRestrictedAccessFrom(), $tariff_plan_observe->space_id);
			}

			return;
		}

		// если пришло время постоплаты и анонса не существует
		if ($need_payment_notify && $payment_notify_time < $current_time
			&& !isset($existing_type_list[\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRATION])) {

			// отправляем задачу для оповещения постоплаты
			Domain_Space_Entity_Tariff_PlanTask::schedule(
				Domain_Space_Entity_Tariff_PlanTask::TASK_TYPE_PAYMENT_NOTIFY, time(), $tariff_plan_observe->space_id);
		}
	}
}