{{ $C2S_REQUIRE_ENCRYPTION := .Env.PROSODY_C2S_REQUIRE_ENCRYPTION | default "1" | toBool -}}
{{ $ENABLE_AUTH := .Env.ENABLE_AUTH | default "0" | toBool -}}
{{ $ENABLE_GUEST_DOMAIN := and $ENABLE_AUTH (.Env.ENABLE_GUESTS | default "0" | toBool) -}}
{{ $ENABLE_VISITORS := .Env.ENABLE_VISITORS | default "0" | toBool -}}
{{ $ENABLE_S2S := or $ENABLE_VISITORS ( .Env.PROSODY_ENABLE_S2S | default "0" | toBool ) }}
{{ $ENABLE_IPV6 := .Env.ENABLE_IPV6 | default "true" | toBool -}}
{{ $GC_TYPE := .Env.GC_TYPE | default "incremental" -}}
{{ $GC_INC_TH := .Env.GC_INC_TH | default 400 -}}
{{ $GC_INC_SPEED := .Env.GC_INC_SPEED | default 250 -}}
{{ $GC_INC_STEP_SIZE := .Env.GC_INC_STEP_SIZE | default 13 -}}
{{ $GC_GEN_MIN_TH := .Env.GC_GEN_MIN_TH | default 20 -}}
{{ $GC_GEN_MAX_TH := .Env.GC_GEN_MAX_TH | default 100 -}}
{{ $LOG_LEVEL := .Env.LOG_LEVEL | default "info" }}
{{ $PROSODY_C2S_LIMIT := .Env.PROSODY_C2S_LIMIT | default "10kb/s" -}}
{{ $PROSODY_METRICS_ALLOWED_CIDR := .Env.PROSODY_METRICS_ALLOWED_CIDR | default "172.16.0.0/12" -}}
{{ $PROSODY_HTTP_PORT := .Env.PROSODY_HTTP_PORT | default "5280" -}}
{{ $PROSODY_ENABLE_METRICS := .Env.PROSODY_ENABLE_METRICS | default "false" | toBool -}}
{{ $PROSODY_ENABLE_STANZA_COUNTS := .Env.PROSODY_ENABLE_STANZA_COUNTS | default "false" | toBool -}}
{{ $PROSODY_ADMINS := .Env.PROSODY_ADMINS | default "" -}}
{{ $PROSODY_ADMIN_LIST := splitList "," $PROSODY_ADMINS -}}
{{ $TRUSTED_PROXIES := .Env.PROSODY_TRUSTED_PROXIES | default "127.0.0.1,::1" -}}
{{ $TRUSTED_PROXY_LIST := splitList "," $TRUSTED_PROXIES -}}
{{ $PROSODY_S2S_LIMIT := .Env.PROSODY_S2S_LIMIT | default "30kb/s" -}}
{{ $S2S_PORT := .Env.PROSODY_S2S_PORT | default "5269" }}
{{ $VISITORS_MUC_PREFIX := .Env.PROSODY_VISITORS_MUC_PREFIX | default "muc" -}}
{{ $VISITORS_XMPP_DOMAIN := .Env.VISITORS_XMPP_DOMAIN | default "meet.jitsi" -}}
{{ $VISITORS_XMPP_SERVER := .Env.VISITORS_XMPP_SERVER | default "" -}}
{{ $VISITORS_XMPP_SERVERS := splitList "," $VISITORS_XMPP_SERVER -}}
{{ $VISITORS_XMPP_PORT := .Env.VISITORS_XMPP_PORT | default 52220 }}
{{ $VISITORS_S2S_PORT := .Env.VISITORS_S2S_PORT | default 52690 }}
{{ $XMPP_DOMAIN := .Env.XMPP_DOMAIN | default "meet.jitsi" -}}
{{ $XMPP_GUEST_DOMAIN := .Env.XMPP_GUEST_DOMAIN | default "guest.meet.jitsi" -}}
{{ $XMPP_MUC_DOMAIN := .Env.XMPP_MUC_DOMAIN | default "muc.meet.jitsi" -}}
{{ $XMPP_PORT := .Env.XMPP_PORT | default "5222" -}}
{{ $MAIN_XMPP_SERVER := .Env.MAIN_XMPP_SERVER | default "" -}}
{{ $MAIN_S2S_PORT := .Env.MAIN_S2S_PORT | default 5269 -}}

