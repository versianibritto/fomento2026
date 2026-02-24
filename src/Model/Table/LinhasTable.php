<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Linhas Model
 *
 * @property \App\Model\Table\AreasFiocruzTable&\Cake\ORM\Association\BelongsTo $AreasFiocruz
 * @property \App\Model\Table\AvaliadorsTable&\Cake\ORM\Association\HasMany $Avaliadors
 * @property \App\Model\Table\ProjetosTable&\Cake\ORM\Association\HasMany $Projetos
 *
 * @method \App\Model\Entity\Linha newEmptyEntity()
 * @method \App\Model\Entity\Linha newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Linha> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Linha get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Linha findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Linha patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Linha> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Linha|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Linha saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Linha>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Linha>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Linha>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Linha> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Linha>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Linha>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Linha>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Linha> deleteManyOrFail(iterable $entities, array $options = [])
 */
class LinhasTable extends Table
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

        $this->setTable('linhas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('AreasFiocruz', [
            'foreignKey' => 'areas_fiocruz_id',
            'joinType' => 'LEFT',
        ]);
        $this->hasMany('Avaliadors', [
            'foreignKey' => 'linha_id',
        ]);
        $this->hasMany('Projetos', [
            'foreignKey' => 'linha_id',
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
            ->scalar('nome')
            ->allowEmptyString('nome');

        $validator
            ->nonNegativeInteger('areas_fiocruz_id')
            ->allowEmptyString('areas_fiocruz_id');

        return $validator;
    }
}
