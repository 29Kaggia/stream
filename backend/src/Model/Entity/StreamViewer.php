<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class StreamViewer extends Entity
{
    protected array $_accessible = [
        'stream_id' => true,
        'session_hash' => true,
        'expires_at' => true,
    ];
}
