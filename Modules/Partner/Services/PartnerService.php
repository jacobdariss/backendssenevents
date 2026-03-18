<?php

namespace Modules\Partner\Services;

use Modules\Partner\Repositories\PartnerRepositoryInterface;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class PartnerService
{
    protected $partnerRepository;

    public function __construct(PartnerRepositoryInterface $partnerRepository)
    {
        $this->partnerRepository = $partnerRepository;
    }

    public function getAllPartners()
    {
        return $this->partnerRepository->all();
    }

    public function getPartnerById(int $id)
    {
        return $this->partnerRepository->find($id);
    }

    public function createPartner(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->partnerRepository->create($data);
    }

    public function updatePartner(int $id, array $data)
    {
        return $this->partnerRepository->update($id, $data);
    }

    public function deletePartner(int $id)
    {
        return $this->partnerRepository->delete($id);
    }

    public function restorePartner(int $id)
    {
        return $this->partnerRepository->restore($id);
    }

    public function forceDeletePartner(int $id)
    {
        return $this->partnerRepository->forceDelete($id);
    }

    public function getDataTable(Datatables $datatable, array $filter)
    {
        $query = $this->getFilteredData($filter);
        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" data-type="partners" onclick="dataTableRowCheck(' . $row->id . ',this)">';
            })
            ->addColumn('logo', function ($data) {
                $imageUrl = $data->logo_url ? setBaseUrlWithFileName($data->logo_url, 'image', 'partners') : asset('images/default.png');
                return view('components.image-name', ['image' => $imageUrl, 'name' => $data->name])->render();
            })
            ->addColumn('action', function ($data) {
                return view('partner::backend.partner.action', compact('data'));
            })
            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked="checked"' : '';
                $disabled = $row->trashed() ? 'disabled' : '';
                return '
                    <div class="form-check form-switch">
                        <input type="checkbox" data-url="' . route('backend.partners.update_status', $row->id) . '"
                            data-token="' . csrf_token() . '" class="switch-status-change form-check-input"
                            id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . ' ' . $disabled . '>
                    </div>
                ';
            })
            ->editColumn('updated_at', function ($data) {
                $diff = Carbon::now()->diffInHours($data->updated_at);
                return $diff < 25 ? $data->updated_at->diffForHumans() : $data->updated_at->isoFormat('llll');
            })
            ->orderColumns(['id'], '-:column $1')
            ->rawColumns(['action', 'status', 'check', 'logo'])
            ->toJson();
    }

    public function getFilteredData(array $filter)
    {
        $query = $this->partnerRepository->query();

        if (isset($filter['column_status']) && $filter['column_status'] !== '') {
            $query->where('status', $filter['column_status']);
        }

        if (isset($filter['name'])) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }

        return $query;
    }
}
