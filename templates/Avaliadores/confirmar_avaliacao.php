<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AvaliadorBolsista $avaliacao
 * @var array<int, \App\Model\Entity\Question> $questoesLista
 * @var array<string, string> $tipoMap
 * @var bool $avaliarSumulas
 * @var array<int, array<string, mixed>> $sumulasAvaliacao
 * @var array<string, mixed> $dadosLancamento
 */

$tipo = (string)($avaliacao->tipo ?? '');
$origemEdital = (string)($avaliacao->editai->origem ?? '');
$parecerMap = [
    'I' => 'Não se aplica',
    'N' => 'É necessário mas não anexou',
    'S' => 'Anexou',
];
$simNaoMap = [0 => 'Não', 1 => 'Sim'];
$dadosQuesitos = (array)($dadosLancamento['q'] ?? []);
$dadosSumulas = (array)($dadosLancamento['sumula'] ?? []);
$valorSumulaLancamento = static function (array $dados, int $sumulaId): int {
    if (array_key_exists($sumulaId, $dados)) {
        return (int)$dados[$sumulaId];
    }
    if (array_key_exists((string)$sumulaId, $dados)) {
        return (int)$dados[(string)$sumulaId];
    }

    return 0;
};
$referencia = match ($tipo) {
    'N' => 'Inscrição #' . (int)($avaliacao->projeto_bolsista->id ?? $avaliacao->bolsista ?? 0),
    'V', 'Z' => 'RAIC #' . (int)($avaliacao->raic->id ?? $avaliacao->bolsista ?? 0),
    'W' => 'Workshop #' . (int)($avaliacao->workshop->id ?? $avaliacao->bolsista ?? 0),
    default => 'Avaliação #' . (int)$avaliacao->id,
};
$bolsista = match ($tipo) {
    'N' => $avaliacao->projeto_bolsista->bolsista_usuario->nome ?? 'Não informado',
    'V', 'Z' => $avaliacao->raic->usuario->nome ?? 'Não informado',
    'W' => $avaliacao->workshop->usuario->nome ?? 'Não informado',
    default => 'Não informado',
};
$orientador = match ($tipo) {
    'N' => $avaliacao->projeto_bolsista->orientadore->nome ?? 'Não informado',
    'V', 'Z' => $avaliacao->raic->orientadore->nome ?? 'Não informado',
    'W' => $avaliacao->workshop->orientadore->nome ?? 'Não informado',
    default => 'Não informado',
};

$hiddenLancamento = static function (array $dados): string {
    $html = '';
    foreach ((array)($dados['q'] ?? []) as $id => $valor) {
        $html .= sprintf(
            '<input type="hidden" name="q[%s]" value="%s">',
            h((string)$id),
            h((string)$valor)
        );
    }
    foreach ((array)($dados['sumula'] ?? []) as $id => $valor) {
        $html .= sprintf(
            '<input type="hidden" name="sumula[%s]" value="%s">',
            h((string)$id),
            h((string)$valor)
        );
    }
    foreach (['observacao_avaliador', 'observacao_sumulas', 'parecer', 'destaque', 'indicado_premio_capes'] as $campo) {
        if (array_key_exists($campo, $dados)) {
            $html .= sprintf(
                '<input type="hidden" name="%s" value="%s">',
                h($campo),
                h((string)$dados[$campo])
            );
        }
    }

    return $html;
};

$totalQuesitos = 0.0;
$totaisSumulasPorBloco = [];
?>

