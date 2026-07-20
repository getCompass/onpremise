local jid = require "util.jid";
local json = require "cjson";
local basexx = require "basexx";

local function is_moderator_from_token(token)
    local dot1 = token:find("%.");
    if not dot1 then return false end
    local rest = token:sub(dot1 + 1)
    local dot2 = rest:find("%.")
    if not dot2 then return false end

    local bodyB64 = token:sub(dot1 + 1, dot1 + dot2 - 1)
    local body = json.decode(basexx.from_url64(bodyB64))
    return body["moderator"] == true
end

local function get_session(event, stanza)
    if event.origin then return event.origin end
    local from = stanza.attr.from
    if from and prosody and prosody.full_sessions then
        return prosody.full_sessions[from]
    end
    return nil
end

module:hook("muc-occupant-pre-join", function(event)
    local room, occupant, stanza = event.room, event.occupant, event.stanza
    local from_full = stanza and stanza.attr and stanza.attr.from
    if not (room and occupant and from_full) then return end

    local session = get_session(event, stanza)
    if not session or not session.auth_token then
        return
    end

    if not is_moderator_from_token(session.auth_token) then
        return
    end

    local bare = jid.prep(jid.bare(from_full))
    if not bare then return end

    local okA, errA = room:set_affiliation(true, bare, "owner")
    module:log("debug", "pre-join set_affiliation(owner): ok=%s err=%s bare=%s room=%s",
        tostring(okA), tostring(errA), tostring(bare), tostring(room.jid))
        
    occupant.affiliation = "owner"
    occupant.role = "moderator"
    room:save_occupant(occupant);

end, -4)
