<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use App\Services\PlaceService;

class PlaceController extends Controller
{
    public function __construct(protected PlaceService $placeService)
    {
        //
    }

    public function search(Request $req)
    {
        return $this->placeService->search($req->query());
    }

    public function getAll(Request $req)
    {
        return $this->placeService->getAll($req->query());
    }

    public function getById($id)
    {
        return $this->placeService->getById($id);
    }

    public function getInitialFormData()
    {
        return $this->placeService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            $placeData = Arr::add($req->all(), "status", 1);

            if($place = $this->placeService->create($placeData)) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'place'     => $place->load($this->placeService->getRelations())
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
            if($place = $this->placeService->update($id, $req->all())) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'place'     => $place->load($this->placeService->getRelations())
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
            if($this->placeService->destroy()) {
                return [
                    'status'    => 1,
                    'message'   => 'Deleting successfully!!',
                    'id'        => $id
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
