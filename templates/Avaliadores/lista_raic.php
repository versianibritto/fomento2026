<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Lista de Avaliadores RAIC</h4>
                    <p class="text-muted mb-3">
                        Listagem dos avaliadores RAIC por ano e unidade, respeitando o escopo de acesso da unidade.
                    </p>

                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3 align-items-end mb-4',
                    ]) ?>
                        <div class="col-md-3">
                            <?= $this->Form->control('ano', [
                                'label' => 'Ano',
                                'options' => $anosOptions,
                                'default' => $filtros['ano'] ?? null,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $this->Form->control('unidade_id', [
                                'label' => 'Unidade',
                                'options' => $unidades,
                                'empty' => $this->request->getAttribute('identity')['yoda'] ? 'Todas' : 'Selecione',
                                'default' => $filtros['unidade_id'] ?? 0,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('nome', [
                                'label' => 'Nome do avaliador',
                                'value' => $filtros['nome'] ?? '',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Avaliadores', 'action' => 'listaRaic'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if ($avaliadores->count() === 0): ?>
                        <div class="alert alert-info mb-0">
                            Nenhum avaliador RAIC localizado com os filtros informados.
                        </div>
                    <?php else: ?>
                        <?php $this->Paginator->options(['url' => $this->request->getQueryParams()]); ?>
                        <div class="mb-3">
                            <?= $this->Html->link(
                                'Exportar CSV',
                                [
                                    'controller' => 'Avaliadores',
                                    'action' => 'listaRaic',
                                    '?' => $this->request->getQueryParams() + ['exportar' => 1],
                                ],
                                ['class' => 'btn btn-success btn-sm']
                            ) ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Nome do avaliador</th>
                                        <th>CPF</th>
                                        <th>Grande área</th>
                                        <th>Área</th>
                                        <th>Ano</th>
                                        <th>Edital</th>
                                        <th>Unidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($avaliadores as $avaliador): ?>
                                        <tr>
                                            <td><?= h((string)($avaliador->usuario->nome ?? 'Não informado')) ?></td>
                                            <td><?= h((string)($avaliador->usuario->cpf ?? 'Não informado')) ?></td>
                                            <td><?= h((string)($avaliador->grandes_area->nome ?? 'Não informada')) ?></td>
                                            <td><?= h((string)($avaliador->area->nome ?? 'Não informada')) ?></td>
                                            <td><?= h((string)($avaliador->ano_convite ?? '-')) ?></td>
                                            <td><?= h((string)($avaliador->editai->nome ?? 'Não informado')) ?></td>
                                            <td><?= h((string)($avaliador->unidade->sigla ?? 'Não informada')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
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