-- Prosody Example Configuration File
--
-- Information on configuring Prosody can be found on our
-- website at http://prosody.im/doc/configure
--
-- Tip: You can check that the syntax of this file is correct
-- when you have finished by running: luac -p prosody.cfg.lua
-- If there are any errors, it will let you know what and where
-- they are, otherwise it will keep quiet.
--
-- The only thing left to do is rename this file to remove the .dist ending, and fill in the
-- blanks. Good luck, and happy Jabbering!


---------- Server-wide settings ----------
-- Settings in this section apply to the whole server and are the default settings
-- for any virtual hosts

-- This is a (by default, empty) list of accounts that are admins
-- for the server. Note that you must create the accounts separately
-- (see http://prosody.im/doc/creating_accounts for info)
-- Example: admins = { "user1@example.com", "user2@example.net" }
admins = { {{ if .Env.PROSODY_ADMINS }}{{ range $index, $element := $PROSODY_ADMIN_LIST -}}{{ if $index }}, {{ end }}"{{ $element }}"{{ end }}{{ end }} }
-- Enable use of libevent for better performance under high load
-- For more information see: http://prosody.im/doc/libevent
--use_libevent = true;

-- This is the list of modules Prosody will load on startup.
-- It looks for mod_modulename.lua in the plugins folder, so make sure that exists too.
-- Documentation on modules can be found at: http://prosody.im/doc/modules
modules_enabled = {

        -- Generally required
                "roster"; -- Allow users to have a roster. Recommended ;)
                "saslauth"; -- Authentication for clients and servers. Recommended if you want to log in.
                "tls"; -- Add support for secure TLS on c2s/s2s connections
                "disco"; -- Service discovery
{{ if eq .Env.PROSODY_MODE "client" -}}
        -- Not essential, but recommended
                "private"; -- Private XML storage (for room bookmarks, etc.)
                "limits"; -- Enable bandwidth limiting for XMPP connections

        -- These are commented by default as they have a performance impact
                --"privacy"; -- Support privacy lists
                --"compression"; -- Stream compression (Debian: requires lua-zlib module to work)

        -- Admin interfaces
                "admin_adhoc"; -- Allows administration via an XMPP client that supports ad-hoc commands
                --"admin_telnet"; -- Opens telnet console interface on localhost port 5582

        -- Nice to have
                "version"; -- Replies to server version requests
                "uptime"; -- Report how long server has been running
                "time"; -- Let others know the time here on this server
{{ end -}}
                "ping"; -- Replies to XMPP pings with pongs
{{ if eq .Env.PROSODY_MODE "visitors" -}}
                "limits"; -- Enable bandwidth limiting for XMPP connections
{{ end -}}
        -- HTTP modules
                --"bosh"; -- Enable BOSH clients, aka "Jabber over HTTP"
                --"http_files"; -- Serve static files from a directory over HTTP

        -- Other specific functionality
                "posix"; -- POSIX functionality, sends server to background, enables syslog, etc.
                --"groups"; -- Shared roster support
                --"announce"; -- Send announcement to all online users
                --"welcome"; -- Welcome users who register accounts
                --"watchregistrations"; -- Alert admins of registrations
                --"motd"; -- Send a message to users when they log in
                --"legacyauth"; -- Legacy authentication. Only used by some old clients and bots.
                "http_health";
                {{ if eq .Env.PROSODY_MODE "brewery" -}}
                "firewall"; -- Enable firewalling
                "secure_interfaces";
                {{ end -}}
                {{ if $ENABLE_S2S -}}
                "dialback"; -- s2s dialback support
                "s2s_bidi";
                "certs_s2soutinjection";
                "s2sout_override";
                "s2s_whitelist";
                {{ end -}}

                {{ if $PROSODY_ENABLE_METRICS }}
                -- metrics collection functionality
                "http_openmetrics";
                {{ end -}}

                {{ if $PROSODY_ENABLE_STANZA_COUNTS }}
                -- Stanza count metrics for monitoring
                "measure_stanza_counts";
                {{ end -}}

                {{ if .Env.GLOBAL_MODULES }}
        "{{ join "\";\n\"" (splitList "," .Env.GLOBAL_MODULES) }}";
        {{ end }}
};

component_ports = { }
https_ports = { }

trusted_proxies = {
{{ range $index, $proxy := $TRUSTED_PROXY_LIST }}
  "{{ $proxy }}";
{{ end }}
}

{{ if eq .Env.PROSODY_MODE "brewery" -}}
firewall_scripts = {
    "/config/rules.d/jvb_muc_presence_filter.pfw";
};
{{ end -}}

