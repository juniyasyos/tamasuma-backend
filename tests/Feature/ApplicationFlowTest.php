<?php

use App\Models\User;
use App\Models\LearningArea;
use App\Models\Program;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ShieldSeeder;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ProgramResource\Pages\CreateProgram;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login when accessing program index', function () {
    $this->get(ProgramResource::getUrl('index'))
        ->assertRedirect('/login');
});

it('allows authenticated users to access program index', function () {
    $this->seed(ShieldSeeder::class);
    $role = Role::firstWhere('name', 'super_admin');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);
    $this->actingAs($user);

    Livewire::test(\App\Filament\Resources\ProgramResource\Pages\ListPrograms::class)
        ->assertStatus(200);
});

it('ensures program belongs to learning area', function () {
    $learningArea = LearningArea::factory()->create(['name' => 'Science']);

    $program = Program::create([
        'learning_area_id' => $learningArea->id,
        'title' => 'Sample Program',
        'description' => 'Sample description',
        'level' => 'pemula',
        'source' => 'external',
        'platform' => 'Example',
        'external_url' => 'https://example.com',
        'is_certified' => true,
        'is_published' => true,
    ]);

    expect($program->learningArea->is($learningArea))->toBeTrue();
    expect($learningArea->programs)->toHaveCount(1);
});

it('seeds database with admin user', function () {
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseHas('users', [
        'email' => 'admin@admin.com',
    ]);
});

it('allows creating a program via filament form', function () {
    $this->seed(ShieldSeeder::class);
    $role = Role::firstWhere('name', 'super_admin');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);
    $this->actingAs($user);
    $learningArea = LearningArea::factory()->create(['name' => 'Science']);

    Livewire::test(CreateProgram::class)
        ->fillForm([
            'learning_area_id' => $learningArea->id,
            'level' => 'pemula',
            'title' => 'Filament Test Program',
            'description' => 'Desc desc desc desc desc',
            'source' => 'internal',
            'is_certified' => true,
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('programs', [
        'title' => 'Filament Test Program',
    ]);
});

