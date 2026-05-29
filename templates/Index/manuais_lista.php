<?php
$filtros = (array)($filtros ?? []);
$acessosManual = (array)($acessosManual ?? []);
$perfilManuais = (array)($perfilManuais ?? []);
$podeFiltrarRestrito = !empty($perfilManuais['ti'])
    || !empty($perfilManuais['yoda'])
    || !empty($perfilManuais['jedi'])
    || !empty($perfilManuais['padauan']);
$podeFiltrarAcesso = $podeFiltrarRestrito;
?>
<section class="mt-n3">
    <div class="card card-secondary card-outline mb-3 manuais-lista">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h4 class="mb-0">Manuais</h4>
                    <div class="text-muted small">Consulta de documentos disponíveis para seu perfil de acesso.</div>
                </div>
            </div>

            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-2 align-items-end mb-3 manuais-filtros']) ?>
                <div class="col-md-5 col-lg-4">
                    <?= $this->Form->control('nome', [
                        'label' => 'Nome',
                        'class' => 'form-control form-control-sm',
                        'value' => $filtros['nome'] ?? '',
                    ]) ?>
                </div>
                <?php if ($podeFiltrarRestrito): ?>
                    <div class="col-md-2 col-lg-2">
                        <?= $this->Form->control('restrito', [
                            'label' => 'Restrito',
                            'type' => 'select',
                            'options' => ['' => 'Todos', '0' => 'Não', '1' => 'Sim'],
                            'class' => 'form-select form-select-sm',
                            'value' => $filtros['restrito'] ?? '',
                        ]) ?>
                    </div>
                <?php endif; ?>
                <?php if ($podeFiltrarAcesso): ?>
                    <div class="col-md-2 col-lg-2">
                        <?= $this->Form->control('acesso', [
                            'label' => 'Acesso',
                            'type' => 'select',
                            'options' => ['' => 'Todos'] + $acessosManual,
                            'class' => 'form-select form-select-sm',
                            'value' => $filtros['acesso'] ?? '',
                        ]) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($perfilManuais['ti'])): ?>
                    <div class="col-md-2 col-lg-2">
                        <?= $this->Form->control('status', [
                            'label' => 'Status',
                            'type' => 'select',
                            'options' => ['T' => 'Todos', 'A' => 'Ativos', 'I' => 'Inativos'],
                            'class' => 'form-select form-select-sm',
                            'value' => $filtros['status'] ?? 'T',
                        ]) ?>
                    </div>
                <?php endif; ?>
                <div class="col-md-3 col-lg-2 d-flex gap-2">
                    <?= $this->Form->button('<i class="fa fa-search"></i>', [
                        'class' => 'btn btn-primary btn-sm flex-fill',
                        'escapeTitle' => false,
                        'title' => 'Filtrar',
                    ]) ?>
                    <?= $this->Html->link('<i class="fa fa-times"></i>', ['controller' => 'Index', 'action' => 'manuaisLista'], [
                        'class' => 'btn btn-outline-secondary btn-sm flex-fill',
                        'escape' => false,
                        'title' => 'Limpar filtros',
                    ]) ?>
                </div>
            <?= $this->Form->end() ?>

            <?php if ($manuais->count() === 0): ?>
                <div class="alert alert-warning mb-0">Nenhum manual encontrado.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle manuais-tabela">
                        <thead>
                            <tr>
                                <th><?= $this->Paginator->sort('nome', 'Nome') ?></th>
                                <th class="text-center" style="width: 90px;"><?= $this->Paginator->sort('restrito', 'Restrito') ?></th>
                                <th style="width: 150px;"><?= $this->Paginator->sort('acesso', 'Acesso') ?></th>
                                <th style="width: 110px;"><?= $this->Paginator->sort('created', 'Criado em') ?></th>
                                <th style="width: 150px;">Anexo</th>
                                <?php if (!empty($perfilManuais['ti'])): ?>
                                    <th class="text-center" style="width: 120px;"><?= $this->Paginator->sort('deleted', 'Status') ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($manuais as $item): ?>
                                <tr>
                                    <td>
                                        <div class="manual-nome" title="<?= h((string)($item->nome ?? '')) ?>">
                                            <?= !empty($item->nome) ? h($item->nome) : '-' ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int)($item->restrito ?? 0) === 1): ?>
                                            <span class="badge bg-warning text-dark">Sim</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">Não</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $acessosItem = array_filter(array_map('trim', explode(',', (string)($item->acesso ?? ''))));
                                            $acessosItem = array_values(array_filter($acessosItem, static function ($codigo) {
                                                return $codigo !== 'T';
                                            }));
                                            $acessosLabels = array_map(static function ($codigo) use ($acessosManual) {
                                                return $acessosManual[$codigo] ?? $codigo;
                                            }, $acessosItem);
                                        ?>
                                        <?php if ($acessosLabels): ?>
                                            <?php foreach ($acessosLabels as $acessoLabel): ?>
                                                <div class="manual-acesso-item"><?= h($acessoLabel) ?></div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($item->created) ? h($item->created->format('d/m/Y')) : '-' ?></td>
                                    <td>
                                        <?php if (!empty($item->arquivo)): ?>
                                            <a href="/uploads/editais/<?= h($item->arquivo) ?>" target="_blank" class="btn btn-outline-primary btn-sm manual-download" title="<?= h($item->arquivo) ?>">
                                                <i class="fa fa-download"></i>
                                                <span>Baixar</span>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <?php if (!empty($perfilManuais['ti'])): ?>
                                        <td class="text-center">
                                            <?php if (!empty($item->deleted)): ?>
                                                <span class="badge bg-danger">Inativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Ativo</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
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
<style>
.manuais-lista .form-label {
    margin-bottom: .25rem;
    font-size: .82rem;
    color: #495057;
}
.manuais-tabela {
    table-layout: fixed;
}
.manuais-tabela thead th {
    background: #f8f9fa;
    border-bottom: 1px solid #dfe3e7;
    color: #343a40;
    font-size: .82rem;
    font-weight: 700;
    white-space: nowrap;
}
.manual-nome {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.manual-acesso-item + .manual-acesso-item {
    margin-top: .15rem;
}
.manual-download {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    max-width: 100%;
}
@media (max-width: 767.98px) {
    .manuais-tabela {
        table-layout: auto;
    }
    .manual-nome {
        min-width: 220px;
    }
}
</style>
