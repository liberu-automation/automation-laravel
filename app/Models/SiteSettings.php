<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'currency',
        'default_language',
        'address',
        'country',
        'email',
        'phone_01',
        'phone_02',
        'phone_03',
        'phone_04',
        'facebook',
        'twitter',
        'github',
        'youtube',
        'sales_commission_percentage',
        'lettings_commission_percentage',
    ];
}