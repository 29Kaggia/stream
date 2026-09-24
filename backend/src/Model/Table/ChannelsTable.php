<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Channels Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CategoriesTable&\Cake\ORM\Association\BelongsTo $Categories
 * @property \App\Model\Table\DonationsTable&\Cake\ORM\Association\HasMany $Donations
 * @property \App\Model\Table\FollowsTable&\Cake\ORM\Association\HasMany $Follows
 * @property \App\Model\Table\StreamsTable&\Cake\ORM\Association\HasMany $Streams
 * @property \App\Model\Table\WalletTransactionsTable&\Cake\ORM\Association\HasMany $WalletTransactions
 *
 * @method \App\Model\Entity\Channel newEmptyEntity()
 * @method \App\Model\Entity\Channel newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Channel> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Channel get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Channel findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Channel patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Channel> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Channel|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Channel saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Channel>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Channel>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Channel>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Channel> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Channel>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Channel>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Channel>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Channel> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ChannelsTable extends Table
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

        $this->setTable('channels');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
        ]);
        $this->hasMany('Donations', [
            'foreignKey' => 'channel_id',
        ]);
        $this->hasMany('Follows', [
            'foreignKey' => 'channel_id',
        ]);
        $this->hasMany('Streams', [
            'foreignKey' => 'channel_id',
        ]);
        $this->hasMany('WalletTransactions', [
            'foreignKey' => 'channel_id',
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
            ->integer('user_id')
            ->notEmptyString('user_id')
            ->add('user_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('category_id')
            ->allowEmptyString('category_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 120)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->boolean('is_live')
            ->notEmptyString('is_live');

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
        $rules->add($rules->isUnique(['user_id']), ['errorField' => 'user_id']);
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['category_id'], 'Categories'), ['errorField' => 'category_id']);

        return $rules;
    }
}
