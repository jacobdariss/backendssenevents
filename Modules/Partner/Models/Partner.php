<?php

namespace Modules\Partner\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends BaseModel
{
    use SoftDeletes;

    protected $table = 'partners';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo_url',
        'description',
        'email',
        'phone',
        'website',
        'status',
        'allowed_content_types',
    ];

    protected $casts = [
        'allowed_content_types' => 'array',
    ];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = slug_format(trim($value));

        if (empty($value)) {
            $this->attributes['slug'] = slug_format(trim($this->attributes['name']));
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

