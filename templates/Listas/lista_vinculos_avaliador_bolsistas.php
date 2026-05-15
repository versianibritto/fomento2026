<?php
/**
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $vinculos
 * @var array<string, mixed> $filtros
 * @var array<string, string> $ativoOptions
 * @var array<string, string> $statusOptions
 * @var array<string, string> $tipoOptions
 * @var array<int, string> $unidades
 * @var array<int, string> $editais
 */
$naoInformado = 'Não informado';
$paginationQuery = array_intersect_key(
    $this->request->getQueryParams(),
    array_flip(['avaliador_nome', 'ativo', 'status', 'unidade_id', 'tipo', 'editai_id'])
);
$exportQuery = $this->request->getQueryParams();
unset($exportQuery['page'], $exportQuery['exportar']);
$this->Paginator->options(['url' => ['?' => $paginationQuery]]);

$identity = $this->request->getAttribute('identity');
$identityId = is_array($identity) ? (int)($identity['id'] ?? 0) : (int)($identity->id ?? 0);
$ehTI = in_array($identityId, [1, 8088], true);

$formatarNota = static function ($valor): string {
    if ($valor === null || $valor === '') {
        return '-';
    }

    return number_format((float)$valor, 2, ',', '.');
};
?>
<style>
    .vinculos-avaliador-paginacao {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
    }
    .vinculos-avaliador-paginacao ul,
    .vinculos-avaliador-paginacao li {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .vinculos-avaliador-paginacao ul {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .vinculos-avaliador-paginacao a,
    .vinculos-avaliador-paginacao .current {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.1rem;
        height: 2.1rem;
        padding: 0 0.65rem;
        border: 1px solid #ced4da;
        border-radius: 0.35rem;
        background: #fff;
        color: #0d6efd;
        line-height: 1;
        text-decoration: none;
    }
    .vinculos-avaliador-paginacao a:hover {
        background: #e9f2ff;
        border-color: #9ec5fe;
    }
    .vinculos-avaliador-paginacao .current {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        font-weight: 700;
    }
    .vinculos-avaliador-paginacao .disabled {
        opacity: 0.55;
        pointer-events: none;
    }
</style>

<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Vínculos de avaliadores</h4>
                    <p class="text-muted mb-3">
                        Listagem dos vínculos em avaliador bolsista, incluindo referência, avaliador, notas e unidades.
                    </p>

                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3 align-items-end mb-4',
                    ]) ?>
                        <div class="col-md-3">
                            <?= $this->Form->control('avaliador_nome', [
                                'label' => 'Nome do avaliador',
                                'value' => $filtros['avaliador_nome'] ?? '',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('ativo', [
                                'label' => 'Ativo',
                                'options' => $ativoOptions,
                                'empty' => 'Todas',
                                'default' => $filtros['ativo'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('status', [
                                'label' => 'Status',
                                'options' => $statusOptions,
                                'empty' => 'Todos',
                                'default' => $filtros['status'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('unidade_id', [
                                'label' => 'Unidade do avaliador',
                                'options' => $unidades,
                                'empty' => 'Todas',
                                'default' => $filtros['unidade_id'] ?? 0,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('tipo', [
                                'label' => 'Tipo',
                                'options' => $tipoOptions,
                                'empty' => 'Todos',
                                'default' => $filtros['tipo'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $this->Form->control('editai_id', [
                                'label' => 'Edital',
                                'options' => $editais,
                                'empty' => 'Todos com avaliação aberta',
                                'default' => $filtros['editai_id'] ?? 0,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Exportar CSV',
                                [
                                    'controller' => 'Listas',
                                    'action' => 'listaVinculosAvaliadorBolsistas',
                                    '?' => $exportQuery + ['exportar' => 1],
                                ],
                                ['class' => 'btn btn-success']
                            ) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Listas', 'action' => 'listaVinculosAvaliadorBolsistas'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if ($vinculos->count() === 0): ?>
                        <div class="alert alert-info mb-0">Nenhum vínculo localizado com os filtros informados.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Referência</th>
                                        <th>Edital</th>
                                        <th>Avaliador</th>
                                        <th>Ativo?</th>
                                        <th>Status</th>
                                        <th>Orientador</th>
                                        <th>Bolsista</th>
                                        <th>Unidade<br>avaliador</th>
                                        <th>Unidade<br>orientador</th>
                                        <th>Ano</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vinculos as $vinculo): ?>
                                        <?php
                                            $referenciaTipo = 'Referência não identificada';
                                            $referenciaId = '-';
                                            $orientadorNome = $naoInformado;
                                            $bolsistaNome = $naoInformado;
                                            $unidadeReferencia = '-';

                                            if (!empty($vinculo->projeto_bolsista_id) || !empty($vinculo->projeto_bolsista_legado_id)) {
                                                $referenciaTipo = 'Inscrição';
                                                $referenciaId = '#' . (int)($vinculo->projeto_bolsista_id ?: $vinculo->projeto_bolsista_legado_id);
                                                $orientadorNome = $vinculo->projeto_bolsista->orientadore->nome
                                                    ?? $vinculo->orientador_legado_nome
                                                    ?? $naoInformado;
                                                $bolsistaNome = $vinculo->projeto_bolsista->bolsista_usuario->nome
                                                    ?? $vinculo->bolsista_legado_nome
                                                    ?? $naoInformado;
                                                $unidadeReferencia = $vinculo->projeto_bolsista->orientadore->unidade->sigla
                                                    ?? $vinculo->unidade_orientador_legado_sigla
                                                    ?? '-';
                                            } elseif (!empty($vinculo->raic_id)) {
                                                $referenciaTipo = 'RAIC';
                                                $referenciaId = '#' . (int)$vinculo->raic_id;
                                                $orientadorNome = $vinculo->raic->orientadore->nome ?? $naoInformado;
                                                $bolsistaNome = $vinculo->raic->usuario->nome ?? $naoInformado;
                                                $unidadeReferencia = $vinculo->raic->unidade->sigla ?? '-';
                                            } elseif (!empty($vinculo->workshop_id)) {
                                                $referenciaTipo = 'Workshop';
                                                $referenciaId = '#' . (int)$vinculo->workshop_id;
                                                $orientadorNome = $vinculo->workshop->orientadore->nome ?? $naoInformado;
                                                $bolsistaNome = $vinculo->workshop->usuario->nome ?? $naoInformado;
                                                $unidadeReferencia = $vinculo->workshop->unidade->sigla ?? '-';
                                            } elseif (!empty($vinculo->bolsista)) {
                                                $referenciaTipo = 'Legado';
                                                $referenciaId = '#' . (int)$vinculo->bolsista;
                                            }

                                            $avaliadorNome = $vinculo->usuario->nome
                                                ?? $vinculo->avaliador->usuario->nome
                                                ?? $naoInformado;
                                            $unidadeAvaliador = $vinculo->usuario->unidade->sigla
                                                ?? $vinculo->avaliador->usuario->unidade->sigla
                                                ?? $vinculo->avaliador->unidade->sigla
                                                ?? '-';
                                            $deletado = (int)($vinculo->deleted ?? 0) === 1;
                                            $avaliado = (string)($vinculo->situacao ?? '') === 'F';
                                            $emEdicao = (string)($vinculo->situacao ?? '') === 'E';
                                            $editalNome = $vinculo->editai->nome
                                                ?? $vinculo->projeto_bolsista->editai->nome
                                                ?? $vinculo->raic->editai->nome
                                                ?? $vinculo->workshop->editai->nome
                                                ?? '-';
                                            $tipoVinculo = (string)($vinculo->tipo ?? '');
                                            $temReferenciaAvaliacao = (
                                                $tipoVinculo === 'N'
                                                && (!empty($vinculo->projeto_bolsista_id) || !empty($vinculo->projeto_bolsista_legado_id) || !empty($vinculo->projeto_bolsista))
                                            ) || (
                                                in_array($tipoVinculo, ['V', 'Z'], true)
                                                && (!empty($vinculo->raic_id) || !empty($vinculo->raic))
                                            ) || (
                                                $tipoVinculo === 'W'
                                                && (!empty($vinculo->workshop_id) || !empty($vinculo->workshop))
                                            );
                                            $urlAvaliarTi = ($ehTI && !$deletado && $temReferenciaAvaliacao)
                                                ? ['controller' => 'Avaliadores', 'action' => 'avaliar', (int)$vinculo->id]
                                                : null;
                                            $inscricaoVinculoId = (int)($vinculo->projeto_bolsista_id ?: $vinculo->projeto_bolsista_legado_id ?: 0);
                                            $urlVincularInscricao = ($tipoVinculo === 'N' && $inscricaoVinculoId > 0)
                                                ? [
                                                    'controller' => 'Avaliadores',
                                                    'action' => 'vincularInscricao',
                                                    $inscricaoVinculoId,
                                                    '?' => ['retorno' => $this->request->getRequestTarget()],
                                                ]
                                                : null;
                                            $raicVinculoId = (int)($vinculo->raic_id ?? 0);
                                            $urlAgendarRaic = (in_array($tipoVinculo, ['V', 'Z'], true) && $raicVinculoId > 0)
                                                ? ['controller' => 'RaicNew', 'action' => 'agendar', $raicVinculoId]
                                                : null;
                                        ?>
                                        <tr class="<?= $deletado ? 'table-danger' : '' ?>">
                                            <td>
                                                <?php if ($urlAvaliarTi !== null): ?>
                                                    <?= $this->Html->link(
                                                        (string)$vinculo->id,
                                                        $urlAvaliarTi,
                                                        [
                                                            'class' => 'text-muted text-decoration-none',
                                                            'title' => 'Abrir avaliação',
                                                        ]
                                                    ) ?>
                                                <?php else: ?>
                                                    <?= h((string)$vinculo->id) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= h($referenciaTipo) ?></strong><br>
                                                <span class="text-muted small"><?= h($referenciaId) ?></span>
                                            </td>
                                            <td><?= h($editalNome) ?></td>
                                            <td><?= h($avaliadorNome) ?></td>
                                            <td><?= $deletado ? 'Não' : 'Sim' ?></td>
                                            <td><?= $avaliado ? 'Avaliado' : 'Não avaliado' ?></td>
                                            <td><?= h($orientadorNome) ?></td>
                                            <td><?= h($bolsistaNome) ?></td>
                                            <td><?= h(mb_substr((string)$unidadeAvaliador, 0, 4)) ?></td>
                                            <td><?= h(mb_substr((string)$unidadeReferencia, 0, 4)) ?></td>
                                            <td><?= h((string)($vinculo->ano ?? '-')) ?></td>
                                            <td class="text-end">
                                                <?php if ($urlVincularInscricao !== null): ?>
                                                    <?= $this->Html->link(
                                                        'Vincular',
                                                        $urlVincularInscricao,
                                                        [
                                                            'class' => 'btn btn-outline-primary btn-sm py-0 px-2',
                                                            'style' => 'font-size: 0.75rem;',
                                                        ]
                                                    ) ?>
                                                <?php elseif ($urlAgendarRaic !== null): ?>
                                                    <?= $this->Html->link(
                                                        'Agendar',
                                                        $urlAgendarRaic,
                                                        [
                                                            'class' => 'btn btn-outline-primary btn-sm py-0 px-2',
                                                            'style' => 'font-size: 0.75rem;',
                                                        ]
                                                    ) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 vinculos-avaliador-paginacao">
                            <?= $this->Paginator->prev('« Anterior', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= $this->Paginator->numbers([
                                'before' => '',
                                'after' => '',
                                'modulus' => 4,
                            ]) ?>
                            <?= $this->Paginator->next('Próxima »', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <span class="ms-2 text-muted">
                                <?= $this->Paginator->counter('Página {{page}} de {{pages}} | Total: {{count}}') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
