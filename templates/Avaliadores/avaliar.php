<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AvaliadorBolsista $avaliacao
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $questoes
 * @var array<string, string> $tipoMap
 * @var bool $avaliarSumulas
 * @var array<int, array<string, mixed>> $sumulasAvaliacao
 * @var array<int, array<string, mixed>> $sumulasAvaliacaoBlocos
 */

$nomeCurto = static function (?string $nome): string {
    $nome = trim((string)$nome);
    return $nome !== '' ? $nome : 'Não informado';
};

$tipo = (string)($avaliacao->tipo ?? '');
$origemEdital = (string)($avaliacao->editai->origem ?? '');
$referencia = null;
$edital = $avaliacao->editai->nome ?? 'Não informado';
$unidade = 'Não informada';
$bolsista = 'Não informado';
$orientador = 'Não informado';
$titulo = 'Não informado';
$urlReferencia = null;

if ($tipo === 'N' && !empty($avaliacao->projeto_bolsista)) {
    $item = $avaliacao->projeto_bolsista;
    $referencia = 'Inscrição #' . (int)$item->id;
    $edital = $item->editai->nome ?? $edital;
    $unidade = $item->orientadore->unidade->sigla ?? $unidade;
    $bolsista = $nomeCurto($item->bolsista_usuario->nome ?? null);
    $orientador = $nomeCurto($item->orientadore->nome ?? null);
    $titulo = $item->projeto->titulo ?? $item->projeto->nome ?? $item->sp_titulo ?? $titulo;
    $urlReferencia = ['controller' => 'Padrao', 'action' => 'visualizar', (int)$item->id];
} elseif (in_array($tipo, ['R', 'V', 'Z'], true) && !empty($avaliacao->raic)) {
    $item = $avaliacao->raic;
    $referencia = 'RAIC #' . (int)$item->id;
    $edital = $item->editai->nome ?? $edital;
    $unidade = $item->unidade->sigla ?? $unidade;
    $bolsista = $nomeCurto($item->usuario->nome ?? null);
    $orientador = $nomeCurto($item->orientadore->nome ?? null);
    $titulo = $item->titulo ?? $item->projeto_bolsista->projeto->titulo ?? $titulo;
    $urlReferencia = ['controller' => 'RaicNew', 'action' => 'ver', (int)$item->id];
} elseif ($tipo === 'J' && (!empty($avaliacao->projeto_bolsista) || !empty($avaliacao->pdj_inscrico))) {
    $item = $avaliacao->projeto_bolsista ?? $avaliacao->pdj_inscrico;
    $referencia = 'Inscrição #' . (int)$item->id;
    $edital = $item->editai->nome ?? $edital;
    $unidade = $item->orientadore->unidade->sigla ?? $unidade;
    $bolsista = $nomeCurto($item->bolsista_usuario->nome ?? null);
    $orientador = $nomeCurto($item->orientadore->nome ?? null);
    $titulo = $item->projeto->titulo ?? $item->projeto->nome ?? $item->sp_titulo ?? $titulo;
    $urlReferencia = ['controller' => 'Padrao', 'action' => 'visualizar', (int)$item->id];
} elseif (in_array($tipo, ['R', 'W'], true) && !empty($avaliacao->workshop)) {
    $item = $avaliacao->workshop;
    $referencia = 'Workshop #' . (int)$item->id;
    $edital = $item->editai->nome ?? $edital;
    $unidade = $item->unidade->sigla ?? $unidade;
    $bolsista = $nomeCurto($item->usuario->nome ?? null);
    $orientador = $nomeCurto($item->orientadore->nome ?? null);
    $titulo = $item->pdj_inscrico->projeto->titulo ?? $item->pdj_inscrico->projeto->nome ?? $titulo;
}

$referencia = $referencia ?? ('Avaliação #' . (int)$avaliacao->id);
$dadosQuesitos = (array)$this->request->getData('q', []);
$dadosSumulas = (array)$this->request->getData('sumula', []);
$sumulasAvaliacaoBlocos = $sumulasAvaliacaoBlocos ?? [];
if ($sumulasAvaliacaoBlocos === [] && !empty($sumulasAvaliacao)) {
    $sumulasAvaliacaoBlocos = [[
        'titulo' => 'súmula do orientador',
        'campo' => 'sumula',
        'destino' => 'orientador',
        'linhas' => $sumulasAvaliacao,
    ]];
}
$valorSumulaLancamento = static function (array $dados, int $sumulaId): string {
    if (array_key_exists($sumulaId, $dados)) {
        return (string)$dados[$sumulaId];
    }
    if (array_key_exists((string)$sumulaId, $dados)) {
        return (string)$dados[(string)$sumulaId];
    }

    return '';
};
$campoPendente = static function ($valor): bool {
    return trim((string)$valor) === '';
};
$origemExigeDestaque = in_array($origemEdital, ['R', 'V'], true);
$observacaoQuesitosPendente = $campoPendente($this->request->getData('observacao_avaliador', ''));
$parecerPendente = $campoPendente($this->request->getData('parecer', ''));
$destaquePendente = $origemExigeDestaque && $campoPendente($this->request->getData('destaque', ''));
$premioPendente = $origemExigeDestaque && $campoPendente($this->request->getData('indicado_premio_capes', ''));
$observacaoSumulasPendente = !empty($avaliarSumulas)
    && !empty($sumulasAvaliacaoBlocos)
    && $campoPendente($this->request->getData('observacao_sumulas', ''));
