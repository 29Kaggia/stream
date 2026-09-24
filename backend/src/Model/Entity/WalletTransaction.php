<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WalletTransaction Entity
 *
 * @property int $id
 * @property int $channel_id
 * @property int|null $donation_id
 * @property string $type
 * @property string $amount
 * @property string $currency
 * @property string|null $description
 * @property string $reference
 * @property string $status
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Channel $channel
 * @property \App\Model\Entity\Donation $donation
 */
class WalletTransaction extends Entity
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
        'channel_id' => true,
        'donation_id' => true,
        'type' => true,
        'amount' => true,
        'currency' => true,
        'description' => true,
        'reference' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'channel' => true,
        'donation' => true,
    ];
}
