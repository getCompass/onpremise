<?php

/**
 * CANNOT BE CHANGED!
 */

use \BaseFrame\Path\PathProvider;
use BaseFrame\System\File;

set_time_limit(0);
ini_set("memory_limit", "256M");

/**
 * основной класс от которого форкаются все кроны
 */
class Cron_Default {

	protected int $memory_limit = 20;

	protected int $break_after_loop_count = 500;                                                                                                                                                                                                                                                                     // в каком цикле осуществлять передподклчюение
	protected int $break_after_left_at    = 59;                                                                                                                                                                                                                                                                      // очищать если последний раз очищали соединения меньше чем {N} секунд
	protected int $max_circle_before_die  = 0;                                                                                                                                                                                                                                                                       // если не задано - то не умираем
	protected const _RABBIT_KEY    = "local";                                                                                                                                                                                                                                                                        // какой ключ для рэббита юзаем
	protected const _EXCHANGE_NAME = null;

	// последняя секунда, после которой умираем
	// например если мы ставим 57 - то время смерти выставится 12:00:57
	protected const _LAST_SECOND = 57;

	protected int $start_at;              // время начала работы крона
	protected int $end_at;                // время конца работы крона
	protected int $end_at_force;          // время форсированного конца работы крона
	protected int $last_cleared_at;       // время последней очистки соединений
	protected int $last_worked_at = 0;    // время последней работы
	protected int $life_period    = 300;  // время жизни в секундах (5 минут)

	protected string $bot_id       = "bot0";
	protected string $bot_name     = "";
	protected int    $bot_num      = 0;
	protected string $queue_prefix = "";
	protected string $bot_type     = "producer";  // по умолчанию все боты являются продюсерами
	protected int    $loop_num     = 0;           // счетчик циклов
	protected array  $config       = [];
	protected int    $pid          = 0;
	protected int    $sleep_time   = 1;           // время между итерациями (в секундах)
	protected int    $max_queue    = 1000;        // максимальный размер очереди

	public function __construct(array $config = []) {

		global $argv;

		// если существует файл /home/cron.lock
		if (file_exists("/home/cron.lock")) {
			self::_die("Die, cron start is locked");
		}

		// если это не тестовый сервер и настало время для бекапов с 5:00 до 5:05
		if (!isTestServer() && date("G") == 5 && intval(date("i")) < 5) {
			self::_die("Die its backup time...");
		}

		// если использовали не полный путь для крона
		if (!str_starts_with($argv[0], "/app/")) {

			console("Для запуска крона надо указывать в РНР - абсолютный путь:");
			console("Пример: php /app/src/application/api/cron/demo.php");
			console("die...");
			self::_die();
		}

		// получаем имя бота
		$this->bot_name = static::_resolveBotName();

		ddefine("CRON_NAME", $this->bot_name);

		$this->pid = getmypid(); // получаем уникальный id процесса в системе

		if (in_array("stop", $argv)) {

			$range = range(0, 1000);

			// умираем
			console("start - with stop param... ");
			foreach ($range as $v) {

				$bot_name = "bot{$v}";
				console("Try kill bot name = {$bot_name}");

				$pid = $this->_getPid($this->_getLockFile($bot_name));
				if ($pid > 0) {

					// убиваем крон
					posix_kill($pid, 9);

					$txt = "[SYSTEM] CRON START WITH PARAM - " . $argv[1];
					$this->write($txt, $bot_name);
					console("{$txt} - {$bot_name}");
				}
			}
			self::_die("DIE - {$argv[1]}\n");
		}

		// усли существует второй аргумент то ставим bot_id
		if (isset($argv[2])) {
			$this->bot_id = $argv[2];
		}

		// если задан конфиг
		if (count($config) > 0) {

			$this->config = $config;

			if (isset($config["memory_limit"])) {
				$this->memory_limit = $config["memory_limit"];
			}

			if (isset($config["rabbit"]["max_queue"])) {
				$this->max_queue = $config["rabbit"]["max_queue"];
			}

			// если это rabbit и он не продюсер
			if (isset($config["rabbit"])) {

				if (!in_array($this->bot_id, $config["rabbit"]["producer"])) {
					$this->bot_type = "consumer";
				}
			}
		}

		$this->bot_num = $this->getBotNum();

		// засыпаем при запуске чтобы запускать кроны по порядку
		$sleep_start = $this->bot_num < 10 ? 0 : intval($this->bot_num / 50);
		console("Sleep before start = {$sleep_start} sec.");
		sleep($sleep_start);

		// отмечаем время работы
		$this->start_at        = time();
		$this->last_cleared_at = time();

		$this->end_at = minuteStart(time() + $this->life_period) + self::_LAST_SECOND;

		// прибавляем от 1 до 2 минут чтобы не погасли все сразу
		$count_minute = $this->bot_num % 3;
		$this->end_at = $this->end_at + (60 * $count_minute);

		// 5 минут от времени конца - время для force kill
		$this->end_at_force = $this->end_at + (60 * 5);

		console("END TIME: " . date(DATE_FORMAT_FULL_S, $this->end_at));
		console("FORCE END TIME: " . date(DATE_FORMAT_FULL_S, $this->end_at_force));
	}

