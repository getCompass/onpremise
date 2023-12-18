<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс сокет-методов для работы с заявками найма или увольнения
 */
class Socket_Hiring_HiringRequest extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"getRequestData",
		"getRequestDataBatching",
		"setThreadMap",
		"getMetaForCreateThread",
		"revoke",
		"setMessageMap",
		"getRequestDataForScript",
	];

	/**
	 * полученние данных заявки найма/увольнения
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getRequestData():array {

		$request_type = $this->post(\Formatter::TYPE_STRING, "request_type");
		$request_id   = $this->post(\Formatter::TYPE_INT, "request_id");

		// получаем заявку; проверяем, доступы у пользователя
		try {

			switch ($request_type) {

				case "hiring_request":

					[$request] = Domain_HiringRequest_Scenario_Socket::get($this->user_id, $request_id);
					$request = Apiv1_Format::hiringRequest($request);
					$users   = [];
					break;

				case "dismissal_request":
					$request = Domain_DismissalRequest_Scenario_Socket::get($this->user_id, $request_id);
					$users   = [$request->dismissal_user_id];
					$request = Apiv1_Format::dismissalRequest($request);
					break;

				default:
					throw new ParseFatalException("unknown request type name");
			}
		} catch (cs_HireRequestNotExist|cs_DismissalRequestNotExist) {
			return $this->error(1010, "Request doesnt exist");
		} catch (cs_UserHasNoRightsToHiring|cs_UserHasNotRightsToDismiss|\cs_UserIsNotMember) {
			return $this->error(1011, "User dont' have access to the request");
		} catch (cs_IncorrectHiringRequestId|cs_IncorrectDismissalRequestId) {
			throw new ParseFatalException("Invalid request id");
		}

		return $this->ok([
			"request" => (array) $request,
			"users"   => (array) $users,
		]);
	}

	/**
	 * полученние данных заявки найма/увольнения батчингом
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getRequestDataBatching():array {

		$request_id_list_by_type = $this->post(\Formatter::TYPE_ARRAY, "request_id_list_by_type");

		// если получили тип, который отсутствует в списке известных/доступных
		if (count(array_diff(array_keys($request_id_list_by_type), ["hiring_request", "dismissal_request"])) > 0) {
			throw new ParamException("unknown type was received");
		}

		// получаем заявки, доступные для пользователя, и список id недоступных для него
		[$hiring_request_list, $not_allowed_hiring_id_list] = Domain_HiringRequest_Scenario_Socket::getAllowedList(
			$this->user_id, $request_id_list_by_type["hiring_request"] ?? []
		);
		[$dismissal_request_list, $not_allowed_dismissal_id_list] = Domain_DismissalRequest_Scenario_Socket::getAllowedList(
			$this->user_id, $request_id_list_by_type["dismissal_request"] ?? []
		);

		// получаем пользователей для использования в action users
		$hiring_users    = [];
		$dismissal_users = array_column((array) $dismissal_request_list, "dismissal_user_id");

		return $this->ok([
			"request_list_by_type"        => (array) [
				"hiring_request"    => Apiv1_Format::hiringRequestList($hiring_request_list),
				"dismissal_request" => Apiv1_Format::dismissalRequestList($dismissal_request_list),
			],
			"not_allowed_id_list_by_type" => (array) [
				"hiring_request"    => $not_allowed_hiring_id_list,
				"dismissal_request" => $not_allowed_dismissal_id_list,
			],
			"users_by_type"               => (array) [
				"hiring_request"    => $hiring_users,
				"dismissal_request" => $dismissal_users,
			],
		]);
	}

	/**
	 * закрепляем мапу треда за заявкой
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function setThreadMap():array {

		$thread_map   = $this->post(\Formatter::TYPE_STRING, "thread_map");
		$request_id   = $this->post(\Formatter::TYPE_STRING, "request_id");
		$request_type = $this->post(\Formatter::TYPE_STRING, "request_name_type");

		switch ($request_type) {

			case "hiring_request":
				Domain_HiringRequest_Action_SetThreadMap::do($request_id, $thread_map);
				break;

			case "dismissal_request":
				Domain_DismissalRequest_Action_SetThreadMap::do($request_id, $thread_map);
				break;

			default:
				throw new ParseFatalException("passed unknown request type");
		}

		return $this->ok();
	}

	/**
	 * получаем данные для создания заявки
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_DismissalRequestNotExist
	 * @throws cs_HireRequestNotExist
	 * @throws \parseException
	 */
	public function getMetaForCreateThread():array {

		$request_id   = $this->post(\Formatter::TYPE_INT, "request_id");
		$request_type = $this->post(\Formatter::TYPE_STRING, "request_name_type");

		// в зависимости от типа заявки
		switch ($request_type) {

			case "hiring_request":

				$request    = Domain_HiringRequest_Entity_Request::get($request_id);
				$thread_map = Domain_HiringRequest_Entity_Request::getThreadMap($request->extra);

				if (!isEmptyString($thread_map)) {
					break;
				}

				// треда нет, потому передаем необходимые данные
				$request = (array) $request;
				return $this->ok([
					"is_exist"        => (int) 0,
					"creator_user_id" => (int) $request["hired_by_user_id"],
					"created_at"      => (int) $request["created_at"],
					"extra"           => (array) $request["extra"],
				]);

			case "dismissal_request":

				$request    = Domain_DismissalRequest_Entity_Request::get($request_id);
				$thread_map = Domain_DismissalRequest_Entity_Request::getThreadMap($request->extra);

				if (!isEmptyString($thread_map)) {
					break;
				}

				// треда нет, потому передаем необходимые данные
				$request = (array) $request;
				return $this->ok([
					"is_exist"        => (int) 0,
					"creator_user_id" => (int) $request["creator_user_id"],
					"created_at"      => (int) $request["created_at"],
					"extra"           => (array) $request["extra"],
				]);

			default:
				throw new ParseFatalException("passed unknown request type");
		}

		// если у заявки уже имеется тред
		return $this->ok([
			"is_exist"   => (int) 1,
			"thread_map" => (string) $thread_map,
		]);
	}

	/**
	 * отклоняем заявку на найм по entry_id
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function revoke():array {

		$entry_id                  = $this->post(\Formatter::TYPE_STRING, "entry_id");
		$candidate_full_name       = $this->post(\Formatter::TYPE_STRING, "candidate_full_name");
		$candidate_avatar_file_key = $this->post(\Formatter::TYPE_STRING, "candidate_avatar_file_key");
		$candidate_avatar_color_id = $this->post(\Formatter::TYPE_STRING, "candidate_avatar_color_id");

		try {

			Domain_HiringRequest_Scenario_Socket::revoke(
				$entry_id, $candidate_full_name, $candidate_avatar_file_key, $candidate_avatar_color_id);
		} catch (cs_HiringRequestNotPostmoderation) {
			return $this->error(404);
		}

		return $this->ok();
	}

	/**
	 * закрепляем мапу сообщения за заявкой
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function setMessageMap():array {

		$message_map  = $this->post(\Formatter::TYPE_STRING, "message_map");
		$request_id   = $this->post(\Formatter::TYPE_STRING, "request_id");
		$request_type = $this->post(\Formatter::TYPE_STRING, "request_name_type");

		switch ($request_type) {

			case Domain_HiringRequest_Entity_Request::HIRING_REQUEST_NAME_TYPE:

				Domain_HiringRequest_Action_SetMessageMap::do($request_id, $message_map);
				break;

			case Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_NAME_TYPE:

				Domain_DismissalRequest_Action_SetMessageMap::do($request_id, $message_map);
				break;

			default:
				throw new ParseFatalException("passed unknown request type");
		}

		return $this->ok();
	}

	/**
	 * полученние данных заявки найма/увольнения без проверки прав (для скрипта)
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_DismissalRequestNotExist
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserHasNoRightsToHiring
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws \cs_UserIsNotMember
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getRequestDataForScript():array {

		$request_name_type = $this->post(\Formatter::TYPE_STRING, "request_name_type");
		$request_id        = $this->post(\Formatter::TYPE_INT, "request_id");

		// получаем заявку; проверяем, доступы у пользователя
		try {

			switch ($request_name_type) {

				case Domain_HiringRequest_Entity_Request::HIRING_REQUEST_NAME_TYPE:

					[$request] = Domain_HiringRequest_Scenario_Socket::get($this->user_id, $request_id, true);
					$request = Apiv1_Format::hiringRequest($request);
					$users   = [];
					break;

				case Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_NAME_TYPE:

					$request = Domain_DismissalRequest_Scenario_Socket::get($this->user_id, $request_id, true);
					$users   = [$request->dismissal_user_id];
					$request = Apiv1_Format::dismissalRequest($request);
					break;

				default:
					throw new ParseFatalException("unknown request type name");
			}
		} catch (cs_HireRequestNotExist) {
			return $this->error(1010, "Hiring request doesnt exist");
		} catch (cs_IncorrectHiringRequestId|cs_IncorrectDismissalRequestId) {
			throw new ParseFatalException("Invalid hiring request id");
		}

		return $this->ok([
			"request" => (array) $request,
			"users"   => (array) $users,
		]);
	}
}
