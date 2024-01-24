<?php

namespace Compass\Company;

/**
 * Базовый класс для действия добавления заявки на найм нового сотрудника
 */
class Domain_HiringRequest_Action_Create {

	/**
	 * Добавляем заявку
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @long
	 */
	public static function do(int $hired_by_user_id, int $invited_user_id, string $user_full_name, string $user_avatar_file_key, string $invite_link_uniq, int $entry_id,
					  int $entry_option, string $comment, string $locale):int {

		// преобразуем комментарий пользователя к заявке
		$comment = Type_Api_Filter::replaceEmojiWithShortName($comment);
		$comment = Type_Api_Filter::prepareText($comment, Domain_JoinLink_Entity_Sanitizer::MAX_LENGTH_COMMENT_TEXT);

		// получаем экстру для заявки в зависимости от статуса модерации
		[$extra, $status] = self::_getExtraByModerationStatus($entry_option, $hired_by_user_id, $comment, $locale);

		// добавляем заявку
		$hiring_request = Gateway_Db_CompanyData_HiringRequest::insert(
			$status,
			$invite_link_uniq,
			$entry_id,
			$hired_by_user_id,
			$extra,
			$invited_user_id
		);

		// инкрементим число заявок
		Domain_Company_Entity_Dynamic::incHiringByStatus($hiring_request->status);

		// если нужна модерация - добавляем новое уведомление
		if ($entry_option === Domain_JoinLink_Entity_Main::ENTRY_OPTION_NEED_POSTMODERATION) {

			// добавляем уведомление всем администраторам о том, что появилась новая заявка
			$company_name    = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];
			$avatar_color_id = \BaseFrame\Domain\User\Avatar::getColorByUserId($invited_user_id);
			$extra           = new Domain_Member_Entity_Notification_Extra(
				$hiring_request->hiring_request_id, $user_full_name, $company_name,
				$user_avatar_file_key, \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id)
			);
			Domain_Member_Action_AddNotification::do($invited_user_id, Domain_Member_Entity_Menu::JOIN_REQUEST, $extra);
		}

		return $hiring_request->hiring_request_id;
	}

	/**
	 * Формируем экстру
	 */
	protected static function _getExtraByModerationStatus(int $entry_option, int $hired_by_user_id, string $comment, string $locale):array {

		$extra = Domain_HiringRequest_Entity_Request::initExtra();

		if ($entry_option === Domain_JoinLink_Entity_Main::ENTRY_OPTION_NEED_POSTMODERATION) {
			$status = Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION;
		} else {

			$status  = Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED;
			$comment = ""; // тут специально обнуляем на всякий случай

			$single_item = [
				"user_id" => $hired_by_user_id,
				"status"  => Domain_HiringRequest_Entity_Request::HIRING_REQUEST_APPROVED_ELEMENT_STATUS,
				"order"   => 1,
			];

			// добавляем диалог с создателем в заявку
			$extra = Domain_HiringRequest_Entity_Request::setSingleListToCreate($extra, [$single_item]);
		}

		$extra = Domain_HiringRequest_Entity_Request::setComment($extra, $comment);
		$extra = Domain_HiringRequest_Entity_Request::setLocale($extra, $locale);
		return [$extra, $status];
	}
}
