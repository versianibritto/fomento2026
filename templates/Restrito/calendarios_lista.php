<?php
$filtros = (array)($filtros ?? []);
?>
<section class="mt-n3">
    <div class="card card-secondary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h4 class="mb-0">Calendários</h4>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Novo registro', ['controller' => 'Restrito', 'action' => 'calendarios'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>

            <?= $this->Form->create(null, ['class' => 'row g-2 mb-3']) ?>
                <?= $this->Form->hidden('acao', ['value' => 'filtrar']) ?>
                <div class="col-md-3">
                    <?= $this->Form->control('dia', [
                        'label' => 'Dia',
                        'type' => 'date',
                        'class' => 'form-control',
                        'value' => $filtros['dia'] ?? '',
                        'templates' => ['inputContainer' => '{{content}}'],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('tipo', [
                        'label' => 'Tipo',
                        'type' => 'select',
                        'options' => ['' => 'Todos'] + $tipos,
                        'class' => 'form-select',
                        'value' => $filtros['tipo'] ?? '',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->control('descricao', [
                        'label' => 'Descrição',
                        'class' => 'form-control',
                        'value' => $filtros['descricao'] ?? '',
                    ]) ?>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Html->link('Limpar', ['controller' => 'Restrito', 'action' => 'calendariosLista', true], ['class' => 'btn btn-outline-secondary w-100']) ?>
                </div>
            <?= $this->Form->end() ?>

            <?php if ($calendarios->count() === 0): ?>
                <div class="alert alert-warning mb-0">Nenhum registro encontrado.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;"><?= $this->Paginator->sort('id', 'ID') ?></th>
                                <th style="width: 150px;"><?= $this->Paginator->sort('dia', 'Dia') ?></th>
                                <th style="width: 180px;"><?= $this->Paginator->sort('tipo', 'Tipo') ?></th>
                                <th><?= $this->Paginator->sort('descricao', 'Descrição') ?></th>
                                <th style="width: 180px;"><?= $this->Paginator->sort('deleted', 'Status') ?></th>
                                <th style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($calendarios as $item): ?>
                                <tr>
                                    <td><?= (int)$item->id ?></td>
                                    <td><?= !empty($item->dia) ? h($item->dia->format('d/m/Y')) : '-' ?></td>
                                    <td><?= h($tipos[$item->tipo] ?? ($item->tipo ?: '-')) ?></td>
                                    <td><?= h($item->descricao ?: '-') ?></td>
                                    <td>
                                        <?php if (!empty($item->deleted)): ?>
                                            <span class="badge bg-danger">Inativo</span>
                                            <div class="small text-muted mt-1">
                                                <?= h($item->deleted->format('d/m/Y H:i')) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($item->deleted)): ?>
                                            <div class="d-flex gap-2">
                                                <?= $this->Html->link('Editar', ['controller' => 'Restrito', 'action' => 'calendarios', (int)$item->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                                <?= $this->Form->postLink(
                                                    'Excluir',
                                                    ['controller' => 'Restrito', 'action' => 'calendariosLista'],
                                                    [
                                                        'class' => 'btn btn-outline-danger btn-sm',
                                                        'confirm' => 'Confirma a exclusão do registro #' . (int)$item->id . '?',
                                                        'data' => ['acao' => 'deletar', 'id' => (int)$item->id],
                                                    ]
                                                ) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Sem ações</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">
                        <?= $this->Paginator->counter('Página {{page}} de {{pages}} | {{count}} registro(s)') ?>
                    </div>
                    <ul class="pagination mb-0">
                        <?= $this->Paginator->first('<<') ?>
                        <?= $this->Paginator->prev('<') ?>
                        <?= $this->Paginator->numbers() ?>
                        <?= $this->Paginator->next('>') ?>
                        <?= $this->Paginator->last('>>') ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
