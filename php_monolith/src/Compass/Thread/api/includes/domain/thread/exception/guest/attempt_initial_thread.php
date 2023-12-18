<?php

namespace Compass\Thread;

use BaseFrame\Exception\DomainException;

/**
 * Попытка гостя инициализировать тред
 */
class Domain_Thread_Exception_Guest_AttemptInitialThread extends DomainException {

	// черты собеседника, с которым пытаются инициировать диалог
	// нужно чтобы в будущем точно определить код ошибки в зависимости от кейса
	public const    OPPONENT_TRAIT_SPACE_RESIDENT  = 1; // полноценный участник пространства
	public const    OPPONENT_TRAIT_GUEST           = 2; // гость пространства
	public const    OPPONENT_TRAIT_BOT             = 3; // бот пространства
	protected const _AVAILABLE_OPPONENT_TRAIT_LIST = [
		self::OPPONENT_TRAIT_SPACE_RESIDENT,
		self::OPPONENT_TRAIT_GUEST,
		self::OPPONENT_TRAIT_BOT,
	];

	// черта опонента
	protected int $_opponent_trait;

	/**
	 * @param int $opponent_trait черта опонента
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function __construct(int $opponent_trait, string $message = "guest attempt initial conversation") {

		if (!in_array($opponent_trait, self::_AVAILABLE_OPPONENT_TRAIT_LIST)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected opponent trait; choose available");
		}

		$this->_opponent_trait = $opponent_trait;

		parent::__construct($message);
	}

	/**
	 * Получаем нужный код ошибки для api-методов
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getApiErrorCode():int {

		return match ($this->_opponent_trait) {
			self::OPPONENT_TRAIT_SPACE_RESIDENT => 2218009,
			self::OPPONENT_TRAIT_GUEST => 2218010,
			self::OPPONENT_TRAIT_BOT => 2218011,
			default => throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected opponent trait; choose available"),
		};
	}
}