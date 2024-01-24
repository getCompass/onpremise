<?php

namespace Compass\Pivot;

/**
 * Класс описывающий socket-контроллер для работы партнерского ядра с пивотом
 */
class Socket_Partner extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getUserAvatarFileLink",
		"getUserCompanyList",
		"getLastRegistrations",
		"getCompanyInfo",
		"uploadInvoice",
		"onInvoiceCreated",
		"onInvoicePayed",
		"onInvoiceCanceled",
		"getFileByKeyList",
		"getUserInfoByPhoneNumber",
		"getUserInfo",
		"getUserInfoList",
		"getOwnerSpaceList",
		"getMemberSpaceList",
		"checkCanAttachSpace",
		"getSpaceInfo",
		"sendToGroupSupport",
		"createSupportCompanyJoinLink",
		"setSpaceAttached",
		"setSpaceDetached",
	];

	##########################################################
	# region
	##########################################################

	/**
	 * Получаем ссылку на аватар пользователя
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function getUserAvatarFileLink():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		// получаем ссылку
		$avatar_link_list = Domain_Partner_Scenario_Socket::getUserAvatarFileLink($user_id_list);

		return $this->ok([
			"avatar_link_list" => (array) $avatar_link_list,
		]);
	}

	/**
	 * Получаем список компаний пользователей
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function getUserCompanyList():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		//
		$user_company_list = Domain_Partner_Scenario_Socket::getUserCompanyList($user_id_list);

		return $this->ok([
			"user_company_list" => (array) $user_company_list,
		]);
	}

	/**
	 * Получаем данные по компании
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 */
	public function getCompanyInfo():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		$company = Domain_Partner_Scenario_Socket::getCompanyInfo($company_id);

		return $this->ok([
			"company" => (object) $company,
		]);
	}

	/**
	 * Возвращает данные по последним регистрациям для указанного партнера
	 */
	public function getLastRegistrations():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		// получаем данные регистраций прямо из базы
		$last_registration_list = count($user_id_list) === 0
			? []
			: Gateway_Db_PivotData_LastRegisteredUser::getList($user_id_list);

		return $this->ok([
			"last_registration_list" => (array) Socket_Format::lastRegistrationList($last_registration_list),
		]);
	}

	/**
	 * Грузим счет
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function uploadInvoice():array {

		if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != UPLOAD_ERR_OK) {
			return $this->error(704, "File was not uploaded");
		}

		$file_key = Domain_Partner_Scenario_Socket::uploadInvoice($_FILES["file"]["tmp_name"], $_FILES["file"]["type"], $_FILES["file"]["name"]);

		return $this->ok([
			"file_key" => (string) $file_key,
		]);
	}

	/**
	 * Был создан счет на оплату
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function onInvoiceCreated():array {

		$created_by_user_id = $this->post(\Formatter::TYPE_INT, "created_by_user_id");
		$company_id         = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			Domain_Partner_Scenario_Socket::onInvoiceCreated($company_id, $created_by_user_id);
		} catch (cs_CompanyIncorrectCompanyId | Domain_User_Exception_IncorrectUserId) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		} catch (cs_CompanyNotExist | cs_CompanyIsHibernate) {
			// ничего не делаем
		}

		return $this->ok();
	}

	/**
	 * Был оплачен счет
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function onInvoicePayed():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			Domain_Partner_Scenario_Socket::onInvoicePayed($company_id);
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		} catch (cs_CompanyNotExist | cs_CompanyIsHibernate) {
			// ничего не делаем
		}

		return $this->ok();
	}

	/**
	 * Был отменен счет
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function onInvoiceCanceled():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");
		$invoice_id = $this->post(\Formatter::TYPE_INT, "invoice_id");

		try {
			Domain_Partner_Scenario_Socket::onInvoiceCanceled($company_id, $invoice_id);
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		} catch (cs_CompanyNotExist | cs_CompanyIsHibernate) {
			// ничего не делаем
		}

		return $this->ok();
	}

	/**
	 * Получаем массив файлов по массиву ключей
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \paramException
	 */
	public function getFileByKeyList():array {

		$file_key_list = $this->post(\Formatter::TYPE_ARRAY, "file_key_list");

		$file_list = Domain_Partner_Scenario_Socket::getFileByKeyList($file_key_list);

		return $this->ok([
			"file_list" => (array) $file_list,
		]);
	}

	# endregion
	##########################################################

	##########################################################
	# region web партнерка
	##########################################################

	/**
	 * Получаем информацию по пользователю по phone_number
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public function getUserInfoByPhoneNumber():array {

		$phone_number = $this->post(\Formatter::TYPE_STRING, "phone_number");

		// получаем user_id по номеру телефона
		try {
			$user_id = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
		} catch (cs_PhoneNumberNotFound) {
			return $this->error(1405001, "user not found");
		}

		return $this->_getUserInfo($user_id);
	}

	/**
	 * Получаем информацию по пользователю по user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public function getUserInfo():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		return $this->_getUserInfo($user_id);
	}

	/**
	 * Получаем информацию по списку пользователей
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public function getUserInfoList():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		try {
			$user_info_list = Domain_Partner_Scenario_Socket::getUserInfoList($user_id_list);
		} catch (cs_UserNotFound) {
			return $this->error(1405001, "user not found in list");
		}

		return $this->ok([
			"user_info_list" => (array) $user_info_list,
		]);
	}

	/**
	 * Получаем пространства пользователя
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function getOwnerSpaceList():array {

		$user_id               = $this->post(\Formatter::TYPE_INT, "user_id");
		$exclude_space_id_list = $this->post(\Formatter::TYPE_ARRAY, "exclude_space_id_list", []);

		try {
			Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("user not found");
		}

		// получаем пространства
		[$space_list, $_] = Domain_User_Action_GetOrderedCompanyList::do($user_id, 0, 100, 1);
		$space_id_list = array_column($space_list, "company_id");
		$space_id_list = array_diff($space_id_list, $exclude_space_id_list);
		$space_list    = Gateway_Db_PivotCompany_CompanyList::getList($space_id_list);

		$output = [];
		foreach ($space_list as $space) {

			// скипаем неактивные
			if ($space->status != Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
				continue;
			}

			// проверяем можем ли привязать компанию
			$private_key         = Domain_Company_Entity_Company::getPrivateKey($space->extra);
			$is_can_attach_space = Gateway_Socket_Company::checkCanAttachSpace($user_id, $space->company_id, $space->domino_id, $private_key);
			if (!$is_can_attach_space) {
				continue;
			}

			$tariff   = Domain_SpaceTariff_Repository_Tariff::get($space->company_id);
			$output[] = Socket_Format::spaceInfo($space, $tariff);
		}

		return $this->ok([
			"list" => (array) $output,
		]);
	}

	/**
	 * Получаем пространства пользователя, где он участник
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public function getMemberSpaceList():array {

		$user_id               = $this->post(\Formatter::TYPE_INT, "user_id");
		$exclude_space_id_list = $this->post(\Formatter::TYPE_ARRAY, "exclude_space_id_list", []);

		try {
			Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("user not found");
		}

		// получаем пространства
		[$space_list, $_] = Domain_User_Action_GetOrderedCompanyList::do($user_id, 0, 100, 1);
		$space_id_list = array_column($space_list, "company_id");
		$space_id_list = array_diff($space_id_list, $exclude_space_id_list);
		$space_list    = Gateway_Db_PivotCompany_CompanyList::getList($space_id_list);

		$output = [];
		foreach ($space_list as $space) {

			// скипаем неактивные
			if ($space->status != Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
				continue;
			}

			$tariff   = Domain_SpaceTariff_Repository_Tariff::get($space->company_id);
			$output[] = Socket_Format::spaceInfo($space, $tariff);
		}

		return $this->ok([
			"list" => (array) $output,
		]);
	}

	/**
	 * Проверяем может ли закреплять пространство
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function checkCanAttachSpace():array {

		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		try {
			Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("user not found");
		}

		$is_can_attach_space = false;

		try {
			$space_info = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (\cs_RowIsEmpty) {
			throw new \BaseFrame\Exception\Request\ParamException("space not found");
		}

		// проверяем только активные компании
		if ($space_info->status === Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {

			// проверяем может ли привязать компанию
			$private_key         = Domain_Company_Entity_Company::getPrivateKey($space_info->extra);
			$is_can_attach_space = Gateway_Socket_Company::checkCanAttachSpace($user_id, $space_info->company_id, $space_info->domino_id, $private_key);
		}

		return $this->ok([
			"can_attach_space" => (int) $is_can_attach_space,
		]);
	}

	/**
	 * Получаем инфу о пространстве
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public function getSpaceInfo():array {

		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		// получаем пространство
		try {
			$space_info = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1405004, "space not exist");
		}

		// получаем тариф
		$tariff = Domain_SpaceTariff_Repository_Tariff::get($space_id);

		return $this->ok([
			"space_info" => (object) Socket_Format::spaceInfo($space_info, $tariff),
		]);
	}

	/**
	 * Отправляем код авторизации в чат службы поддержки
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function sendToGroupSupport():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$text    = $this->post(\Formatter::TYPE_STRING, "text");

		//
		if (mb_strlen($text) < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		try {
			Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		[$space_list, $_] = Domain_User_Action_GetOrderedCompanyList::do($user_id, 0, 50, 1);
		$company_id = 0;
		foreach ($space_list as $item) {

			if ($item->status == Struct_User_Company::ACTIVE_STATUS) {

				$company_id = $item->company_id;
				break;
			}
		}

		if ($company_id < 1) {
			return $this->error(1405005, "user not have active companies");
		}

		$space = Domain_Company_Entity_Company::get($company_id);
		Gateway_Socket_Company::addMessageFromSupportBot($user_id, $text,
			$space->company_id, $space->domino_id, Domain_Company_Entity_Company::getPrivateKey($space->extra));

		return $this->ok();
	}

	/**
	 * Создаем ссылку приглашение в компанию поддержки партнеров
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function createSupportCompanyJoinLink():array {

		$company_id      = $this->post(\Formatter::TYPE_INT, "company_id");
		$creator_user_id = $this->post(\Formatter::TYPE_INT, "creator_user_id");

		// если значения пустые
		if ($company_id < 1 || $creator_user_id < 1) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("empty support partner company");
		}

		// получаем данные по компании, чтобы совершить в нее запрос
		try {
			$company_info = Domain_Partner_Scenario_Socket::getCompanyInfo($company_id);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1405004, "space not exist");
		}

		// идем в компанию создавать ссылку с заданными параметрами
		try {

			$join_link = Gateway_Socket_Company::createJoinLink(
				$creator_user_id,
				2, // переехало со старой партнерки
				10000, // переехало со старой партнерки
				$company_info["company_id"],
				$company_info["domino_id"],
				$company_info["private_key"]);
		} catch (Domain_Link_Exception_DontHavePermissions) {
			return $this->error(1405002, "user don't have permissions");
		} catch (Domain_Link_Exception_TooManyActiveInvites) {
			return $this->error(1405003, "user have too many active invites");
		} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIncorrectCompanyId|cs_CompanyIsHibernate) {
			return $this->error(1405004, "space not exist");
		}

		return $this->ok([
			"join_link" => (string) $join_link["link"],
		]);
	}

	/**
	 * Привязываем пространство
	 * @return array
	 * @throws Domain_SpaceTariff_Exception_AlterationUnsuccessful
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function setSpaceAttached():array {

		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		try {
			Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		try {
			$space = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (\cs_RowIsEmpty) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		// проверяем можем ли привязывать пространство
		$is_can_attach_space = Gateway_Socket_Company::checkCanAttachSpace($user_id,
			$space->company_id, $space->domino_id, Domain_Company_Entity_Company::getPrivateKey($space->extra));
		if (!$is_can_attach_space) {
			return $this->error(1405006, "not enough rights");
		}

		$tariff_rows = Gateway_Db_PivotCompany_TariffPlan::getBySpace($space_id);
		$tariff      = Domain_SpaceTariff_Tariff::load($tariff_rows);

		$active_till = time() + DAY1 * 365;
		if (count($tariff_rows) > 0 && $tariff->memberCount()->getActiveTill() > time()) {
			$active_till = $tariff->memberCount()->getActiveTill() + DAY1 * 365;
		}

		// расширяем до 500 людей и на 1 год
		try {

			$alteration = \Tariff\Plan\MemberCount\Alteration::make()
				->setExtendPolicy(\Tariff\Plan\MemberCount\OptionExtendPolicy::NEVER, $active_till)
				->setProlongation(\Tariff\Plan\BaseAlteration::PROLONGATION_RULE_SET, $active_till)
				->setActions(\Tariff\Plan\BaseAlteration::PROLONG, \Tariff\Plan\BaseAlteration::CHANGE)
				->setMemberCount(500)
				->setAvailability(new \Tariff\Plan\AlterationAvailability(\Tariff\Plan\AlterationAvailability::AVAILABLE_DETACHED));
			Domain_SpaceTariff_Action_AlterMemberCount::run($user_id, $space_id, \Tariff\Plan\BaseAction::METHOD_FORCE, $alteration);
		} catch (Domain_SpaceTariff_Exception_TimeLimitReached) {
		}

		// получаем актуальные данные
		$space  = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		$tariff = Domain_SpaceTariff_Repository_Tariff::get($space_id);

		return $this->ok([
			"space_info" => (object) Socket_Format::spaceInfo($space, $tariff),
		]);
	}

	/**
	 * Отвязываем пространство
	 * @return array
	 */
	public function setSpaceDetached():array {

		return $this->ok();
	}

	# endregion
	##########################################################

	##########################################################
	# region PROTECTED
	##########################################################

	/**
	 * Получаем информацию о пользователе
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	protected function _getUserInfo(int $user_id):array {

		try {
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			return $this->error(1405001, "user not found");
		}

		$avatar_url = "";
		if (mb_strlen($user_info->avatar_file_map) > 0) {

			$file_list  = Gateway_Socket_PivotFileBalancer::getFileList([$user_info->avatar_file_map]);
			$avatar_url = $file_list[0]["url"] ?? "";
		}

		return $this->ok([
			"user_info" => (object) Socket_Format::userInfo($user_info, $avatar_url),
		]);
	}

	# endregion
	##########################################################
}