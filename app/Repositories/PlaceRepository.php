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
        $name = array_key_exists('name', $params) ? $params['name'] : '';
        $place_type_id = array_key_exists('place_type_id', $params) ? $params['place_type_id'] : '';

        return $this->model->with('tambon','amphur','changwat')
                            ->when(!empty($name), function($q) use ($name) {
                                $q->where('name', 'like', '%'.$name.'%');
                            })
                            ->when(!empty($place_type_id), function($q) use ($place_type_id) {
                                $q->where('place_type_id', $place_type_id);
                            });
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