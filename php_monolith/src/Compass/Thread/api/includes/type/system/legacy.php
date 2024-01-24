<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\ParamException;

/*
 * Класс для поддержки legacy-кода
 *
 * Работает через заголовки, чтобы удобно рулить все в одном месте
 * И писать статистику по любому случаю legacy
 */

/**
 *
 * @uses Type_System_Legacy::_isNewAddQuote()
 *
 * @method static isNewAddQuote
 *
 * @uses Type_System_Legacy::_isAddRepostQuote()
 *
 * @method static isAddRepostQuote
 *
 * @uses Type_System_Legacy::_isAddQuoteV2()
 *
 * @method static isAddQuoteV2
 *
 * @uses Type_System_Legacy::_isAddRepostV2()
 *
 * @method static isAddRepostV2
 *
 * @uses Type_System_Legacy::_isLongMessageSupported()
 *
 * @method static isLongMessageSupported()
 *
 * @uses Type_System_Legacy::_isNewTryDeleteMessageError()
 *
 * @method static isNewTryDeleteMessageError
 *
 * @uses Type_System_Legacy::_isNewErrors()
 *
 * @method static isNewErrors
 *
 * @uses Type_System_Legacy::_isWithoutEmail()
 *
 * @method static isWithoutEmail
 *
 * @uses Type_System_Legacy::_isNewThreadMeta()
 *
 * @method static isNewThreadMeta
 *
 * @uses Type_System_Legacy::_isNewUnFollow()
 *
 * @method static isNewUnFollow
 *
 * @uses Type_System_Legacy::_isInitializeThreadWithLongMessageSupported()
 *
 * @method static isInitializeThreadWithLongMessageSupported
 *
 * @uses Type_System_Legacy::_isNewViewPhoto()
 *
 * @method static isNewViewPhoto
 *
 * @uses Type_System_Legacy::_isNewErrorIfNotAccessToParentEntity()
 *
 * @method static isNewErrorIfNotAccessToParentEntity
 *
 * @uses Type_System_Legacy::_isGetMenuV2()
 *
 * @method static isGetMenuV2
 *
 * @uses Type_System_Legacy::_isFollowThreadWithSystemMessage()
 *
 * @method static isFollowThreadWithSystemMessage
 *
 * @uses Type_System_Legacy::_isDuplicateClientMessageIdError()
 *
 * @method static isDuplicateClientMessageIdError
 *
 *
 *
 */
class Type_System_Legacy {

	// функция срабатывает перед тем как вызвать любой из методов
	public static function __callStatic(string $method, array $parameters):bool {

		// подставляем _ тк все методы в классе protected
		$method = "_" . $method;

		// если такого метода нет
		if (!method_exists(__CLASS__, $method)) {
			throw new ParamException(__METHOD__ . ": attempt to call not exist method");
		}

		// запускаем метод
		return forward_static_call_array([__CLASS__, $method], $parameters);
	}

	// массовое добавление цитат и новый тип сообщения (mass_quote)
	protected static function _isNewAddQuote():bool {

		$value = getHeader("HTTP_MASS_ADD_QUOTE");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}
		return false;
	}

	// новая версия, позволяющая репостить/цитировать репост/цитату
	protected static function _isAddRepostQuote():bool {

		$value = getHeader("HTTP_ADD_REPOST_QUOTE");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// версия цитат V2 (c рабитием цитаты на соощения по 15 штук)
	protected static function _isAddQuoteV2():bool {

		$header_value = getHeader("HTTP_X_COMPASS_ADD_QUOTE_V2");

		// если не передали заголовок, то в результате вернем всегда истину
		if (mb_strlen($header_value) < 1) {
			return true;
		}

		if (intval($header_value) == 1) {
			return true;
		}

		return false;
	}

	// версия репостов V2 (c рабитием репостов на соощения по 15 штук)
	protected static function _isAddRepostV2():bool {

		$header_value = getHeader("HTTP_X_COMPASS_ADD_REPOST_V2");

		// если не передали заголовок, то в результате вернем всегда истину
		if (mb_strlen($header_value) < 1) {
			return true;
		}

		if (intval($header_value) == 1) {
			return true;
		}

		return false;
	}

	// поддержка отправки длинных сообщений
	// эта штука на момент создания активно используется в тестах,
	// перед удалением нужно убедиться, что все тесты тоже переехали
	protected static function _isLongMessageSupported():bool {

		$value = getHeader("HTTP_LONG_MESSAGE_SUPPORT");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// новая ошибка для методов conversations.tryDeleteMessage и conversations.tryDeleteMessageList
	protected static function _isNewTryDeleteMessageError():bool {

		$value = getHeader("HTTP_IS_NEW_TRY_DELETE_MESSAGE_ERROR");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// новые ошибки для методов php_threads
	protected static function _isNewErrors():bool {

		$value = getHeader("HTTP_IS_NEW_ERRORS");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// новая версия, когда пользователь не имеет email
	protected static function _isWithoutEmail():bool {

		$value = getHeader("HTTP_IS_WITHOUT_EMAIL");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// новый обезличенный формат thread_meta, скрытие сообщений не влияет на основную мету
	protected static function _isNewThreadMeta():bool {

		$value = getHeader("HTTP_NEW_THREAD_META");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}
		return false;
	}

	// новый threads.unFollow отписываем новым способом
	protected static function _isNewUnFollow():bool {

		$value = getHeader("HTTP_NEW_THREAD_UNFOLLOW");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}
		return false;
	}

	// поддержка отправки длинных сообщений
	protected static function _isInitializeThreadWithLongMessageSupported():bool {

		if (self::_isLongMessageSupported()) {
			return true;
		}

		return false;
	}

	// новый Просмотр фотографий
	protected static function _isNewViewPhoto():bool {

		$value = getHeader("HTTP_NEW_VIEW_PHOTO");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// новая версия ошибки, если нет доступ к родительской сущности
	protected static function _isNewErrorIfNotAccessToParentEntity():bool {

		$value = getHeader("HTTP_NEW_ERROR_IF_NOT_ACCESS_TO_PARENT_ENTITY");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// новая версия метода getMenu
	protected static function _isGetMenuV2():bool {

		$value = getHeader("HTTP_GET_MENU_V2");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	/**
	 * отправка системного сообщения при подписке на тред
	 */
	protected static function _isFollowThreadWithSystemMessage():bool {

		$value = getHeader("HTTP_X_COMPASS_FOLLOW_THREAD_WITH_SYSTEM_MESSAGE");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}
		return false;
	}

	/**
	 * отдаем ли новую ошибку при дубликате client_message_id
	 */
	protected static function _isDuplicateClientMessageIdError():bool {

		$value = getHeader("HTTP_IS_DUPLICATE_CLIENT_MESSAGE_ID_ERROR");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		Gateway_Bus_CollectorAgent::init()->inc("row1"); // количество вызовов legacy
		return false;
	}
}