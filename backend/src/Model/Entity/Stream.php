<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Stream Entity
 *
 * @property int $id
 * @property int $channel_id
 * @property string $title
 * @property string $stream_key
 * @property string|null $mux_live_stream_id
 * @property string|null $playback_id
 * @property string $status
 * @property int $viewer_count
 * @property string|null $thumbnail_url
 * @property \Cake\I18n\DateTime|null $started_at
 * @property \Cake\I18n\DateTime|null $ended_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Channel $channel
 * @property \App\Model\Entity\ChatMessage[] $chat_messages
 * @property \App\Model\Entity\StreamViewer[] $stream_viewers
 */
class Stream extends Entity
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
        'title' => true,
        'stream_key' => true,
        'mux_live_stream_id' => true,
        'playback_id' => true,
        'status' => true,
        'viewer_count' => true,
        'thumbnail_url' => true,
        'started_at' => true,
        'ended_at' => true,
        'created' => true,
        'modified' => true,
        'channel' => true,
        'chat_messages' => true,
        'stream_viewers' => true,
    ];
}
