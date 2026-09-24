<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class ReportsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('reports');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Reporter', ['className' => 'Users', 'foreignKey' => 'reporter_id']);
        $this->belongsTo('ReportedUsers', ['className' => 'Users', 'foreignKey' => 'reported_user_id']);
        $this->belongsTo('Streams', ['foreignKey' => 'stream_id']);
    }
}
