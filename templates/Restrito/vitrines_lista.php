<?php
$formatarDataTabela = function ($valor) {
    if (empty($valor)) {
        return '-';
    }
    try {
        return $valor->modify('+3 hours')->format('d/m/Y H:i');
    } catch (\Throwable $e) {
        return '-';
    }
};
?>
<section class="mt-n3">
    <div class="card card-secondary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h4 class="mb-0">Vitrines cadastradas</h4>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Erratas', ['controller' => 'Restrito', 'action' => 'erratasVitrine'], ['class' => 'btn btn-outline-warning']) ?>
                    <?= $this->Html->link('Nova vitrine', ['controller' => 'Restrito', 'action' => 'vitrines'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>

            <?php if ($vitrines->count() === 0): ?>
                <div class="alert alert-warning mb-0">Nenhuma vitrine cadastrada.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;"><?= $this->Paginator->sort('id', 'ID') ?></th>
                                <th><?= $this->Paginator->sort('nome', 'Nome') ?></th>
                                <th><?= $this->Paginator->sort('divulgacao', 'Divulgação') ?></th>
                                <th><?= $this->Paginator->sort('inicio', 'Início') ?></th>
                                <th><?= $this->Paginator->sort('fim', 'Fim') ?></th>
                                <th><?= $this->Paginator->sort('deleted', 'Status') ?></th>
                                <th style="width: 190px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vitrines as $item): ?>
                                <tr>
                                    <td><?= (int)$item->id ?></td>
                                    <td><?= !empty($item->nome) ? $this->Text->truncate(strip_tags((string)$item->nome), 100) : '-' ?></td>
                                    <td><?= h($formatarDataTabela($item->divulgacao ?? null)) ?></td>
                                    <td><?= h($formatarDataTabela($item->inicio ?? null)) ?></td>
                                    <td><?= h($formatarDataTabela($item->fim ?? null)) ?></td>
                                    <td>
                                        <?php if (!empty($item->deleted)): ?>
                                            <span class="badge bg-danger">Deletado em <?= h($formatarDataTabela($item->deleted)) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($item->deleted)): ?>
                                            <div class="d-flex gap-2">
                                                <?= $this->Html->link('Editar', ['controller' => 'Restrito', 'action' => 'vitrines', (int)$item->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                                <?= $this->Form->postLink(
                                                    'Excluir',
                                                    ['controller' => 'Restrito', 'action' => 'vitrines', (int)$item->id],
                                                    [
                                                        'class' => 'btn btn-outline-danger btn-sm',
                                                        'confirm' => 'Confirma a exclusão da vitrine #' . (int)$item->id . '?',
                                                        'data' => ['acao' => 'deletar', 'id' => (int)$item->id],
                                                    ]
                                                ) ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex gap-2">
                                                <?= $this->Form->postLink(
                                                    'Reativar',
                                                    ['controller' => 'Restrito', 'action' => 'vitrines', (int)$item->id],
                                                    [
                                                        'class' => 'btn btn-outline-success btn-sm',
                                                        'confirm' => 'Confirma a reativação da vitrine #' . (int)$item->id . '?',
                                                        'data' => ['acao' => 'reativar', 'id' => (int)$item->id],
                                                    ]
                                                ) ?>
                                            </div>
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
