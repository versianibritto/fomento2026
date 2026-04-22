<?php
    $programaId = (int)($programaId ?? 0);
    $homologado = (string)($homologado ?? 'P');
    $identity = $this->request->getAttribute('identity');
    $usuarioLogadoId = (int)($identity['id'] ?? 0);
    $ehTi = in_array($usuarioLogadoId, [1, 8088], true);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Lista de Homologação</h4>
        <a href="<?= $this->Url->build(['controller' => 'Index', 'action' => 'dashyoda']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar para o Dashboard
        </a>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-2 align-items-end']) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('programa_id', [
                        'label' => 'Programa',
                        'options' => $programas,
                        'empty' => 'Todos',
                        'value' => $programaId ?: '',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->control('homologado', [
                        'label' => 'Situação de homologação',
                        'options' => $statusHomologacaoOptions,
                        'value' => $homologado,
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                    <a class="btn btn-outline-success"
                       href="<?= $this->Url->build([
                           'controller' => 'Gestao',
                           'action' => 'listahomologacao',
                           '?' => [
                               'programa_id' => $programaId > 0 ? $programaId : '',
                               'homologado' => $homologado,
                               'acao' => 'excel',
                               'origem' => 'gestao',
                           ],
                       ]) ?>">
                        Exportar Excel
                    </a>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Programa</th>
                        <th>Status</th>
                        <th>Homologação</th>
                        <th>Bolsista</th>
                        <th>Orientador</th>
                        <th>Unidade</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inscricoes)): ?>
                        <?php foreach ($inscricoes as $item): ?>
                            <tr>
                                <?php
                                    $programaSigla = trim((string)($item->editai->programa->sigla ?? ($item->editai->programa_id ?? '')));
                                    $nomeEdital = trim((string)($item->editai->nome ?? '-'));
                                    $rotuloPrincipal = $programaSigla;
                                    $rotuloSecundario = '';
                                    if ($ehTi && $nomeEdital !== '') {
                                        $rotuloPrincipal = $nomeEdital;
                                        $rotuloSecundario = $programaSigla;
                                    } elseif ($rotuloPrincipal === '') {
                                        $rotuloPrincipal = $nomeEdital !== '' ? $nomeEdital : '-';
                                    }
                                ?>
                                <td>#<?= h($item->id) ?></td>
                                <td>
                                    <?= h($rotuloPrincipal) ?>
                                    <?php if ($rotuloSecundario !== ''): ?>
                                        <br>
                                        <small class="text-muted"><?= h($rotuloSecundario) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($item->fase->nome ?? '-') ?></td>
                                <td>
                                    <?php
                                        $statusHomologacao = match ((string)($item->homologado ?? '')) {
                                            'S' => 'Homologada',
                                            'N' => 'Não homologada',
                                            default => 'Pendente',
                                        };
                                    ?>
                                    <?= h($statusHomologacao) ?>
                                </td>
                                <td><?= h($item->bolsista_usuario->nome ?? '-') ?></td>
                                <td><?= h($item->orientadore->nome ?? '-') ?></td>
                                <td><?= h($item->orientadore->unidade->sigla ?? '-') ?></td>
                                <td class="text-end">
                                    <?= $this->Html->link('Homologar', [
                                        'controller' => 'Gestao',
                                        'action' => 'telahomologacao',
                                        $item->id,
                                    ], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted fw-bold">Nenhum registro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <?= $this->Paginator->counter('Página {{page}} de {{pages}}') ?>
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <?= $this->Paginator->first('<<', ['class' => 'page-item']) ?>
                        <?= $this->Paginator->prev('<', ['class' => 'page-item']) ?>
                        <?= $this->Paginator->numbers(['class' => 'page-item']) ?>
                        <?= $this->Paginator->next('>', ['class' => 'page-item']) ?>
                        <?= $this->Paginator->last('>>', ['class' => 'page-item']) ?>
                    </ul>
                </nav>
            </div>
</div>
