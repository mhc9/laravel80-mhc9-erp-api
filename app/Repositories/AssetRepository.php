<?php

namespace App\Repositories;

use App\Models\Asset;

class AssetRepository
{
    /**
     *  @var $asset
     */
    protected $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getAssets()
    {
        return $this->asset
                    ->with('group','group.category','brand','budget','obtaining','unit','room')
                    ->with('currentOwner','currentOwner.owner','currentOwner.owner.prefix')
                    ->get();
    }

    public function getAssetById($id)
    {
        return $this->asset
                    ->with('group','group.category','brand','budget','obtaining','unit','room')
                    ->with('currentOwner','currentOwner.owner','currentOwner.owner.prefix')
                    ->find($id);
    }
}