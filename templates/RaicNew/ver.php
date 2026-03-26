<?php
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
$programaTexto = match ((string)($raic->projeto_bolsista->programa ?? '')) {
    'P' => 'Pibic',
    'T' => 'Pibiti',
    default => 'Iniciação Científica',
};
$certificadoLiberado = strtoupper((string)($raic->presenca ?? '')) === 'S';
?>

<style>
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
            <?php if ($certificadoLiberado): ?>
                <?= $this->Html->link('Certificado', ['controller' => 'Certificados', 'action' => 'ver', $raic->id, 'R', !empty($raic->data_apresentacao) ? $raic->data_apresentacao->format('Y') : date('Y')], ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank']) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="raic-resumo">
                <div><strong>Bolsista:</strong> <?= h($raic->usuario->nome ?? 'Não informado') ?></div>
                <div><strong>Orientador:</strong> <?= h($raic->orientadore->nome ?? 'Não informado') ?></div>
                <div><strong>Coorientador:</strong> <?= $coorientadorNome ? h($coorientadorNome) : $naoInformado ?></div>
                <div><strong>Tipo:</strong> <?= h($tipoBolsaTexto) ?></div>
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

                <?php if ($raic->tipo_bolsa == 'R'): ?>
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
                <?php endif; ?>
            </div>

            <?php if ($raic->tipo_bolsa == 'R'): ?>
                <div class="raic-card-muted mt-3">
                    <strong>Local de Apresentação</strong><br>
                    <?= !empty($raic->local_apresentacao) ? h($raic->local_apresentacao) : 'Local não localizado' ?>
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
                        <?php elseif (!empty($raic->data_apresentacao)): ?>
                            <!-- Link antigo desativado: Raics/alteraapresentacao -->
                            <span class="btn btn-sm btn-outline-success disabled" aria-disabled="true">Reagendar apresentação</span>
                            <?= $this->Form->postLink(
                                'Libera Certificado',
                                ['controller' => 'RaicNew', 'action' => 'liberacertificado', $raic->id],
                                ['class' => 'btn btn-sm btn-warning']
                            ) ?>
                        <?php else: ?>
                            <span class="text-muted">Certificado não liberado.</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($raic->tipo_bolsa == 'R'): ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Avaliadores</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle raic-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Ativo</th>
                                <th>Coordenação</th>
                                <th>Situação</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista as $b): ?>
                                <tr class="<?= $b->deleted == 1 ? 'text-danger' : ($b->situacao == 'F' ? 'text-success' : '') ?>">
                                    <td><?= h($b->avaliador->usuario->nome ?? 'Não informado') ?></td>
                                    <td><?= $b->deleted == 1 ? 'Deletado' : 'Sim' ?></td>
                                    <td><?= $b->coordenador == 1 ? 'Coordenador' : '-' ?></td>
                                    <td><?= h($b->deleted == 1 ? 'Deletado' : ($b->situacao == 'F' ? 'Avaliado' : 'Aguardando')) ?></td>
                                    <td class="text-end">
                                        <?php if ($b->situacao != 'F' && $b->deleted == 0): ?>
                                            <!-- Link antigo desativado: Raics/alterabanca -->
                                            <span class="btn btn-sm btn-outline-success disabled" aria-disabled="true">Alterar</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
