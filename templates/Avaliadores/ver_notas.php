<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AvaliadorBolsista $avaliacao
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $notas
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $notasSumulas
 * @var array<int, \App\Model\Entity\AvaliationsSumula> $notasSumulasLista
 * @var array<string, string> $tipoMap
 */

$tipo = (string)($avaliacao->tipo ?? '');
$origemEdital = (string)($avaliacao->editai->origem ?? '');
$parecerMap = [
    'I' => 'Não se aplica',
    'N' => 'É necessário mas não anexou',
    'S' => 'Anexou',
];
$formatNota = static function ($valor): string {
    if ($valor === null || $valor === '') {
        return 'Não informada';
    }

    return number_format((float)$valor, 2, ',', '.');
};

$referencia = '#' . (int)($avaliacao->bolsista ?? 0);
$edital = $avaliacao->editai->nome ?? 'Não informado';
$bolsista = 'Não informado';
$orientador = 'Não informado';
$identityAtual = $this->request->getAttribute('identity');
$identityAtualId = is_array($identityAtual) ? (int)($identityAtual['id'] ?? 0) : (int)($identityAtual->id ?? 0);
$identityAtualYoda = is_array($identityAtual) ? !empty($identityAtual['yoda']) : !empty($identityAtual->yoda);
$identityAtualTi = in_array($identityAtualId, [1, 8088], true);
$podeVerNomeAvaliador = $tipo === 'N'
    ? $identityAtualYoda
    : ($identityAtualYoda || $identityAtualTi);
$avaliadorNome = $avaliacao->avaliador->usuario->nome ?? null;
if ($tipo === 'N' && !$podeVerNomeAvaliador) {
    $avaliadorNome = 'Avaliador ' . (int)($avaliacao->ordem ?? 0);
}
$avaliadorNome = $avaliadorNome ?: ('Avaliador ' . (int)($avaliacao->ordem ?? 0));
$notasLista = !empty($notas) ? $notas->toList() : [];
$notasSumulasLista = $notasSumulasLista ?? (!empty($notasSumulas) ? $notasSumulas->toList() : []);
$sumulasGrupos = [
    'orientador' => [
        'titulo' => 'Súmula do orientador',
        'nota' => $avaliacao->nota_sumula ?? null,
        'itens' => [],
        'totais' => [],
        'card' => 'primary',
    ],
    'bolsista' => [
        'titulo' => 'Súmula do bolsista',
        'nota' => $avaliacao->nota_sumula_bolsista ?? null,
        'itens' => [],
        'totais' => [],
        'card' => 'info',
    ],
];
$observacaoQuesitos = '';
foreach ($notasLista as $notaLancada) {
    $observacaoQuesitos = trim((string)($notaLancada->observacao_avaliador ?? ''));
    if ($observacaoQuesitos !== '') {
        break;
    }
}
if ($observacaoQuesitos === '') {
    $observacaoQuesitos = trim((string)($avaliacao->observacao ?? ''));
}
$observacaoSumulas = '';
foreach ($notasSumulasLista as $notaSumulaLancada) {
    $observacaoSumulas = trim((string)($notaSumulaLancada->observacao_avaliador ?? ''));
    if ($observacaoSumulas !== '') {
        break;
    }
}
foreach ($notasSumulasLista as $notaSumula) {
    $sumulaReferencia = $notaSumula->editais_sumula ?? null;
    $blocoReferencia = $sumulaReferencia->editais_sumulas_bloco ?? null;
    $blocoId = (int)($sumulaReferencia->editais_sumula_bloco_id ?? $notaSumula->editais_sumula_bloco_id ?? 0);
    $blocoNome = $blocoReferencia->nome
        ?? $notaSumula->editais_sumulas_bloco->nome
        ?? 'Sem bloco';
    $blocoMax = $blocoReferencia->max
        ?? $notaSumula->editais_sumulas_bloco->max
        ?? null;
    $grupoSumula = (string)($notaSumula->grupo_sumula ?? '');
    if (!in_array($grupoSumula, ['orientador', 'bolsista'], true)) {
        $nomeBlocoNormalizado = strtoupper((string)$blocoNome);
        $grupoSumula = (
            (int)($notaSumula->bolsista ?? 0) === 1
            || (int)($notaSumula->inscricao_sumula->bolsista ?? 0) === 1
            || strpos($nomeBlocoNormalizado, 'BOLSISTA') !== false
        ) ? 'bolsista' : 'orientador';
    }
    $sumulasGrupos[$grupoSumula]['itens'][] = $notaSumula;

    if (!isset($sumulasGrupos[$grupoSumula]['totais'][$blocoId])) {
        $sumulasGrupos[$grupoSumula]['totais'][$blocoId] = [
            'nome' => $blocoNome,
            'max' => $blocoMax,
            'total' => 0.0,
        ];
    }
    $sumulasGrupos[$grupoSumula]['totais'][$blocoId]['total'] += (float)($notaSumula->nota ?? 0);
}
$sumulasGrupos = array_filter($sumulasGrupos, static function (array $grupo): bool {
    return !empty($grupo['itens']);
});

