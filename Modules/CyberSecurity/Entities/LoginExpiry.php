<?php

namespace Modules\CyberSecurity\Entities;

use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginExpiry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

}
