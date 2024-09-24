<?php
namespace App\Model\Table;

use App\Model\Entity\FederalConstituency;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FederalConstituencies Model
 *
 * @property \Cake\ORM\Association\BelongsTo $States
 * @property \Cake\ORM\Association\HasMany $LocalGovts
 */
class FederalConstituenciesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('federal_constituencies');
        $this->displayField('id_no');
        $this->primaryKey('id_no');
        $this->belongsTo('States', [
            'foreignKey' => 'state_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('LocalGovts', [
            'foreignKey' => 'federal_constituency_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id_no', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id_no', 'create');
            
        $validator
            ->requirePresence('federal_constituency', 'create')
            ->notEmpty('federal_constituency');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['state_id'], 'States'));
        return $rules;
    }
}
