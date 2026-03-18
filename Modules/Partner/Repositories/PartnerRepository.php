<?php

namespace Modules\Partner\Repositories;

use Modules\Partner\Models\Partner;
use Auth;

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
