<?php

namespace App\Filament\Resources;

use Digitonic\FilamentNavigation\Filament\Resources\NavigationResource as BaseNavigationResource;

/**
 * Extend the vendor NavigationResource to keep it out of the sidebar
 * while still allowing relation managers or other features to use it.
 */
class NavigationResource extends BaseNavigationResource
{
    protected static bool $shouldRegisterNavigation = false;
}
