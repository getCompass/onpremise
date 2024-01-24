<?php

namespace Compass\Pivot;

/**
 * контроллер для работы с хабом ботов
 */
class Socket_Bothub extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getUserInfoByUserId",
		"getUserInfoByPhoneNumber",
		"getUserSpaceList",
		"getSpaceInfo",
		"getSpaceMemberList",
		"getBiggestUserSpace",
		"getUserSupportConversationKey",
		"getEventCountInfo",
	];

	/**
	 * Получаем информацию по пользователю по user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getUserInfoByUserId():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// получаем информацию о пользователе
		try {
			$user_info = Domain_Bothub_Scenario_Socket::getUserInfoByUserId($user_id);
		} catch (cs_UserNotFound|cs_UserPhoneSecurityNotFound) {
			return $this->error(1315001, "user not found");
		}

		return $this->ok([
			"user" => (object) $user_info,
		]);
	}

	/**
	 * Получаем информацию по пользователю по номеру телефона
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getUserInfoByPhoneNumber():array {

		$phone_number = $this->post(\Formatter::TYPE_STRING, "phone_number");

		// получаем информацию о пользователе
		try {
			$user_info = Domain_Bothub_Scenario_Socket::getUserInfoByPhoneNumber($phone_number);
		} catch (cs_PhoneNumberNotFound|cs_UserNotFound) {
			return $this->error(1315001, "user not found");
		}

		return $this->ok([
			"user" => (object) $user_info,
		]);
	}

	/**
	 * Получаем список пространств пользователя
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 */
	public function getUserSpaceList():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {

			// получаем пространства пользователя
			$user_space_list = Domain_Bothub_Scenario_Socket::getUserSpaceList($user_id);
		} catch (cs_UserNotFound) {
			return $this->error(1315001, "user not found");
		}

		return $this->ok([
			"space_list" => (array) $user_space_list,
		]);
	}

	/**
	 * Получаем информацию по пространству
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public function getSpaceInfo():array {

		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		try {

			// получаем информацию о пространстве
			[$space_info, $space_user_id_list] = Domain_Bothub_Scenario_Socket::getSpaceInfo($space_id);
		} catch (cs_CompanyNotExist) {
			return $this->error(1315002, "space not found");
		}

		return $this->ok([
			"space"              => (object) $space_info,
			"space_user_id_list" => (array) $space_user_id_list,
		]);
	}

	/**
	 * Получаем список участников пространства
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 */
	public function getSpaceMemberList():array {

		$space_id           = $this->post(\Formatter::TYPE_INT, "space_id");
		$space_user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "space_user_id_list");

		try {

			// получаем список участников пространства
			$member_list = Domain_Bothub_Scenario_Socket::getSpaceMemberList($space_id, $space_user_id_list);
		} catch (cs_CompanyNotExist) {
			return $this->error(1315002, "space not found");
		}

		return $this->ok([
			"member_list" => (array) $member_list,
		]);
	}

	/**
	 * Получаем самое большое пространство пользователя (по количеству участников)
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public function getBiggestUserSpace():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {

			// получаем пространство
			$space_info = Domain_Bothub_Scenario_Socket::getBiggestUserSpace($user_id);
		} catch (cs_UserNotFound) {
			return $this->error(1315001, "user not found");
		} catch (cs_UserNotInCompany) {
			return $this->error(1315003, "space list is empty");
		}

		return $this->ok([
			"space" => (object) $space_info,
		]);
	}

	/**
	 * Получаем ключ диалога для чата службы поддержки
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	public function getUserSupportConversationKey():array {

		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		try {

			// получаем ключ диалога службы поддержки
			$support_conversation_key = Domain_Bothub_Scenario_Socket::getUserSupportConversationKey($user_id, $space_id);
		} catch (cs_UserNotFound) {
			return $this->error(1315001, "user not found");
		} catch (cs_CompanyNotExist) {
			return $this->error(1315002, "space not found");
		} catch (cs_UserNotInCompany) {
			return $this->error(1315004, "user is not space member");
		} catch (cs_CompanyIsNotActive) {
			return $this->error(1315005, "space is not active");
		}

		return $this->ok([
			"support_conversation_key" => (string) $support_conversation_key,
		]);
	}

	/**
	 * Получаем список действий в пространстве
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_CompanyIsNotActive
	 * @throws \cs_SocketRequestIsFailed
	 */
	public function getEventCountInfo():array {

		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		try {

			// получаем информацию о действиях в пространстве
			$event_count_info = Domain_Bothub_Scenario_Socket::getEventCountInfo($space_id);
		} catch (cs_CompanyNotExist) {
			return $this->error(1315002, "space not found");
		} catch (cs_CompanyIsNotActive) {
			return $this->error(1315005, "space is not active");
		}

		return $this->ok([
			"total_event_count"         => (int) $event_count_info["total_event_count"],
			"previous_week_event_count" => (int) $event_count_info["previous_week_event_count"],
			"current_week_event_count"  => (int) $event_count_info["current_week_event_count"],
		]);
	}
}