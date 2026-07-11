<?php

namespace ThreeOhEight\Seo\Tests;

/**
 * Boots the package with the server card enabled; route registration happens
 * in the provider's boot, so the config must be set before the app boots.
 */
abstract class ServerCardTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('seo.server_card', [
            'enabled' => true,
            'card' => [
                'protocolVersion' => '2025-06-18',
                'serverInfo' => ['name' => 'test-server', 'version' => '1.0.0'],
                'transport' => ['type' => 'streamable-http', 'endpoint' => 'https://example.test/mcp'],
                'capabilities' => ['tools' => true],
                'authentication' => ['required' => true, 'method' => 'oauth2'],
            ],
        ]);
    }
}
