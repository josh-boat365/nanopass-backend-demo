<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordPolicy extends Model
{
    protected $table = 'password_policies';

    protected $fillable = [
        'name',
        'description',
        'regex_pattern',
        'expiration',
    ];

    /**
     * Get the categories that use this password policy.
     */
    public function categories()
    {
        return $this->hasMany(PasswordCategory::class, 'password_policy_id');
    }
}
