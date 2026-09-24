<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ChatMessages Model
 *
 * @property \App\Model\Table\StreamsTable&\Cake\ORM\Association\BelongsTo $Streams
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\ChatMessage newEmptyEntity()
 * @method \App\Model\Entity\ChatMessage newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ChatMessage> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ChatMessage get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ChatMessage findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ChatMessage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ChatMessage> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ChatMessage|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ChatMessage saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ChatMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ChatMessage>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ChatMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ChatMessage> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ChatMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ChatMessage>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ChatMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ChatMessage> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ChatMessagesTable extends Table
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

        $this->setTable('chat_messages');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Streams', [
            'foreignKey' => 'stream_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
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
            ->integer('stream_id')
            ->notEmptyString('stream_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('message')
            ->requirePresence('message', 'create')
            ->notEmptyString('message');

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
        $rules->add($rules->existsIn(['stream_id'], 'Streams'), ['errorField' => 'stream_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