	// запуск - один раз
	public function start():void {

		// перед запуском проверяем, если есть просто "мягко" выходим, не запуская цикл
		if ($this->_isRun()) {
			return;
		}

		$this->_begin();
		$this->write(sprintf("CRON START, END AT: %s", date(DATE_FORMAT_FULL_S, $this->end_at)));

		while ($this->_isCanWorkByTime()) {

			if ($this->bot_type == "consumer") {

				$this->doConsumerWork();
			} else {

				$this->doProducerWork();
			}

			// очищаем соединения и т/п/
			$this->_clearGlobalsIfNeeded();
		}

		$this->write(sprintf("CRON END BY TIME (exceed: %ds)...", time() - $this->end_at));
		$this->_done();
	}

	/**
	 * Дефолтная функция инициализации мониторинга.
	 * Внутри этого класса нигде не вызывается.
	 * Использование подразумевается через override _begin и _end методов.
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _monitor(string $cron_id, string $module, \BaseFrame\Monitor\Sender $gateway, bool $enable_logs, bool $enable_metrics, bool $enable_tracing):void {

		// фиксируем идентификатор запроса
		$request_id = (new \BaseFrame\Http\Header\RequestId())->getValue();

		\BaseFrame\Monitor\Core::init($gateway, $enable_logs, $enable_metrics, $enable_tracing);
		\BaseFrame\Monitor\Core::getTraceAggregator()->init($request_id, $module, true);
		\BaseFrame\Monitor\Core::getMetricAggregator()->setDefaultLabel("module", $module);
		\BaseFrame\Monitor\Core::getLogAggregator()
			->setDefaultLabel("module", $module)
			->setDefaultLabel("cron_id", $cron_id)
			->setLogLevel(\BaseFrame\Monitor\LogAggregator::LOG_LEVEL_INFO);
	}

	/**
	 * Функция обертка для старта крона.
	 */
	protected function _begin():void {

	}

	/**
	 * Функция обертка для завершения крона.
	 */
	protected function _done():void {

		static::_die();
	}

	// возвращает true/false в зависимости того можно ли сейчас работать
	// или пора умирать
	protected function _isCanWorkByTime():bool {

		// force kill - сразу die
		if (time() > $this->end_at_force) {
			return false;
		}

		// здесь проверяем чтобы секунда была < заданной
		if (time() > $this->end_at && intval(date("s")) >= self::_LAST_SECOND) {
			return false;
		}

		if (defined("CRON_NEED_DIE") && CRON_NEED_DIE == true) {
			return false;
		}

		return true;
	}

	// ------------------------------------------------------------------------------
	// SEND QUEUE
	// ------------------------------------------------------------------------------

	/**
	 * отправляем в очередь задачу - которая потом приходит в doWork();
	 *
	 * @param $data
	 *
	 * @return false
	 * @mixed
	 */
	public function doQueue($data) {

		global $argv;

		// нужно для теста на локалке
		if (in_array("now", $argv)) {

			console("--- PARAMETR |NOW|... doWork method... ");
			/** @noinspection PhpUndefinedMethodInspection */
			return $this->doWork($data);
		}

		$this->_getBusInstance($this::_RABBIT_KEY)->sendMessage($this->getQueueName(), $data);
		return false;
	}

