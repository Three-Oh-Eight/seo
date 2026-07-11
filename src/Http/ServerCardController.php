<?php

namespace ThreeOhEight\Seo\Http;

use Illuminate\Http\JsonResponse;

/**
 * Serves the MCP server card configured under seo.server_card.card, verbatim,
 * with the headers agent clients expect: cacheable for an hour, safe content
 * type handling, and permissive CORS (the document is public by definition).
 */
class ServerCardController
{
    public function __invoke(): JsonResponse
    {
        return response()
            ->json((object) config('seo.server_card.card', []))
            ->withHeaders([
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET',
            ]);
    }
}
