<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()->record($this->getRecord())
            // <--
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // No custom sync needed; Repeater uses hasMany relationship directly.
}
