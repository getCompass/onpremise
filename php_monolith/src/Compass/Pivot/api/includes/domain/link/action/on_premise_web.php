<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для работы со ссылками на веб-сайте on-premise решений.
 */
class Domain_Link_Action_OnPremiseWeb {

	/**
	 * валидируем ссылку-приглашение, если она передана
	 *
	 * @return Struct_Link_ValidationResult|null
	 * @throws CaseException
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws ParamException
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 */
	public static function validateJoinLinkIfNeeded(string|false $join_link, int $existing_user_id):?Struct_Link_ValidationResult {

		// значение по умолчанию
		$validation_result = null;

		// валидируем переданную ссылку
		// тут есть тонкий момент — если мы будем делать так, то, зная номер, можно понять,
		// какие ссылки приглашения пользователь уже принимал/может принять, но пока оставим так
		// это не выглядит как критическая уязвимость, для решения нужно просто поднять эту проверку выше,
		// чтобы для существующих юзеров не проверять (но тогда после ввода кода можно словить ошибку ссылки)
		if ($join_link !== false) {

			try {

				// пытаемся распарсить текст со ссылкой
				[, $parsed_link] = Domain_Link_Action_Parse::do($join_link);

				if (!is_string($parsed_link) || $parsed_link === "") {
					throw new ParamException("passed incorrect join link");
				}

				// получаем детальную информацию о ссылке
				$invite_link_rel_row = Domain_Company_Entity_JoinLink_Main::getByLink($parsed_link);
			} catch (Domain_Link_Exception_LinkNotFound|cs_IncorrectJoinLink|cs_JoinLinkNotFound) {
				throw new CaseException(1000, "passed bad invite");
			}

			$validation_result = $existing_user_id === 0
				? Domain_Link_Entity_Link::validateBeforeRegistration($invite_link_rel_row)
				: Domain_Link_Entity_Link::validateForUser($existing_user_id, $invite_link_rel_row);
		}

		return $validation_result;
	}
}