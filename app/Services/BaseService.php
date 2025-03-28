<?php

namespace App\Services;

abstract class BaseService
{
    protected $repo;

    public function getAll()
    {
        return $this->repo->all();
    }

    public function getById($id)
    {
        return $this->repo->findOne($id);
    }

    public function paginated()
    {
        return $this->repo->paginated(config('paginate'));
    }

    public function create(array $input)
    {
        return $this->repo->create($input);
    }

    public function createMany(array $data)
    {
        foreach($data as $item) {
            $this->repo->create($item);
        }
    }

    public function update($id, array $input)
    {
        return $this->repo->update($id, $input);
    }

    public function destroy($id)
    {
        return $this->repo->destroy($id);
    }

    public function getRelations()
    {
        return $this->repo->getRelations();
    }
}