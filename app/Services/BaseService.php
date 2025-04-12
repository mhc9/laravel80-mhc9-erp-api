<?php

namespace App\Services;

use Illuminate\Support\Arr;

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

    public function getOneWithConditions(array $conditions)
    {
        return $this->repo
                    ->getModel()
                    ->where($conditions)
                    ->first();
    }

    public function getAllWithConditions(array $conditions)
    {
        return $this->repo
                    ->getModel()
                    ->where($conditions)
                    ->get();
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
            $this->repo->getModel()->create($item);
        }
    }

    public function update($id, array $input)
    {
        return $this->repo->update($id, $input);
    }

    /**
     * Update many rows function
     *
     * @param array $data
     * @param string $checkField
     * @return void
     */
    public function updateMany(array $data, string $checkField, array $additions = null): void
    {
        foreach($data as $item) {
            /** ถ้า element ของ $data ไม่มี $checkField (รายการใหม่) */
            if (!array_key_exists($checkField, $item) || empty($item[$checkField])) {
                $this->repo->getModel()->create(Arr::except($additions ? addMultipleInputs($item, $additions) : $item, 'id'));
            } else {
                /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed หรือไม่ */
                if (array_key_exists('removed', $item) && $item['removed']) {
                    $this->repo->getModel()->find($item['id'])->delete();
                }
            }
        }
    }

    public function destroy($id)
    {
        return $this->repo->destroy($id);
    }

    public function destroyBy(array $conditions)
    {
        return $this->repo->getModel()
                    ->where($conditions)
                    ->delete();
    }

    public function getRelations()
    {
        return $this->repo->getRelations();
    }
}