<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $model;

    public $sortBy = 'created_at';

    public $sortOrder = 'asc';

    public $relations = [];

    public function all()
    {
        return $this->model
                    ->with($this->relations)
                    ->orderBy($this->sortBy, $this->sortOrder)
                    ->get();
    }

    public function findOne($id)
    {
        return $this->model
                    ->with($this->relations)
                    ->find($id);
    }

    public function find(array $conditions)
    {
        return $this->model
                    ->with($this->relations)
                    ->where($conditions)
                    ->get();
    }

    public function paginated($perPage)
    {
        return $this->model
                    ->with($this->relations)
                    ->orderBy($this->sortBy, $this->sortOrder)
                    ->paginate($perPage);
    }

    public function create($input)
    {
        $model = $this->model;
        $model->fill($input);
        $model->save();

        return $model;
    }

    public function update($id, array $data)
    {
        $model = $this->findOne($id);
        $model->fill($input);
        $model->save();

        return $model;
    }

    public function destroy($id)
    {
        return $this->findOne($id)->delete();
    }

    public function getModel()
    {
        return $this->model->with($this->relations);
    }
}