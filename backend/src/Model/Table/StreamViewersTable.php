<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class StreamViewersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('stream_viewers');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Streams', ['foreignKey' => 'stream_id', 'joinType' => 'INNER']);
    }
}
