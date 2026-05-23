<?php

namespace App\Repositories\Contracts;

use App\Models\AdvertisementModel;
use App\Models\CoinShopModel;
use App\Models\LiveShopModel;
use Illuminate\Support\Collection;

interface ShopRepositoryInterface
{
    public function allAdvertisements(): Collection;

    public function allCoinShop(): Collection;

    public function allLiveShop(): Collection;

    public function findAdvertisement(int $id): ?AdvertisementModel;

    public function findCoinShop(int $id): ?CoinShopModel;

    public function findLiveShop(int $id): ?LiveShopModel;
}
