<?php

namespace Compass\Pivot;

/**
 * Третий этап задачи релокации.
 * Непосредственно переносит все данные и вообще делает всю магию.
 */
class Domain_Company_Action_ServiceTask_RelocateCompanyData implements Domain_Company_Action_ServiceTask_Main {

	/**
	 * Выполняет перенос данных компании.
	 * Генерирует конфиг со статусом переезжает.
	 *
	 * @param Struct_Db_PivotCompany_Company                $company_row
	 * @param Struct_Db_PivotCompanyService_CompanyRegistry $company_registry
	 * @param \BaseFrame\System\Log                         $log
	 * @param array                                         $data
	 *
	 * @return \BaseFrame\System\Log
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company_row, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry, \BaseFrame\System\Log $log, array $data = []):\BaseFrame\System\Log {

		// если вдруг компания сменила домино, то что-то явно пошло не так
		if ($data["source_domino_id"] != $company_row->domino_id) {

			$log->addText("исходное домино компании не совпадает с текущим, ожидаю {$data["source_domino_id"]}, получил {$company_row->domino_id}");

			$message = "company domino has changed since the start of the task — expected {$data["source_domino_id"]}, got {$company_row->domino_id}";
			throw new \BaseFrame\Exception\Domain\ParseFatalException($message);
		}

		// добавляем запись о переезде
		$relocation_id = Gateway_Db_PivotCompanyService_RelocationHistory::insert($company_row->company_id, $data["source_domino_id"], $data["target_domino_id"]);

		try {
			$target_domino = static::_relocate($company_row, $data, $log);
		} catch (\Exception $e) {

			// обновляем запись, указываем, что процесс завершился, но без флага успешности
			Gateway_Db_PivotCompanyService_RelocationHistory::update($relocation_id, [
				"finished_at" => time(),
			]);

			throw $e;
		}

		// обновляем запись, накидываем флажок успешного завершения
		Gateway_Db_PivotCompanyService_RelocationHistory::update($relocation_id, [
			"is_success"  => true,
			"finished_at" => time(),
		]);

		// помечаем в tier_observe, что завершился процесс переезда
		$company_tier = Gateway_Db_PivotCompany_CompanyTierObserve::get($company_row->company_id);
		Domain_Company_Entity_Tier::markRelocatingCompleted($company_tier, $target_domino->tier);

		// сохраним аналитику по концу релокации
		Type_System_Analytic::save($company_row->company_id, $company_row->domino_id, Type_System_Analytic::TYPE_POST_RELOCATION);

		return $log;
	}

	/**
	 * Непосредственно порядок исполнения действий с компанией.
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param array                          $data
	 * @param \BaseFrame\System\Log          $log
	 *
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _relocate(Struct_Db_PivotCompany_Company $company, array $data, \BaseFrame\System\Log $log):Struct_Db_PivotCompanyService_DominoRegistry {

		$source_domino = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company->domino_id);
		$target_domino = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($data["target_domino_id"]);

		// обновляем конфиг для компании
		Domain_Domino_Action_Config_UpdateMysql::do($company, $source_domino);

		try {

			$log->addText("запускаю перенос данных на домино {$data["target_domino_id"]}");
			[$target_service_port, $target_common_port] = Domain_Domino_Action_Relocation_CopyCompanyData::run($source_domino, $target_domino, $company);

			$log->addText("запускаю применение данных на домино {$data["target_domino_id"]}");
			Domain_Domino_Action_Relocation_ApplyCompanyData::run($target_domino, $target_service_port, $target_common_port, $company);

			$log->addText("данные успешно переехали, возвращаю компанию в исходное состояние");
			static::_onSuccess($source_domino, $target_domino, $company, $data, $log);
		} catch (\Exception $e) {

			$log->addText("что-то пошло не так: " . $e->getMessage());
			static::_onFail($source_domino, $company, $data, $log);

			throw $e;
		}

		return $target_domino;
	}

	/**
	 * Пытается вернуть все в исходное состояние.
	 *
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _onFail(Struct_Db_PivotCompanyService_DominoRegistry $source_domino, Struct_Db_PivotCompany_Company $company, array $data, \BaseFrame\System\Log $log):void {

		if ($data["need_to_be_active_after"]) {

			try {

				$log->addText("не получилось перенести компанию, пытаюсь вернуть компанию на исходное домино {$source_domino->domino_id}");
				Domain_Domino_Action_StartCompany::run($source_domino, $company);

				$log->addText("успешно вернул в исходное состояние");
			} catch (\Exception $e) {

				$log->addText("не получилось поднять компанию на исходном домино, перевожу компанию в статус невалидной :(");
				throw $e;
			}
		} else {

			// усиленно делаем вид, что ничего не произошло
			$log->addText("не получилось перенести компанию, перевожу компанию в гибернацию на старом домино {$source_domino->domino_id}");
			Domain_Domino_Action_Config_UpdateMysql::do($company, $source_domino);
		}
	}

	/**
	 * Выполняет перезапуск компании на новом домино.
	 * Если компания перед переездом была активна, то и после она должна быть активна.
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $source_domino
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $target_domino
	 * @param Struct_Db_PivotCompany_Company               $company
	 * @param array                                        $data
	 * @param \BaseFrame\System\Log                        $log
	 *
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _onSuccess(Struct_Db_PivotCompanyService_DominoRegistry $source_domino, Struct_Db_PivotCompanyService_DominoRegistry $target_domino, Struct_Db_PivotCompany_Company $company, array $data, \BaseFrame\System\Log $log):void {

		if ($data["need_to_be_active_after"]) {
			static::_restartOnSuccess($source_domino, $target_domino, $company, $log);
		} else {
			static::_hibernateOnSuccess($source_domino, $target_domino, $company, $log);
		}

		Gateway_Socket_Company::onTechnicalWorksDone($company->company_id, $company->domino_id, Domain_Company_Entity_Company::getPrivateKey($company->extra));

		// отправим ws что компания проснулась
		Gateway_Bus_SenderBalancer::companyRelocated($company->company_id, Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($company->company_id));
	}

	/**
	 * Пробуждает компанию на новом домино после релокации.
	 *
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _restartOnSuccess(Struct_Db_PivotCompanyService_DominoRegistry $source_domino, Struct_Db_PivotCompanyService_DominoRegistry $target_domino, Struct_Db_PivotCompany_Company $company, \BaseFrame\System\Log $log):void {

		try {

			// готовим запись для нового домино
			$company_registry_item = new Struct_Db_PivotCompanyService_CompanyRegistry($company->company_id, 0, 0, 1, time(), 0);

			// удаляем конфиг на исходном домино
			Domain_Domino_Entity_Config::invalidate($company, $source_domino, true);

			$log->addText("пытаюсь запустить компанию на новом домино {$target_domino->domino_id}");
			[$company] = Domain_Domino_Action_StartCompany::run($target_domino, $company);

			$log->addText("успешно запустил компанию на новом домино");
		} catch (\Exception $e) {

			$log->addText("не получилось :(");
			throw $e;
		}

		// удаляем запись для старого домино и вставляем новую
		Gateway_Db_PivotCompanyService_CompanyRegistry::delete($source_domino->domino_id, $company->company_id);
		Gateway_Db_PivotCompanyService_CompanyRegistry::insert($target_domino->domino_id, $company_registry_item);
	}

	/**
	 * Уводит компанию в гибернацию после успешной релокации.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	protected static function _hibernateOnSuccess(Struct_Db_PivotCompanyService_DominoRegistry $source_domino, Struct_Db_PivotCompanyService_DominoRegistry $target_domino, Struct_Db_PivotCompany_Company $company, \BaseFrame\System\Log $log):void {

		try {

			$company->status    = Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED;
			$company->domino_id = $target_domino->domino_id;
			$company->url       = Domain_Domino_Entity_Registry_Main::makeCompanyUrl($company->company_id, $target_domino);

			// удаляем конфиг на исходном домино
			Domain_Domino_Entity_Config::invalidate($company, $source_domino);

			// добавляем запись для нового домино
			$company_registry_item = new Struct_Db_PivotCompanyService_CompanyRegistry($company->company_id, 0, 1, 0, time(), 0);

			$log->addText("перевожу компанию в гибернацию на новом домино {$target_domino->domino_id}");

			// обновляем статус компании
			Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
				"status"    => $company->status,
				"domino_id" => $company->domino_id,
				"url"       => $company->url,
			]);

			Domain_Domino_Action_Config_UpdateMysql::do($company, $target_domino);
		} catch (\Exception $e) {

			$log->addText("не получилось :(");
			throw $e;
		}

		// удаляем запись для старого домино и вставляем новую
		Gateway_Db_PivotCompanyService_CompanyRegistry::delete($source_domino->domino_id, $company->company_id);
		Gateway_Db_PivotCompanyService_CompanyRegistry::insert($target_domino->domino_id, $company_registry_item);
	}
}
