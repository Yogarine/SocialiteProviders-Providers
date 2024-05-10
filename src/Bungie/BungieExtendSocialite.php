<?php

namespace SocialiteProviders\Bungie;

use SocialiteProviders\Manager\SocialiteWasCalled;

class BungieExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('bungie', Provider::class);
    }
}