if (in_array($tipo, ['N', 'J'], true) && !empty($avaliacao->projeto_bolsista)) {
    $referencia = 'Inscrição #' . (int)$avaliacao->projeto_bolsista->id;
    $bolsista = $avaliacao->projeto_bolsista->bolsista_usuario->nome ?? $bolsista;
    $orientador = $avaliacao->projeto_bolsista->orientadore->nome ?? $orientador;
} elseif (in_array($tipo, ['R', 'V', 'Z'], true) && !empty($avaliacao->raic)) {
    $referencia = 'RAIC #' . (int)$avaliacao->raic->id;
    $bolsista = $avaliacao->raic->usuario->nome ?? $bolsista;
    $orientador = $avaliacao->raic->orientadore->nome ?? $orientador;
} elseif ($tipo === 'J' && !empty($avaliacao->pdj_inscrico)) {
    $referencia = 'PDJ #' . (int)$avaliacao->pdj_inscrico->id;
    $bolsista = $avaliacao->pdj_inscrico->bolsista_usuario->nome ?? $bolsista;
    $orientador = $avaliacao->pdj_inscrico->orientadore->nome ?? $orientador;
} elseif (in_array($tipo, ['R', 'W'], true) && !empty($avaliacao->workshop)) {
    $referencia = 'Workshop #' . (int)$avaliacao->workshop->id;
    $bolsista = $avaliacao->workshop->usuario->nome ?? $bolsista;
    $orientador = $avaliacao->workshop->orientadore->nome ?? $orientador;
}

$situacaoTexto = (string)$avaliacao->situacao === 'F' ? 'Finalizada' : 'Aguardando notas';
$situacaoClasse = (string)$avaliacao->situacao === 'F' ? 'badge badge-success' : 'badge badge-warning';
$notaTotalTexto = $formatNota($avaliacao->nota ?? null);
$sumulaNaoSeAplica = in_array($origemEdital, ['R', 'V'], true) && $tipo !== 'J';
$notaSumulaTexto = $sumulaNaoSeAplica
    ? 'Não se aplica a este tipo de avaliação'
    : $formatNota($avaliacao->nota_sumula ?? null);
$notaSumulaBolsistaTexto = $sumulaNaoSeAplica
    ? 'Não se aplica a este tipo de avaliação'
    : $formatNota($avaliacao->nota_sumula_bolsista ?? null);
$colunaResumo = $tipo === 'J' ? 'col-md-3' : 'col-md-4';
?>

