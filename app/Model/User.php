<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\DbConnection\Model\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model
{
    /**
     * @var string
     */
    public $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'email', 'password', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];
}
