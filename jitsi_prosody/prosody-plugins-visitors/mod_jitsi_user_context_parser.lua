local formdecode = require "util.http".formdecode;
local token_util = module:require "token/util".new(module);

-- захватим момент создания сессии
module:hook_global("bosh-session", function(event)
    local session, request = event.session, event.request;
    local query = request.url.query;

    -- логика получения из mod_auth_token.lua, функция init_session
    local token = nil;

    -- extract token from Authorization header
    if request.headers["authorization"] then
        -- assumes the header value starts with "Bearer "
        token = request.headers["authorization"]:sub(8,#request.headers["authorization"])
    end

    -- allow override of token via query parameter
    if query ~= nil then
        local params = formdecode(query);

        -- The following fields are filled in the session, by extracting them
        -- from the query and no validation is being done.
        -- After validating auth_token will be cleaned in case of error and few
        -- other fields will be extracted from the token and set in the session

        if query and params.token then
            token = params.token;
        end
    end

    -- in either case set auth_token in the session
    session.auth_token = token;

    -- валидируем, чтобы записать jitsi_context_user в occupant
    local ok, err, reason = token_util:process_and_verify_token(session);
    if not ok then
        module:log("warn", "JWT parse failed: %s %s", err, reason);
    end
    session.auth_token = nil;
end);