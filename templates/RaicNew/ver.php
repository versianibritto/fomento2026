<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Raic $raic
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface|array $lista
 * @var \App\Model\Entity\Anexo|null $anexoRelatorio
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface|array $historicos
 * @var \App\Model\Entity\Editai|null $editalReferencia
 * @var \App\Model\Entity\Editai|null $editalEvento
 */
$naoInformado = '<span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>';
$tipoBolsaTexto = match (strtoupper((string)($raic->tipo_bolsa ?? ''))) {
    'R' => 'Aluno de Renovação',
    'V', 'Z' => 'Voluntário',
    default => 'Não informado',
};
$coorientadorNome = !empty($raic->coorientadore?->nome) ? $raic->coorientadore->nome : null;
$dataCadastroTexto = !empty($raic->created) ? $raic->created->i18nFormat('dd/MM/YYYY') : 'Não informado';
$dataApresentacaoTexto = !empty($raic->data_apresentacao) ? $raic->data_apresentacao->i18nFormat('dd/MM/YYYY') : null;
$tipoApresentacaoTexto = match ((string)($raic->tipo_apresentacao ?? '')) {
    'O' => 'Oral',
    'P' => 'Painel',
    default => null,
};
$programaTexto = 'Outras agências de fomento';
if (!empty($raic->projeto_bolsista_id)) {
    $programaTexto = trim((string)(
        $raic->projeto_bolsista->programa_associado->sigla
        ?? $raic->projeto_bolsista->programa_associado->nome
        ?? $raic->projeto_bolsista->editai->programa->sigla
        ?? $raic->projeto_bolsista->editai->programa->nome
        ?? ''
    ));
    if ($programaTexto === '') {
        $programaTexto = 'Não informado';
    }
}
$certificadoLiberado = strtoupper((string)($raic->presenca ?? '')) === 'S';
$identityRaicView = $this->getRequest()->getAttribute('identity');
$ehYodaRaicView = !empty($identityRaicView['yoda']);
$ehTiRaicView = in_array((int)($identityRaicView['id'] ?? 0), [1, 8088], true);
$jediPermitidoRaicView = false;
if (!empty($identityRaicView['jedi'])) {
    $unidadesPermitidasRaicView = array_filter(array_map('trim', explode(',', (string)$identityRaicView['jedi'])));
    $jediPermitidoRaicView = in_array((string)($raic->unidade_id ?? ''), $unidadesPermitidasRaicView, true);
}
$podeGerenciarApresentacao = ($ehYodaRaicView || $jediPermitidoRaicView) && strtoupper((string)($raic->presenca ?? '')) !== 'S';
$hojeRaicView = new \Cake\I18n\FrozenDate(date('Y-m-d'));
$dataApresentacaoRaicView = !empty($raic->data_apresentacao)
    ? new \Cake\I18n\FrozenDate($raic->data_apresentacao->format('Y-m-d'))
    : null;
$hojeRaicViewIso = $hojeRaicView->format('Y-m-d');
$dataApresentacaoRaicViewIso = $dataApresentacaoRaicView?->format('Y-m-d');
$podeAgendarApresentacao = $podeGerenciarApresentacao && $dataApresentacaoRaicView === null;
$podeReagendarApresentacao = $podeGerenciarApresentacao
    && $dataApresentacaoRaicView !== null
    && $dataApresentacaoRaicViewIso >= $hojeRaicViewIso;
$mensagemAgendamentoRaicView = "O agendamento da apresentação pode ser feito pela Gestão de Fomento ou pela Coordenação da unidade da RAIC.\n\nQuando a RAIC ainda não tiver data marcada, o agendamento fica disponível.\n\nSe já houver uma data definida, o reagendamento pode ser feito até o próprio dia da apresentação.\n\nApós a data agendada, não é mais possível reagendar.";
$podeEditarRaic = (int)($raic->deleted ?? 0) === 0;
$tipoBolsaCodigo = strtoupper((string)($raic->tipo_bolsa ?? ''));
$editalTexto = 'Não informado';
if ($tipoBolsaCodigo === 'R') {
    if (!empty($editalEvento)) {
        $editalTexto = trim((string)$editalEvento->nome) . ' (#' . (int)$editalEvento->id . ')';
    } elseif (!empty($editalReferencia)) {
        $editalTexto = trim((string)$editalReferencia->nome) . ' (#' . (int)$editalReferencia->id . ')';
    }
} elseif (!empty($editalReferencia)) {
    $editalTexto = trim((string)$editalReferencia->nome) . ' (#' . (int)$editalReferencia->id . ')';
}
?>

