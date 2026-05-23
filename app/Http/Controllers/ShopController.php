<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ListAdvertisementsRequest;
use App\Http\Requests\Shop\PurchaseAdvertisementRequest;
use App\Http\Requests\Shop\PurchaseItemRequest;
use App\Http\Resources\AdvertisementResource;
use App\Http\Resources\CoinShopResource;
use App\Http\Resources\LiveShopResource;
use App\Services\ShopService;

class ShopController extends Controller
{
    public function __construct(private readonly ShopService $service) {}

    public function listAdvertisements(ListAdvertisementsRequest $request)
    {
        return $this->ok(
            'Anuncios',
            AdvertisementResource::collection($this->service->listAdvertisements()),
        );
    }

    public function listCoinShop()
    {
        return $this->ok(
            'Tienda de monedas',
            CoinShopResource::collection($this->service->listCoinShop()),
        );
    }

    public function listLiveShop()
    {
        return $this->ok(
            'Tienda de vidas',
            LiveShopResource::collection($this->service->listLiveShop()),
        );
    }

    public function purchaseAdvertisement(PurchaseAdvertisementRequest $request)
    {
        $this->service->purchaseAdvertisement(
            (int) $request->user_id,
            (int) $request->advertisement_id,
        );

        return $this->ok('Recompensa entregada');
    }

    public function purchaseCoinShopItem(PurchaseItemRequest $request)
    {
        $this->service->purchaseCoinShopItem(
            (int) $request->user_id,
            (int) $request->item_id,
        );

        return $this->ok('Compra realizada');
    }

    public function purchaseLiveShopItem(PurchaseItemRequest $request)
    {
        $this->service->purchaseLiveShopItem(
            (int) $request->user_id,
            (int) $request->item_id,
        );

        return $this->ok('Compra realizada');
    }
}
