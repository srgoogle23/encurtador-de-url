<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

use Carbon\Carbon;
use Hyperf\DbConnection\Model\Model;

/**
 * @property string $user_id
 * @property string $url
 * @property string $shortened_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Link extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'links';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['user_id', 'url', 'shortened_url', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];
}
