<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "name",
        "user_id",
        "size",
        "height",
        "width",
        "format"
    ];
    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    /**
     * Established relationship with users.
     * @return BelongsTo<User, Image>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
