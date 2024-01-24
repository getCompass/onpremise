<?php

namespace Compass\Thread;

/**
 * Класс для взаимодействия с заявкой на найм
 */
class Domain_HiringRequest_Entity_Request {

	public const STATUS_NEED_POSTMODERATION      = 1;  // на постмодерации
	public const STATUS_CONFIRMED                = 11; // принята, пользователь вступил в компанию
	public const STATUS_NEED_CONFIRM             = 12; // пользователь вступил в компанию, но заявка остается на подтверждении у собственника
	public const STATUS_CONFIRMED_POSTMODERATION = 13; // принята, пользователь вступил в компанию после модерации
	public const STATUS_DISMISSED                = 21; // пользователя уволили
	public const STATUS_REJECTED                 = 22; // отклонена собственником
	public const STATUS_REVOKED                  = 23; // отклонена приглашаемым
	public const STATUS_SYSTEM_DELETED           = 24; // отклонена системой
}
