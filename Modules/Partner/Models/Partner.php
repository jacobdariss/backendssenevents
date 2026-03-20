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
        'commission_rate',
        'revenue_model',
        'video_quota',
        'contract_url',
        'contract_signed_at',
        'contract_status',
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

