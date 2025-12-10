<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordCategory extends Model
{
    protected $table = 'passwords_category';

    protected $fillable = [
        'name',
        'description',
        'password_policy_id',
    ];

    /**
     * Get the password policy that this category belongs to.
     */
    public function policy()
    {
        return $this->belongsTo(PasswordPolicy::class, 'password_policy_id');
    }

    /**
     * Get the system passwords in this category.
     */
    public function passwords()
    {
        return $this->hasMany(SystemPassword::class, 'passwords_category_id');
    }
}
