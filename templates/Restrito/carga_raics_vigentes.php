<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Carga de RAIC para Bolsistas Vigentes</h4>
                    <p class="text-muted mb-3">
                        Filtre por unidade e edital ativo para revisar a carga antes da criação dos registros em RAIC.
                    </p>

                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3 align-items-end mb-4',
                    ]) ?>
                        <?= $this->Form->hidden('filtrar', ['value' => '1']) ?>
                        <div class="col-md-3">
                            <?= $this->Form->control('raic_editai_id', [
                                'label' => 'RAIC',
                                'options' => $editaisRaicAbertos,
                                'empty' => 'Selecione',
                                'default' => $filtros['raic_editai_id'] ?? 0,
                                'class' => 'form-select',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('unidade_id', [
                                'label' => 'Unidade',
                                'options' => $unidades,
                                'empty' => 'Todas',
                                'default' => $filtros['unidade_id'] ?? 0,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('editai_id', [
                                'label' => 'Edital Ativo',
                                'options' => $editaisAtivos,
                                'empty' => 'Todos',
                                'default' => $filtros['editai_id'] ?? 0,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('status_lista', [
                                'label' => 'Listagem',
                                'options' => [
                                    'elegiveis' => 'Elegíveis',
                                    'inelegiveis' => 'Inelegíveis',
                                    'todos' => 'Todos',
                                ],
                                'default' => $filtros['status_lista'] ?? 'elegiveis',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-12 d-flex gap-2">
                            <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Restrito', 'action' => 'cargaRaicsVigentes'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if (!$buscaSolicitada): ?>
                        <div class="alert alert-info mb-0">
                            A tela abre sem resultados. Clique em Filtrar para carregar a prévia, com ou sem filtros preenchidos.
                        </div>
                    <?php else: ?>
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-2">Prévia da Carga</h5>
                                <p class="text-muted mb-3">
                                    A elegibilidade considera duplicidade por bolsista no edital RAIC selecionado.
                                </p>
                                <div class="d-flex flex-wrap gap-3">
                                    <span class="badge bg-success">Elegíveis: <?= (int)$totalElegiveis ?></span>
                                    <span class="badge bg-danger">Inelegíveis: <?= (int)$totalInelegiveis ?></span>
                                    <span class="badge bg-primary">Total: <?= (int)$totalCandidatos ?></span>
                                    <span class="badge bg-light text-dark border">
                                        Unidade:
                                        <?= !empty($filtros['unidade_id']) && !empty($unidades[$filtros['unidade_id']])
                                            ? h($unidades[$filtros['unidade_id']])
                                            : 'Todas' ?>
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        Edital:
                                        <?= !empty($filtros['editai_id']) && !empty($editaisAtivos[$filtros['editai_id']])
                                            ? h($editaisAtivos[$filtros['editai_id']])
                                            : 'Todos' ?>
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        RAIC:
                                        <?= !empty($filtros['raic_editai_id']) && !empty($editaisRaicAbertos[$filtros['raic_editai_id']])
                                            ? h($editaisRaicAbertos[$filtros['raic_editai_id']])
                                            : 'Nao selecionado' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    <?php if (empty($candidatos) || $totalCandidatos === 0): ?>
                        <div class="alert alert-warning mb-0">
                            Nenhum registro foi localizado com os filtros informados.
                        </div>
                    <?php else: ?>
                        <?php if ($totalCandidatos > count($candidatos)): ?>
                            <div class="alert alert-info">
                                A prévia abaixo exibe os primeiros <?= (int)count($candidatos) ?> registros de um total de <?= (int)$totalCandidatos ?> elegíveis.
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Inscrição</th>
                                        <th>Edital</th>
                                        <th>Unidade</th>
                                        <th>Bolsista</th>
                                        <th>Orientador</th>
                                        <th>Projeto</th>
                                        <th>Subprojeto</th>
                                        <th>Tipo Bolsa</th>
                                        <th>Situação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidatos as $item): ?>
                                        <tr>
                                            <td>
                                                <?= $this->Html->link(
                                                    '#' . (int)$item['id'],
                                                    ['controller' => 'Padrao', 'action' => 'visualizar', (int)$item['id']],
                                                    ['target' => '_blank']
                                                ) ?>
                                            </td>
                                            <td><?= h((string)($item['edital_nome'] ?? 'Nao informado')) ?></td>
                                            <td><?= h((string)($item['unidade_sigla'] ?? 'Nao informado')) ?></td>
                                            <td><?= h((string)($item['bolsista_nome'] ?? 'Nao informado')) ?></td>
                                            <td><?= h((string)($item['orientador_nome'] ?? 'Nao informado')) ?></td>
                                            <td><?= h((string)($item['projeto_titulo'] ?? 'Nao informado')) ?></td>
                                            <td><?= h((string)($item['sp_titulo'] ?? 'Nao informado')) ?></td>
                                            <td><?= h((string)($item['tipo_bolsa'] ?? 'Nao informado')) ?></td>
                                            <td>
                                                <?php if (($item['situacao_carga'] ?? '') === 'inelegivel'): ?>
                                                    <span class="badge bg-danger">Inelegível</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Elegível</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?= $this->Form->create(null, ['type' => 'post', 'id' => 'form-executar-carga-raic']) ?>
                            <?= $this->Form->hidden('acao', ['value' => 'executar']) ?>
                            <?= $this->Form->hidden('unidade_id', ['value' => (int)($filtros['unidade_id'] ?? 0)]) ?>
                            <?= $this->Form->hidden('editai_id', ['value' => (int)($filtros['editai_id'] ?? 0)]) ?>
                            <?= $this->Form->hidden('raic_editai_id', ['value' => (int)($filtros['raic_editai_id'] ?? 0)]) ?>
                            <?= $this->Form->hidden('status_lista', ['value' => (string)($filtros['status_lista'] ?? 'elegiveis')]) ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?= $this->Form->button('Executar Carga', [
                                    'class' => 'btn btn-danger',
                                    'onclick' => "return confirm('Confirma a criacao de RAIC para todos os bolsistas listados na previa?');",
                                ]) ?>
                                <?= $this->Html->link(
                                    'Voltar',
                                    ['controller' => 'Restrito', 'action' => 'index'],
                                    ['class' => 'btn btn-outline-secondary']
                                ) ?>
                            </div>
                        <?= $this->Form->end() ?>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
