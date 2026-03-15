<?php
$filtros = (array)($filtros ?? []);
?>
<section class="mt-n3">
    <div class="card card-secondary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h4 class="mb-0">Manuais</h4>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Novo manual', ['controller' => 'Restrito', 'action' => 'manuais'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>

            <?= $this->Form->create(null, ['class' => 'row g-2 mb-3']) ?>
                <?= $this->Form->hidden('acao', ['value' => 'filtrar']) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('nome', [
                        'label' => 'Nome',
                        'class' => 'form-control',
                        'value' => $filtros['nome'] ?? '',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('restrito', [
                        'label' => 'Restrito',
                        'type' => 'select',
                        'options' => ['' => 'Todos', '0' => 'Não', '1' => 'Sim'],
                        'class' => 'form-select',
                        'value' => $filtros['restrito'] ?? '',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('status', [
                        'label' => 'Status',
                        'type' => 'select',
                        'options' => ['A' => 'Ativos', 'I' => 'Inativos', 'T' => 'Todos'],
                        'class' => 'form-select',
                        'value' => $filtros['status'] ?? 'A',
                    ]) ?>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Html->link('Limpar', ['controller' => 'Restrito', 'action' => 'manuaisLista', true], ['class' => 'btn btn-outline-secondary w-100']) ?>
                </div>
            <?= $this->Form->end() ?>

            <?php if ($manuais->count() === 0): ?>
                <div class="alert alert-warning mb-0">Nenhum manual encontrado.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;"><?= $this->Paginator->sort('id', 'ID') ?></th>
                                <th><?= $this->Paginator->sort('nome', 'Nome') ?></th>
                                <th style="width: 90px;"><?= $this->Paginator->sort('restrito', 'Restrito') ?></th>
                                <th>Anexo</th>
                                <th style="width: 180px;"><?= $this->Paginator->sort('created', 'Criado em') ?></th>
                                <th style="width: 180px;"><?= $this->Paginator->sort('deleted', 'Status') ?></th>
                                <th style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($manuais as $item): ?>
                                <tr>
                                    <td><?= (int)$item->id ?></td>
                                    <td><?= !empty($item->nome) ? $item->nome : '-' ?></td>
                                    <td><?= (int)($item->restrito ?? 0) === 1 ? 'Sim' : 'Não' ?></td>
                                    <td>
                                        <?php if (!empty($item->arquivo)): ?>
                                            <a href="/uploads/editais/<?= h($item->arquivo) ?>" target="_blank"><?= h($item->arquivo) ?></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($item->created) ? h($item->created->format('d/m/Y H:i')) : '-' ?></td>
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
                                                <?= $this->Html->link('Editar', ['controller' => 'Restrito', 'action' => 'manuais', (int)$item->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                                <?= $this->Form->postLink(
                                                    'Excluir',
                                                    ['controller' => 'Restrito', 'action' => 'manuaisLista'],
                                                    [
                                                        'class' => 'btn btn-outline-danger btn-sm',
                                                        'confirm' => 'Confirma a exclusão do manual #' . (int)$item->id . '?',
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
