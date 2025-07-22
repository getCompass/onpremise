--- Component to trigger an HTTP POST call on room/occupant events
--
--  Example config:
--
--    Component "esync.meet.mydomain.com" "event_sync_component"
--        muc_component = "conference.meet.mydomain.com"
--        breakout_component = "breakout.meet.mydomain.com"
--
--        api_prefix = "http://external_app.mydomain.com/api"
--
--        --- The following are all optional
--        include_speaker_stats = true  -- if true, total_dominant_speaker_time included in occupant payload
--        api_headers = {
--            ["Authorization"] = "Bearer TOKEN-237958623045";
--        }
--        api_timeout = 10  -- timeout if API does not respond within 10s
--        retry_count = 5  -- retry up to 5 times
--        api_retry_delay = 1  -- wait 1s between retries
--        api_should_retry_for_code = function (code)
--            return code >= 500 or code == 408
--        end
--

local json = require "util.json";
local jid = require 'util.jid';
local http = require "net.http";
local urlencode = http.urlencode;
local timer = require 'util.timer';
local is_healthcheck_room = module:require "util".is_healthcheck_room;
local st = require "util.stanza";

local main_muc_component_host = module:get_option_string("muc_component");
local muc_domain_base = module:get_option_string("muc_mapper_domain_base");
local breakout_muc_component_host = module:get_option_string("breakout_component", "breakout." .. muc_domain_base);

local api_prefix = module:get_option("api_prefix");
local collector_prefix = module:get_option("collector_prefix");
local api_timeout = module:get_option("api_timeout", 20);
local api_headers = module:get_option("api_headers");
local api_retry_count = tonumber(module:get_option("api_retry_count", 3));
local api_retry_delay = tonumber(module:get_option("api_retry_delay", 1));

local include_speaker_stats = module:get_option("include_speaker_stats", false);


-- Option for user to control HTTP response codes that will result in a retry.
-- Defaults to returning true on any 5XX code or 0
local api_should_retry_for_code = module:get_option("api_should_retry_for_code", function(code)
    return code >= 500;
end)

-- Cannot proceed if "api_prefix" not configured
if not api_prefix then
    module:log("error", "api_prefix not specified. Disabling %s", module:get_name());
    return ;
end

if main_muc_component_host == nil then
    log("error", "No muc_component specified. No muc to operate on!");
    return ;
end

-- common HTTP headers added to all API calls
local http_headers = {
    ["User-Agent"] = "Prosody (" .. prosody.version .. "; " .. prosody.platform .. ")";
    ["Content-Type"] = "application/json";
};
if api_headers then
    -- extra headers from config
    for key, value in pairs(api_headers) do
        http_headers[key] = value;
    end
end

local URL_EVENT_ENDPOINT = api_prefix;
local URL_COLLECTOR_ENDPOINT = collector_prefix;

-- Заголовки специально для запроса на URL_COLLECTOR_ENDPOINT (POST форма)
local collector_headers = {
    ["User-Agent"] = http_headers["User-Agent"],
    ["Content-Type"] = "application/x-www-form-urlencoded"
};
-- Если у вас в api_headers был Authorization, и он нужен и тут:
if api_headers and api_headers["Authorization"] then
    collector_headers["Authorization"] = api_headers["Authorization"];
end

--- Start non-blocking HTTP call
-- @param url URL to call
-- @param options options table as expected by net.http where we provide optional headers, body or method.
-- @param callback if provided, called with callback(response_body, response_code) when call complete.
-- @param timeout_callback if provided, called without args when request times out.
-- @param retries how many times to retry on failure; 0 means no retries.
local function async_http_request(url, options, callback, timeout_callback, retries)
    local completed = false;
    local timed_out = false;
    local _retries = retries or api_retry_count;

    local function cb_(response_body, response_code)
        if not timed_out then
            -- request completed before timeout
            completed = true;
            if (response_code == 0 or api_should_retry_for_code(response_code)) and _retries > 0 then
                module:log("warn", "API Response code %d. Will retry after %ds", response_code, api_retry_delay);
                timer.add_task(api_retry_delay, function()
                    async_http_request(url, options, callback, timeout_callback, _retries - 1)
                end)
                return ;
            end

            module:log("debug", "%s %s returned code %s", options.method, url, response_code);

            if callback then
                callback(response_body, response_code)
            end
        end
    end

    local request = http.request(url, options, cb_);

    timer.add_task(api_timeout, function()
        timed_out = true;

        if not completed then
            http.destroy_request(request);
            if timeout_callback then
                timeout_callback()
            end
        end
    end);

end

--- Returns current timestamp
local function now()
    return os.time();
