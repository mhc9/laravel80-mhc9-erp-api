<?php

namespace App\Services;

use App\Repositories\PlaceRepository;
use App\Models\Place;
use App\Models\Tambon;
use App\Models\Amphur;
use App\Models\Changwat;

class PlaceService
{
    /**
     * @var $placeRepo
     */
    protected $placeRepo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    public function __construct(PlaceRepository $placeRepo)
    {
        $this->placeRepo = $placeRepo;
    }

    public function find($id)
    {
        return $this->placeRepo->getPlace($id);
    }

    public function findAll($params = [])
    {
        return $this->placeRepo->getPlaces($params)->get();
    }

    public function search($params = [])
    {
        $limit = (array_key_exists('limit', $params) && $params['limit']) ? $params['limit'] : 10;

        return $this->placeRepo->getPlaces($params)->paginate(10);
    }

    public function findById($id)
    {
        return $this->placeRepo->getPlaceById($id);
    }

    public function initForm()
    {
        return [
            'tambons'       => Tambon::all(),
            'amphurs'       => Amphur::all(),
            'changwats'     => Changwat::all(),
        ];
    }

    public function store($req)
    {
        $data = [
            'name'          => $req['name'],
            'category_id'   => $req['category_id'],
            'description'   => $req['description'],
            'cost'          => $req['cost'],
            'price'         => $req['price'],
            'unit_id'       => $req['unit_id'],
            // 'status'        => $req['status'] ? 1 : 0,
        ];

        return $this->placeRepo->store($data);
    }

    public function delete($id)
    {
        return $this->placeRepo->delete($id);
    }
}