<div class="container-fluid p-1 pt-1">
    <div class="card card-warning card-outline mb-3">
        <div class="card-body">
            <h4 class="mb-1">Conferir avaliação</h4>
            <p class="text-muted mb-0">
                <?= h($tipoMap[$tipo] ?? $tipo) ?> - <?= h($referencia) ?>
            </p>
            <div class="row g-2 mt-3">
                <div class="col-md-6">
                    <strong>Bolsista/Candidato:</strong>
                    <?= h((string)$bolsista) ?>
                </div>
                <div class="col-md-6">
                    <strong>Orientador:</strong>
                    <?= h((string)$orientador) ?>
                </div>
            </div>
            <p class="text-danger fw-semibold mt-3 mb-0">
                Confira os dados das notas. 
                </br>Caso precise alterar, clique no botão 'Alterar Notas' no fim desta página.
                </br>Se optar por 'Confirmar e Finalizar a Avaliação', não será possível editar posteriormente. Confira cuidadosamente.
            </p>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-body">
            <h5 class="mb-3">Quesitos</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Critério</th>
                            <th style="width: 130px">Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questoesLista as $questao): ?>
                            <?php
                            $questaoId = (int)$questao->id;
                            $nota = (float)str_replace(',', '.', (string)($dadosQuesitos[$questaoId] ?? $dadosQuesitos[(string)$questaoId] ?? 0));
                            $totalQuesitos += $nota;
                            ?>
                            <tr>
                                <td><?= h((string)$questao->questao) ?></td>
                                <td><?= h(number_format($nota, 2, ',', '.')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <th>Total dos quesitos</th>
                            <th><?= h(number_format($totalQuesitos, 2, ',', '.')) ?></th>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <strong>Observações dos quesitos</strong>
                <div class="border rounded p-3 mt-1">
                    <?= nl2br(h((string)($dadosLancamento['observacao_avaliador'] ?? ''))) ?>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <strong>Parecer do Comitê de Ética</strong><br>
                    <?= h($parecerMap[(string)($dadosLancamento['parecer'] ?? '')] ?? 'Não informado') ?>
                </div>
                <?php if (in_array($origemEdital, ['R', 'V'], true)): ?>
                    <div class="col-md-4">
                        <strong>Destacou-se?</strong><br>
                        <?= h($simNaoMap[(int)($dadosLancamento['destaque'] ?? -1)] ?? 'Não informado') ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Indicação ao Prêmio Destaque CNPq</strong><br>
                        <?= h($simNaoMap[(int)($dadosLancamento['indicado_premio_capes'] ?? -1)] ?? 'Não informado') ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($avaliarSumulas) && !empty($sumulasAvaliacao)): ?>
                <hr>
                <h5 class="mb-3">Súmula da inscrição</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Bloco</th>
                                <th>Súmula</th>
                                <th style="width: 120px">Informado</th>
                                <th style="width: 120px">Avaliado</th>
                                <th style="width: 100px">Fator</th>
                                <th style="width: 110px">Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sumulasAvaliacao as $linha): ?>
                                <?php
                                $sumula = $linha['sumula'];
                                $bloco = $sumula->editais_sumulas_bloco ?? null;
                                $sumulaId = (int)$sumula->id;
                                $blocoId = (int)($sumula->editais_sumula_bloco_id ?? 0);
                                $quantidadeOriginal = (int)($linha['quantidade'] ?? 0);
                                $quantidadeAvaliada = $valorSumulaLancamento($dadosSumulas, $sumulaId);
                                $fator = (float)($sumula->fator ?? 0);
                                $notaItem = round($quantidadeAvaliada * $fator, 2);
                                if (($sumula->max ?? null) !== null) {
                                    $notaItem = min($notaItem, (float)$sumula->max);
                                }
                                $totaisSumulasPorBloco[$blocoId]['nome'] = (string)($bloco->nome ?? 'Sem bloco');
                                $totaisSumulasPorBloco[$blocoId]['max'] = $bloco->max ?? null;
                                $totaisSumulasPorBloco[$blocoId]['total'] = ($totaisSumulasPorBloco[$blocoId]['total'] ?? 0) + $notaItem;
                                ?>
                                <tr>
                                    <td>
                                        <?= h((string)($bloco->nome ?? 'Sem bloco')) ?>
                                        <?php if (($bloco->max ?? null) !== null): ?>
                                            <div class="text-muted small">Máx. <?= h((string)$bloco->max) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= h((string)$sumula->sumula) ?>
                                        <?php if (trim((string)($sumula->parametro ?? '')) !== ''): ?>
                                            <div class="text-muted small"><?= nl2br(h((string)$sumula->parametro)) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= h((string)$quantidadeOriginal) ?></td>
                                    <td><?= h((string)$quantidadeAvaliada) ?></td>
                                    <td>
                                        <?= h((string)($sumula->fator ?? '0.00')) ?>
                                        <?php if (($sumula->max ?? null) !== null): ?>
                                            <div class="text-muted small">Máx. súmula <?= h((string)$sumula->max) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= h(number_format($notaItem, 2, ',', '.')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Bloco</th>
                                <th style="width: 130px">Soma</th>
                                <th style="width: 130px">Máximo</th>
                                <th style="width: 160px">Pontuação considerada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $notaSumulaFinal = 0.0; ?>
                            <?php foreach ($totaisSumulasPorBloco as $totalBloco): ?>
                                <?php
                                $totalOriginalBloco = round((float)$totalBloco['total'], 2);
                                $maxBloco = $totalBloco['max'];
                                $totalConsideradoBloco = $maxBloco !== null
                                    ? min($totalOriginalBloco, (float)$maxBloco)
                                    : $totalOriginalBloco;
                                $notaSumulaFinal += $totalConsideradoBloco;
                                ?>
                                <tr>
                                    <td><?= h((string)$totalBloco['nome']) ?></td>
                                    <td><?= h(number_format($totalOriginalBloco, 2, ',', '.')) ?></td>
                                    <td><?= $maxBloco !== null ? h(number_format((float)$maxBloco, 2, ',', '.')) : 'Sem teto' ?></td>
                                    <td><?= h(number_format($totalConsideradoBloco, 2, ',', '.')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th colspan="3">Nota final da súmula</th>
                                <th><?= h(number_format($notaSumulaFinal, 2, ',', '.')) ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <strong>Observações da súmula</strong>
                    <div class="border rounded p-3 mt-1">
                        <?= nl2br(h((string)($dadosLancamento['observacao_sumulas'] ?? ''))) ?>
                    </div>
                </div>
            <?php endif; ?>

            <hr>
            <div class="d-flex flex-wrap gap-2 justify-content-between">
                <?= $this->Form->create(null) ?>
                    <?= $hiddenLancamento($dadosLancamento) ?>
                    <input type="hidden" name="_voltar_lancamento" value="1">
                    <?= $this->Form->button('Alterar notas', ['class' => 'btn btn-outline-secondary']) ?>
                <?= $this->Form->end() ?>

                <?= $this->Form->create(null) ?>
                    <?= $hiddenLancamento($dadosLancamento) ?>
                    <input type="hidden" name="confirmar_lancamento" value="1">
                    <?= $this->Form->button('Confirmar e finalizar avaliação', ['class' => 'btn btn-success']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