end

--- Start EventData implementation
local EventData = {};
EventData.__index = EventData;

function new_EventData(room_jid)
    return setmetatable({
        room_jid = room_jid;
        room_name = jid.node(room_jid);
        created_at = now();
        occupants = {}; -- table of all (past and present) occupants data
        active = {}; -- set of active occupants (by occupant jid)
    }, EventData);
end

--- Handle new occupant joining room
function EventData:on_occupant_joined(occupant_jid, event_origin)
    local user_context = event_origin.jitsi_meet_context_user or {};

    -- N.B. we only store user details on join and assume they don't change throughout the duration of the meeting
    local occupant_data = {
        occupant_jid = occupant_jid;
        name = user_context.name;
        id = user_context.id;
        email = user_context.email;
        joined_at = now();
        left_at = nil;
    };

    self.occupants[occupant_jid] = occupant_data;
    self.active[occupant_jid] = true;

    return occupant_data;
end

--- Handle occupant leaving room
function EventData:on_occupant_leave(occupant_jid, room)
    local left_at = now();
    self.active[occupant_jid] = nil;

    local occupant_data = self.occupants[occupant_jid];
    if occupant_data then
        occupant_data['left_at'] = left_at;
    end

    if include_speaker_stats and room.speakerStats then
        occupant_data['total_dominant_speaker_time'] = room.speakerStats[occupant_jid].totalDominantSpeakerTime
    end

    return occupant_data;
end

--- Returns array of all (past or present) occupants
function EventData:get_occupant_array()
    local output = {};
    for _, occupant_data in pairs(self.occupants) do
        table.insert(output, occupant_data)
    end

    return output;
end

--- End EventData implementation


--- Checks if event is triggered by healthchecks or focus user.
function is_system_event(event)
    if is_healthcheck_room(event.room.jid) then
        return true;
    end

    if event.occupant and jid.node(event.occupant.jid) == "focus" then
        return true;
    end

    return false;
end

--- Updates payload with additional attributes from room._data.event_sync_extra_payload
function update_with_room_attributes(payload, room)
    if room._data and room._data.event_sync_extra_payload then
        for k, v in pairs(room._data.event_sync_extra_payload) do
            payload[k] = v;
        end
    end
end

-- вспомогательная функция для проверки наличия элемента в таблице
function table_contains(tbl, element)
    for _, value in pairs(tbl) do
        if value == element then
            return true
        end
    end
    return false
end

--- Callback when new room created
function room_created(event)
    if is_system_event(event) then
        return ;
    end

    local room = event.room;

    module:log("info", "Start tracking occupants for %s", room.jid);
    local room_data = new_EventData(room.jid);
    room.event_data = room_data;

    local payload = {
        ['event_name'] = 'muc-room-created';
        ['created_at'] = room_data.created_at;
    };
    update_with_room_attributes(payload, room);

    async_http_request(URL_EVENT_ENDPOINT, {
        headers = http_headers;
        method = "POST";
        body = json.encode(payload);
    })
end

--- Callback when room destroyed
function room_destroyed(event)
    if is_system_event(event) then
        return ;
    end

    local room = event.room;
    local room_data = room.event_data;
    local destroyed_at = now();

    module:log("info", "Room destroyed - %s", room.jid);

    if not room_data then
        module:log("error", "(room destroyed) Room has no Event data - %s", room.jid);
        return ;
    end

    local payload = {
        ['event_name'] = 'muc-room-destroyed';
        ['created_at'] = room_data.created_at;
        ['destroyed_at'] = destroyed_at;
        ['all_occupants'] = room_data:get_occupant_array();
    };
    update_with_room_attributes(payload, room);

    async_http_request(URL_EVENT_ENDPOINT, {
        headers = http_headers;
        method = "POST";
        body = json.encode(payload);
    })
end

-- Возвращает словарь только для тех, у кого left_at == nil.
-- Ключом будет occupant_data.id, а значением { joined_at = ... }.
local function build_active_participants_map(room)
    local participants_map = {};

    local all_participants_array = room.event_data:get_occupant_array();
    for _, occupant_data in pairs(all_participants_array) do
        -- проверяем, что участник ещё в комнате
        if occupant_data.left_at == nil then
            local nick = room._jid_nick[occupant_data.occupant_jid];
            local name = occupant_data.name;
            local joined_at = occupant_data.joined_at;

            if nick then
                participants_map[nick] = {
                    name = name,
                    joined_at = joined_at
                };
            end
        end
    end

    return participants_map;
end