-- These modules are auto-loaded, but should you want
-- to disable them then uncomment them here:
modules_disabled = {
    "offline"; -- Store offline messages
    "register";
        -- "c2s"; -- Handle client connections

        {{ if not $ENABLE_S2S -}}
        "s2s"; -- Handle server-to-server connections
        {{ end -}}
};

-- Disable account creation by default, for security
-- For more information see http://prosody.im/doc/creating_accounts
allow_registration = false;

{{ if ne .Env.PROSODY_MODE "brewery" -}}
-- Enable rate limits for incoming client and server connections
limits = {
{{ if ne $PROSODY_C2S_LIMIT "" }}
  c2s = {
    rate = "{{ $PROSODY_C2S_LIMIT }}";
  };
{{ end }}
{{ if ne $PROSODY_S2S_LIMIT "" }}
  s2sin = {
    rate = "{{ $PROSODY_S2S_LIMIT }}";
  };
{{ end }}
}
{{ end -}}

--Prosody garbage collector settings
--For more information see https://prosody.im/doc/advanced_gc
{{ if eq $GC_TYPE "generational" }}
gc = {
    mode = "generational";
    minor_threshold = {{ $GC_GEN_MIN_TH }};
    major_threshold = {{ $GC_GEN_MAX_TH }};
}
{{ else }}
gc = {
        mode = "incremental";
        threshold = {{ $GC_INC_TH }};
        speed = {{ $GC_INC_SPEED }};
        step_size = {{ $GC_INC_STEP_SIZE }};
}
{{ end }}

pidfile = "/config/data/prosody.pid";

-- Force clients to use encrypted connections? This option will
-- prevent clients from authenticating unless they are using encryption.

c2s_require_encryption = {{ $C2S_REQUIRE_ENCRYPTION }};

-- set c2s port
c2s_ports = { {{ $XMPP_PORT }} } -- Listen on specific c2s port
{{ if $ENABLE_IPV6 }}
c2s_interfaces = { "*", "::" }
{{ else }}
c2s_interfaces = { "*" }
{{ end }}

{{ if $ENABLE_S2S -}}
-- set s2s port
s2s_ports = { {{ $S2S_PORT }} } -- Listen on specific s2s port

{{ if eq .Env.PROSODY_MODE "visitors" -}}
s2s_whitelist = {
        {{ if $ENABLE_VISITORS -}}
    '{{ $XMPP_MUC_DOMAIN }}'; -- needed for visitors to send messages to main room
    'visitors.{{ $XMPP_DOMAIN }}'; -- needed for sending promotion request to visitors.{{ $XMPP_DOMAIN }} component
    '{{ $XMPP_DOMAIN }}'; -- unavailable presences back to main room

        {{ end -}}
        {{ if $ENABLE_GUEST_DOMAIN -}}
    '{{ $XMPP_GUEST_DOMAIN }}';
        {{ end -}}
}
{{ end -}}

{{ end -}}

{{ if $ENABLE_VISITORS -}}
{{ if $.Env.VISITORS_XMPP_SERVER -}}
s2sout_override = {
{{ if eq .Env.PROSODY_MODE "visitors" -}}
        ["{{ $XMPP_MUC_DOMAIN }}"] = "tcp://{{ $MAIN_XMPP_SERVER }}:{{ $MAIN_S2S_PORT }}"; -- needed for visitors to send messages to main room
        ["{{ $XMPP_DOMAIN }}"] = "tcp://{{ $MAIN_XMPP_SERVER }}:{{ $MAIN_S2S_PORT }}";
        ["visitors.{{ $XMPP_DOMAIN }}"] = "tcp://{{ $MAIN_XMPP_SERVER }}:{{ $MAIN_S2S_PORT }}";
{{ if $ENABLE_GUEST_DOMAIN -}}
        ["{{ $XMPP_GUEST_DOMAIN }}"] = "tcp://{{ $MAIN_XMPP_SERVER }}:{{ $MAIN_S2S_PORT }}";
{{ end -}}
{{ else }}
{{ range $index, $element := $VISITORS_XMPP_SERVERS -}}
{{ $SERVER := splitn ":" 2 $element }}
{{ $DEFAULT_PORT := add $VISITORS_S2S_PORT $index }}
        ["{{ $VISITORS_MUC_PREFIX }}.v{{ $index }}.{{ $VISITORS_XMPP_DOMAIN }}"] = "tcp://{{ $SERVER._0 }}:{{ $SERVER._1 | default $DEFAULT_PORT }}";
        ["v{{ $index }}.{{ $VISITORS_XMPP_DOMAIN }}"] = "tcp://{{ $SERVER._0 }}:{{ $SERVER._1 | default $DEFAULT_PORT }}";
{{ end -}}
{{ end -}}
};
{{ if ne .Env.PROSODY_MODE "visitors" -}}
s2s_whitelist = {
{{ if eq .Env.PROSODY_MODE "visitors" -}}
    '{{ $XMPP_MUC_DOMAIN }}', -- needed for visitors to send messages to main room
    'visitors.{{ $XMPP_DOMAIN }}'; -- needed for sending promotion request to visitors.compasstalk.ru component
    '{{ $XMPP_DOMAIN }}'; -- unavailable presences back to main room
{{ else }}
{{ range $index, $element := $VISITORS_XMPP_SERVERS -}}
        "{{ $VISITORS_MUC_PREFIX }}.v{{ $index }}.{{ $VISITORS_XMPP_DOMAIN }}";
        "v{{ $index }}.{{ $VISITORS_XMPP_DOMAIN }}";
{{ end -}}
"visitors.{{ $VISITORS_XMPP_DOMAIN }}"
{{ end -}}
};
{{ end -}}
{{ end -}}
{{ end -}}


