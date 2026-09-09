<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardDocument extends Model
{
    protected $fillable = [
        'shop',
        'shopify_gift_card_id',
        'gift_card_code',
        'amount',
        'currency',
        'expires_on',
        'note',
        'qr_url',
        'qr_code',
        'shopify_file_id',
        'shopify_image_url',
        'shopify_image_alt',
        'template_html',
        'template_css',
    ];

    protected function casts(): array
    {
        return [
            'gift_card_code' => 'encrypted',
            'amount' => 'decimal:2',
            'expires_on' => 'date:Y-m-d',
        ];
    }
}
