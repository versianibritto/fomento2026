<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Carga por Avaliador</h4>
                    <p class="text-muted mb-3">
                        Resumo consolidado por usuário no ano informado, com uma única linha por avaliador.
                    </p>

                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3 align-items-end mb-4',
                    ]) ?>
                        <div class="col-md-3">
                            <?= $this->Form->control('ano', [
                                'label' => 'Ano',
                                'options' => $anosOptions,
                                'empty' => 'Selecione',
                                'default' => $filtros['ano'] ?? '',
                                'required' => true,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('ordenacao', [
                                'label' => 'Ordenação',
                                'options' => $ordenacaoOptions,
                                'default' => $filtros['ordenacao'] ?? 'nome',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('total_minimo', [
                                'label' => 'Total mínimo',
                                'type' => 'number',
                                'min' => 0,
                                'value' => $filtros['total_minimo'] ?? '',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('total_maximo', [
                                'label' => 'Total máximo',
                                'type' => 'number',
                                'min' => 0,
                                'value' => $filtros['total_maximo'] ?? '',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Listas', 'action' => 'dashcountavaliadores'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if (!$buscaSolicitada): ?>
                        <div class="alert alert-info mb-0">
                            Informe o ano para carregar a tabela.
                        </div>
                    <?php elseif ($registros === null || count($registros) === 0): ?>
                        <div class="alert alert-warning mb-0">
                            Nenhum registro localizado para o ano informado.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th>Ano</th>
                                        <th class="text-end">Finalizadas</th>
                                        <th class="text-end">Aguardando</th>
                                        <th class="text-end">Deletadas</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros as $registro): ?>
                                        <tr>
                                            <td><?= h((string)($registro['usuario_nome'] ?? 'Não informado')) ?></td>
                                            <td><?= h((string)($registro['ano'] ?? '')) ?></td>
                                            <td class="text-end"><?= h((string)($registro['finalizadas'] ?? 0)) ?></td>
                                            <td class="text-end"><?= h((string)($registro['aguardando'] ?? 0)) ?></td>
                                            <td class="text-end"><?= h((string)($registro['deletadas'] ?? 0)) ?></td>
                                            <td class="text-end"><?= h((string)($registro['total'] ?? 0)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-muted small mt-3">
                            Exibindo <?= count($registros) ?> avaliador(es).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