<div class="container-fluid p-1 pt-1">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Notas da Avaliação</h4>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="text-muted"><?= h($tipoMap[$tipo] ?? $tipo) ?> - <?= h($referencia) ?></span>
                        <span class="<?= h($situacaoClasse) ?>"><?= h($situacaoTexto) ?></span>
                    </div>
                </div>
                <?= $this->Html->link(
                    '<i class="fas fa-arrow-left me-1"></i> Voltar',
                    'javascript:history.back()',
                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                ) ?>
            </div>

            <div class="row g-3 mb-3">
                <div class="<?= h($colunaResumo) ?>">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small text-uppercase">Nota dos quesitos</div>
                        <div class="h3 mb-0"><?= h($notaTotalTexto) ?></div>
                    </div>
                </div>
                <div class="<?= h($colunaResumo) ?>">
                    <div class="border rounded p-3 h-100 <?= $sumulaNaoSeAplica ? 'bg-warning bg-opacity-10' : '' ?>">
                        <div class="text-muted small text-uppercase">Súmula do orientador</div>
                        <div class="h3 mb-0"><?= h($notaSumulaTexto) ?></div>
                    </div>
                </div>
                <?php if ($tipo === 'J'): ?>
                    <div class="<?= h($colunaResumo) ?>">
                        <div class="border rounded p-3 h-100 <?= $sumulaNaoSeAplica ? 'bg-warning bg-opacity-10' : '' ?>">
                            <div class="text-muted small text-uppercase">Súmula do bolsista</div>
                            <div class="h3 mb-0"><?= h($notaSumulaBolsistaTexto) ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="<?= h($colunaResumo) ?>">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small text-uppercase">Avaliador</div>
                        <div class="fw-semibold"><?= h($avaliadorNome) ?></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <tbody>
                        <tr>
                            <th style="width: 160px">Edital</th>
                            <td><?= h($edital) ?></td>
                            
                            <th>Orientador</th>
                            <td><?= h($orientador) ?></td>
                        </tr>
                        <tr>
                            <th>Referência</th>
                            <td><?= h($referencia) ?></td>
                            <th style="width: 160px">Bolsista/Candidato</th>
                            <td><?= h($bolsista) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                        <i class="fas fa-tasks mr-2"></i>Critérios
                    </h5>
                <span class="badge badge-light border"><?= count($notasLista) ?> item(ns)</span>
            </div>
            <?php if (count($notasLista) === 0): ?>
                <div class="alert alert-light border mb-0">Nenhuma nota lançada para esta avaliação.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Critério</th>
                                <th class="text-end" style="width: 110px">Nota</th>
                                <th style="width: 150px">Intervalo</th>
                                <th>Parâmetros</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notasLista as $nota): ?>
                                <tr>
                                    <td><?= h($nota->question->questao ?? 'Critério não localizado') ?></td>
                                    <td class="text-end fw-semibold"><?= h($formatNota($nota->nota ?? null)) ?></td>
                                    <td>
                                        <?= h((string)($nota->question->limite_min ?? '-')) ?>
                                        -
                                        <?= h((string)($nota->question->limite_max ?? '-')) ?>
                                    </td>
                                    <td><?= nl2br(h((string)($nota->question->prametros ?? ''))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <strong>Observações dos quesitos</strong>
                    <div class="border rounded p-3 mt-1 bg-light">
                        <?= $observacaoQuesitos !== '' ? nl2br(h($observacaoQuesitos)) : 'Não informado' ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100 <?= in_array($tipo, ['V', 'Z'], true) ? 'bg-warning bg-opacity-10' : '' ?>">
                        <div class="text-muted small">Parecer do Comitê de Ética</div>
                        <div>
                            <?php if (in_array($tipo, ['V', 'Z'], true)): ?>
                                <span class="text-danger fw-semibold"><?= h('Não se aplica a este tipo de avaliação') ?></span>
                            <?php else: ?>
                                <?= h($parecerMap[(string)($avaliacao->parecer ?? '')] ?? 'Não informado') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100 <?= !in_array($origemEdital, ['R', 'V'], true) ? 'bg-warning bg-opacity-10' : '' ?>">
                        <div class="text-muted small">Destacou-se?</div>
                        <div>
                            <?php if (!in_array($origemEdital, ['R', 'V'], true)): ?>
                                <span class="text-danger fw-semibold"><?= h('Não se aplica a este tipo de avaliação') ?></span>
                            <?php else: ?>
                                <?= $avaliacao->destaque === null ? 'Não informado' : ((int)$avaliacao->destaque === 1 ? 'Sim' : 'Não') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100 <?= !in_array($origemEdital, ['R', 'V'], true) ? 'bg-warning bg-opacity-10' : '' ?>">
                        <div class="text-muted small">Indicação ao Prêmio Destaque CNPq</div>
                        <div>
                            <?php if (!in_array($origemEdital, ['R', 'V'], true)): ?>
                                <span class="text-danger fw-semibold"><?= h('Não se aplica a este tipo de avaliação') ?></span>
                            <?php else: ?>
                                <?= $avaliacao->indicado_premio_capes === null ? 'Não informado' : ((int)$avaliacao->indicado_premio_capes === 1 ? 'Sim' : 'Não') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($sumulasGrupos)): ?>
        <div class="card card-info card-outline">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list mr-2"></i>Súmulas da inscrição
                    </h5>
                    <span class="badge badge-light border"><?= count($notasSumulasLista) ?> item(ns)</span>
                </div>
                <?php foreach ($sumulasGrupos as $grupoKey => $grupoSumula): ?>
                    <?php
                    $classeGrupo = $grupoSumula['card'] === 'info' ? 'border-info' : 'border-primary';
                    $classeCabecalho = $grupoSumula['card'] === 'info' ? 'bg-info text-white' : 'bg-primary text-white';
                    $notaFinalGrupoTexto = $grupoKey === 'bolsista' ? $notaSumulaBolsistaTexto : $notaSumulaTexto;
                    ?>
                    <div class="border <?= h($classeGrupo) ?> rounded mb-4">
                        <div class="<?= h($classeCabecalho) ?> px-3 py-2 fw-semibold">
                            <?= h((string)$grupoSumula['titulo']) ?>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bloco</th>
                                            <th>Súmula</th>
                                            <th class="text-end" style="width: 110px">Informado</th>
                                            <th class="text-end" style="width: 110px">Avaliado</th>
                                            <th class="text-end" style="width: 110px">Fator</th>
                                            <th class="text-end" style="width: 110px">Nota</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grupoSumula['itens'] as $notaSumula): ?>
                                            <?php
                                                $sumula = $notaSumula->editais_sumula ?? null;
                                                $bloco = $sumula->editais_sumulas_bloco ?? $notaSumula->editais_sumulas_bloco ?? null;
                                                $quantidadeInformada = $notaSumula->quantidade_original;
                                                if (($quantidadeInformada === null || (int)$quantidadeInformada === 0) && !empty($notaSumula->inscricao_sumula)) {
                                                    $quantidadeInformada = $notaSumula->inscricao_sumula->quantidade ?? $quantidadeInformada;
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <?= h((string)($bloco->nome ?? 'Sem bloco')) ?>
                                                    <?php if (($bloco->max ?? null) !== null): ?>
                                                        <div class="text-muted small">Máx. <?= h((string)$bloco->max) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= h((string)($sumula->sumula ?? 'Súmula não localizada')) ?>
                                                    <?php if (trim((string)($sumula->parametro ?? '')) !== ''): ?>
                                                        <div class="text-muted small"><?= nl2br(h((string)$sumula->parametro)) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end"><?= h((string)($quantidadeInformada ?? 0)) ?></td>
                                                <td class="text-end"><?= h((string)($notaSumula->quantidade_avaliada ?? 0)) ?></td>
                                                <td class="text-end">
                                                    <?= h((string)($sumula->fator ?? '0.00')) ?>
                                                    <?php if (($sumula->max ?? null) !== null): ?>
                                                        <div class="text-muted small">
                                                            Máx. súmula <?= h((string)$sumula->max) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end fw-semibold"><?= h($formatNota($notaSumula->nota ?? 0)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bloco</th>
                                            <th class="text-end" style="width: 130px">Soma</th>
                                            <th class="text-end" style="width: 130px">Máximo</th>
                                            <th class="text-end" style="width: 170px">Pontuação considerada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grupoSumula['totais'] as $totalBloco): ?>
                                            <?php
                                                $maxBloco = $totalBloco['max'];
                                                $totalOriginalBloco = round((float)$totalBloco['total'], 2);
                                                $totalConsideradoBloco = $maxBloco !== null
                                                    ? min($totalOriginalBloco, (float)$maxBloco)
                                                    : $totalOriginalBloco;
                                            ?>
                                            <tr>
                                                <td><?= h((string)$totalBloco['nome']) ?></td>
                                                <td class="text-end"><?= h(number_format($totalOriginalBloco, 2, ',', '.')) ?></td>
                                                <td class="text-end"><?= $maxBloco !== null ? h(number_format((float)$maxBloco, 2, ',', '.')) : 'Sem teto' ?></td>
                                                <td class="text-end fw-semibold"><?= h(number_format($totalConsideradoBloco, 2, ',', '.')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <th colspan="3">Nota final da <?= h(mb_strtolower((string)$grupoSumula['titulo'])) ?></th>
                                            <th class="text-end"><?= h($notaFinalGrupoTexto) ?></th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="mt-3">
                    <strong>Observações da súmula</strong>
                    <div class="border rounded p-3 mt-1 bg-light">
                        <?= $observacaoSumulas !== '' ? nl2br(h($observacaoSumulas)) : 'Não informado' ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($identityAtualTi): ?>
        <div class="text-end mt-3">
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Avaliadores', 'action' => 'deletarNotas', (int)$avaliacao->id],
                'class' => 'd-inline-flex flex-wrap justify-content-end align-items-center gap-1',
            ]) ?>
                <input
                    type="text"
                    name="chamado"
                    class="form-control form-control-sm"
                    style="max-width: 180px"
                    placeholder="Nº chamado"
                    maxlength="60"
                    required
                >
                <?= $this->Form->button(
                    '<i class="fas fa-trash-alt"></i>',
                    [
                        'class' => 'btn btn-sm btn-outline-secondary py-0 px-2',
                        'escapeTitle' => false,
                        'title' => 'Excluir notas lançadas',
                        'onclick' => "return confirm('Confirma a exclusão das notas deste avaliador? A avaliação voltará para aguardando notas.');",
                    ]
                ) ?>
            <?= $this->Form->end() ?>
        </div>
    <?php endif; ?>
</div>
