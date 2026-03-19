<?php

namespace Modules\Entertainment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntertainmentView extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'entertainment_id',
        'user_id',
        'profile_id',
        'content_type',
        'episode_id',
        'video_id',
        'partner_id',
        'watch_time',
        'device_type',
        'platform',
        'country_code',
        'ip_address',
    ];

    public function entertainment()
    {
        return $this->belongsTo(Entertainment::class, 'entertainment_id');
    }
}
