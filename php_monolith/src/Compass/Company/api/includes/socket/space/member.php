<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CompanyIsHibernatedException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Exception\ActionNotAllowed;
use CompassApp\Domain\Member\Exception\UserIsGuest;

/**
 * контроллер для работы учатниками пространства
 */
class Socket_Space_Member extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"updatePermissions",
		"isMediaConferenceCreatingAllowed",
		"checkIsAllowedForCall",
		"addConversationMessage",
		"incConferenceMembershipRating",
		"checkSession",
	];

	/**
	 * Обновляем права в пространстве
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function updatePermissions():array {

		Domain_Member_Scenario_Socket::updatePermissions();

		return $this->ok();
	}

	/**
	 * Проверяет возможность создавать медиа-конференции указанным пользователем.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \queryException
	 */
	public function isMediaConferenceCreatingAllowed():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			$result = Domain_Member_Scenario_Socket::isMediaConferenceCreatingAllowed($user_id);
		} catch (UserIsGuest) {
			return $this->ok(["is_allowed" => 0, "reason" => 2419011]);
		} catch (ActionNotAllowed) {
			return $this->ok(["is_allowed" => 0, "reason" => 2419010]);
		}

		if (!$result) {

			// возникла ошибка, не попадающая под существующие бизнес-случаи,
			// например, пользователь не найден или является ботом
			return $this->ok(["is_allowed" => 0, "reason" => -1]);
		}

		return $this->ok(["is_allowed" => 1, "reason" => 0]);
	}

	/**
	 * Проверяет возможность создавать сингл звонки указанным пользователем.
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws CompanyIsHibernatedException
	 * @throws CompanyNotServedException
	 * @throws ParamException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public function checkIsAllowedForCall():array {

		$user_id          = $this->post(\Formatter::TYPE_INT, "user_id");
		$opponent_user_id = $this->post(\Formatter::TYPE_INT, "opponent_user_id");

		try {
			[$result, $conversation_map] = Domain_Member_Scenario_Socket::checkIsAllowedForCall($user_id, $opponent_user_id);
		} catch (Gateway_Socket_Exception_Conversation_GuestInitiator) {
			return $this->ok(["is_allowed" => 0, "reason" => 2419011, "conversation_map" => ""]);
		} catch (Gateway_Socket_Exception_Conversation_NotAllowed|ActionNotAllowed) {
			return $this->ok(["is_allowed" => 0, "reason" => 2419010, "conversation_map" => ""]);
		}

		if (!$result) {

			// возникла ошибка, не попадающая под существующие бизнес-случаи,
			// например, пользователь не найден или является ботом
			return $this->ok(["is_allowed" => 0, "reason" => -1, "conversation_map" => ""]);
		}

		return $this->ok(["is_allowed" => 1, "reason" => 0, "conversation_map" => $conversation_map]);
	}

	/**
	 * Инкрементим статистику участия пользователя в конференции
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 */
	public function incConferenceMembershipRating():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Domain_Member_Scenario_Socket::incConferenceMembershipRating($user_id);

		return $this->ok();
	}

	/**
	 * Метод проверки данных авторизации пользователя.
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 */
	public function checkSession():array {

		$source = $this->post(\Formatter::TYPE_STRING, "source");
		$value  = $this->post(\Formatter::TYPE_STRING, "value");

		try {
			Domain_Member_Scenario_Socket::checkSession($source, $value);
		} catch (Domain_Member_Exception_SessionValidationFailed $e) {
			return $this->error($e->getCode(), $e->getMessage());
		}

		return $this->ok();
	}
}