<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Локация не подходит для поиска.
 */
class Domain_Search_Exception_LocationDenied extends \BaseFrame\Exception\DomainException {

	public const REASON_OTHER                  = 1;  // причина не установлена явно
	public const REASON_MEMBER_PLAN_RESTRICTED = 10; // ограничение тарифа пользователей

	/** @var int причина недоступности локации */
	protected int $reason_code;

	/**
	 * Локация не подходит для поиска.
	 */
	public function __construct(string $message, int $code = self::REASON_OTHER) {

		$this->reason_code = $code;
		parent::__construct($message);
	}

	/**
	 * Возвращает код причины.
	 */
	public function getReasonCode():int {

		return $this->reason_code;
	}
}