-- Force certificate authentication for server-to-server connections?
-- This provides ideal security, but requires servers you communicate
-- with to support encryption AND present valid, trusted certificates.
-- NOTE: Your version of LuaSec must support certificate verification!
-- For more information see http://prosody.im/doc/s2s#security

s2s_secure_auth = false

-- Many servers don't support encryption or have invalid or self-signed
-- certificates. You can list domains here that will not be required to
-- authenticate using certificates. They will be authenticated using DNS.

--s2s_insecure_domains = { "gmail.com" }

-- Even if you leave s2s_secure_auth disabled, you can still require valid
-- certificates for some domains by specifying a list here.

--s2s_secure_domains = { "jabber.org" }

-- Select the authentication backend to use. The 'internal' providers
-- use Prosody's configured data storage to store the authentication data.
-- To allow Prosody to offer secure authentication mechanisms to clients, the
-- default provider stores passwords in plaintext. If you do not trust your
-- server please see http://prosody.im/doc/modules/mod_auth_internal_hashed
-- for information about using the hashed backend.

authentication = "internal_hashed"

-- Select the storage backend to use. By default Prosody uses flat files
-- in its configured data directory, but it also supports more backends
-- through modules. An "sql" backend is included by default, but requires
-- additional dependencies. See http://prosody.im/doc/storage for more info.

--storage = "sql" -- Default is "internal" (Debian: "sql" requires one of the
-- lua-dbi-sqlite3, lua-dbi-mysql or lua-dbi-postgresql packages to work)

-- For the "sql" backend, you can uncomment *one* of the below to configure:
--sql = { driver = "SQLite3", database = "prosody.sqlite" } -- Default. 'database' is the filename.
--sql = { driver = "MySQL", database = "prosody", username = "prosody", password = "secret", host = "localhost" }
--sql = { driver = "PostgreSQL", database = "prosody", username = "prosody", password = "secret", host = "localhost" }

-- Logging configuration
-- For advanced logging see http://prosody.im/doc/logging
--
-- Debian:
--  Logs info and higher to /var/log
--  Logs errors to syslog also
log = {
        { levels = {min = "{{ $LOG_LEVEL }}"}, timestamps = "%Y-%m-%d %X", to = "console"};
{{ if .Env.PROSODY_LOG_CONFIG }}
        {{ join "\n" (splitList "\\n" .Env.PROSODY_LOG_CONFIG) }}
{{ end }}
}

{{ if $PROSODY_ENABLE_METRICS }}
-- Statistics Provider configuration
statistics = "internal"
statistics_interval = "manual"
openmetrics_allow_cidr = "{{ $PROSODY_METRICS_ALLOWED_CIDR }}"
{{ end }}

{{ if .Env.GLOBAL_CONFIG }}
{{ join "\n" (splitList "\\n" .Env.GLOBAL_CONFIG) }}
{{ end }}

-- Enable use of native prosody 0.11 support for epoll over select
network_backend = "epoll";
-- Set the TCP backlog to 511 since the kernel rounds it up to the next power of 2: 512.
network_settings = {
  tcp_backlog = 511;
}
unbound = {
    resolvconf = true
}

http_ports = { {{ $PROSODY_HTTP_PORT }} }
{{ if $ENABLE_IPV6 }}
http_interfaces = { "*", "::" }
{{ else }}
http_interfaces = { "*" }
{{ end }}

data_path = "/config/data"

Include "conf.d/*.cfg.lua"