--- Callback when an occupant joins room
function occupant_joined(event)
    if is_system_event(event) then
        return ;
    end

    local room = event.room;
    local room_data = room.event_data;
    if not room_data then
        module:log("error", "(occupant joined) Room has no Event data - %s", room.jid);
        return ;
    end

    local occupant_jid = event.occupant.jid;
    local occupant_data = room_data:on_occupant_joined(occupant_jid, event.origin);
    module:log("info", "New occupant - %s", json.encode(occupant_data));

    -- получаем текущее качество видео в конференции
    local quality_level = room.quality_level or "high";

    -- получаем текущий массив записывающих пользователей
    local user_recording_users = room.user_recording_users or {};
    room.user_recording_users = user_recording_users;

    local payload = {
        ['event_name'] = 'muc-occupant-joined';
        ['occupant'] = occupant_data;
    };
    update_with_room_attributes(payload, room);

    async_http_request(URL_EVENT_ENDPOINT, {
        headers = http_headers;
        method = "POST";
        body = json.encode(payload);
    })

    -- собираем «универсальный» ивент для отправки
    -- по мере необходимости можно добавлять новые поля
    local participants_map = build_active_participants_map(room);
    local universal_data = {
        -- тип события
        type = "participant-joined-info",

        -- список событий
        event_list = {
            ["quality-level"] = {
                value = quality_level
            },
            ["user-recording-count"] = {
                value = #user_recording_users
            },
            ["active-participants-info"] = {
                participants = participants_map
            }
        }
    }

    local universal_data_json, encode_error = json.encode(universal_data);
    if not universal_data_json then
        module:log('error', 'Error encoding universal_data for room:%s, error:%s', room.jid, encode_error);
        return ;
    end

    -- отправляем
    local universal_stanza = st.message({
        from = room.jid,
        to = occupant_jid
    })                         :tag("json-message", { xmlns = "http://jitsi.org/jitmeet" })
                               :text(universal_data_json)
                               :up();

    room:route_stanza(universal_stanza);
end

--- Callback when an occupant has left room
function occupant_left(event)
    if is_system_event(event) then
        return ;
    end

    local room = event.room;
    local room_data = room.event_data;

    if not room_data then
        module:log("error", "(occupant left) Room has no Event data - %s", room.jid);
        return ;
    end

    local occupant_jid = event.occupant.jid;
    local occupant_data = room_data:on_occupant_leave(occupant_jid, room);
    module:log("info", "Occupant left - %s", json.encode(occupant_data));

    local stanza = event.stanza;
    local payload = {
        ['event_name'] = 'muc-occupant-left';
        ['occupant'] = occupant_data;
        ['stanza'] = stanza;
    };
    update_with_room_attributes(payload, room);

    async_http_request(URL_EVENT_ENDPOINT, {
        headers = http_headers;
        method = "POST";
        body = json.encode(payload);
    })

    -- инициализируем массив записывающих пользователей, если его ещё нет
    local user_recording_users = room.user_recording_users or {};
    room.user_recording_users = user_recording_users;

    -- обновляем массив записывающих пользователей
    local changed_recording = false;
    for i, recording_jid in ipairs(user_recording_users) do
        if recording_jid == occupant_jid then
            table.remove(user_recording_users, i);
            changed_recording = true;
            break ;
        end
    end

    -- собираем «универсальный» ивент для отправки
    -- по мере необходимости можно добавлять новые поля
    local universal_data = {
        -- тип события
        type = "participant-joined-info",

        -- список событий
        event_list = {}
    }

    -- если список user_recording_users реально изменился,
    -- добавляем информацию о его новом размере
    if changed_recording then
        universal_data.event_list["user-recording-count"] = {
            value = #user_recording_users
        }
    end

    -- если data всё ещё пустая (ничего не изменилось),
    -- то не отправляем событие
    if next(universal_data.event_list) == nil then
        return ;
    end

    local universal_data_json, encode_error = json.encode(universal_data);
    if not universal_data_json then
        module:log('error', 'Error encoding universal_data for room:%s, error:%s', room.jid, encode_error);
        return ;
    end

    -- отправляем
    for _, _occupant_data in pairs(event.room._occupants) do
        local universal_stanza = st.message({
            from = room.jid,
            to = _occupant_data.jid
        })                         :tag("json-message", { xmlns = "http://jitsi.org/jitmeet" })
                                   :text(universal_data_json)
                                   :up();
        room:route_stanza(universal_stanza);
    end
end


