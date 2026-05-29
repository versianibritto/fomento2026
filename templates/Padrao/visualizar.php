<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $inscricao
 * @var \Cake\Datasource\EntityInterface $edital
 * @var array<string, array<int, array<string, mixed>>> $anexosPorBloco
 * @var array<int, array<string, mixed>> $anexosProjetoTipos924
 * @var array<int, array<string, mixed>> $anexosSubprojetoTela
 * @var \Cake\Datasource\EntityInterface|null $relatorioFinalAtual
 * @var bool $podeGerenciarRelatorioFinal
 * @var bool $podeEnviarNovoRelatorioFinal
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $historicos
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $avaliacoes
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface|array $sumulasEdital
 * @var array<int, mixed> $quantidadesSumula
 * @var array<string, string> $origens
 * @var array<string, string> $cotas
 * @var array<string, string> $fontes
 * @var array<string, string> $resultadoMap
 * @var array<string, string> $statusAvaliacaoMap
 * @var string $controllerFluxo
 * @var string $origemAtual
 */
$naoInformado = '<span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>';
$filhosMap = [
    '0' => 'não possui filhos, ou sao maiores de 8 anos',
    '1' => 'Possui um filho menor de 8 anos',
    '2' => 'Possui mais de um filho menor de 8 anos',
];
$origemAtual = strtoupper((string)($origemAtual ?? ($inscricao->origem ?? '')));
$ehYoda = !empty($this->request->getAttribute('identity')['yoda']);
$isRenovacao = $origemAtual === 'R';
$isProgramaSupervisor = (int)($edital->programa_id ?? 0) === 1;
$rotuloOrientador = $isProgramaSupervisor ? 'Supervisor' : 'Orientador';
$rotuloOrientadorMinusculo = $isProgramaSupervisor ? 'supervisor' : 'orientador';
$rotuloOrientadora = $isProgramaSupervisor ? 'Supervisora' : 'Orientadora';
$resultadoEditalTimestamp = !empty($edital->resultado) ? strtotime((string)$edital->resultado) : false;
$podeVisualizarResultadoAvaliacao = $ehYoda || ($resultadoEditalTimestamp !== false && $resultadoEditalTimestamp < time());
$pdjInscricaoId = !empty($inscricao->pdj_inscricoe_id) ? (int)$inscricao->pdj_inscricoe_id : null;
$ehTrocaProjeto = (int)($inscricao->troca_projeto ?? 0) === 1;
$mostrarReferenciaAnterior = in_array($origemAtual, ['R', 'S', 'A'], true) || $ehTrocaProjeto;
$referenciaAnteriorValor = null;
$referenciaAnteriorDescricao = null;
if ($origemAtual === 'R') {
    $referenciaAnteriorValor = $inscricao->referencia_inscricao_anterior ?? null;
    $referenciaAnteriorDescricao = 'inscricao que foi renovada';
} elseif (in_array($origemAtual, ['S', 'A'], true)) {
    $referenciaAnteriorValor = $inscricao->bolsista_anterior ?? null;
    $referenciaAnteriorDescricao = 'inscricao que foi substituida';
} elseif ($ehTrocaProjeto) {
    $referenciaAnteriorValor = $inscricao->referencia_inscricao_anterior ?? null;
    $referenciaAnteriorDescricao = 'inscricao do ' . $rotuloOrientadorMinusculo . ' original';
}
$referenciaAnteriorId = (is_numeric((string)$referenciaAnteriorValor) && (int)$referenciaAnteriorValor > 0)
    ? (int)$referenciaAnteriorValor
    : null;
