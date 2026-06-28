<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebHostingAccount extends Model
{
    use HasFactory;

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