-- Helper function to wait till a component is loaded before running the given callback
function run_when_component_loaded(component_host_name, callback)
    local function trigger_callback()
        module:log('info', 'Component loaded %s', component_host_name);
        callback(module:context(component_host_name), component_host_name);
    end

    if prosody.hosts[component_host_name] == nil then
        module:log('debug', 'Host %s not yet loaded. Will trigger when it is loaded.', component_host_name);
        prosody.events.add_handler('host-activated', function(host)
            if host == component_host_name then
                trigger_callback();
            end
        end);
    else
        trigger_callback();
    end
end

-- Helper function to wait till a component's muc module is loaded before running the given callback
function run_when_muc_module_loaded(component_host_module, component_host_name, callback)
    local function trigger_callback()
        module:log('info', 'MUC module loaded for %s', component_host_name);
        callback(prosody.hosts[component_host_name].modules.muc, component_host_module);
    end

    if prosody.hosts[component_host_name].modules.muc == nil then
        module:log('debug', 'MUC module for %s not yet loaded. Will trigger when it is loaded.', component_host_name);
        prosody.hosts[component_host_name].events.add_handler('module-loaded', function(event)
            if (event.module == 'muc') then
                trigger_callback();
            end
        end);
    else
        trigger_callback()
    end
end

local main_muc_service; -- luacheck: ignore

-- No easy way to infer main room from breakout room object, so search all rooms in main muc component and cache
-- it on room so we don't have to search again
-- Speakerstats component does exactly the same thing, so if that is loaded, we get this for free.
local function get_main_room(breakout_room)
    if breakout_room._data and breakout_room._data.main_room then
        return breakout_room._data.main_room;
    end

    -- let's search all rooms to find the main room
    for room in main_muc_service.each_room() do
        if room._data and room._data.breakout_rooms_active and room._data.breakout_rooms[breakout_room.jid] then
            breakout_room._data.main_room = room;
            return room;
        end
    end
end


-- Predefine room attributes to be included in API payload for all events
function handle_main_room_created(event)
    local room = event.room;
    if is_healthcheck_room(room.jid) then
        return ;
    end

    room._data.event_sync_extra_payload = {
        ['is_breakout'] = false;
        ['room_jid'] = room.jid;
        ['room_name'] = jid.split(room.jid);
    }
    room:save();
end


-- Predefine breakout room attributes to be included in API payload for all events
-- This should be scheduled AFTER speakerStats module, but BEFORE handler that compiles and sends API payload
function handle_breakout_room_created(event)
    local room = event.room;
    if is_healthcheck_room(room.jid) then
        return ;
    end

    local main_room = get_main_room(room);
    room._data.event_sync_extra_payload = {
        ['is_breakout'] = true;
        ['breakout_room_id'] = jid.split(room.jid);
        -- use name/jid of parent room as the room_* info
        ['room_jid'] = main_room.jid;
        ['room_name'] = jid.split(main_room.jid);
    }
    room:save();
end

-- когда участнику конференции изменили права
function occupant_affiliation_changed(event)

    -- сюда запишем нового модератора
    local new_moderator = nil
    if event.affiliation == "owner" and event.previous_affiliation == "member" then

        -- пройдемся по каждому участнику комнаты
        local room = event.room;
        for _, occupant in pairs(room.event_data.occupants) do

            if string.find(occupant.occupant_jid, event.jid, 1, true) then

                new_moderator = occupant
                break
            end
        end
    end

    -- если не смогли найти участника, которому выдали модератора
    if new_moderator == nil then

        module:log("info", "new moderator occupant not found");
        return
    end

    local payload = {
        ["event_name"] = "moderator-rights-granted";
        ["occupant"] = new_moderator;
        ["room_name"] = event.room.event_data.room_name;
    };

    async_http_request(URL_EVENT_ENDPOINT, {
        headers = http_headers;
        method = "POST";
        body = json.encode(payload);
    })
end

local get_room_by_name_and_subdomain = module:require "util".get_room_by_name_and_subdomain;

