-- Prosody IM
-- Copyright (C) 2017 Atlassian
--

local jid = require "util.jid";

-- сессии всех участников ноды
local sessions = prosody.full_sessions;

function urldecode(s)
    s = s:gsub('+', ' ')
         :gsub('%%(%x%x)', function(h)
        return string.char(tonumber(h, 16))
    end)
    return s
end

function parse(s)
    local ans = {}
    for k, v in s:gmatch('([^&=?]-)=([^&=?]+)') do
        ans[k] = urldecode(v)
    end
    return ans
end

--
local get_room_by_name_and_subdomain = module:require "util".get_room_by_name_and_subdomain;

-- истинный токен авторизации rest api запросов
local true_auth_token = module:get_option_string("auth_token")

--- хендлер создания комнат
function create_room(event)

    -- проверяем, что передали корректный токен
    local request_auth_token = event.request.headers["authorization"]
    if not request_auth_token or request_auth_token ~= true_auth_token then
        return { status_code = 401; body = "Unauthorized" }
    end

    -- если не передали параметры
    if (not event.request.url.query) then
        return { status_code = 400; };
    end
    local params = parse(event.request.url.query);
    if not params["room"] or not params["lobby_enabled"] then
        return { status_code = 400; body = "Missing required parameters" }
    end

    -- проверяем существование комнаты с таким идентификатором
    local room = get_room_by_name_and_subdomain(params["room"]);
    if room then

        module:log("info", "Room with passed id (%s) already exists", params["room"]);
        return { status_code = 409; };
    end

    local muc_domain_prefix = module:get_option_string("muc_mapper_domain_prefix", "conference");
    local muc_domain_base = module:get_option_string("muc_mapper_domain_base", module.host);
    local muc_domain = module:get_option_string("muc_mapper_domain", muc_domain_prefix .. "." .. muc_domain_base);

    -- получаем название комнаты, которую намереваемся создать
    local room_name = params["room"];
    local lobby_enabled = params["lobby_enabled"];
    local lobby_password = params["lobby_password"];
    local room_address = jid.join(room_name, muc_domain);

    local component = hosts[muc_domain];
    if component then
        local muc = component.modules.muc;
        local room, err = muc.create_room(room_address);
        if room then

            room.quality_level = "high";

            -- если нужно включить лобби
            if lobby_enabled == "true" or lobby_enabled == "1" then

                enable_room_lobby(room_name, lobby_password)
            end

            return { status_code = 200; };
        else

            -- если не удалось создать комнату, то формируем ошибку
            local err_str = "Room (id: %s, address: %s) is not created";
            local err_str_values = { params["room"], room_address }
            if err ~= nil then

                err_str = err_str .. ", err: %s"
                table.insert(err_str_values, err)
            end

            -- собираем текст ошибки и логируем
            local unpack = table.unpack or unpack
            local err_str_formatted = string.format(err_str, unpack(err_str_values))
            module:log("warn", err_str_formatted)

            return { status_code = 500; body = err_str_formatted };
        end
    else
        return { status_code = 404; };
    end
end

--- хендлер изменения комнаты
function change_room(event)

    -- проверяем, что передали корректный токен
    local request_auth_token = event.request.headers["authorization"]
    if not request_auth_token or request_auth_token ~= true_auth_token then
        return { status_code = 401; body = "Unauthorized" }
    end

    -- если не передали параметры
    if (not event.request.url.query) then
        return { status_code = 400; };
    end

    -- получаем параметры
    local params = parse(event.request.url.query);

    -- если не передали действие
    if not params["action"] or not params["room"] then
        return { status_code = 400; body = "Missing required parameters" }
    end

    -- проверяем, что переданная команда существует
    local room_name = params["room"];
    local room = get_room_by_name_and_subdomain(room_name);
    if not room then
        return { status_code = 404; };
    end

    -- проверяем параметр action и вызываем соответствующую функцию
    if params["action"] == "enable_room_lobby" then

        if not params["lobby_password"] then
            return { status_code = 400; body = "Missing lobby_password parameter" }
        end
        local lobby_password = params["lobby_password"];

        enable_room_lobby(room_name, lobby_password)
    elseif params["action"] == "disable_room_lobby" then
        disable_room_lobby(room_name)
    elseif params["action"] == "kick_member" then

        if not params["member_id"] then
            return { status_code = 400; body = "Missing member_id parameter" }
        end
        local member_id = params["member_id"];

        kick_member(room_name, member_id)
    else
        return { status_code = 400; body = "Invalid action" }
    end

    return { status_code = 200; body = "Action executed successfully" }
end

--- функция включения лобби (зала ожидания) в комнате
function enable_room_lobby(room_name, room_password)

    -- опускаем проверку корректности токена, поскольку данная функция запускается из change_room, где эта проверка уже произведена

    -- получаем комнату
    local room = get_room_by_name_and_subdomain(room_name);
    room:set_password(room_password);

    -- включаем лобби
    prosody.events.fire_event("create-persistent-lobby-room", { room = room; });
end

--- функция отключения лобби (зала ожидания) в комнате
function disable_room_lobby(room_name)

    -- опускаем проверку корректности токена, поскольку данная функция запускается из change_room, где эта проверка уже произведена

    -- получаем комнату
    local room = get_room_by_name_and_subdomain(room_name);

    -- отключаем лобби
    room._data.lobby_deactivated = true
    room:set_members_only(false)
    room:set_password(nil)
    prosody.events.fire_event('destroy-lobby-room', {
        room = room,
        newjid = room.jid,
    });
end

--- функция исключения участника из комнаты
function kick_member(room_name, member_id)

    -- опускаем проверку корректности токена, поскольку данная функция запускается из change_room, где эта проверка уже произведена

    -- получаем комнату
    local room = get_room_by_name_and_subdomain(room_name);

    ------ пробегаемся по всем участникам
    for _, occupant in room:each_occupant() do

        -- получаем сессию участника
        local occupant_session = sessions[occupant.jid];

        -- если нашли сессию участника
        if occupant_session ~= nil then

            -- если это сессия участника, которого намереваемся кикнуть
            if occupant_session.jitsi_meet_context_user ~= nil and occupant_session.jitsi_meet_context_user.id == member_id then

                -- кикаем
                room:set_role(true, occupant.nick, nil)

                -- завершаем выполнение функции
                return
            end
        end
    end
end

--- хендлер удаления комнат
function destroy_room(event)

    -- проверяем, что передали корректный токен
    local request_auth_token = event.request.headers["authorization"]
    if not request_auth_token or request_auth_token ~= true_auth_token then
        return { status_code = 401; body = "Unauthorized" }
    end

    -- если не передали параметры
    if (not event.request.url.query) then
        return { status_code = 400; };
    end

    -- получаем название комнаты, которую намереваемся удалить
    local params = parse(event.request.url.query);
    local room_name = params["room"];

    -- удаляем комнату, если нашли
    local room = get_room_by_name_and_subdomain(room_name);
    if not room then
        return { status_code = 404; };
    end

    room:destroy();
    return { status_code = 200; };
end

function module.load()
    module:depends("http");
    module:provides("http", {
        default_path = "/";
        route = {
            ["DELETE api/room"] = destroy_room;
            ["PUT api/room"] = create_room;
            ["PATCH api/room"] = change_room;
        };
    });
end