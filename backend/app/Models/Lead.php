<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use HasFactory;

    public const PERSONA_COLORS = ['green', 'yellow', 'pink'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'age_consent',
        'terms_consent',
        'marketing_consent',
        'persona_color',
        'has_visited_casino',
        'quiz_started_at',
        'answers',
        'redemption_code',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'age_consent' => 'boolean',
        'terms_consent' => 'boolean',
        'marketing_consent' => 'boolean',
        'has_visited_casino' => 'boolean',
        'quiz_started_at' => 'datetime',
        'answers' => 'array',
    ];

    protected $hidden = [
        'ip_address',
        'user_agent',
    ];

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function latestSmsLog(): HasOne
    {
        return $this->hasOne(SmsLog::class)->latestOfMany();
    }
}
