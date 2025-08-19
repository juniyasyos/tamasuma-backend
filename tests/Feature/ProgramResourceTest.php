<?php

use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ProgramResource\Pages\CreateProgram;
use App\Filament\Resources\ProgramResource\Pages\EditProgram;
use App\Filament\Resources\ProgramResource\Pages\ViewProgram;
use App\Models\LearningArea;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function actingAsSuperAdmin(): User {
    test()->seed(ShieldSeeder::class);
    $role = Role::firstWhere('name', 'super_admin');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);
    test()->actingAs($user);
    return $user;
}

it('redirects guests from program create page', function () {
    $this->get(ProgramResource::getUrl('create'))
        ->assertRedirect('/login');
});

it('forbids non privileged users from program index', function () {
    $this->seed(ShieldSeeder::class);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $this->get(ProgramResource::getUrl('index'))
        ->assertForbidden();
});

it('allows viewing program details', function () {
    actingAsSuperAdmin();
    $learningArea = LearningArea::factory()->create(['name' => 'Science']);
    $program = Program::create([
        'learning_area_id' => $learningArea->id,
        'title' => 'Sample Program',
        'description' => 'Sample description for viewing test',
        'level' => 'pemula',
        'source' => 'internal',
        'is_certified' => true,
        'is_published' => true,
    ]);

    Livewire::test(ViewProgram::class, ['record' => $program->getKey()])
        ->assertStatus(200)
        ->assertSee('Sample Program');
});

it('updates a program via filament form', function () {
    actingAsSuperAdmin();
    $learningArea = LearningArea::factory()->create(['name' => 'Science']);
    $program = Program::create([
        'learning_area_id' => $learningArea->id,
        'title' => 'Original Title',
        'description' => 'Original description for update test',
        'level' => 'pemula',
        'source' => 'internal',
        'is_certified' => true,
        'is_published' => true,
    ]);

    Livewire::test(EditProgram::class, ['record' => $program->getKey()])
        ->fillForm([
            'learning_area_id' => $learningArea->id,
            'title' => 'Updated Title',
            'description' => 'Original description for update test',
            'level' => 'pemula',
            'source' => 'internal',
            'is_certified' => true,
            'is_published' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('programs', [
        'id' => $program->id,
        'title' => 'Updated Title',
    ]);
});

it('validates required fields when creating a program', function () {
    actingAsSuperAdmin();
    $learningArea = LearningArea::factory()->create(['name' => 'Science']);

    Livewire::test(CreateProgram::class)
        ->fillForm([
            'learning_area_id' => $learningArea->id,
            'level' => 'pemula',
            'description' => 'Desc desc desc desc desc',
            'source' => 'internal',
            'is_certified' => true,
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

