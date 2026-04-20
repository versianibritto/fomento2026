<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Avaliador $avaliador
 * @var string[]|\Cake\Collection\CollectionInterface $usuarios
 * @var string[]|\Cake\Collection\CollectionInterface $grandesAreas
 * @var string[]|\Cake\Collection\CollectionInterface $areas
 * @var string[]|\Cake\Collection\CollectionInterface $subAreas
 * @var string[]|\Cake\Collection\CollectionInterface $especialidades
 * @var string[]|\Cake\Collection\CollectionInterface $linhas
 * @var string[]|\Cake\Collection\CollectionInterface $unidades
 * @var string[]|\Cake\Collection\CollectionInterface $editais
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $avaliador->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $avaliador->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Avaliadors'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="avaliadors form content">
            <?= $this->Form->create($avaliador) ?>
            <fieldset>
                <legend><?= __('Edit Avaliador') ?></legend>
                <?php
                    echo $this->Form->control('usuario_id', ['options' => $usuarios, 'empty' => true]);
                    echo $this->Form->control('grandes_area_id', ['options' => $grandesAreas, 'empty' => true]);
                    echo $this->Form->control('area_id', ['options' => $areas, 'empty' => true]);
                    echo $this->Form->control('sub_area_id', ['options' => $subAreas, 'empty' => true]);
                    echo $this->Form->control('especialidade_id', ['options' => $especialidades, 'empty' => true]);
                    echo $this->Form->control('areas_fiocruz_id');
                    echo $this->Form->control('linha_id', ['options' => $linhas, 'empty' => true]);
                    echo $this->Form->control('ano_convite');
                    echo $this->Form->control('ano_aceite');
                    echo $this->Form->control('voluntario');
                    echo $this->Form->control('unidade_id', ['options' => $unidades, 'empty' => true]);
                    echo $this->Form->control('tipo_avaliador');
                    echo $this->Form->control('deleted');
                    echo $this->Form->control('editai_id', ['options' => $editais, 'empty' => true]);
                    echo $this->Form->control('aceite');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
