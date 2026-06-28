<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebHostingAccount extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Defense-in-depth: while a Filament tenant is active, scope every query
        // to it and stamp new records with it. No tenant (console/jobs) => no-op.
        static::addGlobalScope('tenant', function (Builder $query) {
            if ($tenant = self::currentTenant()) {
                $query->where($query->getModel()->getTable().'.team_id', $tenant->getKey());
            }
        });

        static::creating(function (self $account) {
            if ($account->team_id === null && $tenant = self::currentTenant()) {
                $account->team_id = $tenant->getKey();
            }
        });
    }

    private static function currentTenant(): ?Team
    {
        $tenant = Filament::getCurrentPanel() ? Filament::getTenant() : null;

        return $tenant instanceof Team ? $tenant : null;
    }

    protected $fillable = [
        'team_id',
        'domain',
        'username',
        'password',
        'control_panel',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
