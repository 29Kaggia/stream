<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WalletTransactions Model
 *
 * @property \App\Model\Table\ChannelsTable&\Cake\ORM\Association\BelongsTo $Channels
 * @property \App\Model\Table\DonationsTable&\Cake\ORM\Association\BelongsTo $Donations
 *
 * @method \App\Model\Entity\WalletTransaction newEmptyEntity()
 * @method \App\Model\Entity\WalletTransaction newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\WalletTransaction> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WalletTransaction get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WalletTransaction findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\WalletTransaction patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\WalletTransaction> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WalletTransaction|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\WalletTransaction saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\WalletTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WalletTransaction>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WalletTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WalletTransaction> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WalletTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WalletTransaction>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WalletTransaction>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WalletTransaction> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WalletTransactionsTable extends Table
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

        $this->setTable('wallet_transactions');
        $this->setDisplayField('type');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Channels', [
            'foreignKey' => 'channel_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Donations', [
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
            ->integer('channel_id')
            ->notEmptyString('channel_id');

        $validator
            ->integer('donation_id')
            ->allowEmptyString('donation_id');

        $validator
            ->scalar('type')
            ->maxLength('type', 30)
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->decimal('amount')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount');

        $validator
            ->scalar('currency')
            ->maxLength('currency', 3)
            ->notEmptyString('currency');

        $validator
            ->scalar('description')
            ->maxLength('description', 255)
            ->allowEmptyString('description');

        $validator
            ->scalar('reference')
            ->maxLength('reference', 255)
            ->requirePresence('reference', 'create')
            ->notEmptyString('reference')
            ->add('reference', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->notEmptyString('status');

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
        $rules->add($rules->isUnique(['reference']), ['errorField' => 'reference']);
        $rules->add($rules->existsIn(['channel_id'], 'Channels'), ['errorField' => 'channel_id']);
        $rules->add($rules->existsIn(['donation_id'], 'Donations'), ['errorField' => 'donation_id']);

        return $rules;
    }
}