function occupant_groupchat(event)
    local room_name = event.room.event_data.room_name;
    local room = get_room_by_name_and_subdomain(room_name);
    local stanza = event.stanza;
    local body = stanza:get_child_text('body');

    if body then

        local data = json.decode(body)
        if data and type(data) == "table" then

            if data.type == 'quality-level' then
                local qualityLevel = data.qualityLevel;
                local event_occupant_jid = event.stanza.attr.from;
                local event_occupant = room:get_occupant_by_real_jid(event_occupant_jid);
                if not event_occupant then
                    module:log("error", "Occupant %s was not found in room %s", event_occupant_jid, room.jid)
                    return
                end

                -- Access control: Check if the occupant is a moderator
                local affiliation = room:get_affiliation(event_occupant.bare_jid);
                if affiliation ~= 'owner' and affiliation ~= 'admin' then
                    module:log('warn', 'Unauthorized user %s attempted to update quality_level', event_occupant.jid);
                    return ;
                end

                -- обновляем quality_level в room
                room.quality_level = tostring(qualityLevel)
            end

            if data.type == 'participant-stats' then
                -- Пример, что нужно отправить method=stat.log, request=<json> в форме
                local form_data = {
                    method = "stat.log",
                    request = json.encode(data.payload)
                };

                -- Собираем тело формы
                local collector_parts = {};
                for k, v in pairs(form_data) do
                    table.insert(collector_parts, urlencode(k) .. "=" .. urlencode(v));
                end
                local collector_body = table.concat(collector_parts, "&");

                -- Делаем HTTP-запрос только если URL_COLLECTOR_ENDPOINT указан и не пуст
                if URL_COLLECTOR_ENDPOINT and URL_COLLECTOR_ENDPOINT ~= "" then
                    async_http_request(URL_COLLECTOR_ENDPOINT, {
                        headers = collector_headers;
                        method = "POST";
                        body = collector_body;
                    })
                end
            end

            if data.type == 'update-user-recording' then
                local action = data.action;
                local event_occupant_jid = event.stanza.attr.from;
                local event_occupant = room:get_occupant_by_real_jid(event_occupant_jid);
                if not event_occupant then
                    module:log("error", "Occupant %s was not found in room %s", event_occupant_jid, room.jid)
                    return
                end

                -- Access control: Check if the occupant is a moderator
                local affiliation = room:get_affiliation(event_occupant.bare_jid);
                if affiliation ~= 'owner' and affiliation ~= 'admin' then
                    module:log('warn', 'Unauthorized user %s attempted to update user_recording_users', event_occupant.jid);
                    return ;
                end

                -- инициализируем массив записывающих пользователей, если его ещё нет
                local user_recording_users = room.user_recording_users or {};
                room.user_recording_users = user_recording_users;

                -- обновляем массив записывающих пользователей
                if action == 'inc' then
                    if not table_contains(user_recording_users, event_occupant_jid) then
                        table.insert(user_recording_users, event_occupant_jid);
                    end
                else
                    for i, recording_jid in ipairs(user_recording_users) do
                        if recording_jid == event_occupant_jid then
                            table.remove(user_recording_users, i);
                            break ;
                        end
                    end
                end

                -- отправляем сообщение каждому участнику о текущем количестве записывающих пользователей
                local recording_data = {
                    type = "user-recording-count",
                    value = #user_recording_users,
                };
                local recording_json_msg_str, error = json.encode(recording_data);
                if not recording_json_msg_str then
                    module:log('error', 'Error encoding data room:%s error:%s', room.jid, error);
                end
                for _, occupant_data in pairs(event.room._occupants) do
                    local recording_stanza = st.message({
                        from = room.jid,
                        to = occupant_data.jid
                    })
                                               :tag("json-message", { xmlns = "http://jitsi.org/jitmeet" })
                                               :text(recording_json_msg_str)
                                               :up();
                    room:route_stanza(recording_stanza);
                end
            end
        end
    end
end

-- Handle events on main muc module
run_when_component_loaded(main_muc_component_host, function(host_module, host_name)
    run_when_muc_module_loaded(host_module, host_name, function(main_muc, main_module)
        main_muc_service = main_muc;  -- so it can be accessed from breakout muc event handlers

        -- the following must run after speakerstats (priority -1)
        main_module:hook("muc-room-created", handle_main_room_created, -2);
        main_module:hook("muc-room-created", room_created, -3);  -- must run after handle_main_room_created
        main_module:hook("muc-occupant-joined", occupant_joined, -2);
        main_module:hook("muc-occupant-left", occupant_left, -2);
        main_module:hook("muc-room-destroyed", room_destroyed, -2);
        main_module:hook('muc-set-affiliation', occupant_affiliation_changed, -1);
        main_module:hook('muc-occupant-groupchat', occupant_groupchat, -4); -- must run after room_created
    end);
end);

-- Handle events on breakout muc module
run_when_component_loaded(breakout_muc_component_host, function(host_module, host_name)
    run_when_muc_module_loaded(host_module, host_name, function(_, breakout_module)

        -- the following must run after speakerstats (priority -1)
        breakout_module:hook("muc-room-created", handle_breakout_room_created, -2);
        breakout_module:hook("muc-room-created", room_created, -3); -- must run after handle_breakout_room_created
        breakout_module:hook("muc-occupant-joined", occupant_joined, -2);
        breakout_module:hook("muc-occupant-left", occupant_left, -2);
        breakout_module:hook("muc-room-destroyed", room_destroyed, -2);
    end);
end);