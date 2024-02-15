<?php

namespace Compass\Pivot;

/**
 * Класс описывающий socket-контроллер для работы партнерского ядра с пивотом
 */
class Socket_Partner extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getUserAvatarFileLink",
		"uploadInvoice",
		"onInvoiceCreated",
		"onInvoicePayed",
		"onInvoiceCanceled",
		"getFileByKeyList",
		"sendToGroupSupport",
		"sendSms",
		"resendSms",
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
		} catch (cs_CompanyIncorrectCompanyId|Domain_User_Exception_IncorrectUserId) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		} catch (cs_CompanyNotExist|cs_CompanyIsHibernate) {
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
		} catch (cs_CompanyNotExist|cs_CompanyIsHibernate) {
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
		} catch (cs_CompanyNotExist|cs_CompanyIsHibernate) {
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
	 * Отправляем смс
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \queryException
	 */
	public function sendSms():array {

		$phone_number = $this->post(\Formatter::TYPE_STRING, "phone_number");
		$text         = $this->post(\Formatter::TYPE_STRING, "text");
		$story_id     = $this->post(\Formatter::TYPE_STRING, "story_id");

		$sms_id = Type_Sms_Queue::send($phone_number, $text, Type_Sms_Analytics_Story::STORY_TYPE_OTHER_PRODUCT, $story_id);

		return $this->ok([
			"sms_id" => (string) $sms_id,
		]);
	}

	/**
	 * Переотправляем смс
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \queryException
	 */
	public function resendSms():array {

		$phone_number = $this->post(\Formatter::TYPE_STRING, "phone_number");
		$text         = $this->post(\Formatter::TYPE_STRING, "text");
		$sms_id       = $this->post(\Formatter::TYPE_STRING, "sms_id");
		$story_id     = $this->post(\Formatter::TYPE_STRING, "story_id");

		$sms_id = Type_Sms_Queue::resend($phone_number, $text, $sms_id, Type_Sms_Analytics_Story::STORY_TYPE_OTHER_PRODUCT, $story_id);

		return $this->ok([
			"sms_id" => (string) $sms_id,
		]);
	}
}