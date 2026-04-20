<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Avaliador $avaliador
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Avaliador'), ['action' => 'edit', $avaliador->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Avaliador'), ['action' => 'delete', $avaliador->id], ['confirm' => __('Are you sure you want to delete # {0}?', $avaliador->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Avaliadors'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Avaliador'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="avaliadors view content">
            <h3><?= h($avaliador->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Usuario') ?></th>
                    <td><?= $avaliador->has('usuario') ? $this->Html->link($avaliador->usuario->id, ['controller' => 'Usuarios', 'action' => 'view', $avaliador->usuario->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Grandes Area') ?></th>
                    <td><?= $avaliador->has('grandes_area') ? $this->Html->link($avaliador->grandes_area->id, ['controller' => 'GrandesAreas', 'action' => 'view', $avaliador->grandes_area->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Area') ?></th>
                    <td><?= $avaliador->has('area') ? $this->Html->link($avaliador->area->nome, ['controller' => 'Areas', 'action' => 'view', $avaliador->area->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Sub Area') ?></th>
                    <td><?= $avaliador->has('sub_area') ? $this->Html->link($avaliador->sub_area->id, ['controller' => 'SubAreas', 'action' => 'view', $avaliador->sub_area->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Especialidade') ?></th>
                    <td><?= $avaliador->has('especialidade') ? $this->Html->link($avaliador->especialidade->id, ['controller' => 'Especialidades', 'action' => 'view', $avaliador->especialidade->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Linha') ?></th>
                    <td><?= $avaliador->has('linha') ? $this->Html->link($avaliador->linha->nome, ['controller' => 'Linhas', 'action' => 'view', $avaliador->linha->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Ano Convite') ?></th>
                    <td><?= h($avaliador->ano_convite) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ano Aceite') ?></th>
                    <td><?= h($avaliador->ano_aceite) ?></td>
                </tr>
                <tr>
                    <th><?= __('Voluntario') ?></th>
                    <td><?= h($avaliador->voluntario) ?></td>
                </tr>
                <tr>
                    <th><?= __('Unidade') ?></th>
                    <td><?= $avaliador->has('unidade') ? $this->Html->link($avaliador->unidade->nome, ['controller' => 'Unidades', 'action' => 'view', $avaliador->unidade->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Tipo Avaliador') ?></th>
                    <td><?= h($avaliador->tipo_avaliador) ?></td>
                </tr>
                <tr>
                    <th><?= __('Editai') ?></th>
                    <td><?= $avaliador->has('editai') ? $this->Html->link($avaliador->editai->nome, ['controller' => 'Editais', 'action' => 'view', $avaliador->editai->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($avaliador->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Areas Fiocruz Id') ?></th>
                    <td><?= $this->Number->format($avaliador->areas_fiocruz_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Deleted') ?></th>
                    <td><?= $this->Number->format($avaliador->deleted) ?></td>
                </tr>
                <tr>
                    <th><?= __('Aceite') ?></th>
                    <td><?= $this->Number->format($avaliador->aceite) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Avaliador Bolsistas') ?></h4>
                <?php if (!empty($avaliador->avaliador_bolsistas)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Avaliador Id') ?></th>
                            <th><?= __('Bolsista') ?></th>
                            <th><?= __('Tipo') ?></th>
                            <th><?= __('Ano') ?></th>
                            <th><?= __('Situacao') ?></th>
                            <th><?= __('Coordenador') ?></th>
                            <th><?= __('Observacao') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Deleted') ?></th>
                            <th><?= __('Editai Id') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($avaliador->avaliador_bolsistas as $avaliadorBolsistas) : ?>
                        <tr>
                            <td><?= h($avaliadorBolsistas->id) ?></td>
                            <td><?= h($avaliadorBolsistas->avaliador_id) ?></td>
                            <td><?= h($avaliadorBolsistas->bolsista) ?></td>
                            <td><?= h($avaliadorBolsistas->tipo) ?></td>
                            <td><?= h($avaliadorBolsistas->ano) ?></td>
                            <td><?= h($avaliadorBolsistas->situacao) ?></td>
                            <td><?= h($avaliadorBolsistas->coordenador) ?></td>
                            <td><?= h($avaliadorBolsistas->observacao) ?></td>
                            <td><?= h($avaliadorBolsistas->created) ?></td>
                            <td><?= h($avaliadorBolsistas->deleted) ?></td>
                            <td><?= h($avaliadorBolsistas->editai_id) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'AvaliadorBolsistas', 'action' => 'view', $avaliadorBolsistas->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'AvaliadorBolsistas', 'action' => 'edit', $avaliadorBolsistas->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'AvaliadorBolsistas', 'action' => 'delete', $avaliadorBolsistas->id], ['confirm' => __('Are you sure you want to delete # {0}?', $avaliadorBolsistas->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Avaliador Projetos') ?></h4>
                <?php if (!empty($avaliador->avaliador_projetos)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Avaliador Id') ?></th>
                            <th><?= __('Projeto Id') ?></th>
                            <th><?= __('Ano') ?></th>
                            <th><?= __('Situacao') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($avaliador->avaliador_projetos as $avaliadorProjetos) : ?>
                        <tr>
                            <td><?= h($avaliadorProjetos->id) ?></td>
                            <td><?= h($avaliadorProjetos->avaliador_id) ?></td>
                            <td><?= h($avaliadorProjetos->projeto_id) ?></td>
                            <td><?= h($avaliadorProjetos->ano) ?></td>
                            <td><?= h($avaliadorProjetos->situacao) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'AvaliadorProjetos', 'action' => 'view', $avaliadorProjetos->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'AvaliadorProjetos', 'action' => 'edit', $avaliadorProjetos->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'AvaliadorProjetos', 'action' => 'delete', $avaliadorProjetos->id], ['confirm' => __('Are you sure you want to delete # {0}?', $avaliadorProjetos->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
