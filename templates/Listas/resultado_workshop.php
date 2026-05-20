<?php
$usuarioId = (int)($this->request->getAttribute('identity')->id ?? 0);
$tiposTabela = [
    'R' => 'Renovação',
    'V' => 'Workshops de Outras Agências',
    'Z' => 'Workshops de Outras Agências',
];

$anoLabel = ($filtros['ano'] ?? '') !== '' ? ($filtros['ano'] ?? '') : 'Todos';
$agendadaLabel = ($filtros['agendada'] ?? '') === 'S' ? 'Sim' : (($filtros['agendada'] ?? '') === 'N' ? 'Não' : 'Todas');
$certificadoLabel = ($filtros['certificado'] ?? '') === 'S' ? 'Sim' : (($filtros['certificado'] ?? '') === 'N' ? 'Não' : 'Todos');
$unidadeLabel = (($filtros['unidade_id'] ?? '') !== '' && isset($unidades[$filtros['unidade_id']])) ? $unidades[$filtros['unidade_id']] : 'Todas';
$tipoBolsaLabel = (($filtros['tipo_bolsa'] ?? '') !== '' && isset($tipoBolsa[$filtros['tipo_bolsa']])) ? $tipoBolsa[$filtros['tipo_bolsa']] : 'Todos';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Resultado - Listagem Workshop</h4>
        <a href="<?= $this->Url->build(['controller' => 'Listas', 'action' => 'buscaWorkshop']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Nova busca
        </a>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="text-muted small">
                Filtros aplicados:
                <strong>Ano</strong> <?= h($anoLabel) ?>,
                <strong>Agendada</strong> <?= h($agendadaLabel) ?>,
                <strong>Unidade</strong> <?= h($unidadeLabel) ?>,
                <strong>Certificado</strong> <?= h($certificadoLabel) ?>,
                <strong>Tipo</strong> <?= h($tipoBolsaLabel) ?>
            </div>
            <a class="btn btn-outline-success"
               href="<?= $this->Url->build([
                   'controller' => 'Listas',
                   'action' => 'resultadoWorkshop',
                   '?' => [
                       'ano' => $filtros['ano'] ?: null,
                       'agendada' => $filtros['agendada'] ?: null,
                       'unidade_id' => $filtros['unidade_id'] ?: null,
                       'certificado' => $filtros['certificado'] ?: null,
                       'tipo_bolsa' => $filtros['tipo_bolsa'] ?: null,
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
                        <th><?= $this->Paginator->sort('Workshops.id', 'ID') ?></th>
                        <th><?= $this->Paginator->sort('bolsista_nome_ordenacao', 'Bolsista') ?></th>
                        <th><?= $this->Paginator->sort('orientador_nome_ordenacao', 'Orientador') ?></th>
                        <th><?= $this->Paginator->sort('Unidades.sigla', 'Unidade') ?></th>
                        <th><?= $this->Paginator->sort('Workshops.tipo_bolsa', 'Tipo') ?></th>
                        <th><?= $this->Paginator->sort('Workshops.data_apresentacao', 'Agendada') ?></th>
                        <th><?= $this->Paginator->sort('Workshops.presenca', 'Certificado') ?></th>
                        <th><?= $this->Paginator->sort('Workshops.nota_final', 'Nota') ?></th>
                        <th><?= $this->Paginator->sort('Workshops.deleted', 'Status') ?></th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listas)): ?>
                        <?php foreach ($listas as $workshop): ?>
                            <?php
                            $anoCertificado = !empty($workshop->data_apresentacao)
                                ? $workshop->data_apresentacao->format('Y')
                                : (!empty($workshop->editai->fim_vigencia) ? date('Y', strtotime((string)$workshop->editai->fim_vigencia)) : date('Y'));
                            ?>
                            <tr>
                                <td>
                                    <?= $this->Html->link(
                                        '#' . (int)$workshop->id,
                                        ['controller' => 'Workshops', 'action' => 'ver', $workshop->id],
                                        ['class' => 'btn btn-sm btn-outline-primary']
                                    ) ?>
                                </td>
                                <td>
                                    <?php if ((int)($workshop->bolsista ?? 0) === $usuarioId): ?>
                                        <span class="badge bg-danger rounded-circle d-inline-block" title="Você" style="width:10px;height:10px;padding:0;vertical-align:middle;"></span>
                                    <?php else: ?>
                                        <?= h($workshop->usuario->nome ?? 'Não informado') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($workshop->orientadore->nome ?? 'Não informado') ?></td>
                                <td><?= h($workshop->unidade->sigla ?? 'Não informado') ?></td>
                                <td><?= h($tiposTabela[strtoupper((string)($workshop->tipo_bolsa ?? ''))] ?? ($workshop->tipo_bolsa ?? 'Não informado')) ?></td>
                                <td>
                                    <?php if (!empty($workshop->data_apresentacao)): ?>
                                        <span class="badge bg-success">Sim</span>
                                        <div class="small text-muted mt-1">
                                            <?= h($workshop->data_apresentacao->i18nFormat('dd/MM/YYYY')) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Não</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (strtoupper((string)($workshop->presenca ?? '')) === 'S'): ?>
                                        <?php if ((int)($workshop->bolsista ?? 0) === $usuarioId): ?>
                                            <?= $this->Html->link(
                                                'Certificado',
                                                ['controller' => 'Certificados', 'action' => 'ver', $workshop->id, 'R', $anoCertificado],
                                                ['class' => 'btn btn-info btn-xs', 'target' => '_blank']
                                            ) ?>
                                        <?php else: ?>
                                            <span class="text-success small">Liberado</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="d-flex flex-column align-items-start gap-1">
                                            <span class="text-muted small">Não liberado</span>
                                            <?php if (!empty($workshop->data_apresentacao)): ?>
                                                <?= $this->Form->postLink(
                                                    'Liberar',
                                                    ['controller' => 'Workshops', 'action' => 'liberacertificado', $workshop->id],
                                                    [
                                                        'class' => 'btn btn-xs btn-outline-success',
                                                        'confirm' => 'Confirma a liberação do certificado desta Workshop?',
                                                    ]
                                                ) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= $workshop->nota_final !== null ? h(number_format((float)$workshop->nota_final, 2, ',', '.')) : '-' ?></td>
                                <td>
                                    <?php if ((int)($workshop->deleted ?? 0) === 1): ?>
                                        <span class="badge bg-danger">Deletada</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Ativa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ((int)($workshop->deleted ?? 0) !== 1): ?>
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <?php if (empty($workshop->data_apresentacao)): ?>
                                                <?= $this->Html->link(
                                                    'Agendar',
                                                    ['controller' => 'Workshops', 'action' => 'agendar', $workshop->id],
                                                    ['class' => 'btn btn-sm btn-outline-primary']
                                                ) ?>
                                            <?php elseif ($workshop->data_apresentacao->format('Ymd') >= date('Ymd')): ?>
                                                <?= $this->Html->link(
                                                    'Reagendar',
                                                    ['controller' => 'Workshops', 'action' => 'agendar', $workshop->id],
                                                    ['class' => 'btn btn-sm btn-outline-secondary']
                                                ) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted fw-bold">Nenhum registro encontrado.</td>
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
