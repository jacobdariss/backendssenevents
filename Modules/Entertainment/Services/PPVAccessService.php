<?php

namespace Modules\Entertainment\Services;

use Carbon\Carbon;
use Modules\Frontend\Models\PayPerView;
use Modules\Entertainment\Models\Entertainment;
use Modules\Episode\Models\Episode;
use Modules\Video\Models\Video;

/**
 * PPVAccessService
 * Responsable : vérification et gestion des accès Pay Per View
 * Extrait de EntertainmentsController
 */
class PPVAccessService
{
    /**
     * Vérifier si un utilisateur a accès à un contenu PPV
     */
    public function hasAccess(int $userId, int $contentId, string $contentType = 'movie'): bool
    {
        $ppv = PayPerView::where('user_id', $userId)
            ->where('movie_id', $contentId)
            ->where('type', $contentType)
            ->first();

        if (!$ppv) return false;

        // Vérifier l'expiration
        if ($ppv->view_expiry_date && Carbon::parse($ppv->view_expiry_date)->isPast()) {
            return false;
        }

        // Durée d'accès depuis première lecture
        if ($ppv->first_play_date && $ppv->access_duration) {
            $expiry = Carbon::parse($ppv->first_play_date)->addHours($ppv->access_duration);
            if ($expiry->isPast()) return false;
        }

        return true;
    }

    /**
     * Récupérer tous les IDs achetés par un utilisateur
     */
    public function getPurchasedIds(int $userId): array
    {
        return PayPerView::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('view_expiry_date')
                  ->orWhere('view_expiry_date', '>', now());
            })
            ->pluck('movie_id', 'type')
            ->toArray();
    }

    /**
     * Enregistrer la première lecture d'un contenu PPV
     */
    public function recordFirstPlay(int $userId, int $contentId, string $contentType = 'movie'): void
    {
        PayPerView::where('user_id', $userId)
            ->where('movie_id', $contentId)
            ->where('type', $contentType)
            ->whereNull('first_play_date')
            ->update(['first_play_date' => now()]);
    }

    /**
     * Vérifier l'accès et retourner les infos d'accès
     */
    public function getAccessInfo(int $userId, int $contentId, string $contentType = 'movie'): array
    {
        $ppv = PayPerView::where('user_id', $userId)
            ->where('movie_id', $contentId)
            ->where('type', $contentType)
            ->first();

        if (!$ppv) {
            return ['has_access' => false, 'reason' => 'not_purchased'];
        }

        if ($ppv->view_expiry_date && Carbon::parse($ppv->view_expiry_date)->isPast()) {
            return ['has_access' => false, 'reason' => 'expired'];
        }

        return [
            'has_access'       => true,
            'expiry'           => $ppv->view_expiry_date,
            'first_play_date'  => $ppv->first_play_date,
            'access_duration'  => $ppv->access_duration,
        ];
    }
}
