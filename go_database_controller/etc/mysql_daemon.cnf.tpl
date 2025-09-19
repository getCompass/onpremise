[client]
default_character_set = utf8mb4
ssl_mode=DISABLED

[mysqld]

# General
sql_mode = STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_VALUE_ON_ZERO,NO_ENGINE_SUBSTITUTION,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY
sysdate_is_now = 1
innodb = FORCE
binlog_expire_logs_seconds = 1209600
open_files_limit = 65535
user = mysql

# Слушать подключения откуда угодно
bind_address = 0.0.0.0

# Папка с данными MySQL
datadir = /var/lib/mysql/

innodb_buffer_pool_size = 8M            # кэш таблиц, данных и индексов
innodb_thread_concurrency = 4           # кол-во одновременно обрабатываемых запросов, оптимально = кол-во ядер на машине если один mysql
innodb_log_buffer_size = 1M             # буфера лога транзакций
innodb_flush_log_at_trx_commit = 0      # сброс данных на диск, 0 - раз в секунду (быстро, ненадожно), 1 - при каждой транзакции (очень медленно), 2 - сброс в память системы, потом на диск
innodb_flush_method = O_DIRECT           # метод сброса данных на диск O_DIRECT - надёжнее, O_DSYNC - быстрее
innodb_file_per_table = 1               # опция 0/1, 0 - один файл на всю базу, 1 - для каждой таблицы отдельный файл
innodb_open_files = 65555               # кол-во открытых файлов, в основном файлов таблиц
innodb_use_native_aio = 0               # опция 0/1, aio - асинхронный метод ввода/вывода, отправляющий запросы напрямую в линукс, то есть быстрее, стоит включить когда на машине один экз mysql
innodb_sort_buffer_size = 512K          # буфер для сортировки, в основном используется при построении индексов
innodb_max_dirty_pages_pct=0
innodb_sync_array_size = 1024
binlog_cache_size = 32K
key-buffer-size = 128K                  # кэш для индексов, используется в myisam, в innodb можно занизить до минимума
thread-cache-size = 16                   # кол-во потоков которые будут хранится в кэше
thread_stack = 256K                     # размер стека для каждого потока
table-definition-cache = 5000            # кэш для структур таблиц, сам не занимает много места, но им можно мягко ограничить кол-во таблиц в кэше
table-open-cache = 200                  # кол-во таблиц в кэше для всех потоков
tmp_table_size = 128K                   # кэш временных таблиц
max_heap_table_size = 128K              # для innodb тоже самое что и tmp_table_size, для MEMORY ограничивает размер таблицы в памяти
read_buffer_size = 128K                 # буфер при сканировании/чтении таблицы, для каждого потока
read_rnd_buffer_size = 128K             # буфер для сканирования при order by
sort_buffer_size = 128K                 # буфер сортировки
parser_max_mem_size = 100000000
stored_program_cache = 16
max_binlog_cache_size = 128K
innodb_log_file_size = 16M

max-allowed-packet = 16M                # макс размер данных, которые могут быть переданы за один запрос
max-connect-errors = 1000000            # макс кол-во ошибок который должен поймать mysql перед тем как заблокировать хост
max_connections = 450                  # макс кол-во одновременных соединений, для оптимальной работы, нужно переиспользовать соединения на клиенте
open-files-limit = 65535                #

skip_external_locking
skip-log-bin                            #
skip_name_resolve
mysqlx=off

# статистика по работе mysql, и разные интрументы для мониторинга и оптимизации, если есть лишние 400M памяти, смело включай,
# занижает скорость обработки запросов на 20%
performance_schema = OFF
