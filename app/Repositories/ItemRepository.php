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

    public function getItem($id)
    {
        return $this->model->find($id);
    }

    public function getItems()
    {
        return $this->model
                    ->with('category','unit')
                    ->get();
    }

    public function getItemById($id)
    {
        return $this->model
                    ->with('category','unit')
                    ->find($id);
    }

    public function store($data)
    {
        $newItem = $this->model->create($data);

        return $newItem;
    }

    public function delete($id)
    {
        return $this->getItem($id)->delete();
    }
}