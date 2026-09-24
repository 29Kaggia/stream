<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Streams Model
 *
 * @property \App\Model\Table\ChannelsTable&\Cake\ORM\Association\BelongsTo $Channels
 * @property \App\Model\Table\ChatMessagesTable&\Cake\ORM\Association\HasMany $ChatMessages
 *
 * @method \App\Model\Entity\Stream newEmptyEntity()
 * @method \App\Model\Entity\Stream newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Stream> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Stream get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Stream findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Stream patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Stream> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Stream|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Stream saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Stream>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Stream>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Stream>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Stream> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Stream>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Stream>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Stream>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Stream> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class StreamsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('streams');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Channels', [
            'foreignKey' => 'channel_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('ChatMessages', [
            'foreignKey' => 'stream_id',
        ]);
        $this->hasMany('StreamViewers', [
            'foreignKey' => 'stream_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('channel_id')
            ->notEmptyString('channel_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 200)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('stream_key')
            ->maxLength('stream_key', 255)
            ->requirePresence('stream_key', 'create')
            ->notEmptyString('stream_key')
            ->add('stream_key', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->notEmptyString('status');

        $validator
            ->integer('viewer_count')
            ->notEmptyString('viewer_count');

        $validator
            ->scalar('thumbnail_url')
            ->maxLength('thumbnail_url', 500)
            ->allowEmptyString('thumbnail_url');

        $validator
            ->dateTime('started_at')
            ->allowEmptyDateTime('started_at');

        $validator
            ->dateTime('ended_at')
            ->allowEmptyDateTime('ended_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['stream_key']), ['errorField' => 'stream_key']);
        $rules->add($rules->existsIn(['channel_id'], 'Channels'), ['errorField' => 'channel_id']);

        return $rules;
    }
}
