<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pages render without a front-end build (public/build): @vite and Vite::asset() print nothing.
        $this->withoutVite();

        // A failed sign-in waits out Laravel's timebox (200 ms, twice: attempt() and validate()) so
        // its timing does not tell a wrong password from an unknown account. Production keeps it;
        // the throttle tests would otherwise spend seconds sleeping. The guard is built lazily, so
        // it reads this value on first use.
        config(['auth.timebox_duration' => 0]);
    }

    /**
     * actingAs() signs the account in afresh, as from its own browser. Every request of a test
     * shares one session store, and AuthenticateSession keeps the signed-in account's password hash
     * there: the hash left by the account acted as before would end the new account's session.
     *
     * @param  string|null  $guard
     */
    public function be(Authenticatable $user, $guard = null): static
    {
        $this->app['session']->forget('password_hash_'.$this->app['auth']->getDefaultDriver());

        return parent::be($user, $guard);
    }
}
