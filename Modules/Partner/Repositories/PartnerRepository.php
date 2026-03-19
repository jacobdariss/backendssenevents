<?php

namespace Modules\Partner\Repositories;

use Modules\Partner\Models\Partner;
use Auth;
use Illuminate\Support\Facades\Schema;

class PartnerRepository implements PartnerRepositoryInterface
{
    public function all()
    {
        return Partner::where('status', 1)->orderBy('name', 'asc')->get();
    }

    public function find(int $id)
    {
        $query = Partner::query();

        if (Auth::user()->hasRole('user')) {
            $query->whereNull('deleted_at');
        }

        $partner = $query->withTrashed()->findOrFail($id);

        if ($partner->logo_url) {
            $partner->logo_url = setBaseUrlWithFileName($partner->logo_url, 'image', 'partners');
        }

        return $partner;
    }

    public function create(array $data)
    {
        return Partner::create($data);
    }

    public function update(int $id, array $data)
    {
        $partner = Partner::findOrFail($id);

        // Guard: remove columns that don't exist yet in DB (migrations pending)
        $guardColumns = ['commission_rate', 'revenue_model', 'allowed_content_types', 'user_id', 'contract_url', 'contract_signed_at', 'contract_status'];
        foreach ($guardColumns as $col) {
            if (isset($data[$col]) && !\Schema::hasColumn('partners', $col)) {
                unset($data[$col]);
            }
        }

        $partner->update($data);
        return $partner;
    }

    public function delete(int $id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();
        return $partner;
    }

    public function restore(int $id)
    {
        $partner = Partner::withTrashed()->findOrFail($id);
        $partner->restore();
        return $partner;
    }

    public function forceDelete(int $id)
    {
        $partner = Partner::withTrashed()->findOrFail($id);
        $partner->forceDelete();
        return $partner;
    }

    public function query()
    {
        $query = Partner::query()->withTrashed();

        if (Auth::user()->hasRole('user')) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    public function list(int $perPage, string $searchTerm = null)
    {
        $query = Partner::query();

        if ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        $query->where('status', 1)
              ->whereNull('deleted_at')
              ->orderBy('name', 'asc');

        return $query->paginate($perPage);
    }
}
