<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    /**
     *  @var $model
     */
    protected $model;

    public function __construct(Item $model)
    {
        $this->model = $model;
    }

    public function getAsset($id)
    {
        return $this->model->find($id);
    }

    public function getAssets()
    {
        return $this->model
                    ->with('group','group.category','brand','budget','obtaining','unit','room')
                    ->with('currentOwner','currentOwner.owner','currentOwner.owner.prefix')
                    ->get();
    }

    public function getAssetById($id)
    {
        return $this->model
                    ->with('group','group.category','brand','budget','obtaining','unit','room')
                    ->with('currentOwner','currentOwner.owner','currentOwner.owner.prefix')
                    ->find($id);
    }

    public function store($data)
    {
        $newItem = $this->model->create($data);

        return $newItem;
    }

    public function delete($id)
    {
        return $this->getAsset($id)->delete();
    }
}