<?php

namespace App\Repositories;

use App\Models\Place;

class PlaceRepository
{
    /**
     *  @var $model
     */
    protected $model;

    public function __construct(Place $model)
    {
        $this->model = $model;
    }

    public function getPlace($id)
    {
        return $this->model->find($id);
    }

    public function getPlaces($params = [])
    {
        return $this->model->with('tambon','amphur','changwat');
    }

    public function getPlaceById($id)
    {
        return $this->model->with('tambon','amphur','changwat')->find($id);
    }

    public function store($data)
    {
        $newItem = $this->model->create($data);

        return $newItem;
    }

    public function delete($id)
    {
        return $this->getPlace($id)->delete();
    }
}