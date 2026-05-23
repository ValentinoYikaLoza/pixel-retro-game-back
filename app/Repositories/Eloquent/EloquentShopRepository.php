<?php

namespace App\Repositories\Eloquent;

use App\Models\AdvertisementModel;
use App\Models\CoinShopModel;
use App\Models\LiveShopModel;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentShopRepository implements ShopRepositoryInterface
{
    private const AD_COLUMNS = ['id', 'reward', 'reward_type'];
    private const COIN_COLUMNS = ['id', 'quantity', 'price'];
    private const LIVE_COLUMNS = ['id', 'quantity', 'price', 'type_id'];

    public function allAdvertisements(): Collection
    {
        return AdvertisementModel::query()->select(self::AD_COLUMNS)->orderBy('id')->get();
    }

    public function allCoinShop(): Collection
    {
        return CoinShopModel::query()->select(self::COIN_COLUMNS)->orderBy('id')->get();
    }

    public function allLiveShop(): Collection
    {
        return LiveShopModel::query()->select(self::LIVE_COLUMNS)->orderBy('id')->get();
    }

    public function findAdvertisement(int $id): ?AdvertisementModel
    {
        return AdvertisementModel::query()->select(self::AD_COLUMNS)->find($id);
    }

    public function findCoinShop(int $id): ?CoinShopModel
    {
        return CoinShopModel::query()->select(self::COIN_COLUMNS)->find($id);
    }

    public function findLiveShop(int $id): ?LiveShopModel
    {
        return LiveShopModel::query()->select(self::LIVE_COLUMNS)->find($id);
    }
}