	// функция для работы producer
	public function doProducerWork():void {

		// получаем количество зачад очереди и если их много то спим
		if (isset($this->config["rabbit"])) {

			$now_queue_size = $this->_getBusInstance($this::_RABBIT_KEY)->getQueueSize($this->getQueueName());
			if ($this->max_queue < $now_queue_size) {

				console("ВНИМАНИЕ! Максимальный размер очереди {$this->max_queue} сейчас {$now_queue_size}, спим 1 секунду и пробуем заново");
				sleep(1);
				return;
			}
		}

		// отмечаем время начала работы
		$start_at = time();

		// до начала работы
		$this->_beforeWork();

		// выполняем работу и увеличиваем счетчик циклов
		/** @noinspection PhpUndefinedMethodInspection */
		$this->work();
		$this->_afterProducerWork($start_at);
	}

	// функция для работы consumer
	public function doConsumerWork():void {

		$this->_beforeWork();

		// ждем сообщение
		// @mixed
		$this->_getBusInstance($this::_RABBIT_KEY)->waitMessages($this->getQueueName(), $this::_EXCHANGE_NAME, function($message):string {

			// проверяем – если последний doWork был слишком давно – прямо здесь очищаем коннекты ко всему кроме рэббита перед doWork
			if ($this->last_worked_at > 0 && (time() - $this->last_worked_at) > $this->break_after_left_at) {
				sharding::end(false);
			}

			/** @noinspection PhpUndefinedMethodInspection */
			$this->doWork($message);
			$this->_afterConsumerWork();

			if ($this->_isNeedStopRabbitConsumer()) {
				return "die";
			}
			return "";
		}, 5, $this->end_at_force - time());
	}

