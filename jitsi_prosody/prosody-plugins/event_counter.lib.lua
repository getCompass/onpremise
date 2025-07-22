-- event_counter.lua
local event_counter = {}    -- Таблица, которая будет экспортироваться
local event_counters = {}   -- Локальная таблица для хранения счётчиков по скриптам

-- Функция для учёта и логирования событий
function event_counter.count_event(script_name, event_name)
    -- Если для данного скрипта ещё не создана таблица счётчиков, инициализируем её
    if not event_counters[script_name] then
        event_counters[script_name] = {
            total = 0,    -- общий счётчик для всех событий
            events = {}   -- отдельные счётчики для каждого события
        }
    end

    local sc = event_counters[script_name]
    -- Увеличиваем общий счётчик для скрипта
    sc.total = sc.total + 1

    -- Если для данного события счётчик ещё не создан, инициализируем его
    if not sc.events[event_name] then
        sc.events[event_name] = 0
    end
    -- Увеличиваем счётчик конкретного события
    sc.events[event_name] = sc.events[event_name] + 1

    -- Формируем строку с перечнем всех событий и их счётчиками
    local events_summary = {}
    for ev, cnt in pairs(sc.events) do
        table.insert(events_summary, string.format("'%s': %d", ev, cnt))
    end
    local events_list = table.concat(events_summary, ", ")

    -- Формирование лог-сообщения
    local log_message = string.format("Скрипт '%s' отправил событие '%s'. Общий счётчик: %d. Состояние событий: { %s }",
        script_name,
        event_name,
        sc.total,
        events_list
    )

    -- Вывод лог-сообщения (используем логгер Prosody, если доступен, иначе print)
    if module and module.log then
        module:log("info", log_message)
    else
        print(log_message)
    end
end

return event_counter
