<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'action',
        'model_type', 'model_id', 'model_name',
        'details', 'ip_address',
    ];

    // Pas de softDeletes — les logs d'audit ne se suppriment jamais

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistrer une action d'audit
     */
    public static function log(string $action, $model = null, ?string $details = null): void
    {
        try {
            $user = Auth::user();

            $entry = [
                'user_id'    => $user?->id,
                'user_name'  => $user?->full_name ?? $user?->email ?? 'Système',
                'action'     => $action,
                'ip_address' => Request::ip(),
                'details'    => $details,
            ];

            if ($model) {
                $entry['model_type'] = class_basename($model);
                $entry['model_id']   = $model->id ?? null;
                $entry['model_name'] = $model->name ?? $model->title ?? null;
            }

            self::create($entry);
        } catch (\Exception $e) {
            // Ne jamais faire planter l'appli à cause d'un log d'audit
            \Log::error('AuditLog failed: ' . $e->getMessage());
        }
    }
}
