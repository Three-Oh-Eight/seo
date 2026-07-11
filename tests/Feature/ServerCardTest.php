<?php

namespace ThreeOhEight\Seo\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use ThreeOhEight\Seo\Tests\ServerCardTestCase;

/**
 * A plain PHPUnit class (not a Pest closure file) because it needs its own
 * TestCase: the server card must be enabled before the app boots, and Pest's
 * global uses() already binds the default TestCase to everything in Feature.
 */
class ServerCardTest extends ServerCardTestCase
{
    /** @return array<string, array{string}> */
    public static function probePaths(): array
    {
        return [
            'mcp.json' => ['/.well-known/mcp.json'],
            'mcp' => ['/.well-known/mcp'],
            'server-card.json' => ['/.well-known/mcp/server-card.json'],
        ];
    }

    #[DataProvider('probePaths')]
    public function test_it_serves_the_card_at_every_probe_path(string $path): void
    {
        $this->get($path)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('serverInfo.name', 'test-server')
            ->assertJsonPath('transport.endpoint', 'https://example.test/mcp')
            ->assertJsonPath('authentication.required', true);
    }

    public function test_it_serves_an_identical_payload_across_all_probe_paths(): void
    {
        $payloads = array_map(
            fn (array $args) => $this->get($args[0])->getContent(),
            self::probePaths(),
        );

        $this->assertCount(1, array_unique($payloads));
    }

    public function test_it_sends_the_discovery_headers(): void
    {
        $this->get('/.well-known/mcp.json')
            ->assertHeader('Cache-Control', 'max-age=3600, public')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Access-Control-Allow-Methods', 'GET');
    }

    public function test_it_returns_a_json_object_even_when_the_configured_card_is_empty(): void
    {
        config(['seo.server_card.card' => []]);

        $this->assertSame('{}', $this->get('/.well-known/mcp')->getContent());
    }
}
