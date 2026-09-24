<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Donations Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ChannelsTable&\Cake\ORM\Association\BelongsTo $Channels
 * @property \App\Model\Table\WalletTransactionsTable&\Cake\ORM\Association\HasMany $WalletTransactions
 *
 * @method \App\Model\Entity\Donation newEmptyEntity()
 * @method \App\Model\Entity\Donation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Donation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Donation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Donation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Donation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Donation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Donation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Donation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Donation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Donation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Donation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Donation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Donation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Donation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Donation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Donation> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DonationsTable extends Table
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

        $this->setTable('donations');
        $this->setDisplayField('currency');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Channels', [
            'foreignKey' => 'channel_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('WalletTransactions', [
            'foreignKey' => 'donation_id',
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
            ->notEmptyString('user_id');

        $validator
            ->integer('channel_id')
            ->notEmptyString('channel_id');

        $validator
            ->decimal('amount')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount');

        $validator
            ->scalar('currency')
            ->maxLength('currency', 3)
            ->notEmptyString('currency');

        $validator
            ->scalar('message')
            ->allowEmptyString('message');

        $validator
            ->scalar('payment_method')
            ->maxLength('payment_method', 30)
            ->notEmptyString('payment_method');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->notEmptyString('status');

        $validator
            ->scalar('provider_reference')
            ->maxLength('provider_reference', 255)
            ->allowEmptyString('provider_reference')
            ->add('provider_reference', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

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
        $rules->add($rules->isUnique(['provider_reference'], ['allowMultipleNulls' => true]), ['errorField' => 'provider_reference']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['channel_id'], 'Channels'), ['errorField' => 'channel_id']);

        return $rules;
    }
}
