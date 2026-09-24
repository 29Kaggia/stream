<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Donation Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $channel_id
 * @property string $amount
 * @property string $currency
 * @property string|null $message
 * @property string $payment_method
 * @property string $status
 * @property string|null $provider_reference
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Channel $channel
 * @property \App\Model\Entity\WalletTransaction[] $wallet_transactions
 */
class Donation extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'channel_id' => true,
        'amount' => true,
        'currency' => true,
        'message' => true,
        'payment_method' => true,
        'status' => true,
        'provider_reference' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'channel' => true,
        'wallet_transactions' => true,
    ];
}
