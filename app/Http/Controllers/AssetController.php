<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Services\AssetService;
use App\Models\Asset;

class AssetController extends Controller
{
    /**
     * @var $assetService
     */
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function search(Request $req)
    {
        return $this->assetService->search($req->all());
    }

    public function getAll(Request $req)
    {
        return $this->assetService->getAll($req->query());
    }

    public function getById($id)
    {
        return $this->assetService->getById($id);
    }

    public function getInitialFormData()
    {
        return $this->assetService->initForm();
    }

    public function store(Request $req)
    {
        try {
            $asset = new Asset();
            $asset->asset_no            = $req['asset_no'];
            $asset->fsn_no              = $req['fsn_no'];
            $asset->name                = $req['name'];
            $asset->description         = $req['description'];
            $asset->asset_category_id   = $req['asset_category_id'];
            $asset->asset_group_id      = $req['asset_group_id'];
            $asset->price               = $req['price'];
            $asset->unit_id             = $req['unit_id'];
            $asset->brand_id            = $req['brand_id'];
            $asset->model               = $req['model'];
            $asset->purchased_at        = $req['purchased_at'];
            $asset->date_in             = $req['date_in'];
            $asset->first_year          = $req['first_year'];
            $asset->obtain_type_id      = $req['obtain_type_id'];
            $asset->budget_id           = $req['budget_id'];
            $asset->location            = $req['location'];
            $asset->room_id             = $req['room_id'];
            $asset->remark              = $req['remark'];
            $asset->status              = 1;
            $asset->img_url             = $this->assetService->saveImage($req->file('img_url'), 'assets');

            if($asset->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'asset'     => $asset
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function update(Request $req, $id)
    {
        try {
            $asset = Asset::find($id);
            $asset->asset_no            = $req['asset_no'];
            $asset->fsn_no              = $req['fsn_no'];
            $asset->name                = $req['name'];
            $asset->description         = $req['description'];
            $asset->asset_category_id   = $req['asset_category_id'];
            $asset->asset_group_id      = $req['asset_group_id'];
            $asset->price               = $req['price'];
            $asset->unit_id             = $req['unit_id'];
            $asset->brand_id            = $req['brand_id'];
            $asset->model               = $req['model'];
            $asset->purchased_at        = $req['purchased_at'];
            $asset->date_in             = $req['date_in'];
            $asset->first_year          = $req['first_year'];
            $asset->obtain_type_id      = $req['obtain_type_id'];
            $asset->budget_id           = $req['budget_id'];
            $asset->location            = $req['location'];
            $asset->room_id             = $req['room_id'];
            $asset->remark              = $req['remark'];

            if($asset->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'asset'     => $asset
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function destroy(Request $req, $id)
    {
        try {
            if($this->assetService->destroy($id)) {
                return [
                    'status'     => 1,
                    'message'    => 'Deleting successfully!!',
                    'id'         => $id
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function uploadImage(Request $req, $id)
    {
        try {
            if($asset = $this->assetService->updateImage($id, $req->file('img_url'))) {
                return [
                    'status'    => 1,
                    'message'   => 'Uploading avatar successfully!!',
                    'img_url'   => $asset->img_url
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }
}
