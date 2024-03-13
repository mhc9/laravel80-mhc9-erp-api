<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
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
        /** Get params from query string */
        $category   = $req->get('category');
        $name       = $req->get('name');
        $status     = $req->get('status');
        $pageSize   = $req->get('limit') ? $req->get('limit') : 10;

        $items = Item::with('category','unit')
                    ->when(!empty($category), function($q) use ($category) {
                        $q->where('category_id', $category);
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%');
                    })
                    ->when($status != '', function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->paginate($pageSize);

        return $items;
    }

    public function getAll(Request $req)
    {
        return $this->itemService->findAll();
    }

    public function getById($id)
    {
        return $this->itemService->find($id);
    }

    public function getInitialFormData()
    {
        return $this->itemService->initForm();
    }

    public function store(Request $request)
    {
        try {
            if($item = $this->itemService->store($request)) {
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
            $item = Item::find($id);
            $item->name         = $req['name'];
            $item->category_id  = $req['category_id'];
            $item->cost         = $req['cost'];
            $item->price        = $req['price'];
            $item->unit_id      = $req['unit_id'];
            $item->description  = $req['description'];

            if($item->save()) {
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
            if($this->itemService->delete($id)) {
                return [
                    'status'    => 1,
                    'message'   => 'Deleting successfully!!',
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