<style>
    .nav-pills.tabs-raic-visualizacao {
        gap: 0.5rem;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }
    .nav-pills.tabs-raic-visualizacao .nav-link {
        border: 1px solid #d7dde4;
        border-radius: 999px;
        color: #344054;
        background: #f3f5f7;
        font-weight: 600;
        padding: 0.45rem 0.9rem;
        transition: all 0.15s ease-in-out;
    }
    .nav-pills.tabs-raic-visualizacao .nav-link:hover {
        border-color: #9aa4b2;
        color: #1f2937;
        background: #e9edf2;
    }
    .nav-pills.tabs-raic-visualizacao .nav-link.active {
        color: #fff;
        background: #0b5ed7;
        border-color: #0b5ed7;
        box-shadow: 0 1px 4px rgba(11, 94, 215, 0.22);
    }
    .raic-resumo {
        line-height: 1.45;
    }
    .raic-resumo > div {
        margin-bottom: 0.35rem;
    }
    .raic-resumo strong {
        color: #344054;
        font-weight: 600;
    }
    .raic-card-muted {
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        background: #f8f9fa;
        padding: 1rem;
    }
    .raic-table thead th {
        white-space: nowrap;
    }
    .raic-collapse-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 600;
    }
    .raic-historico-lista {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .raic-historico-item {
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        background: #fff;
        padding: 0.85rem 1rem;
    }
    .raic-historico-meta {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
    }
    .raic-tab-pane {
        padding-top: 0.25rem;
    }
</style>

<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0">
            Detalhes da RAIC #<?= (int)$raic->id ?>
            <?php if (!empty($raic->deleted)): ?>
                <span class="badge bg-danger ms-2">Deletada / Inativa</span>
            <?php endif; ?>
        </h4>

        <div class="d-flex gap-2">
            <?= $this->Html->link('Voltar para RAIC', ['controller' => 'RaicNew', 'action' => 'painel'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?php if ($podeEditarRaic): ?>
                <?= $this->Html->link('Editar', ['controller' => 'RaicNew', 'action' => 'editar', $raic->id], ['class' => 'btn btn-sm btn-outline-warning']) ?>
            <?php endif; ?>
            <?php if ($ehTiRaicView && (int)($raic->deleted ?? 0) === 0): ?>
                <?= $this->Html->link(
                    '<i class="fa fa-trash"></i>',
                    ['controller' => 'RaicNew', 'action' => 'deletar', $raic->id],
                    [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'escape' => false,
                        'title' => 'Deletar RAIC',
                    ]
                ) ?>
            <?php endif; ?>
            <?php if ($certificadoLiberado): ?>
                <?= $this->Html->link('Certificado', ['controller' => 'Certificados', 'action' => 'ver', $raic->id, 'R', !empty($raic->data_apresentacao) ? $raic->data_apresentacao->format('Y') : date('Y')], ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank']) ?>
            <?php endif; ?>
        </div>
    </div>

    <ul class="nav nav-pills flex-wrap tabs-raic-visualizacao" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-raic-dados" type="button">Dados da RAIC</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-raic-avaliacao" type="button">Avaliação</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-raic-historico" type="button">Histórico</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active raic-tab-pane" id="tab-raic-dados">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="raic-resumo">
                        <div><strong>Bolsista:</strong> <?= h($raic->usuario->nome ?? 'Não informado') ?></div>
                        <div><strong>Orientador:</strong> <?= h($raic->orientadore->nome ?? 'Não informado') ?></div>
                        <div><strong>Coorientador:</strong> <?= $coorientadorNome ? h($coorientadorNome) : $naoInformado ?></div>
                        <div><strong>Tipo:</strong> <?= h($tipoBolsaTexto) ?></div>
                        <div><strong>Edital:</strong> <?= h($editalTexto) ?></div>
                        <div><strong>Unidade:</strong> <?= !empty($raic->unidade->sigla) ? h($raic->unidade->sigla) : $naoInformado ?></div>
                        <div>
                            <strong>Cadastro:</strong>
                            <?php if (($raic->usuario_cadastro == null) && ($raic->tipo_bolsa == 'R')): ?>
                                <?= h(($raic->orientadore->nome ?? 'Não informado') . ' em ' . $dataCadastroTexto) ?>
                            <?php elseif (($raic->usuario_cadastro == null) && ($raic->tipo_bolsa == 'V')): ?>
                                <?= h('Automático em ' . $dataCadastroTexto) ?>
                            <?php elseif ($raic->usuario_cadastro != null): ?>
                                <?= h(($raic->cadastro->nome ?? 'Não informado') . ' em ' . $dataCadastroTexto) ?>
                            <?php else: ?>
                                <?= $naoInformado ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($raic->deleted)): ?>
                        <div class="alert alert-danger mt-3 mb-0">
                            RAIC deletada. Justificativa:
                            <?= !empty($raic->projeto_bolsista->justificativa_cancelamento)
                                ? h($raic->projeto_bolsista->justificativa_cancelamento)
                                : 'Não informada' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Subprojeto</h5>

                    <div class="mb-3">
                        <strong>Título:</strong><br>
                        <span><?= !empty($raic->titulo) ? h($raic->titulo) : $naoInformado ?></span>
                    </div>

                    <?php if ($raic->tipo_bolsa == 'R'): ?>
                        <?php /*
                        <div class="mb-3">
                            <button
                                class="btn btn-sm btn-outline-secondary raic-collapse-toggle"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#raicResumoSubprojeto"
                                aria-expanded="false"
                                aria-controls="raicResumoSubprojeto">
                                Resumo do Subprojeto
                            </button>
                            <div class="collapse mt-2" id="raicResumoSubprojeto">
                                <div class="raic-card-muted">
                                    <?= !empty($raic->resumo) ? nl2br(h($raic->resumo)) : $naoInformado ?>
                                </div>
                            </div>
                        </div>
                        */ ?>

                        <?php /*
                        <div class="raic-card-muted mb-3">
                            <div class="mb-3">
                                <strong>Apresentação na RAIC?</strong><br>
                                <span>
                                    <?= h((string)($raic->projeto_bolsista->apresentar_raic ?? '') === '1'
                                        ? 'Apresentar'
                                        : ((string)($raic->projeto_bolsista->apresentar_raic ?? '') === '0'
                                            ? 'Não apresentar'
                                            : 'RAIC Obrigatória')) ?>
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Referência do subprojeto</strong><br>
                                <span>
                                    <?= h((int)($raic->projeto_bolsista->referencia_raic ?? -1) === 0
                                        ? 'Cadastrar Novo Subprojeto'
                                        : ((int)($raic->projeto_bolsista->referencia_raic ?? -1) === 1
                                            ? 'Manter subprojeto da vigência anterior'
                                            : 'Não se aplica')) ?>
                                </span>
                            </div>

                            <?php if ((int)($raic->projeto_bolsista->referencia_raic ?? -1) === 0): ?>
                                <div>
                                    <strong>Justificativa da alteração de subprojeto</strong><br>
                                    <span><?= h($raic->projeto_bolsista->justificativa_alteracao ?? 'Não informado') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        */ ?>

                        <div class="mb-3">
                            <?= $this->Html->link(
                                'Solicitação de renovação: ' . $raic->projeto_bolsista_id,
                                ['controller' => 'Padrao', 'action' => 'visualizar', $raic->projeto_bolsista_id],
                                ['class' => 'btn btn-sm btn-outline-info']
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Apresentação</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="raic-card-muted h-100">
                                <strong>Data de Apresentação</strong><br>
                                <?= $dataApresentacaoTexto ? h($dataApresentacaoTexto) : 'Data de apresentação não localizada' ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="raic-card-muted h-100">
                                <strong>Tipo de Apresentação</strong><br>
                                <?= $tipoApresentacaoTexto ? h($tipoApresentacaoTexto) : 'Tipo de apresentação não localizado' ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="raic-card-muted h-100">
                                <strong>Programa</strong><br>
                                <?= h($programaTexto) ?>
                            </div>
                        </div>
                    </div>

                    <div class="raic-card-muted mt-3">
                        <strong>Local de Apresentação</strong><br>
                        <?= !empty($raic->local_apresentacao) ? h($raic->local_apresentacao) : 'Local não localizado' ?>
                    </div>

                    <div class="alert alert-light border mt-3 mb-0">
                        <?= nl2br(h($mensagemAgendamentoRaicView)) ?>
                    </div>

                    <?php if ((int)$raic->deleted === 0): ?>
                        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                            <?php if ($certificadoLiberado): ?>
                                <span class="badge bg-success">
                                    Certificado liberado
                                    <?= !empty($raic->data_liberacao)
                                        ? ' em ' . h($raic->data_liberacao->i18nFormat('dd/MM/YYYY')) . ', por ' . h($raic->libera->nome ?? 'Processo interno')
                                        : ' em processo interno' ?>
                                </span>
                            <?php else: ?>
                                <?= $this->Html->link(
                                    $dataApresentacaoRaicView !== null ? 'Reagendar apresentação' : 'Agendar apresentação',
                                    ['controller' => 'RaicNew', 'action' => 'agendar', $raic->id],
                                    ['class' => 'btn btn-sm btn-outline-primary']
                                ) ?>
                            <?php endif; ?>

                            <?php if (!$certificadoLiberado && $dataApresentacaoRaicView !== null && $podeGerenciarApresentacao): ?>
                                <?= $this->Form->postLink(
                                    'Libera Certificado',
                                    ['controller' => 'RaicNew', 'action' => 'liberacertificado', $raic->id],
                                    ['class' => 'btn btn-sm btn-warning']
                                ) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Relatório Anexado</h5>

                    <?php if (empty($anexoRelatorio)): ?>
                        <p class="mb-0"><?= $naoInformado ?></p>
                    <?php else: ?>
                        <div class="raic-card-muted">
                            <div class="mb-2">
                                <strong>Tipo:</strong>
                                <?= h((string)($anexoRelatorio->anexos_tipo->nome ?? 'Relatório')) ?>
                            </div>
                            <div class="mb-2">
                                <strong>Arquivo:</strong>
                                <?= !empty($anexoRelatorio->anexo) ? h($anexoRelatorio->anexo) : 'Não informado' ?>
                            </div>
                            <div class="mb-3">
                                <strong>Incluído por:</strong>
                                <?= h((string)($anexoRelatorio->usuario->nome ?? 'Não informado')) ?>
                                em
                                <?= !empty($anexoRelatorio->created) ? h($anexoRelatorio->created->subHours(3)->i18nFormat('dd/MM/YYYY HH:mm')) : 'Não informado' ?>
                            </div>
                            <?php if (!empty($anexoRelatorio->anexo)): ?>
                                <a
                                    href="/uploads/anexos/<?= h($anexoRelatorio->anexo) ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-primary">
                                    Baixar relatório
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade raic-tab-pane" id="tab-raic-avaliacao">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="mb-0">Avaliadores</h5>
                        <?php
                            $identityAtual = $this->request->getAttribute('identity');
                            $identityAtualId = is_array($identityAtual)
                                ? (int)($identityAtual['id'] ?? 0)
                                : (int)($identityAtual->id ?? 0);
                            $identityAtualYoda = is_array($identityAtual)
                                ? !empty($identityAtual['yoda'])
                                : !empty($identityAtual->yoda);
                            $identityAtualJedi = is_array($identityAtual)
                                ? (string)($identityAtual['jedi'] ?? '')
                                : (string)($identityAtual->jedi ?? '');
                            $identityAtualTi = in_array($identityAtualId, [1, 8088], true);
                            $unidadesJedi = array_filter(array_map('trim', explode(',', $identityAtualJedi)));
                            $podeGerenciarAgendamento = $identityAtualYoda
                                || $identityAtualTi
                                || in_array((string)($raic->unidade_id ?? ''), $unidadesJedi, true);
                            $podeReagendarPorData = true;
                            if (!empty($raic->data_apresentacao) && $raic->data_apresentacao instanceof \DateTimeInterface) {
                                $podeReagendarPorData = $raic->data_apresentacao->format('Y-m-d') >= date('Y-m-d') || $identityAtualTi;
                            }
                            $podeVerNotas = $identityAtualYoda
                                || in_array($identityAtualId, [1, 8088], true)
                                || $identityAtualId === (int)($raic->orientador ?? 0)
                                || $identityAtualId === (int)($raic->usuario_id ?? 0);
                        ?>
                        <?php if ($podeGerenciarAgendamento && $podeReagendarPorData): ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-user-edit"></i> Alterar banca',
                                ['controller' => 'RaicNew', 'action' => 'agendar', (int)$raic->id],
                                ['class' => 'btn btn-sm btn-outline-success', 'escape' => false]
                            ) ?>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($lista) || $lista->count() === 0): ?>
                        <p class="text-muted mb-0">Nenhum avaliador vinculado.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle raic-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>Deletado?</th>
                                        <th>Situação</th>
                                        <th>Cadastro</th>
                                        <th>Exclusão</th>
                                        <th class="text-end"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lista as $b): ?>
                                        <?php
                                        $deletado = (int)($b->deleted ?? 0) === 1;
                                        $avaliado = !$deletado && (string)($b->situacao ?? '') === 'F';
                                        $dataCadastro = $b->created ?? null;
                                        $dataCadastroTexto = $dataCadastro && method_exists($dataCadastro, 'i18nFormat')
                                            ? $dataCadastro->subHours(3)->i18nFormat('dd/MM/yyyy HH:mm')
                                            : 'Não informado';
                                        $usuarioCadastro = trim((string)($b->criador->nome ?? ''));
                                        if ($usuarioCadastro === '') {
                                            $usuarioCadastro = 'Não informado';
                                        }
                                        $dataExclusao = $b->deletado_em ?? null;
                                        $dataExclusaoTexto = $dataExclusao && method_exists($dataExclusao, 'i18nFormat')
                                            ? $dataExclusao->subHours(3)->i18nFormat('dd/MM/yyyy HH:mm')
                                            : 'Não informado';
                                        $usuarioExclusao = trim((string)($b->deletador->nome ?? ''));
                                        if ($usuarioExclusao === '') {
                                            $usuarioExclusao = 'Não informado';
                                        }
                                        ?>
                                        <tr class="<?= $deletado ? 'table-danger' : ($avaliado ? 'table-success' : '') ?>">
                                            <td>
                                                <?= h($b->avaliador->usuario->nome ?? 'Não informado') ?>
                                                <span class="text-muted small">(AV <?= h((string)($b->ordem ?? '-')) ?>)</span>
                                            </td>
                                            <td><?= $deletado ? 'Sim' : 'Não' ?></td>
                                            <td><?= h($deletado ? 'Deletado' : ($avaliado ? 'Avaliado' : 'Aguardando')) ?></td>
                                            <td>
                                                <div class="small">
                                                    <?= h($dataCadastroTexto) ?><br>
                                                    <span class="text-muted"><?= h($usuarioCadastro) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($deletado): ?>
                                                    <div class="small">
                                                        <?= h($dataExclusaoTexto) ?><br>
                                                        <span class="text-muted"><?= h($usuarioExclusao) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($avaliado && $podeVerNotas): ?>
                                                    <?= $this->Html->link(
                                                        '<i class="fas fa-file-alt"></i> Ver notas',
                                                        ['controller' => 'Avaliadores', 'action' => 'verNotas', (int)$b->id],
                                                        ['class' => 'btn btn-xs btn-outline-primary py-0 px-1', 'escape' => false]
                                                    ) ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade raic-tab-pane" id="tab-raic-historico">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Histórico</h5>

                    <?php if (empty($historicos) || $historicos->count() === 0): ?>
                        <p class="text-muted mb-0">Nenhum histórico localizado.</p>
                    <?php else: ?>
                        <div class="raic-historico-lista">
                            <?php foreach ($historicos as $historico): ?>
                                <div class="raic-historico-item">
                                    <div class="raic-historico-meta">
                                        <?php $dataHistoricoTela = !empty($historico->created) ? $historico->created->subHours(3) : null; ?>
                                        <strong>Data:</strong>
                                        <?= $dataHistoricoTela ? h($dataHistoricoTela->i18nFormat('dd/MM/YYYY HH:mm')) : 'Não informado' ?>
                                        <span class="ms-2">
                                            <strong>Por:</strong>
                                            <?= h((string)($historico->usuario->nome ?? 'Não informado')) ?>
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Alteração:</strong>
                                        <?= !empty($historico->alteracao) ? h($historico->alteracao) : 'Não informado' ?>
                                    </div>
                                    <div>
                                        <strong>Justificativa:</strong>
                                        <?= !empty($historico->justificativa) ? h($historico->justificativa) : 'Não informado' ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