	// нужно ли стопнуть ребит
	protected function _isNeedStopRabbitConsumer():bool {

		// если циклы достигли максимума
		if ($this->loop_num % $this->break_after_loop_count == 0) {

			$this->write("CRON END BY LOOP COUNT");
			return true;
		}

		// если по времени давно не обновлялись
		if ($this->last_cleared_at < time() - $this->break_after_left_at) {

			$this->write("CRON END BY CLEAR TIME");
			return true;
		}

		// время для бекапов с 5:00 до 5:05
		if (date("G") == 5 && intval(date("i")) < 5) {

			$this->write("CRON END BY BACKUP TIME");
			return true;
		}

		// force kill - сразу die
		if (time() > $this->end_at_force) {

			$this->write("CRON END BCZ FORCE KILL");
			return true;
		}

		// здесь проверяем чтобы секунда была < заданной
		if (time() > $this->end_at && intval(date("s")) >= self::_LAST_SECOND) {

			$this->write("CRON END BY TIME");
			return true;
		}

		return false;
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// сколько времени спим до следующего цикла
	public function sleep(int $sleep):void {

		$this->sleep_time = $sleep;
	}

	// функция для того чтобы получить id бота
	public function getBotNum():int {

		return intval(str_replace("bot", "", $this->bot_id));
	}

	// метод для того чтобы записать в лог
	public function write(string $message, string $bot_id = null):void {

		$this->say($message);
		if ($bot_id == null) {
			$bot_id = $this->bot_id;
		}

		$type    = $this->bot_name . ".{$bot_id}";
		$date    = date(DATE_FORMAT_FULL);
		$message = "$date\t$message";
		File::init(PathProvider::configLogCron(), $type . ".log")->write($message, true);
	}

	// название очереди для крона
	public function getQueueName():string {

		return $this->bot_name . $this->queue_prefix;
	}

	/**
	 * метод для вывода в консоль
	 *
	 * @param $message
	 *
	 * @mixed
	 */
	public function say($message):void {

		console($message);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// функция выполняемая перед работой крона
	protected function _beforeWork():void {

		// если первый цикл
		if ($this->loop_num == 0) {
			console("-------------------------------");
			console("BOT {$this->bot_id} IS {$this->bot_type}");
			console("-------------------------------");
		}
	}

	// функция выполняемая после работы продюсера
	protected function _afterProducerWork(int $start_at):void {

		$this->loop_num++;
		$this->last_worked_at = time();

		$sleep = limit($start_at + $this->sleep_time - time(), 0);
		console("SLEEP: {$sleep}");
		sleep($sleep);
	}

	// функция выполняемая после работы кансамера
	protected function _afterConsumerWork():void {

		$this->loop_num++;
		$this->last_worked_at = time();

		$this->say("[LOOP: {$this->loop_num}] END CONSUMER WORK --");
	}

	// очистить подклчюения - если надо
	protected function _clearGlobalsIfNeeded():void {

		// очищаем соединения и т/п/
		if ($this->loop_num % $this->break_after_loop_count == 0) {

			$this->_clearGlobals();
			return;
		}

		// если давно не очищали соединения и надо очистить, чтобы не было отвалов коннектов
		if ($this->last_cleared_at < time() - $this->break_after_left_at) {

			$this->_clearGlobals();
			return;
		}
	}

	// метод для очистки соединений
	protected function _clearGlobals():void {

		console("Clear Globals ... ");
		sharding::end();

		// проверяем используемую память
		$this->_checkMemory();

		// каждые 100 циклов пишем что крон жив
		if ($this->loop_num % 100 == 0) {
			$this->write("[SYSTEM] CRON IS OK [while loop = {$this->loop_num}]");
		}

		// если дошли до максимального количества циклов
		if ($this->max_circle_before_die > 0 && $this->loop_num >= $this->max_circle_before_die) {

			$this->write("[SYSTEM] CRON IS {$this->max_circle_before_die} LOOP ... die");
			self::_die();
		}

		// фиксируем когда полсдений раз очищали соединения
		$this->last_cleared_at = time();
		$this->onClearGlobals();
	}

	// получаем pid крона
	protected function _getPid(File $file = null):int {

		if ($file == null) {
			$file = $this->_getLockFile($this->bot_id);
		}

		if (!$file->isExists()) {
			return 0;
		}

		return (int) $file->read();
	}

	// устанавливаем pid крона
	protected function _setPid(int $pid):void {

		$file = $this->_getLockFile($this->bot_id);
		$file->write($pid);
	}

	/**
	 * Получаем lock файл, в котором крон хранит свой pid
	 *
	 * @return File
	 */
	protected function _getLockFile(string $bot_id = "bot0"):File {

		global $argv;
		return File::init(PathProvider::root() . "cache/", md5($argv[0]) . "_{$bot_id}.lock");
	}

	// проверяем используемую память
	protected function _checkMemory():void {

		$memory  = round(memory_get_usage() / 1024 / 1024, 3);
		$memory2 = round(memory_get_usage(true) / 1024 / 1024, 3);
		console("MEMORY: {$memory}MB / {$memory2}MB");
		if ($memory2 > $this->memory_limit) {

			$this->write("CRON END BY MEMORY");
			self::_die("--- end memory ---");
		}
	}

	// проверяем на вторую копию скрипта
	protected function _isRun():bool {

		// получаем pid
		$pid = $this->_getPid();

		// если уже есть такой
		if ($pid > 0 && posix_kill($pid, 0) && $pid != $this->pid) {

			console("Daemon already running");
			return true;
		}

		// устанавливаем pid
		$this->_setPid($this->pid);
		return false;
	}

	/**
	 * Возвращает экземпляр Rabbit для указанного ключа.
	 */
	protected static function _getBusInstance(string $bus_key):Rabbit {

		return sharding::rabbit($bus_key);
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		global $argv;

		// актуальная реализация с модульной структурой
		if (str_starts_with($argv[0], "/app/src")) {
			return strtolower(basename(str_replace("/", "_", explode("app/src/", $argv[0])[1]), ".php"));
		}

		return strtolower(basename(str_replace("/", "_", explode("api/cron/", $argv[0])[1]), ".php"));
	}

	// умираем
	protected static function _die(string $txt = ""):void {

		die($txt);
	}

	// -------------------------------------------------------
	// переопределяемые функции
	// -------------------------------------------------------

	// метод заглушка для переопределение в кроне
	public function onClearGlobals():void {

		// функция переобределяется в рабочем основном скрипте
		// нужна чтобы в момент сброса обнулить какие-то параметры в самом исполняемом кроне
	}
}
