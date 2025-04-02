<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Item;
use App\Models\AssetType;
use App\Models\AssetCategory;
use App\Models\Unit;
use App\Services\ItemService;
use App\Traits\SaveImage;

class ItemController extends Controller
{
    use SaveImage;

    /**
     * @var $itemService
     */
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function search(Request $req)
    {
        return $this->itemService->search($req->all());
    }

    public function getAll(Request $req)
    {
        return $this->itemService->getAll();
    }

    public function getById($id)
    {
        return $this->itemService->getById($id);
    }

    public function getInitialFormData()
    {
        return $this->itemService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            $itemData = addMultipleInputs(
                $req->except(['id','img_url']),
                [
                    'img_url'   => $this->itemService->saveImage($req->file('img_url'), 'products'),
                    // 'status'    => $req['status'] ? 1 : 0,
                ],
            );

            if($item = $this->itemService->create($itemData)) {
                /** Log info */
                Log::channel('daily')->info('Added item ID:' .$item->id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'item'      => $item
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
            if($item = $this->itemService->update($id, $req->all())) {
                /** Log info */
                Log::channel('daily')->info('Updated item ID:' .$id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'item'      => $item
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
            if($this->itemService->destroy($id)) {
                /** Log info */
                Log::channel('daily')->info('Deleted item ID:' .$id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Deleting successfully!!'
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
            if($item = $this->itemService->updateImage($id, $req->file('img_url'))) {
                return [
                    'status'    => 1,
                    'message'   => 'Uploading avatar successfully!!',
                    'img_url'   => $item->img_url
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
