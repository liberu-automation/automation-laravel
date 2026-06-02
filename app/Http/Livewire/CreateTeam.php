<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;

class CreateTeam extends CreateTeamForm
{
    public function createTeam(CreatesTeams $creator)
    {
        $this->resetErrorBag();

        $team = $creator->create(Auth::user(), $this->state);

        return redirect()->route('filament.pages.edit-team', ['team' => $team]);
    }
}
