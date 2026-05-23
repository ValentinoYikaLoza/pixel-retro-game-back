<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Support\Collection;

class ShopService
{
    public function __construct(
        private readonly ShopRepositoryInterface $shop,
        private readonly UserService $users,
    ) {}

    public function listAdvertisements(): Collection
    {
        return $this->shop->allAdvertisements();
    }

    public function listCoinShop(): Collection
    {
        return $this->shop->allCoinShop();
    }

    public function listLiveShop(): Collection
    {
        return $this->shop->allLiveShop();
    }

    /**
     * Reclamar un anuncio entrega su recompensa (emite StatsUpdated vía UserService).
     */
    public function purchaseAdvertisement(int $userId, int $advertisementId): void
    {
        $ad = $this->shop->findAdvertisement($advertisementId);

        if (!$ad) {
            throw ApiException::notFound('Anuncio no encontrado');
        }

        if ($ad->reward_type === 'life') {
            $this->users->addLives($userId, (int) $ad->reward);
        } else {
            $this->users->addCoins($userId, (int) $ad->reward);
        }
    }

    public function purchaseCoinShopItem(int $userId, int $itemId): void
    {
        if (!$this->shop->findCoinShop($itemId)) {
            throw ApiException::notFound('Paquete de monedas no encontrado');
        }

        // TODO: integrar la pasarela de pago real antes de acreditar las monedas.
    }

    public function purchaseLiveShopItem(int $userId, int $itemId): void
    {
        if (!$this->shop->findLiveShop($itemId)) {
            throw ApiException::notFound('Paquete de vidas no encontrado');
        }

        // TODO: integrar la pasarela de pago real antes de acreditar las vidas.
    }
}
