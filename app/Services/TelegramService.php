<?php

namespace App\Services;

use App\Models\BarCodeData;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected static function getBotToken()
    {
        return setting('telegram_bot_token');
    }

    protected static function getChatId()
    {
        return setting('telegram_chat_id');
    }

    public static function sendLowStockAlert(BarCodeData $product, $totalStock)
    {
        $message = "Low stock alert for product: {$product->product_name}. Current stock: {$totalStock}.";
        self::sendMessage($message);
    }

    public static function sendOutOfStockAlert($barCodeId)
    {
        $product = BarCodeData::find($barCodeId);
        if ($product) {
            $message = "Out of stock alert for product: {$product->product_name}.";
            self::sendMessage($message);
        }
    }

    public static function sendExpiredProductAlert($purchase)
    {
        $product = $purchase->barCodeData;
        if ($product) {
            $message = "Expired product alert for product: {$product->product_name}. Expiry date: {$purchase->expiry_date}.";
            self::sendMessage($message);
        }
    }

    protected static function sendMessage($message)
    {
        $url = "https://api.telegram.org/bot" . self::getBotToken() . "/sendMessage";
        Http::post($url, [
            'chat_id' => self::getChatId(),
            'text' => $message,
        ]);
    }
}
