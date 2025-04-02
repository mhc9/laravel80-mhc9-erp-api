<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\PlaceRepository;
use App\Models\Tambon;
use App\Models\Amphur;
use App\Models\Changwat;

class PlaceService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    public function __construct(PlaceRepository $repo)
    {
        $this->repo = $repo;

        // $this->repo->setSortBy('date_in');
        // $this->repo->setSortOrder('desc');

        $this->repo->setRelations(['tambon','amphur','changwat']);
    }

    public function search(array $params, $all = false, $perPage = 10)
    {
        $limit = (array_key_exists('limit', $params) && $params['limit']) ? $params['limit'] : 10;
        $name = array_key_exists('name', $params) ? $params['name'] : '';
        $place_type_id = array_key_exists('place_type_id', $params) ? $params['place_type_id'] : '';

        $collections = $this->repo
                            ->getModelwithRelations()
                            ->when(!empty($name), function($q) use ($name) {
                                $q->where('name', 'like', '%'.$name.'%');
                            })
                            ->when(!empty($place_type_id), function($q) use ($place_type_id) {
                                $q->where('place_type_id', $place_type_id);
                            });

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    public function getFormData()
    {
        return [
            'tambons'       => Tambon::all(),
            'amphurs'       => Amphur::all(),
            'changwats'     => Changwat::all(),
        ];
    }
}