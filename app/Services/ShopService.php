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

    /**
     * Las vidas se compran con MONEDAS (el dinero real solo compra monedas).
     * `price` del item es el costo en monedas; valida el saldo y acredita.
     */
    public function purchaseLiveShopItem(int $userId, int $itemId): void
    {
        $item = $this->shop->findLiveShop($itemId);
        if (!$item) {
            throw ApiException::notFound('Paquete de vidas no encontrado');
        }

        $this->users->purchaseLivesWithCoins(
            $userId,
            (int) $item->price,
            (int) $item->quantity,
        );
    }
}
