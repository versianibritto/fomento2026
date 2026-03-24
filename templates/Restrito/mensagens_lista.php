<?php
$filtros = (array)($filtros ?? []);
?>
<section class="mt-n3">
    <div class="card card-secondary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h4 class="mb-0">Mensagens</h4>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Nova mensagem', ['controller' => 'Restrito', 'action' => 'mensagens'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>

            <?= $this->Form->create(null, ['class' => 'row g-2 mb-3']) ?>
                <?= $this->Form->hidden('acao', ['value' => 'filtrar']) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('titulo', [
                        'label' => 'Título',
                        'class' => 'form-control',
                        'value' => $filtros['titulo'] ?? '',
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
                <div class="col-md-3">
                    <?= $this->Form->control('status', [
                        'label' => 'Status',
                        'type' => 'select',
                        'options' => ['A' => 'Ativas', 'I' => 'Inativas', 'T' => 'Todas'],
                        'class' => 'form-select',
                        'value' => $filtros['status'] ?? 'A',
                    ]) ?>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Html->link('Limpar', ['controller' => 'Restrito', 'action' => 'mensagensLista', true], ['class' => 'btn btn-outline-secondary w-100']) ?>
                </div>
            <?= $this->Form->end() ?>

            <?php if ($mensagens->count() === 0): ?>
                <div class="alert alert-warning mb-0">Nenhuma mensagem encontrada.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;"><?= $this->Paginator->sort('id', 'ID') ?></th>
                                <th><?= $this->Paginator->sort('titulo', 'Título') ?></th>
                                <th style="width: 110px;">Mensagem</th>
                                <th style="width: 100px;"><?= $this->Paginator->sort('tipo', 'Tipo') ?></th>
                                <th style="width: 110px;">Imagem</th>
                                <th style="width: 180px;"><?= $this->Paginator->sort('inicio', 'Início') ?></th>
                                <th style="width: 180px;"><?= $this->Paginator->sort('fim', 'Fim') ?></th>
                                <th style="width: 180px;"><?= $this->Paginator->sort('deleted', 'Status') ?></th>
                                <th style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mensagens as $item): ?>
                                <tr>
                                    <td><?= (int)$item->id ?></td>
                                    <td><?= !empty($item->titulo) ? strip_tags((string)$item->titulo) : '-' ?></td>
                                    <td>
                                        <span class="badge <?= !empty(trim(strip_tags((string)$item->testo))) ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= !empty(trim(strip_tags((string)$item->testo))) ? 'Sim' : 'Não' ?>
                                        </span>
                                    </td>
                                    <td><?= h($tipos[$item->tipo] ?? '-') ?></td>
                                    <td>
                                        <span class="badge <?= !empty($item->imagem) ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= !empty($item->imagem) ? 'Sim' : 'Não' ?>
                                        </span>
                                    </td>
                                    <td><?= !empty($item->inicio) ? h($item->inicio->format('d/m/Y H:i')) : '-' ?></td>
                                    <td><?= !empty($item->fim) ? h($item->fim->format('d/m/Y H:i')) : '-' ?></td>
                                    <td>
                                        <?php if (!empty($item->deleted)): ?>
                                            <span class="badge bg-danger">Inativa</span>
                                            <div class="small text-muted mt-1">
                                                <?= h($item->deleted->format('d/m/Y H:i')) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-success">Ativa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($item->deleted)): ?>
                                            <div class="d-flex gap-2">
                                                <?= $this->Html->link('Editar', ['controller' => 'Restrito', 'action' => 'mensagens', (int)$item->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                                <?= $this->Form->postLink(
                                                    'Excluir',
                                                    ['controller' => 'Restrito', 'action' => 'mensagensLista'],
                                                    [
                                                        'class' => 'btn btn-outline-danger btn-sm',
                                                        'confirm' => 'Confirma a exclusão da mensagem #' . (int)$item->id . '?',
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
