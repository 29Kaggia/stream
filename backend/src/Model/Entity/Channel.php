<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Channel Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_live
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\Donation[] $donations
 * @property \App\Model\Entity\Follow[] $follows
 * @property \App\Model\Entity\Stream[] $streams
 * @property \App\Model\Entity\WalletTransaction[] $wallet_transactions
 */
class Channel extends Entity
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
        'category_id' => true,
        'name' => true,
        'slug' => true,
        'description' => true,
        'is_live' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'category' => true,
        'donations' => true,
        'follows' => true,
        'streams' => true,
        'wallet_transactions' => true,
    ];
}
