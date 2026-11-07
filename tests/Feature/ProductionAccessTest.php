<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionAccessTest extends TestCase
{
    public function test_production_requires_configured_credentials(): void
    {
        $this->app->instance('env', 'production');
        config(['app.access_username' => '', 'app.access_password' => '']);
        $this->get('/up')->assertStatus(503);
    }

    public function test_production_rejects_invalid_credentials_and_accepts_valid_credentials(): void
    {
        $this->app->instance('env', 'production');
        config(['app.access_username' => 'owner', 'app.access_password' => 'test-password']);
        $this->get('/up')->assertUnauthorized()->assertHeader('WWW-Authenticate');
        $this->withServerVariables(['PHP_AUTH_USER' => 'owner', 'PHP_AUTH_PW' => 'wrong'])->get('/up')->assertUnauthorized();
        $this->withServerVariables(['PHP_AUTH_USER' => 'owner', 'PHP_AUTH_PW' => 'test-password'])->get('/up')->assertOk();
    }

    public function test_local_development_does_not_require_credentials(): void
    {
        $this->get('/up')->assertOk();
    }
}
