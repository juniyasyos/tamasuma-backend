<?php

use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\SocialiteWasCalled;

it('registers the google socialite provider', function () {
    config([
        'services.google.client_id' => 'foo',
        'services.google.client_secret' => 'bar',
        'services.google.redirect' => 'http://localhost/callback',
    ]);

    $event = new SocialiteWasCalled(app(), app(ConfigRetrieverInterface::class));
    Event::dispatch($event);

    $provider = Socialite::driver('google');

    expect($provider)->toBeInstanceOf(\SocialiteProviders\Google\Provider::class);
});
