local json = require "util.json";
local http = require "net.http";
local st = require "util.stanza";
local hashes = require "util.hashes";
local async = require "util.async"
local util = module:require 'util';
local jid = require "util.jid";
local get_room_from_jid = util.get_room_from_jid;

local main_muc_component_host = module:get_option_string("muc_component");

local SOCKET_URL_JITSI = module:get_option("entrypoint_jitsi", "") .. "/api/socket/jitsi/"
local IS_DLP_ENABLED = module:get_option("is_dlp_enabled", false)
local SOCKET_KEY_PROSODY = module:get_option("socket_key_prosody", "")

local MESSAGE_STATUS_APPROVED = "approved"
local MESSAGE_STATUS_RESTRICTED = "restricted"

local REQUEST_TIMEOUT = 2

local function can_send_message(from, to, payload_str)
    if not IS_DLP_ENABLED then
        return true
    end

    local json_params = json.encode({ payload = payload_str })
    local ar_post = {
        method = "conference.performDlpCheck",
        user_id = "0",
        sender_module = "prosody",
        json_params = json_params,
        signature = hashes.md5(SOCKET_KEY_PROSODY .. json_params, true)
    }

    local form_body = http.formencode(ar_post)

    --  POST-запрос с телом и заголовками
    local request = http.request(SOCKET_URL_JITSI, {
        method = "POST",
        body = form_body,
        headers = {
            ["Content-Type"] = "application/x-www-form-urlencoded",
            ["Content-Length"] = tostring(#form_body)
        },
        insecure = true
    })

    local result, err = async.wait_for(request, REQUEST_TIMEOUT)

    if err then
        -- Таймаут или ошибка сети - разрешаем отправку по умолчанию
        module:log("warn", "DLP API error for message from %s to %s", from, to);
        return true;
    end

    local response = json.decode(result.body)

    if result.code ~= 200 or type(response) ~= "table" or response.status == "error" then
        return false;
    end

    return true
end

local function send_stanza(room, to_jid, stanza_type, message_status)
    local message_status_data = {
        type = stanza_type,
        status = message_status,

    }

    local message_status_data_str, error = json.encode(message_status_data);

    local message_status_stanza = st.message({
            from = room.jid,
            to = to_jid
        })
        :tag("json-message", { xmlns = "http://jitsi.org/jitmeet" })
        :text(message_status_data_str)
        :up();

    room:route_stanza(message_status_stanza)
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

-- обработать опросы
local function get_poll_payload(json_data)
    local data, error = json.decode(json_data);
    if not data or data.type ~= "new-poll" then
        if error then
            module:log('error', 'Error decoding data error:%s', error);
        end
        return nil;
    end

    local compact_answers = {}
    for i, name in ipairs(data.answers) do
        table.insert(compact_answers, { key = i, name = name });
    end

    return {
        question = data.question,
        answers = compact_answers
    }
end

-- обработать текст
local function get_message_payload(body)
    local json_data = json.decode(body)

    if type(json_data) == "table" and json_data.payload ~= nil and json_data.payload.type == "TYPE_DEBUG_MESSAGE" then
        return nil;
    end

    return {
        text = body
    }
end

-- функция для обработки сообщений
local function process_message(event)

    local stanza = event.stanza;

    local from_jid = stanza.attr.from;
    local room = get_room_from_jid(jid.bare(event.stanza.attr.to));

    if stanza.attr.type ~= "groupchat" and stanza.attr.type ~= "chat" then
        return nil;
    end

    local json_data = stanza:get_child_text("json-message", "http://jitsi.org/jitmeet");
    local body = stanza:get_child_text("body");
    local payload = nil
    local stanza_type = "message-status-updated"

    if json_data ~= nil then
        payload = get_poll_payload(json_data)
        stanza_type = "poll-status-updated"
    end

    if json_data == nil and body ~= nil then
        payload = get_message_payload(body)
    end

    if payload == nil then
        return nil
    end

    local payload_str = json.encode(payload)
    local is_allowed = can_send_message(from_jid, room.jid, payload_str)

    -- если нельзя отправить сообщение, то завершаем цепочку
    if not is_allowed then
        send_stanza(room, from_jid, stanza_type, MESSAGE_STATUS_RESTRICTED)
        return true;
    end

    -- отправляем ивент, что сообщение аппрувнуто
    send_stanza(room, from_jid, stanza_type, MESSAGE_STATUS_APPROVED)
end;

-- Handle events on main muc module
run_when_component_loaded(main_muc_component_host, function(host_module, host_name)
    run_when_muc_module_loaded(host_module, host_name, function(main_muc, main_module)
        main_muc_service = main_muc; -- so it can be accessed from breakout muc event handlers

        -- самый высокий приоритет, чтобы сообщение нигде не успело сохраниться
        main_module:hook("message/full", process_message, 99);
        main_module:hook("message/bare", process_message, 99);
    end);
end);
