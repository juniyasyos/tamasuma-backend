<?php

namespace App\Filament\Support;

use Filament\Pages\Dashboard;
use Illuminate\Support\Str;

class Breadcrumbs
{
    public static function panelDashboardUrl(): string
    {
        return Dashboard::getUrl();
    }

    public static function resourcePluralLabel(string $resourceClass): string
    {
        if (method_exists($resourceClass, 'getPluralModelLabel')) {
            return $resourceClass::getPluralModelLabel();
        }
        if (property_exists($resourceClass, 'navigationLabel') && !empty($resourceClass::$navigationLabel)) {
            return $resourceClass::$navigationLabel;
        }
        return Str::headline(class_basename($resourceClass));
    }

    public static function recordTitle(object $record): string
    {
        foreach (['title','name','label'] as $field) {
            if (isset($record->{$field}) && filled($record->{$field})) {
                return Str::limit((string) $record->{$field}, 40);
            }
        }
        return (string) ($record->getKey() ?? 'Record');
    }
}

