<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $avaliacoes
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $avaliacoesAnoCorrente
 * @var int $anoCorrente
 * @var array<string, string> $anosOptions
 * @var array<string, string> $filtros
 * @var array<string, string> $tipoMap
 * @var array<string, string> $situacaoMap
 */

$this->Paginator->options(['url' => ['?' => $filtros]]);

$nomeCurto = static function (?string $nome): string {
    $nome = trim((string)$nome);
    if ($nome === '') {
        return 'Não informado';
    }
    $partes = preg_split('/\s+/', $nome) ?: [];
    if (count($partes) <= 1) {
        return $nome;
    }
    return $partes[0] . ' ' . end($partes);
};

$dadosAvaliacao = static function ($avaliacao) use ($nomeCurto): array {
    $tipo = (string)($avaliacao->tipo ?? '');
    $referencia = '#' . (int)$avaliacao->bolsista;
    $edital = $avaliacao->editai->nome ?? null;
    $unidade = null;
    $bolsista = null;
    $orientador = null;
    $urlConsulta = null;
    $urlTrabalho = null;

    if ($tipo === 'N' && !empty($avaliacao->projeto_bolsista)) {
        $inscricao = $avaliacao->projeto_bolsista;
        $referencia = '#' . (int)$inscricao->id;
        $edital = $inscricao->editai->nome ?? $edital;
        $unidade = $inscricao->orientadore->unidade->sigla ?? null;
        $bolsista = $inscricao->bolsista_usuario->nome ?? null;
        $orientador = $inscricao->orientadore->nome ?? null;
        $urlConsulta = ['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id];
        $urlTrabalho = ['controller' => 'Avaliadores', 'action' => 'avaliar', (int)$avaliacao->id];
    } elseif (in_array($tipo, ['V', 'Z'], true) && !empty($avaliacao->raic)) {
        $raic = $avaliacao->raic;
        $referencia = '#' . (int)$raic->id;
        $edital = $raic->editai->nome ?? $edital;
        $unidade = $raic->unidade->sigla ?? null;
        $bolsista = $raic->usuario->nome ?? null;
        $orientador = $raic->orientadore->nome ?? null;
        $urlConsulta = ['controller' => 'RaicNew', 'action' => 'ver', (int)$raic->id];
        $urlTrabalho = ['controller' => 'Avaliadores', 'action' => 'avaliar', (int)$avaliacao->id];
    } elseif ($tipo === 'J' && !empty($avaliacao->pdj_inscrico)) {
        $inscricao = $avaliacao->pdj_inscrico;
        $referencia = '#' . (int)$inscricao->id;
        $edital = $inscricao->editai->nome ?? $edital;
        $unidade = $inscricao->orientadore->unidade->sigla ?? null;
        $bolsista = $inscricao->bolsista_usuario->nome ?? null;
        $orientador = $inscricao->orientadore->nome ?? null;
        $urlTrabalho = ['controller' => 'Avaliadores', 'action' => 'avaliar', (int)$avaliacao->id];
    } elseif ($tipo === 'W' && !empty($avaliacao->workshop)) {
        $workshop = $avaliacao->workshop;
        $referencia = '#' . (int)$workshop->id;
        $edital = $workshop->editai->nome ?? $edital;
        $unidade = $workshop->unidade->sigla ?? null;
        $bolsista = $workshop->usuario->nome ?? null;
        $orientador = $workshop->orientadore->nome ?? null;
        $urlTrabalho = ['controller' => 'Avaliadores', 'action' => 'avaliar', (int)$avaliacao->id];
    }

    return [
        'referencia' => $referencia,
        'edital' => $edital ?: 'Não informado',
        'unidade' => $unidade ?: 'Não informada',
        'bolsista' => $nomeCurto($bolsista),
        'orientador' => $nomeCurto($orientador),
        'urlConsulta' => $urlConsulta,
        'urlTrabalho' => $urlTrabalho,
    ];
};

$situacaoAvaliacao = static function ($avaliacao): array {
    if ((string)($avaliacao->situacao ?? '') === 'F') {
        return ['Finalizada', 'success'];
    }
    return ['Pendente', 'warning text-dark'];
};

$renderTabela = function ($lista, bool $anoAtual) use ($dadosAvaliacao, $situacaoAvaliacao, $tipoMap) {
    ob_start();
    ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-secondary">
                <tr>
                    <th>Tipo</th>
                    <th>Ano</th>
                    <th>Referência</th>
                    <th>Edital</th>
                    <th>Unidade</th>
                    <th>Bolsista/Candidato</th>
                    <th>Orientador</th>
                    <th>Situação</th>
                    <th class="actions">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $avaliacao): ?>
                    <?php
                    $dados = $dadosAvaliacao($avaliacao);
                    [$situacaoTexto, $situacaoClasse] = $situacaoAvaliacao($avaliacao);
                    $pendente = $situacaoTexto === 'Pendente';
                    ?>
                    <tr>
                        <td><?= h($tipoMap[(string)$avaliacao->tipo] ?? (string)$avaliacao->tipo) ?></td>
                        <td><?= h((string)($avaliacao->ano ?? '-')) ?></td>
                        <td>
                            <div><?= h($dados['referencia']) ?></div>
                            <?php if (!empty($dados['urlConsulta'])): ?>
                                <?= $this->Html->link(
                                    '<i class="fas fa-folder-open"></i> Dados',
                                    $dados['urlConsulta'],
                                    [
                                        'class' => 'btn btn-xs btn-outline-primary mt-1 py-0 px-1 text-nowrap',
                                        'style' => 'font-size: 0.75rem; line-height: 1.2;',
                                        'title' => 'Consultar dados da inscrição/RAIC',
                                        'escape' => false,
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= h($dados['edital']) ?></td>
                        <td><?= h($dados['unidade']) ?></td>
                        <td><?= h($dados['bolsista']) ?></td>
                        <td><?= h($dados['orientador']) ?></td>
                        <td><span class="badge bg-<?= h($situacaoClasse) ?>"><?= h($situacaoTexto) ?></span></td>
                        <td class="actions">
                            <div class="d-flex flex-wrap gap-1">
                            <?php if ($anoAtual && $pendente && !empty($dados['urlTrabalho'])): ?>
                                <?= $this->Html->link(
                                    '<i class="fas fa-check-circle"></i> Avaliar',
                                    $dados['urlTrabalho'],
                                    [
                                        'class' => 'btn btn-sm btn-success py-1 px-2 text-nowrap',
                                        'style' => 'font-size: 0.82rem; line-height: 1.2;',
                                        'escape' => false,
                                    ]
                                ) ?>
                            <?php endif; ?>
                            <?php if (!($anoAtual && $pendente && !empty($dados['urlTrabalho']))): ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
};
?>

<div class="container-fluid p-1 pt-1">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Avaliações <?= h((string)$anoCorrente) ?></h4>
                    <p class="text-muted mb-0">Avaliações do ano corrente ficam sempre no topo da tela.</p>
                </div>
                <span class="badge bg-info"><?= (int)$avaliacoesAnoCorrente->count() ?> registro(s)</span>
            </div>

            <?php if ($avaliacoesAnoCorrente->count() === 0): ?>
                <div class="alert alert-light border mb-0">
                    Não há avaliações vinculadas ao usuário logado para <?= h((string)$anoCorrente) ?>.
                </div>
            <?php else: ?>
                <?= $renderTabela($avaliacoesAnoCorrente, true) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Histórico consultivo</h4>
                    <p class="text-muted mb-0">Avaliações de anos anteriores, incluindo pendentes e finalizadas.</p>
                </div>
                <span class="badge bg-secondary"><?= (int)$avaliacoes->count() ?> nesta página</span>
            </div>

            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-2 align-items-end mb-3']) ?>
                <div class="col-md-3">
                    <?= $this->Form->control('ano', [
                        'label' => 'Ano',
                        'options' => $anosOptions,
                        'empty' => '- Todos -',
                        'value' => $filtros['ano'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('tipo', [
                        'label' => 'Tipo',
                        'options' => $tipoMap,
                        'empty' => '- Todos -',
                        'value' => $filtros['tipo'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('situacao', [
                        'label' => 'Situação',
                        'options' => $situacaoMap,
                        'empty' => '- Todas -',
                        'value' => $filtros['situacao'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <?= $this->Form->button('<i class="fas fa-filter"></i> Filtrar', [
                        'class' => 'btn btn-primary',
                        'escapeTitle' => false,
                    ]) ?>
                    <?= $this->Html->link('Limpar', ['action' => 'avaliacoes'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= $this->Form->end() ?>

            <?php if ($avaliacoes->count() === 0): ?>
                <div class="alert alert-light border mb-0">
                    Não há avaliações no histórico para os filtros selecionados.
                </div>
            <?php else: ?>
                <?= $renderTabela($avaliacoes, false) ?>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <?= $this->Paginator->counter('Página {{page}} de {{pages}}, exibindo {{current}} de {{count}} registro(s).') ?>
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
</div>
