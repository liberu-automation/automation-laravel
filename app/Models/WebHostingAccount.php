<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebHostingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'username',
        'password',
        'control_panel',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}