?>

<div class="container-fluid p-1 pt-1">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Lançar Avaliação</h4>
                    <p class="text-muted mb-0"><?= h($tipoMap[$tipo] ?? $tipo) ?> - <?= h($referencia) ?></p>
                </div>
                <?= $this->Html->link('Voltar', ['action' => 'avaliacoes'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 160px">Edital</th>
                                <td><?= h($edital) ?></td>
                            </tr>
                            <tr>
                                <th>Unidade</th>
                                <td><?= h($unidade) ?></td>
                            </tr>
                            <tr>
                                <th>Bolsista/Candidato</th>
                                <td><?= h($bolsista) ?></td>
                            </tr>
                            <tr>
                                <th>Orientador</th>
                                <td><?= h($orientador) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 160px">Referência</th>
                                <td><?= h($referencia) ?></td>
                            </tr>
                            <tr>
                                <th>Título</th>
                                <td><?= h($titulo) ?></td>
                            </tr>
                            <tr>
                                <th>Consulta</th>
                                <td>
                                    <?php if (!empty($urlReferencia)): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-folder-open"></i> Abrir dados completos',
                                            $urlReferencia,
                                            ['class' => 'btn btn-sm btn-primary', 'target' => '_blank', 'escape' => false]
                                        ) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sem tela de consulta vinculada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-body">
            <h4 class="mb-3">Formulário de Avaliação</h4>

            <?= $this->Form->create(null) ?>
                <table class="table table-bordered align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Critério de Avaliação</th>
                            <th style="width: 120px">Nota mínima</th>
                            <th style="width: 120px">Nota máxima</th>
                            <th>Parâmetros</th>
                            <th style="width: 130px">Sua nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questoes as $questao): ?>
                            <?php
                            $questaoId = (int)$questao->id;
                            $valorQuestao = $dadosQuesitos[$questaoId] ?? $dadosQuesitos[(string)$questaoId] ?? '';
                            $questaoPendente = $campoPendente($valorQuestao);
                            ?>
                            <tr>
                                <td><?= h((string)$questao->questao) ?></td>
                                <td><?= h((string)$questao->limite_min) ?></td>
                                <td><?= h((string)$questao->limite_max) ?></td>
                                <td><?= nl2br(h((string)$questao->prametros)) ?></td>
                                <td>
                                    <input
                                        name="q[<?= (int)$questao->id ?>]"
                                        type="number"
                                        step="0.01"
                                        min="<?= h((string)$questao->limite_min) ?>"
                                        max="<?= h((string)$questao->limite_max) ?>"
                                        class="form-control<?= $questaoPendente ? ' is-invalid' : '' ?>"
                                        value="<?= h((string)$valorQuestao) ?>"
                                    >
                                    <?php if ($questaoPendente): ?>
                                        <div class="invalid-feedback">Informe a nota.</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="5">
                                <?= $this->Form->control('observacao_avaliador', [
                                    'label' => 'Observações dos quesitos',
                                    'rows' => 4,
                                    'class' => 'form-control' . ($observacaoQuesitosPendente ? ' is-invalid' : ''),
                                ]) ?>
                                <?php if ($observacaoQuesitosPendente): ?>
                                    <div class="invalid-feedback d-block">Informe as observações dos quesitos.</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!in_array($tipo, ['V', 'Z'], true)): ?>
                            <tr>
                                <td colspan="4">O orientador anexou o Parecer do Comitê de Ética em Pesquisa?</td>
                                <td>
                                    <?= $this->Form->control('parecer', [
                                        'label' => false,
                                        'options' => [
                                            'I' => 'Não se aplica',
                                            'N' => 'É necessário mas não anexou',
                                            'S' => 'Anexou',
                                        ],
                                        'empty' => '- Selecione -',
                                        'class' => 'form-control' . ($parecerPendente ? ' is-invalid' : ''),
                                    ]) ?>
                                    <?php if ($parecerPendente): ?>
                                        <div class="invalid-feedback d-block">Informe a situação do parecer.</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($origemExigeDestaque): ?>
                            <tr>
                                <td colspan="2">
                                    <?= $this->Form->control('destaque', [
                                        'label' => 'O aluno se destacou?',
                                        'options' => [0 => 'Não', 1 => 'Sim'],
                                        'empty' => '- Escolha -',
                                        'class' => 'form-control' . ($destaquePendente ? ' is-invalid' : ''),
                                    ]) ?>
                                    <?php if ($destaquePendente): ?>
                                        <div class="invalid-feedback d-block">Informe se o aluno se destacou.</div>
                                    <?php endif; ?>
                                </td>
                                <td colspan="3">
                                    <?= $this->Form->control('indicado_premio_capes', [
                                        'label' => 'Indica ao Prêmio Destaque CNPq?',
                                        'options' => [0 => 'Não', 1 => 'Sim'],
                                        'empty' => '- Escolha -',
                                        'class' => 'form-control' . ($premioPendente ? ' is-invalid' : ''),
                                    ]) ?>
                                    <?php if ($premioPendente): ?>
                                        <div class="invalid-feedback d-block">Informe se há indicação ao Prêmio Destaque CNPq.</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($avaliarSumulas)): ?>
                            <tr>
                                <td colspan="5">
                                    <?php if (empty($sumulasAvaliacaoBlocos)): ?>
                                        <div class="alert alert-light border mb-0">Não há súmulas cadastradas para este edital.</div>
                                    <?php else: ?>
                                        <?php foreach ($sumulasAvaliacaoBlocos as $blocoSumula): ?>
                                            <?php
                                            $campoSumula = (string)$blocoSumula['campo'];
                                            $dadosSumulasBloco = (array)$this->request->getData($campoSumula, []);
                                            $linhasSumula = (array)$blocoSumula['linhas'];
                                            $destinoSumula = (string)($blocoSumula['destino'] ?? '');
                                            $classeBloco = $destinoSumula === 'bolsista' ? 'border-info' : 'border-primary';
                                            $classeCabecalho = $destinoSumula === 'bolsista' ? 'bg-info text-white' : 'bg-primary text-white';
                                            ?>
                                            <div class="border <?= h($classeBloco) ?> rounded mb-4">
                                                <div class="<?= h($classeCabecalho) ?> px-3 py-2 fw-semibold">
                                                    <?= h(ucfirst((string)$blocoSumula['titulo'])) ?>
                                                </div>
                                                <div class="p-3 table-responsive">
                                                <table class="table table-sm table-bordered align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Bloco</th>
                                                            <th>Súmula</th>
                                                            <th style="width: 130px">Informado</th>
                                                            <th style="width: 110px">Fator</th>
                                                            <th style="width: 170px">Quantidade avaliada</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($linhasSumula as $linha): ?>
                                                            <?php
                                                            $sumula = $linha['sumula'];
                                                            $quantidadeOriginal = (int)($linha['quantidade'] ?? 0);
                                                            $sumulaId = (int)$sumula->id;
                                                            $valorSumula = $valorSumulaLancamento($dadosSumulasBloco, $sumulaId);
                                                            $sumulaPendente = $campoPendente($valorSumula);
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <?= h((string)($sumula->editais_sumulas_bloco->nome ?? 'Sem bloco')) ?>
                                                                    <?php if ($sumula->editais_sumulas_bloco->max !== null): ?>
                                                                        <div class="text-muted small">
                                                                            Máx. <?= h((string)$sumula->editais_sumulas_bloco->max) ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?= h((string)$sumula->sumula) ?>
                                                                    <?php if (trim((string)($sumula->parametro ?? '')) !== ''): ?>
                                                                        <div class="text-muted small"><?= nl2br(h((string)$sumula->parametro)) ?></div>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= h((string)$quantidadeOriginal) ?></td>
                                                                <td>
                                                                    <?= h((string)($sumula->fator ?? '0.00')) ?>
                                                                    <?php if (($sumula->max ?? null) !== null): ?>
                                                                        <div class="text-muted small">
                                                                            Máx. súmula <?= h((string)$sumula->max) ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <input
                                                                        name="<?= h($campoSumula) ?>[<?= (int)$sumula->id ?>]"
                                                                        type="number"
                                                                        step="1"
                                                                        min="0"
                                                                        max="50"
                                                                        class="form-control<?= $sumulaPendente ? ' is-invalid' : '' ?>"
                                                                        value="<?= h((string)$valorSumula) ?>"
                                                                    >
                                                                    <?php if ($sumulaPendente): ?>
                                                                        <div class="invalid-feedback">Informe a quantidade.</div>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="mt-3">
                                            <?= $this->Form->control('observacao_sumulas', [
                                                'label' => 'Observações da súmula',
                                                'rows' => 4,
                                                'class' => 'form-control' . ($observacaoSumulasPendente ? ' is-invalid' : ''),
                                            ]) ?>
                                            <?php if ($observacaoSumulasPendente): ?>
                                                <div class="invalid-feedback d-block">Informe as observações da súmula.</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?= $this->Form->button('Conferir lançamento de notas', [
                    'class' => 'btn btn-success btn-block',
                    'onclick' => "return confirm('As notas serão compiladas para sua verificação e confirmação na próxima tela. Deseja continuar?');",
                ]) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
