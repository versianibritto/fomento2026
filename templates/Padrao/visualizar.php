<?php
$naoInformado = '<span class="badge border border-danger text-danger bg-transparent fw-normal">Não informado</span>';
$filhosMap = [
    '0' => 'não possui filhos, ou sao maiores de 5 anos',
    '1' => 'Possui um filho menor de 5 anos',
    '2' => 'Possui mais de um filho menor de 5 anos',
];
$origemAtual = strtoupper((string)($origemAtual ?? ($inscricao->origem ?? '')));
$ehYoda = !empty($this->request->getAttribute('identity')['yoda']);
$isRenovacao = $origemAtual === 'R';
$pdjInscricaoId = !empty($inscricao->pdj_inscricoe_id) ? (int)$inscricao->pdj_inscricoe_id : null;
$mostrarReferenciaAnterior = in_array($origemAtual, ['R', 'S', 'A'], true);
$referenciaAnteriorValor = null;
if ($origemAtual === 'R') {
    $referenciaAnteriorValor = $inscricao->referencia_inscricao_anterior ?? null;
} elseif (in_array($origemAtual, ['S', 'A'], true)) {
    $referenciaAnteriorValor = $inscricao->bolsista_anterior ?? null;
}
$referenciaAnteriorId = (is_numeric((string)$referenciaAnteriorValor) && (int)$referenciaAnteriorValor > 0)
    ? (int)$referenciaAnteriorValor
    : null;
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
    .sumula-campos p:last-child {
        margin-bottom: 0;
    }
    .sumula-separador {
        margin: 1rem 0 0.75rem;
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
        $ehTIVisualizacao = !empty($identityTela->yoda) || !empty($identityTela->jedi);
        $origemTela = strtoupper((string)($inscricao->origem ?? ''));
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

        <?php if (in_array($faseAtual, [5], true)): ?>
            <?= $this->Html->link('Finalizar', [
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
                'controller' => 'Padrao',
                'action' => 'substituir',
                (int)$inscricao->id,
            ], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

        <?php if ($ehTIVisualizacao && in_array($faseAtual, [14], true)): ?>
            <?= $this->Html->link('Reativar', [
                'controller' => 'Padrao',
                'action' => 'reativar',
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
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 resumo-principal">
                <?php if ($mostrarReferenciaAnterior): ?>
                    <div class="col-md-6">
                        <strong>Data da inscrição:</strong>
                        <?= !empty($inscricao->created) ? h($inscricao->created->i18nFormat('dd/MM/yyyy')) : $naoInformado ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Referência anterior:</strong>
                        <?php if ($referenciaAnteriorId !== null): ?>
                            <a href="<?= $this->Url->build([
                                'controller' => 'Padrao',
                                'action' => 'visualizar',
                                $referenciaAnteriorId,
                            ]) ?>" target="_blank" class="text-decoration-none fw-semibold">
                                #<?= h((string)$referenciaAnteriorId) ?>
                            </a>
                        <?php elseif ($referenciaAnteriorValor !== null && $referenciaAnteriorValor !== ''): ?>
                            <?= h((string)$referenciaAnteriorValor) ?>
                        <?php else: ?>
                            <?= $naoInformado ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="col-md-12">
                        <strong>Data da inscricao:</strong>
                        <?= !empty($inscricao->created) ? h($inscricao->created->i18nFormat('dd/MM/yyyy')) : $naoInformado ?>
                    </div>
                <?php endif; ?>
                <div class="col-md-6"><strong>Edital:</strong> <?= !empty($edital->nome) ? h($edital->nome) : $naoInformado ?></div>
                <div class="col-md-6">
                    <strong>Situação:</strong>
                    <?php if (!empty($inscricao->fase->nome)): ?>
                        <?php if ((int)($inscricao->vigente ?? 0) === 1): ?>
                            <span class="badge bg-success"><?= h($inscricao->fase->nome) ?></span>
                        <?php else: ?>
                            <?= h($inscricao->fase->nome) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= $naoInformado ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6"><strong>Cota:</strong> <?php
                    $cotaValor = (string)($inscricao->cota ?? '');
                    echo !empty($cotas[$cotaValor]) ? h($cotas[$cotaValor]) : ($cotaValor !== '' ? h($cotaValor) : $naoInformado);
                ?></div>
                <div class="col-md-6"><strong>Origem:</strong> <?= !empty($origens[(string)($inscricao->origem ?? '')]) ? h($origens[(string)($inscricao->origem ?? '')]) : $naoInformado ?></div>
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
                <div class="col-md-12"><strong>Orientador:</strong> <?= !empty($inscricao->orientadore->nome) ? h($inscricao->orientadore->nome) : $naoInformado ?></div>
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
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-historico" type="button">Histórico</button></li>
                <?php if ($ehYoda): ?>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-gestao" type="button">Gestão</button></li>
                <?php endif; ?>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-bolsista">
                    <?php if (empty($inscricao->bolsista_usuario)): ?>
                        <p><span class="badge bg-danger">Bolsista não informado</span></p>
                    <?php else: ?>
                        <p><strong>Nome:</strong> <?= !empty($inscricao->bolsista_usuario->nome) ? h($inscricao->bolsista_usuario->nome) : $naoInformado ?></p>
                        <p><strong>Universidade:</strong> <?= !empty($inscricao->bolsista_usuario->instituicao_curso) ? h($inscricao->bolsista_usuario->instituicao_curso) : $naoInformado ?></p>
                        <p><strong>Curso:</strong> <?= !empty($inscricao->bolsista_usuario->curso) ? h($inscricao->bolsista_usuario->curso) : $naoInformado ?></p>
                    <?php endif; ?>
                    <p><strong>Primeiro período:</strong> <?= $inscricao->primeiro_periodo === null ? $naoInformado : ((int)$inscricao->primeiro_periodo === 1 ? 'Sim' : 'Nao') ?></p>
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
                                            <?php
                                                if (!empty($anexo['created']) && $anexo['created'] instanceof \Cake\I18n\FrozenTime) {
                                                    echo h($anexo['created']->i18nFormat('dd/MM/yyyy HH:mm'));
                                                } elseif (!empty($anexo['created'])) {
                                                    $tsAnexo = strtotime((string)$anexo['created']);
                                                    echo h($tsAnexo ? date('d/m/Y H:i', $tsAnexo) : 'Não informado');
                                                } else {
                                                    echo 'Não informado';
                                                }
                                            ?>
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
                        <div class="sumula-campos">
                            <?php if ($mostrarFilhosOrientadora): ?>
                                <p>
                                    <strong>Orientadora, possui filhos menores de 5 anos?</strong>
                                    <?= isset($filhosMap[$filhosKey]) ? h($filhosMap[$filhosKey]) : $naoInformado ?>
                                </p>
                            <?php endif; ?>
                            <p>
                                <strong>Ano de conclusão do doutorado:</strong>
                                <?= $inscricao->ano_doutorado !== null && $inscricao->ano_doutorado !== '' ? h((string)$inscricao->ano_doutorado) : $naoInformado ?>
                            </p>
                            <p>
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
                            </p>
                        </div>
                        <hr class="sumula-separador">
                        <h6 class="mb-2">Itens de Súmula</h6>
                        <?php if (empty($sumulasEdital) || $sumulasEdital->count() === 0): ?>
                            <p><span class="badge bg-danger">Nenhum item de súmula encontrado</span></p>
                        <?php else: ?>
                            <div class="table-responsive">
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
                        <hr>
                        <h6 class="mb-2">Anexos da Súmula</h6>
                        <?php if (empty($anexosPorBloco['O'])): ?>
                            <p><span class="badge bg-danger">Nenhum anexo encontrado</span></p>
                        <?php else: ?>
                            <ul class="anexos-lista">
                                <?php foreach ($anexosPorBloco['O'] as $anexo): ?>
                                    <li>
                                        <div class="anexo-titulo">
                                            <span class="anexo-tipo"><?= h($anexo['tipo_nome']) ?></span>
                                            <span class="anexo-arquivo"><?= !empty($anexo['arquivo']) ? h($anexo['arquivo']) : 'Não informado' ?></span>
                                            <span class="anexo-meta">
                                                Incluído por <?= !empty($anexo['usuario_nome']) ? h($anexo['usuario_nome']) : 'Não informado' ?>
                                                em
                                                <?php
                                                    if (!empty($anexo['created']) && $anexo['created'] instanceof \Cake\I18n\FrozenTime) {
                                                        echo h($anexo['created']->i18nFormat('dd/MM/yyyy HH:mm'));
                                                    } elseif (!empty($anexo['created'])) {
                                                        $tsAnexo = strtotime((string)$anexo['created']);
                                                        echo h($tsAnexo ? date('d/m/Y H:i', $tsAnexo) : 'Não informado');
                                                    } else {
                                                        echo 'Não informado';
                                                    }
                                                ?>
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
                                                    <?php
                                                        if (!empty($anexo['created']) && $anexo['created'] instanceof \Cake\I18n\FrozenTime) {
                                                            echo h($anexo['created']->i18nFormat('dd/MM/yyyy HH:mm'));
                                                        } elseif (!empty($anexo['created'])) {
                                                            $tsAnexo = strtotime((string)$anexo['created']);
                                                            echo h($tsAnexo ? date('d/m/Y H:i', $tsAnexo) : 'Não informado');
                                                        } else {
                                                            echo 'Não informado';
                                                        }
                                                    ?>
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
                                                            <?php
                                                                if (!empty($anexo['created']) && $anexo['created'] instanceof \Cake\I18n\FrozenTime) {
                                                                    echo h($anexo['created']->i18nFormat('dd/MM/yyyy HH:mm'));
                                                                } elseif (!empty($anexo['created'])) {
                                                                    $tsAnexo = strtotime((string)$anexo['created']);
                                                                    echo h($tsAnexo ? date('d/m/Y H:i', $tsAnexo) : 'Não informado');
                                                                } else {
                                                                    echo 'Não informado';
                                                                }
                                                            ?>
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
                                                    <?php
                                                        if (!empty($anexo['created']) && $anexo['created'] instanceof \Cake\I18n\FrozenTime) {
                                                            echo h($anexo['created']->i18nFormat('dd/MM/yyyy HH:mm'));
                                                        } elseif (!empty($anexo['created'])) {
                                                            $tsAnexo = strtotime((string)$anexo['created']);
                                                            echo h($tsAnexo ? date('d/m/Y H:i', $tsAnexo) : 'Não informado');
                                                        } else {
                                                            echo 'Não informado';
                                                        }
                                                    ?>
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

                <div class="tab-pane fade" id="tab-coorientador">
                    <?php if (empty($inscricao->coorientadore)): ?>
                        <p><span class="badge bg-danger">Coorientador não informado</span></p>
                    <?php else: ?>
                        <p><strong>Nome:</strong> <?= !empty($inscricao->coorientadore->nome) ? h($inscricao->coorientadore->nome) : $naoInformado ?></p>
                        <div class="row g-2">
                            <div class="col-md-6"><strong>Curso:</strong> <?= !empty($inscricao->coorientadore->curso) ? h($inscricao->coorientadore->curso) : $naoInformado ?></div>
                            <div class="col-md-6"><strong>Universidade:</strong> <?= !empty($inscricao->coorientadore->instituicao_curso) ? h($inscricao->coorientadore->instituicao_curso) : $naoInformado ?></div>
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
                                            <?php
                                                if (!empty($anexo['created']) && $anexo['created'] instanceof \Cake\I18n\FrozenTime) {
                                                    echo h($anexo['created']->i18nFormat('dd/MM/yyyy HH:mm'));
                                                } elseif (!empty($anexo['created'])) {
                                                    $tsAnexo = strtotime((string)$anexo['created']);
                                                    echo h($tsAnexo ? date('d/m/Y H:i', $tsAnexo) : 'Não informado');
                                                } else {
                                                    echo 'Não informado';
                                                }
                                            ?>
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
                    <p>
                        <strong>Resultado final:</strong>
                        <?= !empty($resultadoMap[(string)($inscricao->resultado ?? '')]) ? h($resultadoMap[(string)$inscricao->resultado]) : $naoInformado ?>
                    </p>
                    <?php if (!empty($avaliacoes) && $avaliacoes->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Avaliador</th>
                                        <th>Status</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($avaliacoes as $av): ?>
                                        <tr>
                                            <td>
                                                <?= !empty($av->avaliador->usuario->nome) ? h($av->avaliador->usuario->nome) : 'Avaliador #' . (int)$av->avaliador_id ?>
                                            </td>
                                            <td>
                                                <?= !empty($statusAvaliacaoMap[(string)($av->situacao ?? '')]) ? h($statusAvaliacaoMap[(string)$av->situacao]) : (!empty($av->situacao) ? h((string)$av->situacao) : $naoInformado) ?>
                                            </td>
                                            <td>
                                                <?= $av->nota !== null ? h((string)$av->nota) : $naoInformado ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nenhum avaliador vinculado.</p>
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
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($ehTIVisualizacao && in_array($faseAtual, [1, 3], true)): ?>
        <div class="d-flex justify-content-end mt-3">
            <?= $this->Html->link('Deletar inscrição', [
                'controller' => 'Padrao',
                'action' => 'deletar',
                (int)$inscricao->id,
            ], [
                'class' => 'btn btn-link btn-sm text-danger p-0',
            ]) ?>
        </div>
    <?php endif; ?>
</div>
