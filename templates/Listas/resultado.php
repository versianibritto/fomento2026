<?php
$tipoLabel = 'Listagem';
if ($tipo === 'V') {
    $tipoLabel = 'Vigentes';
} elseif ($tipo === 'A') {
    $tipoLabel = 'Andamentos';
} elseif ($tipo === 'T') {
    $tipoLabel = 'Todos';
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Resultado - <?= h($tipoLabel) ?></h4>
        <a href="<?= $this->Url->build(['controller' => 'Listas', 'action' => 'busca', $tipo]) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Nova busca
        </a>
    </div>

    <?php
        $programaSelecionado = (int)($this->request->getQuery('programa') ?? 0);
        $programaLabel = $programaSelecionado && isset($prog[$programaSelecionado])
            ? $prog[$programaSelecionado]
            : 'Todos';
        $faseSelecionada = (int)($this->request->getQuery('fase_id') ?? 0);
        $faseLabel = $faseSelecionada && isset($situacao[$faseSelecionada])
            ? $situacao[$faseSelecionada]
            : 'Todas';
    ?>

    <div class="card mb-3 shadow-sm">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="text-muted small">
                Filtros aplicados:
                <strong>Programa</strong>
                <?= h($programaLabel) ?>,
                <strong>Fase</strong>
                <?= h($faseLabel) ?>
            </div>
            <a class="btn btn-outline-success"
               href="<?= $this->Url->build([
                   'controller' => 'Listas',
                   'action' => 'resultado',
                   $tipo ?: null,
                   '?' => [
                       'programa' => $this->request->getQuery('programa') ?: null,
                       'fase_id' => $this->request->getQuery('fase_id') ?: null,
                       'acao' => 'excel',
                   ],
               ]) ?>">
                Exportar Excel
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->Paginator->sort('id', 'Inscrição') ?></th>
                        <th><?= $this->Paginator->sort('nome_orientador', 'Orientador') ?></th>
                        <th><?= $this->Paginator->sort('nome_bolsista', 'Bolsista') ?></th>
                        <th><?= $this->Paginator->sort('unidade_orientador', 'Unidade') ?></th>
                        <th><?= $this->Paginator->sort('data_inicio', 'Data início') ?></th>
                        <th><?= $this->Paginator->sort('fase_nome', 'Fase') ?></th>
                        <th><?= $this->Paginator->sort('origem', 'Origem') ?></th>
                        <th><?= $this->Paginator->sort('programa_nome', 'Programa') ?></th>
                        <th><?= $this->Paginator->sort('editai_nome', 'Edital') ?></th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listas)): ?>
                        <?php foreach ($listas as $b): ?>
                            <?php
                                $nome = explode(' ', (string)($b->nome_bolsista ?? ''));
                                $nome_o = explode(' ', (string)($b->nome_orientador ?? ''));
                            ?>
                            <tr>
                                <td>
                                    <?=$this->Html->link(
                                        '#' . ($b->id ?? ''),
                                        ['controller' => 'Padrao', 'action' => 'visualizar', $b->id],
                                        ['class' => 'btn btn-sm btn-outline-primary']
                                    )?>
                                </td>
                                <td><?= h(($nome_o[0] ?? '') . ' ' . (end($nome_o) ?: '')) ?></td>
                                <td><?= h(($nome[0] ?? '') . ' ' . (end($nome) ?: '')) ?></td>
                                <td><?= h($b->unidade_orientador ?? '-') ?></td>
                                <td><?= h($b->data_inicio ?? '-') ?></td>
                                <td><?= h($b->fase_nome ?? ($b->fase_id ?? '-')) ?></td>
                                <td><?= h(($origem[(string)($b->origem ?? '')] ?? ($b->origem ?? '-'))) ?></td>
                                <td><?= h($b->programa_nome ?? '-') ?></td>
                                <td><?= h($b->editai_nome ?? '-') ?></td>
                                <td class="text-end">
                                    <?php if (!empty($this->request->getAttribute('identity')['yoda'])): ?>
                                        <?php
                                            $acaoCancelar = ($tipo === 'V') || ($tipo === 'T' && (int)($b->vigente ?? 0) === 1);
                                            $acaoHomologar = (
                                                in_array((int)($b->fase_id ?? 0), [4, 6, 7], true)
                                                && (($tipo === 'A') || ($tipo === 'T' && (int)($b->vigente ?? 0) !== 1))
                                            );
                                        ?>
                                        <?php if ($acaoCancelar): ?>
                                            <?=$this->Html->link(
                                                'Cancelar',
                                                ['controller' => 'Padrao', 'action' => 'cancelar', (int)$b->id],
                                                ['class' => 'btn btn-sm btn-outline-danger ms-1']
                                            )?>
                                        <?php endif; ?>
                                        <?php if ($acaoHomologar): ?>
                                            <?=$this->Html->link(
                                                'Homologar',
                                                ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$b->id],
                                                ['class' => 'btn btn-sm btn-outline-success ms-1']
                                            )?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted fw-bold">Nenhum registro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-3">
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
    </div>
</div>
