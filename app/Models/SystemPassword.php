<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemPassword extends Model
{
    protected $fillable = [
        'name',
        'password_hash',
        'description',
        'passwords_category_id',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the category that this system password belongs to.
     */
    public function category()
    {
        return $this->belongsTo(PasswordCategory::class, 'passwords_category_id');
    }

    /**
     * Get the users that have been assigned this system password.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_system_passwords', 'system_password_id', 'user_id')->withTimestamps();
    }
}
