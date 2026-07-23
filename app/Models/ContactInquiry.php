<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    protected $fillable = [
        'name',
        'company',
        'email',
        'contact',
        'message',
        'admin_notified',
        'client_notified',
    ];

    protected $casts = [
        'admin_notified' => 'boolean',
        'client_notified' => 'boolean',
    ];
}
