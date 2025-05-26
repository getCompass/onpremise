<?php

namespace BaseFrame\Domain\ActivityToken\Exception;

use BaseFrame\Exception\RequestException;

/**
 * Не удалось декрипнуть ключ токена активности
 */
class DecryptFailed extends RequestException {

}