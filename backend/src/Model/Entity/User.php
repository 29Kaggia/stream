<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $status
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Channel $channel
 * @property \App\Model\Entity\Profile $profile
 * @property \App\Model\Entity\ChatMessage[] $chat_messages
 * @property \App\Model\Entity\Donation[] $donations
 * @property \App\Model\Entity\Follow[] $follows
 */
class User extends Entity
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
        'username' => true,
        'email' => true,
        'password' => true,
        'role' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'channel' => true,
        'profile' => true,
        'chat_messages' => true,
        'donations' => true,
        'follows' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'password',
    ];
}
