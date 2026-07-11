<?php

return [
    'site_name' => env('APP_NAME', 'Laravel'),
    'separator' => ' - ',
    'title' => null,
    'description' => 'A Laravel application.',
    'auto_canonical' => true,
    'robots' => null,
    'og_type' => 'website',
    'og_image' => '/android-chrome-512x512.png',
    'twitter_card' => 'summary_large_image',
    'twitter_image' => '/android-chrome-512x512.png',
    'twitter_site' => null,

    'canonical_strip' => [],

    /*
    |--------------------------------------------------------------------------
    | MCP server card (agent discovery)
    |--------------------------------------------------------------------------
    |
    | Opt-in discovery document for an MCP server hosted by this application,
    | served at /.well-known/mcp.json, /.well-known/mcp and
    | /.well-known/mcp/server-card.json (SEP-2127's canonical path has moved
    | between draft revisions, so all three probe paths answer identically).
    | The card is emitted verbatim, so the application controls the exact
    | field shape and this package never has to chase spec drift. Keep the
    | shape aligned with the current SEP-2127 text (protocolVersion,
    | serverInfo.name/version, transport.type/endpoint, capabilities,
    | authentication.required).
    |
    */
    'server_card' => [
        'enabled' => false,
        'card' => [],
    ],
];