$trocaProjetoTexto = (int)($inscricao->troca_projeto ?? 0) === 1 ? 'Sim' : 'Não';
$herancaTexto = (int)($inscricao->heranca ?? 0) === 1 ? 'Sim' : 'Não';
$dataInicioTexto = null;
$dataFimTexto = null;
$temDataInicio = !empty($inscricao->data_inicio);
$temDataFim = !empty($inscricao->data_fim);
if (!$temDataInicio && !$temDataFim) {
    $dataInicioTexto = 'Não implantado';
    $dataFimTexto = 'Não implantado';
} elseif ($temDataInicio && !$temDataFim) {
    if ($inscricao->data_inicio instanceof \Cake\I18n\FrozenTime) {
        $dataInicioTexto = $inscricao->data_inicio->i18nFormat('dd/MM/yyyy');
    } else {
        $tsInicio = strtotime((string)$inscricao->data_inicio);
        $dataInicioTexto = $tsInicio ? date('d/m/Y', $tsInicio) : 'Não informado';
    }
    $dataFimTexto = ((int)($inscricao->vigente ?? 0) === 1) ? 'Ainda vigente' : 'Encerrada';
} else {
    if ($inscricao->data_inicio instanceof \Cake\I18n\FrozenTime) {
        $dataInicioTexto = $inscricao->data_inicio->i18nFormat('dd/MM/yyyy');
    } else {
        $tsInicio = strtotime((string)$inscricao->data_inicio);
        $dataInicioTexto = $tsInicio ? date('d/m/Y', $tsInicio) : 'Não informado';
    }
    if ($inscricao->data_fim instanceof \Cake\I18n\FrozenTime) {
        $dataFimTexto = $inscricao->data_fim->i18nFormat('dd/MM/yyyy');
    } else {
        $tsFim = strtotime((string)$inscricao->data_fim);
        $dataFimTexto = $tsFim ? date('d/m/Y', $tsFim) : 'Não informado';
    }
}
$formatarDataHoraAnexo = static function ($valor): string {
    if (empty($valor)) {
        return 'Não informado';
    }

    if (is_object($valor) && method_exists($valor, 'i18nFormat')) {
        return (string)$valor->i18nFormat('dd/MM/yyyy HH:mm');
    }

    $ts = strtotime((string)$valor);
    return $ts ? date('d/m/Y H:i', $ts) : 'Não informado';
};
$anexosSumulaPorTipo = [];
foreach ((array)($anexosPorBloco['O'] ?? []) as $anexoSumula) {
    $tipoIdSumula = (int)($anexoSumula['tipo_id'] ?? 0);
    if ($tipoIdSumula <= 0) {
        continue;
    }
    if (
        !isset($anexosSumulaPorTipo[$tipoIdSumula])
        || (empty($anexosSumulaPorTipo[$tipoIdSumula]['arquivo']) && !empty($anexoSumula['arquivo']))
    ) {
        $anexosSumulaPorTipo[$tipoIdSumula] = $anexoSumula;
    }
}
$renderAnexoSumula = static function (?array $anexo, bool $exigido, string $tipoNomeFallback) use ($formatarDataHoraAnexo): string {
    $tipoNome = !empty($anexo['tipo_nome']) ? h((string)$anexo['tipo_nome']) : h($tipoNomeFallback);
    if (empty($anexo) || empty($anexo['arquivo'])) {
        if (!$exigido) {
            return '<div class="sumula-anexo-status">'
                . '<span class="sumula-anexo-status-tipo">' . $tipoNome . '</span>'
                . '<span class="badge border border-secondary text-secondary bg-transparent fw-normal">Não se aplica</span>'
                . '</div>';
        }

        return '<div class="sumula-anexo-status">'
            . '<span class="sumula-anexo-status-tipo">' . $tipoNome . '</span>'
            . '<span class="badge border border-danger text-danger bg-transparent fw-normal">Anexo exigido, mas não imputado</span>'
            . '</div>';
    }

    $arquivo = (string)$anexo['arquivo'];
    $usuarioNome = !empty($anexo['usuario_nome']) ? h((string)$anexo['usuario_nome']) : 'Não informado';
    $created = h($formatarDataHoraAnexo($anexo['created'] ?? null));

    return '<div class="sumula-anexo-inline">'
        . '<div class="sumula-anexo-info">'
        . '<span class="sumula-anexo-label">' . $tipoNome . '</span>'
        . '<span class="sumula-anexo-arquivo">' . h($arquivo) . '</span>'
        . '<span class="sumula-anexo-meta">Incluído por ' . $usuarioNome . ' em ' . $created . '</span>'
        . '</div>'
        . '<a href="/uploads/anexos/' . h($arquivo) . '" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">'
        . '<i class="fa fa-download"></i>'
        . '</a>'
        . '</div>';
};
?>
<style>
    .nav-pills.tabs-visualizacao {
        gap: 0.5rem;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }
    .nav-pills.tabs-visualizacao .nav-link {
        border: 1px solid #d7dde4;
        border-radius: 999px;
        color: #344054;
        background: #f3f5f7;
        font-weight: 600;
        padding: 0.45rem 0.9rem;
        transition: all 0.15s ease-in-out;
    }
    .nav-pills.tabs-visualizacao .nav-link:hover {
        border-color: #9aa4b2;
        color: #1f2937;
        background: #e9edf2;
    }
    .nav-pills.tabs-visualizacao .nav-link.active {
        color: #fff;
        background: #0b5ed7;
        border-color: #0b5ed7;
        box-shadow: 0 1px 4px rgba(11, 94, 215, 0.22);
    }
    .resumo-principal {
        line-height: 1.45;
    }
    .resumo-principal > div {
        margin-bottom: 0.2rem;
    }
    .resumo-principal strong {
        color: #344054;
        font-weight: 600;
    }
    .anexos-lista {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    .anexos-lista li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        background: #fff;
        padding: 0.45rem 0.6rem;
        margin-bottom: 0.4rem;
    }
    .anexos-lista li:last-child {
        margin-bottom: 0;
    }
    .anexos-lista .anexo-titulo {
        min-width: 0;
    }
    .anexos-lista .anexo-tipo {
        display: block;
        color: #495057;
    }
    .anexos-lista .anexo-arquivo {
        display: block;
        color: #6c757d;
        font-size: 0.8rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .anexos-lista .anexo-meta {
        display: block;
        color: #6c757d;
        font-size: 0.78rem;
        margin-top: 0.15rem;
    }
    .historico-lista {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .historico-item {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        background: #fff;
        padding: 0.75rem;
    }
    .historico-item .meta {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 0.35rem;
    }
    .sumula-campos {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        background: #f8f9fa;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    .sumula-campo-item {
        align-items: center;
        padding: 0.45rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .sumula-campo-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .sumula-campo-valor {
        min-width: 0;
    }
    .sumula-campo-anexo {
        min-width: 0;
    }
    .sumula-anexo-inline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        min-width: 0;
    }
    .sumula-anexo-info {
        min-width: 0;
    }
    .sumula-anexo-label,
    .sumula-anexo-arquivo,
    .sumula-anexo-meta,
    .sumula-anexo-status-tipo {
        display: block;
    }
    .sumula-anexo-label,
    .sumula-anexo-status-tipo {
        color: #495057;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .sumula-anexo-status {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.15rem;
        max-width: 100%;
    }
    .sumula-anexo-arquivo {
        color: #6c757d;
        font-size: 0.8rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sumula-anexo-meta {
        color: #6c757d;
        font-size: 0.72rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    @media (max-width: 767.98px) {
        .sumula-anexo-inline {
            justify-content: flex-start;
        }
    }
    .sumula-separador {
        margin: 1rem 0 0.75rem;
    }
    .visualizacao-tabela {
        overflow-x: visible;
    }
    .visualizacao-tabela .table {
        margin-bottom: 0;
    }
    .status-avaliacao-icone {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 0.85rem;
        height: 0.85rem;
        margin-right: 0.4rem;
        vertical-align: -0.08rem;
    }
    .status-avaliacao-icone--finalizado {
        background: #198754;
        border-radius: 999px;
    }
    .status-avaliacao-icone--aguardando {
        background: #f0ad4e;
        border-radius: 999px;
    }
    .status-avaliacao-icone--desvinculado {
        color: #dc3545;
        font-size: 0.85rem;
        font-weight: 700;
        line-height: 1;
    }
    .homologacao-expansivo {
        border: 1px solid #d7dde4;
        border-radius: 8px;
        background: #fff;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .homologacao-expansivo > summary {
        cursor: pointer;
        padding: 0.85rem 1rem;
        font-weight: 600;
        color: #344054;
        background: #f8fafc;
        border-bottom: 1px solid transparent;
    }
    .homologacao-expansivo[open] > summary {
        border-bottom-color: #e5e7eb;
    }
    .homologacao-status-card {
        border: 1px solid #d7dde4;
        border-radius: 8px;
        margin: 1rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .homologacao-status-card.status-homologado {
        background: #f0fdf4;
        border-color: #86efac;
    }
    .homologacao-status-card.status-nao-homologado {
        background: #fef2f2;
        border-color: #fca5a5;
    }
    .homologacao-status-card.status-homologado-pendencia {
        background: #fffbeb;
        border-color: #fcd34d;
    }
    .homologacao-status-card.status-nao-verificado {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
</style>
<div class="container mt-4">
    <h4 class="mb-2">
        Visualização da <?= $isRenovacao ? 'Renovação' : 'Inscrição' ?> #<?= (int)$inscricao->id ?>
        <?php if ($pdjInscricaoId !== null): ?>
            / PDJ Inscrição #<?= h((string)$pdjInscricaoId) ?>
        <?php endif; ?>
        <?php if (!empty($inscricao->deleted)): ?>
            <span class="badge bg-danger ms-2">Deletado</span>
        <?php endif; ?>
    </h4>
    <?php
        $faseAtual = (int)($inscricao->fase_id ?? 0);
        $controllerFluxo = (string)($controllerFluxo ?? ($isRenovacao ? 'Renovacoes' : 'Inscricoes'));
        $identityTela = $this->request->getAttribute('identity');
        $ehTIVisualizacao = in_array((int)($identityTela->id ?? 0), [1, 8088], true);
        $podeImplementarBolsaManual = empty($inscricao->deleted)
            && ($ehTIVisualizacao || in_array($faseAtual, [8, 9], true));
        $homologadoAtualTela = strtoupper((string)($inscricao->homologado ?? ''));
        $homologadoTextoTela = match ($homologadoAtualTela) {
            'S' => 'Sim',
            'P' => 'Homologado com pendência',
            'N' => 'Não',
            default => 'Não verificado',
        };
        $homologadoBadgeTela = match ($homologadoAtualTela) {
            'S' => 'bg-success',
            'P' => 'bg-warning text-dark',
            'N' => 'bg-danger',
            default => 'bg-secondary',
        };
        $homologadoStatusCardClasseTela = match ($homologadoAtualTela) {
            'S' => 'status-homologado',
            'P' => 'status-homologado-pendencia',
            'N' => 'status-nao-homologado',
            default => 'status-nao-verificado',
        };
        $homologadoDataTextoTela = $formatarDataHoraAnexo($inscricao->homologado_data ?? null);
        $homologadoUsuarioTextoTela = trim((string)($inscricao->homologador->nome ?? ''));
        $homologadoJustificativaTextoTela = trim((string)($inscricao->homologado_justificativa ?? ''));
        $origemTela = strtoupper((string)($inscricao->origem ?? ''));
        $resultadoJaLancadoTela = trim((string)($inscricao->resultado ?? '')) !== '';
        $labelAcaoResultadoTela = $resultadoJaLancadoTela ? 'Alterar Resultado' : 'Lançar Resultado';
        $podeAlterarResultado = empty($inscricao->deleted)
            && in_array($origemTela, ['N', 'R'], true)
            && in_array($faseAtual, [4, 8, 9, 10], true)
            && in_array($homologadoAtualTela, ['S', 'N', 'P'], true);
        $ehOrientadorTela = !empty($identityTela->id) && (int)$identityTela->id === (int)($inscricao->orientador ?? 0);
        $podeDesistirProcesso = !$isRenovacao
            && $origemTela === 'N'
            && $faseAtual < 8
            && empty($inscricao->deleted)
            && $ehOrientadorTela;
    ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php if (in_array($faseAtual, [1, 3], true)): ?>
            <?= $this->Html->link('Editar', [
                'controller' => $controllerFluxo,
                'action' => 'direcionarAcao',
                (int)$edital->id,
                (int)$inscricao->id,
                'E',
            ], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= $this->Html->link('Termo', [
                'controller' => $controllerFluxo,
                'action' => 'direcionarAcao',
                (int)$edital->id,
                (int)$inscricao->id,
                'T',
            ], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

        <?php  if ((int)($inscricao->vigente ?? 0) === 1): ?>
            <?php
            $programaIdTermo = (int)($inscricao->programa_id ?? $edital->programa_id ?? 0);
            $acaoTermoVigente = $programaIdTermo === 1 ? 'termopdj' : 'imprimirSolicitacao';
            ?>
            <?= $this->Html->link('Termo 2025', [
                'controller' => 'Padrao',
                'action' => $acaoTermoVigente,
                (int)$inscricao->id,
            ], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

        <?php if (in_array($faseAtual, [5], true)): ?>
            <?= $this->Html->link('Anexar Termo', [
                'controller' => $controllerFluxo,
                'action' => 'direcionarAcao',
                (int)$edital->id,
                (int)$inscricao->id,
                'F',
            ], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

        <?php if (in_array($faseAtual, [11, 18, 19], true)): ?>
            <?= $this->Html->link('Cancelar', [
                'controller' => 'Padrao',
                'action' => 'cancelar',
                (int)$inscricao->id,
            ], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

        <?php if (in_array($faseAtual, [11, 22, 16], true)): ?>
            <?= $this->Html->link('Substituir', [
                'controller' => 'Substituicoes',
                'action' => 'iniciar',
                (int)$inscricao->id,
            ], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

        <?php if ($podeDesistirProcesso): ?>
            <?= $this->Form->postLink('Desistir do processo', [
                'controller' => 'Inscricoes',
                'action' => 'desistir',
                (int)$edital->id,
                (int)$inscricao->id,
            ], [
                'class' => 'btn btn-sm btn-outline-danger',
                'confirm' => 'Confirma a desistência do processo? Esta ação não poderá ser desfeita.',
            ]) ?>
        <?php endif; ?>
    </div>
    <?php
        // Paliativo temporario na visualizacao.
        // Remover este bloco quando as fases 6 e 7 voltarem a ser exibidas normalmente para nao-Yoda.
        $forcarSituacaoFinalizadoNaVisualizacao = !$ehYoda && in_array((int)($inscricao->fase_id ?? 0), [6, 7], true);
        $situacaoExibicao = $forcarSituacaoFinalizadoNaVisualizacao
            ? 'Finalizado'
            : ($inscricao->fase->nome ?? null);
    ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 resumo-principal">
                <div class="col-md-6">
                    <strong>Data da inscrição:</strong>
                    <?= !empty($inscricao->created) ? h($inscricao->created->i18nFormat('dd/MM/yyyy')) : $naoInformado ?>
                </div>
                <div class="col-md-6">
                    <strong>Situação:</strong>
                    <?php if (!empty($situacaoExibicao)): ?>
                        <?php if ((int)($inscricao->vigente ?? 0) === 1): ?>
                            <span class="badge bg-success"><?= h($situacaoExibicao) ?></span>
                        <?php else: ?>
                            <?= h($situacaoExibicao) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= $naoInformado ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <strong>Programa:</strong>
                    <?php
                        $programaTexto = $edital->programa->sigla
                            ?? $edital->programa->nome
                            ?? null;
                        $editalTexto = $edital->nome ?? null;
                    ?>
                    <?php if (!empty($programaTexto)): ?>
                        <?= h($programaTexto) ?>
                        <?php if ($ehTIVisualizacao && !empty($editalTexto)): ?>
                            <small><i>(<?= h($editalTexto) ?>)</i></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= $naoInformado ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <strong>Cota:</strong>
                    <?php
                        $cotaValor = (string)($inscricao->cota ?? '');
                        echo !empty($cotas[$cotaValor]) ? h($cotas[$cotaValor]) : ($cotaValor !== '' ? h($cotaValor) : $naoInformado);
                    ?>
                </div>
                <div class="col-md-6">
                    <strong>Origem:</strong>
                    <?= !empty($origens[(string)($inscricao->origem ?? '')]) ? h($origens[(string)($inscricao->origem ?? '')]) : $naoInformado ?>
                </div>
                <div class="col-md-6">
                    <strong>Referência anterior:</strong>
                    <?php if ($mostrarReferenciaAnterior && $referenciaAnteriorId !== null): ?>
                        <a href="<?= $this->Url->build([
                            'controller' => 'Padrao',
                            'action' => 'visualizar',
                            $referenciaAnteriorId,
                        ]) ?>" target="_blank" class="text-decoration-none fw-semibold">
                            #<?= h((string)$referenciaAnteriorId) ?><?= $referenciaAnteriorDescricao ? ' (' . h($referenciaAnteriorDescricao) . ')' : '' ?>
                        </a>
                    <?php elseif ($mostrarReferenciaAnterior && $referenciaAnteriorValor !== null && $referenciaAnteriorValor !== ''): ?>
                        <?= h((string)$referenciaAnteriorValor) ?><?= $referenciaAnteriorDescricao ? ' (' . h($referenciaAnteriorDescricao) . ')' : '' ?>
                    <?php else: ?>
                        <?= $naoInformado ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <strong>Troca de Projeto:</strong>
                    <?= $trocaProjetoTexto ?>
                </div>
                <div class="col-md-6">
                    <strong>Troca de <?= h($rotuloOrientador) ?> (herança):</strong>
                    <?= $herancaTexto ?>
                </div>
                <?php if ($isRenovacao): ?>
                    <div class="col-md-6">
                        <strong>Autorização da revista:</strong>
                        <?= in_array((string)($inscricao->autorizacao ?? ''), ['0', '1'], true) ? ((int)$inscricao->autorizacao === 1 ? 'Sim' : 'Nao') : $naoInformado ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Alteração do subprojeto:</strong>
                        <?php
                            $subRenovacao = strtoupper(trim((string)($inscricao->subprojeto_renovacao ?? '')));
                            echo $subRenovacao === 'I'
                                ? 'Manteve o subprojeto original'
                                : ($subRenovacao === 'D' ? 'Novo subprojeto cadastrado' : $naoInformado);
                        ?>
                    </div>
                <?php endif; ?>
                <div class="col-md-12"><strong><?= h($rotuloOrientador) ?>:</strong> <?= !empty($inscricao->orientadore->nome) ? h($inscricao->orientadore->nome) : $naoInformado ?></div>
                <div class="col-md-12"><strong>Bolsista:</strong> <?= !empty($inscricao->bolsista_usuario->nome) ? h($inscricao->bolsista_usuario->nome) : $naoInformado ?></div>
                <div class="col-md-12"><strong>Coorientador:</strong> <?= !empty($inscricao->coorientadore->nome) ? h($inscricao->coorientadore->nome) : $naoInformado ?></div>
                <div class="col-md-12"><strong>Projeto:</strong> <?php
                    if (!empty($inscricao->projeto->titulo)) {
                        echo h($inscricao->projeto->titulo);
                    } elseif (!empty($inscricao->projeto_id)) {
                        echo 'ID ' . h((string)$inscricao->projeto_id);
                    } else {
                    }
                ?></div>
                <div class="col-md-12"><strong>Subprojeto:</strong> <?php
                    if (!empty($inscricao->sp_titulo)) {
                        echo h($inscricao->sp_titulo);
                    } elseif (!empty($inscricao->projeto_id)) {
                        echo $naoInformado;
                    } 
                ?></div>
                <div class="col-md-6 mt-2"><strong>Data início:</strong> <?= !empty($dataInicioTexto) ? h($dataInicioTexto) : $naoInformado ?></div>
                <div class="col-md-6 mt-2"><strong>Data fim:</strong> <?= !empty($dataFimTexto) ? h($dataFimTexto) : $naoInformado ?></div>
            </div>
        </div>
    </div>

    <?php if ($ehYoda): ?>
        <details class="homologacao-expansivo">
            <summary>Dados da homologação</summary>
            <div class="homologacao-status-card <?= h($homologadoStatusCardClasseTela) ?>">
                <div class="row g-3 align-items-start">
                    <div class="col-md-4">
                        <div class="text-muted small">Homologado</div>
                        <div class="fw-semibold">
                            <span class="badge <?= h($homologadoBadgeTela) ?>"><?= h($homologadoTextoTela) ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Data da última alteração</div>
                        <div class="fw-semibold"><?= $homologadoDataTextoTela !== 'Não informado' ? h($homologadoDataTextoTela) : $naoInformado ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Alterado por</div>
                        <div class="fw-semibold"><?= $homologadoUsuarioTextoTela !== '' ? h($homologadoUsuarioTextoTela) : $naoInformado ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Justificativa</div>
                        <div class="fw-semibold"><?= $homologadoJustificativaTextoTela !== '' ? nl2br(h($homologadoJustificativaTextoTela)) : '<span class="text-muted">-</span>' ?></div>
                    </div>
                </div>
            </div>
        </details>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-pills tabs-visualizacao mb-3" id="tabs-visualizacao-padrao" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-bolsista" type="button">Dados do Bolsista</button></li>
                <?php if (!$isRenovacao): ?>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sumula" type="button">Súmula</button></li>
                <?php endif; ?>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-projeto" type="button">Projeto</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-subprojeto" type="button">Subprojeto/Relatório</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-coorientador" type="button">Coorientador</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-avaliacao" type="button">Avaliação</button></li>
                <?php if ($ehYoda): ?>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-historico" type="button">Histórico</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-gestao" type="button">Gestão</button></li>
                <?php endif; ?>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-bolsista">
                    <?php if (empty($inscricao->bolsista_usuario)): ?>
                        <p><span class="badge bg-danger">Bolsista não informado</span></p>
                    <?php else: ?>
                        <p><strong>Nome:</strong> <?= !empty($inscricao->bolsista_usuario->nome) ? h($inscricao->bolsista_usuario->nome) : $naoInformado ?></p>
                        <p><strong>Universidade:</strong> <?= !empty($inscricao->bolsista_usuario->instituicao?->sigla) ? h($inscricao->bolsista_usuario->instituicao->sigla) : (!empty($inscricao->bolsista_usuario->instituicao_curso) && !is_numeric($inscricao->bolsista_usuario->instituicao_curso) ? h($inscricao->bolsista_usuario->instituicao_curso) : $naoInformado) ?></p>
                        <p><strong>Curso:</strong> <?= !empty($inscricao->bolsista_usuario->curso) ? h($inscricao->bolsista_usuario->curso) : $naoInformado ?></p>
                    <?php endif; ?>
                    <?php if ((int)($edital->programa_id ?? 0) > 1): ?>
                        <p><strong>Primeiro período:</strong> <?= $inscricao->primeiro_periodo === null ? $naoInformado : ((int)$inscricao->primeiro_periodo === 1 ? 'Sim' : 'Nao') ?></p>
                    <?php elseif (strtoupper((string)($inscricao->origem ?? '')) === 'N'): ?>
                        <p>
                            <strong>Bolsista, possui filhos menores de 8 anos?</strong>
                            <?php
                                $bolsistaSexo = strtoupper((string)($inscricao->bolsista_usuario->sexo ?? ''));
                                $filhosMenorBolsista = (string)($inscricao->filhos_menor_bolsista ?? '');
                                if ($bolsistaSexo !== 'F') {
                                    echo 'Não se aplica, pois bolsista não é do sexo feminino';
                                } else {
                                    echo $filhosMenorBolsista !== '' ? h($filhosMenorBolsista) : $naoInformado;
                                }
                            ?>
                        </p>
                    <?php endif; ?>
                    <?php if ((int)($edital->programa_id ?? 0) === 1 && strtoupper((string)($inscricao->origem ?? '')) === 'R'): ?>
                        <hr>
                        <h6>Workshop</h6>
                        <?php if (empty($workshopsVinculados) || $workshopsVinculados->count() === 0): ?>
                            <p><?= $naoInformado ?></p>
                        <?php else: ?>
                            <ul class="anexos-lista">
                                <?php foreach ($workshopsVinculados as $workshop): ?>
                                    <?php
                                        $workshopDeletado = (int)($workshop->deleted ?? 0) === 1;
                                        $workshopPresenca = strtoupper((string)($workshop->presenca ?? ''));
                                        $workshopStatusTexto = $workshopDeletado
                                            ? 'Deletado'
                                            : ($workshopPresenca === 'S' ? 'Certificado liberado' : (!empty($workshop->data_apresentacao) ? 'Agendado' : 'Pendente de agendamento'));
                                    ?>
                                    <li>
                                        <div class="anexo-titulo">
                                            <span class="anexo-tipo">
                                                Workshop #<?= h((string)$workshop->id) ?>
                                            </span>
                                            <span class="anexo-arquivo">
                                                <?= !empty($workshop->editai->nome) ? h($workshop->editai->nome) : 'Edital não informado' ?>
                                            </span>
                                            <span class="anexo-meta">
                                                Status: <?= h($workshopStatusTexto) ?>
                                            </span>
                                            <span class="anexo-meta">
                                                <?= !empty($workshop->data_apresentacao)
                                                    ? 'Apresentação em ' . h($workshop->data_apresentacao->i18nFormat('dd/MM/yyyy'))
                                                    : 'Apresentação não agendada' ?>
                                                <?= !empty($workshop->unidade->sigla) ? ' - ' . h($workshop->unidade->sigla) : '' ?>
                                            </span>
                                        </div>
                                        <?= $this->Html->link(
                                            'Abrir',
                                            ['controller' => 'Workshops', 'action' => 'ver', (int)$workshop->id],
                                            ['class' => 'btn btn-light border btn-sm py-0 px-2']
                                        ) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endif; ?>
                    <hr>
                    <h6>Anexos do Bolsista</h6>
                    <?php if (empty($anexosPorBloco['B'])): ?>
                        <p><span class="badge bg-danger">Nenhum anexo encontrado</span></p>
                    <?php else: ?>
                        <ul class="anexos-lista">
                            <?php foreach ($anexosPorBloco['B'] as $anexo): ?>
                                <li>
                                    <div class="anexo-titulo">
                                        <span class="anexo-tipo"><?= h($anexo['tipo_nome']) ?></span>
                                        <span class="anexo-arquivo"><?= !empty($anexo['arquivo']) ? h($anexo['arquivo']) : 'Não informado' ?></span>
                                        <span class="anexo-meta">
                                            Incluído por <?= !empty($anexo['usuario_nome']) ? h($anexo['usuario_nome']) : 'Não informado' ?>
                                            em
                                            <?= h($formatarDataHoraAnexo($anexo['created'] ?? null)) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($anexo['arquivo'])): ?>
                                        <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <?php if (!$isRenovacao): ?>
                    <div class="tab-pane fade" id="tab-sumula">
                        <?php $mostrarFilhosOrientadora = strtoupper((string)($inscricao->orientadore->sexo ?? '')) === 'F'; ?>
                        <?php if ($mostrarFilhosOrientadora): ?>
                            <?php $filhosKey = $inscricao->filhos_menor !== null ? (string)$inscricao->filhos_menor : ''; ?>
                        <?php endif; ?>
                        <?php
                            $anexoFilhosExigido = $mostrarFilhosOrientadora && (int)($inscricao->filhos_menor ?? 0) > 0;
                            $anexoDiplomaDoutoradoExigido = false;
                            $anexoRecemServidorExigido = (int)($inscricao->recem_servidor ?? 0) === 1;
                        ?>
                        <div class="sumula-campos">
                            <?php if ($mostrarFilhosOrientadora): ?>
                                <div class="row g-2 sumula-campo-item">
                                    <div class="col-md-6 sumula-campo-valor">
                                        <strong><?= h($rotuloOrientadora) ?>, possui filhos menores de 8 anos?</strong>
                                        <?= isset($filhosMap[$filhosKey]) ? h($filhosMap[$filhosKey]) : $naoInformado ?>
                                    </div>
                                    <div class="col-md-6 sumula-campo-anexo">
                                        <?= $renderAnexoSumula($anexosSumulaPorTipo[27] ?? null, $anexoFilhosExigido, 'Certidão de Nascimento (Filhos Orientadora)') ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row g-2 sumula-campo-item">
                                <div class="col-md-6 sumula-campo-valor">
                                    <strong>Ano de conclusão do doutorado:</strong>
                                    <?= $inscricao->ano_doutorado !== null && $inscricao->ano_doutorado !== '' ? h((string)$inscricao->ano_doutorado) : $naoInformado ?>
                                </div>
                                <div class="col-md-6 sumula-campo-anexo">
                                    <?php /*
                                    <?= $renderAnexoSumula($anexosSumulaPorTipo[28] ?? null, $anexoDiplomaDoutoradoExigido, 'Diploma de doutorado (recém doutor)') ?>
                                    */ ?>
                                </div>
                            </div>
                            <div class="row g-2 sumula-campo-item">
                                <div class="col-md-6 sumula-campo-valor">
                                    <strong>Você ingressou na Fiocruz por meio dos concursos de 2016 e 2024?</strong>
                                    <?php
                                        $recemServidorValor = $inscricao->recem_servidor;
                                        if ((string)$recemServidorValor === '1') {
                                            echo 'Sim, incluirei o anexo do DO';
                                        } elseif ((string)$recemServidorValor === '0') {
                                            echo 'Não';
                                        } else {
                                            echo $naoInformado;
                                        }
                                    ?>
                                </div>
                                <div class="col-md-6 sumula-campo-anexo">
                                    <?= $renderAnexoSumula($anexosSumulaPorTipo[29] ?? null, $anexoRecemServidorExigido, 'Cópia do DO (recém concursados)') ?>
                                </div>
                            </div>
                        </div>
                        <hr class="sumula-separador">
                        <h6 class="mb-2">Itens de Súmula</h6>
                        <?php if (empty($sumulasEdital) || $sumulasEdital->count() === 0): ?>
                            <p><span class="badge bg-danger">Nenhum item de súmula encontrado</span></p>
                        <?php else: ?>
                            <div class="visualizacao-tabela">
                                <table class="table table-sm table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Súmula</th>
                                            <th>Parâmetro</th>
                                            <th>Quantidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sumulasEdital as $sumula): ?>
                                            <?php $qtdSumula = $quantidadesSumula[(int)$sumula->id] ?? null; ?>
                                            <tr>
                                                <td><?= h((string)$sumula->sumula) ?></td>
                                                <td><?= h((string)$sumula->parametro) ?></td>
                                                <td><?= $qtdSumula !== null && $qtdSumula !== '' ? h((string)$qtdSumula) : $naoInformado ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="tab-pane fade" id="tab-projeto">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Título do projeto</div>
                                        <div><?= !empty($inscricao->projeto->titulo) ? h($inscricao->projeto->titulo) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Grande área CNPQ</div>
                                        <div><?= !empty($inscricao->projeto->area->grandes_area->nome) ? h($inscricao->projeto->area->grandes_area->nome) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Área CNPQ</div>
                                        <div><?= !empty($inscricao->projeto->area->nome) ? h($inscricao->projeto->area->nome) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Área de pesquisa FIOCRUZ</div>
                                        <div><?= !empty($inscricao->projeto->linha->areas_fiocruz->nome) ? h($inscricao->projeto->linha->areas_fiocruz->nome) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Linha de pesquisa FIOCRUZ</div>
                                        <div><?= !empty($inscricao->projeto->linha->nome) ? h($inscricao->projeto->linha->nome) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Instituições financiadoras</div>
                                        <div><?= !empty($inscricao->projeto->financiamento) ? h($inscricao->projeto->financiamento) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Palavras-chave</div>
                                        <div><?= !empty($inscricao->projeto->palavras_chaves) ? h($inscricao->projeto->palavras_chaves) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <details class="border rounded p-2 bg-white">
                                        <summary class="fw-semibold">Resumo do projeto</summary>
                                        <div class="mt-2" style="white-space: pre-line;"><?= !empty($inscricao->projeto->resumo) ? h($inscricao->projeto->resumo) : $naoInformado ?></div>
                                    </details>
                                </div>
                            </div>
                            <hr>
                            <h6 class="mb-2">Anexos do Projeto</h6>
                            <?php if (empty($anexosPorBloco['P'])): ?>
                                <p><span class="badge bg-danger">Nenhum anexo encontrado</span></p>
                            <?php else: ?>
                                <ul class="anexos-lista">
                                    <?php foreach ($anexosPorBloco['P'] as $anexo): ?>
                                        <li>
                                            <div class="anexo-titulo">
                                                <span class="anexo-tipo"><?= h($anexo['tipo_nome']) ?></span>
                                                <span class="anexo-arquivo"><?= !empty($anexo['arquivo']) ? h($anexo['arquivo']) : 'Não informado' ?></span>
                                                <span class="anexo-meta">
                                                    Incluído por <?= !empty($anexo['usuario_nome']) ? h($anexo['usuario_nome']) : 'Não informado' ?>
                                                    em
                                                    <?= h($formatarDataHoraAnexo($anexo['created'] ?? null)) ?>
                                                </span>
                                                <?php if ((int)($anexo['tipo_id'] ?? 0) !== 5 && !empty($anexo['inscricao_origem_id'])): ?>
                                                    <span class="anexo-meta">
                                                        Carregado na inscrição #<?= h((string)$anexo['inscricao_origem_id']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($anexo['arquivo'])): ?>
                                                <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <div class="card border mt-3">
                                <div class="card-body py-2">
                                    <h6 class="mb-2">Anexos específicos da Inscrição: Anuência da Chefia e Termo da Inscrição</h6>
                                    <?php if (empty($anexosProjetoTipos924)): ?>
                                        <p class="mb-0"><span class="badge bg-danger">Nenhum anexo encontrado</span></p>
                                    <?php else: ?>
                                        <ul class="anexos-lista mb-0">
                                            <?php foreach ($anexosProjetoTipos924 as $anexo): ?>
                                                <li>
                                                    <div class="anexo-titulo">
                                                        <span class="anexo-tipo"><?= h($anexo['tipo_nome']) ?></span>
                                                        <span class="anexo-arquivo"><?= !empty($anexo['arquivo']) ? h($anexo['arquivo']) : 'Não informado' ?></span>
                                                        <span class="anexo-meta">
                                                            Incluído por <?= !empty($anexo['usuario_nome']) ? h($anexo['usuario_nome']) : 'Não informado' ?>
                                                            em
                                                            <?= h($formatarDataHoraAnexo($anexo['created'] ?? null)) ?>
                                                        </span>
                                                    </div>
                                                    <?php if (!empty($anexo['arquivo'])): ?>
                                                        <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                            <i class="fa fa-download"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-subprojeto">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded p-2 bg-white h-100">
                                        <div class="small text-muted">Título do subprojeto</div>
                                        <div><?= !empty($inscricao->sp_titulo) ? h($inscricao->sp_titulo) : $naoInformado ?></div>
                                    </div>
                                </div>
                                <?php if ($isRenovacao): ?>
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 bg-white h-100">
                                            <div class="small text-muted">Tipo de subprojeto</div>
                                            <div>
                                                <?php
                                                    $subRenovacaoAba = strtoupper(trim((string)($inscricao->subprojeto_renovacao ?? '')));
                                                    echo $subRenovacaoAba === 'I'
                                                        ? 'Manteve o subprojeto original'
                                                        : ($subRenovacaoAba === 'D' ? 'Novo subprojeto cadastrado' : $naoInformado);
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 bg-white h-100">
                                            <div class="small text-muted">Autorização da revista</div>
                                            <div><?= in_array((string)($inscricao->autorizacao ?? ''), ['0', '1'], true) ? ((int)$inscricao->autorizacao === 1 ? 'Sim' : 'Nao') : $naoInformado ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <details class="border rounded p-2 bg-white">
                                            <summary class="fw-semibold">Justificativa da alteração</summary>
                                            <div class="mt-2" style="white-space: pre-line;"><?= !empty($inscricao->justificativa_alteracao) ? h($inscricao->justificativa_alteracao) : $naoInformado ?></div>
                                        </details>
                                    </div>
                                <?php endif; ?>
                                <div class="col-12">
                                    <details class="border rounded p-2 bg-white">
                                        <summary class="fw-semibold">Resumo do subprojeto</summary>
                                        <div class="mt-2" style="white-space: pre-line;"><?= !empty($inscricao->sp_resumo) ? h($inscricao->sp_resumo) : $naoInformado ?></div>
                                    </details>
                                </div>
                                <div class="col-12">
                                    <details class="border rounded p-2 bg-white">
                                        <summary class="fw-semibold">Justificativa da bolsa</summary>
                                        <div class="mt-2" style="white-space: pre-line;"><?= !empty($inscricao->justificativa_bolsa) ? h($inscricao->justificativa_bolsa) : $naoInformado ?></div>
                                    </details>
                                </div>
                                <?php if ($isRenovacao): ?>
                                    <div class="col-12">
                                        <details class="border rounded p-2 bg-white">
                                            <summary class="fw-semibold">Resumo do relatório</summary>
                                            <div class="mt-2" style="white-space: pre-line;"><?= !empty($inscricao->resumo_relatorio) ? h($inscricao->resumo_relatorio) : $naoInformado ?></div>
                                        </details>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <hr>
                            <h6>Anexos do Subprojeto/Relatório</h6>
                            <?php if (empty($anexosSubprojetoTela)): ?>
                                <p><span class="badge bg-danger">Nenhum anexo encontrado</span></p>
                            <?php else: ?>
                                <ul class="anexos-lista">
                                    <?php foreach ($anexosSubprojetoTela as $anexo): ?>
                                        <li>
                                            <div class="anexo-titulo">
                                                <span class="anexo-tipo"><?= h($anexo['tipo_nome']) ?></span>
                                                <span class="anexo-arquivo"><?= !empty($anexo['arquivo']) ? h($anexo['arquivo']) : 'Não informado' ?></span>
                                                <span class="anexo-meta">
                                                    Incluído por <?= !empty($anexo['usuario_nome']) ? h($anexo['usuario_nome']) : 'Não informado' ?>
                                                    em
                                                    <?= h($formatarDataHoraAnexo($anexo['created'] ?? null)) ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($anexo['arquivo'])): ?>
                                                <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if (!empty($podeGerenciarRelatorioFinal)): ?>
                                <hr>
                                <div id="bloco-relatorio-final" class="card border">
                                    <div class="card-body">
                                        <h6 class="mb-3">Relatório Final</h6>
                                        <?php if (!empty($relatorioFinalAtual) && !empty($relatorioFinalAtual->anexo)): ?>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                                <div>
                                                    <div class="fw-semibold">Arquivo atual</div>
                                                    <div class="small text-muted"><?= h((string)$relatorioFinalAtual->anexo) ?></div>
                                                </div>
                                                <a href="/uploads/anexos/<?= h((string)$relatorioFinalAtual->anexo) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                    Download
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">Nenhum relatório final anexado até o momento.</p>
                                        <?php endif; ?>

                                        <?php if (!empty($podeEnviarNovoRelatorioFinal)): ?>
                                            <?= $this->Form->create(null, [
                                                'type' => 'file',
                                                'url' => ['controller' => 'Padrao', 'action' => 'uploadRelatorioFinal', (int)$inscricao->id],
                                                'class' => 'row g-3 align-items-end',
                                            ]) ?>
                                                <div class="col-md-8">
                                                    <?= $this->Form->control('relatorio_final', [
                                                        'label' => 'Arquivo do relatório final',
                                                        'type' => 'file',
                                                        'class' => 'form-control',
                                                        'required' => true,
                                                    ]) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <?= $this->Form->button('Enviar relatório final', ['class' => 'btn btn-success w-100']) ?>
                                                </div>
                                            <?= $this->Form->end() ?>
                                        <?php elseif (!empty($relatorioFinalAtual)): ?>
                                            <div class="alert alert-secondary mb-0">
                                                O relatório final já foi incluído e não pode ser substituído por esta tela.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-coorientador">
                    <?php if (empty($inscricao->coorientadore)): ?>
                        <p><span class="badge bg-danger">Coorientador não informado</span></p>
                    <?php else: ?>
                        <p><strong>Nome:</strong> <?= !empty($inscricao->coorientadore->nome) ? h($inscricao->coorientadore->nome) : $naoInformado ?></p>
                        <div class="row g-2">
                            <div class="col-md-6"><strong>Curso:</strong> <?= !empty($inscricao->coorientadore->curso) ? h($inscricao->coorientadore->curso) : $naoInformado ?></div>
                            <div class="col-md-6"><strong>Universidade:</strong> <?= !empty($inscricao->coorientadore->instituicao?->sigla) ? h($inscricao->coorientadore->instituicao->sigla) : (!empty($inscricao->coorientadore->instituicao_curso) && !is_numeric($inscricao->coorientadore->instituicao_curso) ? h($inscricao->coorientadore->instituicao_curso) : $naoInformado) ?></div>
                            <div class="col-md-6"><strong>Escolaridade:</strong> <?= !empty($inscricao->coorientadore->escolaridade->nome) ? h($inscricao->coorientadore->escolaridade->nome) : $naoInformado ?></div>
                            <div class="col-md-6"><strong>Vinculo:</strong> <?= !empty($inscricao->coorientadore->vinculo->nome) ? h($inscricao->coorientadore->vinculo->nome) : $naoInformado ?></div>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <h6>Anexos do Coorientador</h6>
                    <?php if (empty($anexosPorBloco['C'])): ?>
                        <p><span class="badge bg-danger">Nenhum anexo encontrado</span></p>
                    <?php else: ?>
                        <ul class="anexos-lista">
                            <?php foreach ($anexosPorBloco['C'] as $anexo): ?>
                                <li>
                                    <div class="anexo-titulo">
                                        <span class="anexo-tipo"><?= h($anexo['tipo_nome']) ?></span>
                                        <span class="anexo-arquivo"><?= !empty($anexo['arquivo']) ? h($anexo['arquivo']) : 'Não informado' ?></span>
                                        <span class="anexo-meta">
                                            Incluído por <?= !empty($anexo['usuario_nome']) ? h($anexo['usuario_nome']) : 'Não informado' ?>
                                            em
                                            <?= h($formatarDataHoraAnexo($anexo['created'] ?? null)) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($anexo['arquivo'])): ?>
                                        <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="tab-avaliacao">
                    <?php if (!$podeVisualizarResultadoAvaliacao): ?>
                        <p class="text-muted mb-0">Resultado e avaliações disponíveis após a data de resultado do edital.</p>
                    <?php else: ?>
                        <p>
                            <strong>Resultado final:</strong>
                            <?= !empty($resultadoMap[(string)($inscricao->resultado ?? '')]) ? h($resultadoMap[(string)$inscricao->resultado]) : $naoInformado ?>
                        </p>
                        <?php if ($ehYoda): ?>
                            <div class="alert alert-light border py-2 px-3 mb-3" style="font-size: 0.92rem; line-height: 1.4;">
                                <strong>Informativo à Gestão de Fomento</strong><br>
                                A tabela de avaliação está disponível para os perfis com acesso a esta visualização:
                                Gestão de Fomento, coordenação da unidade da inscrição, orientador e bolsista.<br>
                                São apresentados status, valor da nota e link para os detalhes da nota.<br>
                                O nome do avaliador é exibido apenas para a Gestão de Fomento.<br>
                                Para a Gestão de Fomento, os dados são apresentados a partir do cadastro. Para os demais perfis, somente após a data de resultado do edital.
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($avaliacoes) && $avaliacoes->count() > 0): ?>
                            <div class="visualizacao-tabela">
                                <table class="table table-sm table-striped align-middle">
                                    <thead>
                                        <tr>
	                                            <th>Avaliador</th>
	                                            <th>Status</th>
                                                <th>Cadastro</th>
                                                <th>Exclusão</th>
	                                            <th>Nota</th>
	                                            <th>Nota súmula</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($avaliacoes as $av): ?>
                                            <?php
                                                $avaliacaoDeletada = (int)($av->deleted ?? 0) === 1;
                                                $dataCadastro = $av->created ?? null;
                                                $dataCadastroTexto = $dataCadastro && method_exists($dataCadastro, 'format')
                                                    ? $dataCadastro->format('d/m/Y H:i')
                                                    : 'Não informado';
                                                $usuarioCadastro = trim((string)($av->criador->nome ?? ''));
                                                if ($usuarioCadastro === '') {
                                                    $usuarioCadastro = 'Não informado';
                                                }
                                                $dataExclusao = $av->deletado_em ?? null;
                                                $dataExclusaoTexto = $dataExclusao && method_exists($dataExclusao, 'format')
                                                    ? $dataExclusao->format('d/m/Y H:i')
                                                    : 'Não informado';
                                                $usuarioExclusao = trim((string)($av->deletador->nome ?? ''));
                                                if ($usuarioExclusao === '') {
                                                    $usuarioExclusao = 'Não informado';
                                                }
                                            ?>
	                                        <tr class="<?= $avaliacaoDeletada ? 'table-danger' : '' ?>">
	                                            <td>
	                                                <?php
	                                                    $ordemAvaliador = (int)($av->ordem ?? 0);
	                                                    if ($ehYoda) {
	                                                        $nomeAvaliadorTela = !empty($av->avaliador->usuario->nome)
	                                                            ? h($av->avaliador->usuario->nome)
	                                                            : 'Avaliador #' . (int)$av->avaliador_id;
                                                            echo $nomeAvaliadorTela;
                                                            if ($ordemAvaliador > 0) {
                                                                echo ' <span class="text-muted small">(AV ' . h((string)$ordemAvaliador) . ')</span>';
                                                            }
	                                                    } else {
	                                                        echo 'Avaliador';
	                                                        if ($ordemAvaliador > 0) {
	                                                            echo ' <span class="text-muted small">(AV ' . h((string)$ordemAvaliador) . ')</span>';
	                                                        }
	                                                    }
	                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $statusAvaliacao = '';
                                                    $statusAvaliacaoIcone = '';
                                                    if ((int)($av->deleted ?? 0) === 1) {
                                                        $statusAvaliacao = 'Desvinculado';
                                                        $statusAvaliacaoIcone = '<span class="status-avaliacao-icone status-avaliacao-icone--desvinculado">×</span>';
                                                    } else {
                                                        $statusAvaliacaoKey = (string)($av->situacao ?? '');
                                                        $statusAvaliacao = $statusAvaliacaoMap[$statusAvaliacaoKey] ?? ($statusAvaliacaoKey !== '' ? $statusAvaliacaoKey : '');
                                                        if ($statusAvaliacaoKey === 'F') {
                                                            $statusAvaliacaoIcone = '<span class="status-avaliacao-icone status-avaliacao-icone--finalizado"></span>';
                                                        } elseif ($statusAvaliacaoKey === 'E') {
                                                            $statusAvaliacaoIcone = '<span class="status-avaliacao-icone status-avaliacao-icone--aguardando"></span>';
                                                        }
                                                    }
                                                ?>
                                                <?php if ($statusAvaliacao !== ''): ?>
                                                    <?= $statusAvaliacaoIcone ?><?= h($statusAvaliacao) ?>
                                                <?php else: ?>
	                                                    <?= $naoInformado ?>
	                                                <?php endif; ?>
	                                            </td>
                                                <td>
                                                    <div class="small">
                                                        <?= h($dataCadastroTexto) ?><br>
                                                        <span class="text-muted"><?= h($usuarioCadastro) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($avaliacaoDeletada): ?>
                                                        <div class="small">
                                                            <?= h($dataExclusaoTexto) ?><br>
                                                            <span class="text-muted"><?= h($usuarioExclusao) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
	                                            <td>
                                                <?php
                                                    $situacaoAvaliacao = (string)($av->situacao ?? '');
                                                    if ($situacaoAvaliacao === 'F') {
                                                        echo $av->nota !== null ? h(number_format((float)$av->nota, 2, ',', '.')) : $naoInformado;
                                                    } elseif ($situacaoAvaliacao === 'E') {
                                                        echo 'Não lançada';
                                                    } else {
                                                        echo $naoInformado;
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if ($situacaoAvaliacao === 'F') {
                                                        echo $av->nota_sumula !== null ? h(number_format((float)$av->nota_sumula, 2, ',', '.')) : $naoInformado;
                                                    } elseif ($situacaoAvaliacao === 'E') {
                                                        echo 'Não lançada';
                                                    } else {
                                                        echo $naoInformado;
                                                    }
                                                ?>
                                            </td>
	                                            <td>
                                                <?php
                                                    $identityAtual = $this->request->getAttribute('identity');
                                                    $identityAtualId = is_array($identityAtual)
                                                        ? (int)($identityAtual['id'] ?? 0)
                                                        : (int)($identityAtual->id ?? 0);
                                                    $podeVerNotas = $situacaoAvaliacao === 'F'
                                                        && (
                                                            $ehYoda
                                                            || in_array($identityAtualId, [1, 8088], true)
                                                            || $identityAtualId === (int)($inscricao->orientador ?? 0)
                                                            || $identityAtualId === (int)($inscricao->bolsista ?? 0)
                                                        );
                                                ?>
                                                <?php if ($podeVerNotas && (int)($av->deleted ?? 0) === 0): ?>
                                                    <?= $this->Html->link(
                                                        '<i class="fas fa-file-alt"></i> Ver notas',
                                                        ['controller' => 'Avaliadores', 'action' => 'verNotas', (int)$av->id],
                                                        ['class' => 'btn btn-xs btn-outline-primary py-0 px-1', 'escape' => false]
                                                    ) ?>
                                                <?php else: ?>
                                                    <?= $naoInformado ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Nenhum avaliador vinculado.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="tab-historico">
                    <?php if ($historicos->count() === 0): ?>
                        <p class="text-muted">Nenhum historico localizado.</p>
                    <?php else: ?>
                        <div class="historico-lista">
                            <?php foreach ($historicos as $h): ?>
                                <?php $justificativa = trim((string)($h->justificativa ?? '')); ?>
                                <div class="historico-item">
                                    <div class="meta">
                                        <?php $dataHistoricoTela = !empty($h->created) ? $h->created->subHours(3) : null; ?>
                                        <strong>Data:</strong> <?= $dataHistoricoTela ? h($dataHistoricoTela->i18nFormat('dd/MM/yyyy HH:mm')) : $naoInformado ?>
                                        <?php if (!empty($h->usuario)): ?>
                                            <span class="ms-2"><strong>Por:</strong> <?= h($h->usuario->nome) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                        $faseOriginalNome = !empty($h->fase_original_ref->nome) ? (string)$h->fase_original_ref->nome : '';
                                        $faseAtualNome = !empty($h->fase_atual_ref->nome) ? (string)$h->fase_atual_ref->nome : '';
                                    ?>
                                    <?php if ($faseOriginalNome !== '' || $faseAtualNome !== ''): ?>
                                        <div class="mb-2">
                                            <span class="badge bg-secondary"><?= $faseOriginalNome !== '' ? h($faseOriginalNome) : '-' ?></span>
                                            <span class="mx-1">→</span>
                                            <span class="badge bg-success"><?= $faseAtualNome !== '' ? h($faseAtualNome) : '-' ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($justificativa === ''): ?>
                                        <div><strong>Justificativa:</strong> <?= $naoInformado ?></div>
                                    <?php elseif (strlen($justificativa) > 180): ?>
                                        <details>
                                            <summary class="fw-semibold">Justificativa (clique para expandir)</summary>
                                            <div class="mt-2" style="white-space: pre-line;"><?= h($justificativa) ?></div>
                                        </details>
                                    <?php else: ?>
                                        <div><strong>Justificativa:</strong> <?= h($justificativa) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($ehYoda): ?>
                    <div class="tab-pane fade" id="tab-gestao">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <h6 class="mb-0 text-uppercase text-muted">Ações exclusivas da gestão</h6>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <?= $this->Html->link(
                                            'Suspender',
                                            ['controller' => 'Gestao', 'action' => 'suspender', (int)$inscricao->id],
                                            ['class' => 'btn btn-outline-danger w-100']
                                        ) ?>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-0 text-muted">
                                            Utilize esta ação para registrar suspensões por licença médica ou maternidade.
                                            O histórico será atualizado e a suspensão ficará registrada na inscrição.
                                        </p>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <?= $this->Html->link(
                                            'Alterar Fonte',
                                            ['controller' => 'Gestao', 'action' => 'fonte', (int)$inscricao->id],
                                            ['class' => 'btn btn-outline-primary w-100']
                                        ) ?>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-1 text-muted">
                                            <?php
                                                $fonteAtual = (string)($inscricao->tipo_bolsa ?? '');
                                                $fonteNome = $fontes[$fonteAtual] ?? $fonteAtual;
                                            ?>
                                            <strong class="text-body">Fonte atual: <?= h($fonteNome !== '' ? $fonteNome : '-') ?></strong>
                                        </p>
                                        <p class="mb-0 text-muted">
                                            Alteração de fonte pagadora.
                                        </p>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <?php if ($podeImplementarBolsaManual): ?>
                                            <?= $this->Html->link(
                                                'Implementar Bolsa',
                                                ['controller' => 'Gestao', 'action' => 'ativarbolsasunitario', (int)$inscricao->id],
                                                ['class' => 'btn btn-outline-success w-100']
                                            ) ?>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary w-100" disabled>Implementar Bolsa</button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-1 text-muted">
                                            <strong class="text-body">Situação atual: <?= !empty($inscricao->fase->nome) ? h($inscricao->fase->nome) : '-' ?></strong>
                                        </p>
                                        <p class="mb-0 text-muted">
                                            Implementação manual da bolsa. Para gestão, disponível apenas em Banco Reserva ou Aprovado.
                                        </p>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <?= $this->Html->link(
                                            'Gerenciar Anexos',
                                            ['controller' => 'Gestao', 'action' => 'addarquivo', (int)$inscricao->id],
                                            ['class' => 'btn btn-outline-secondary w-100']
                                        ) ?>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-0 text-muted">
                                            Tela administrativa para incluir/alterar anexos da inscrição, com download e edição por tipo de anexo.
                                        </p>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <?= $this->Html->link(
                                            'Gerenciar Avaliadores',
                                            ['controller' => 'Avaliadores', 'action' => 'vincularInscricao', (int)$inscricao->id],
                                            ['class' => 'btn btn-outline-dark w-100']
                                        ) ?>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-0 text-muted">
                                            Acesse a tela de vinculação para definir, substituir ou revisar os avaliadores associados a esta inscrição.
                                        </p>
                                    </div>
                                </div>
                                <?php if (in_array($faseAtual, [4, 6, 7], true)): ?>
                                    <hr class="my-3">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-4">
                                            <?= $this->Html->link(
                                                'Homologação',
                                                ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id],
                                                ['class' => 'btn btn-outline-success w-100']
                                            ) ?>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="mb-1 text-muted">
                                                <strong class="text-body">Situação atual: <?= !empty($inscricao->fase->nome) ? h($inscricao->fase->nome) : '-' ?></strong>
                                            </p>
                                            <p class="mb-0 text-muted">
                                                Acesse a tela de homologação para verificar anexos/dados e registrar homologação ou não homologação.
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <hr class="my-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <?php if ($podeAlterarResultado): ?>
                                            <?= $this->Html->link(
                                                $labelAcaoResultadoTela,
                                                ['controller' => 'Padrao', 'action' => 'addresultado', (int)$inscricao->id],
                                                ['class' => 'btn btn-outline-primary w-100']
                                            ) ?>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary w-100" disabled><?= h($labelAcaoResultadoTela) ?></button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-1 text-muted">
                                            <strong class="text-body">Homologação: <?= h($homologadoAtualTela === 'S' ? 'Homologada' : ($homologadoAtualTela === 'P' ? 'Homologada com pendência' : ($homologadoAtualTela === 'N' ? 'Não homologada' : 'Não definida'))) ?></strong>
                                        </p>
                                        <p class="mb-0 text-muted">
                                            <?php if ($homologadoAtualTela === ''): ?>
                                                Homologação não definida não permite alteração de resultado.
                                                Disponível apenas para as fases Finalizada, Banco Reserva, Aprovado ou Reprovado.
                                            <?php else: ?>
                                                Registra aprovação, banco reserva ou reprovação com justificativa no histórico.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($ehTIVisualizacao && (in_array($faseAtual, [14], true) || in_array($faseAtual, [1, 3], true))): ?>
        <div class="d-flex justify-content-end mt-4">
            <div class="text-end">
                <div class="small text-muted mb-2">Ações técnicas</div>
                <div class="d-flex justify-content-end gap-3">
                    <?php if (in_array($faseAtual, [14], true)): ?>
                        <?= $this->Html->link('Reativar', [
                            'controller' => 'Padrao',
                            'action' => 'reativar',
                            (int)$inscricao->id,
                        ], [
                            'class' => 'btn btn-link btn-sm text-secondary p-0 text-decoration-none',
                        ]) ?>
                    <?php endif; ?>
                    <?php if (in_array($faseAtual, [1, 3], true)): ?>
                        <?= $this->Html->link('Deletar inscrição', [
                            'controller' => 'Padrao',
                            'action' => 'deletar',
                            (int)$inscricao->id,
                        ], [
                            'class' => 'btn btn-link btn-sm text-danger p-0 text-decoration-none',
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
