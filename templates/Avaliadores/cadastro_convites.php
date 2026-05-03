<?php
/**
 * @var \App\View\AppView $this
 * @var array<int|string, string> $editais
 * @var array{
 *     editais?: array<int, int>,
 *     cpfs?: string
 * } $dados
 * @var array{
 *     processado?: bool,
 *     confirmado?: bool,
 *     elegiveis?: array<int, array<string, mixed>>,
 *     inelegiveis?: array<int, array<string, mixed>>,
 *     totalInformados?: int,
 *     totalElegiveis?: int,
 *     totalInelegiveis?: int
 * } $resultado
 */
?>
<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Cadastro Massivo de Convites para Avaliadores</h4>
                    <p class="text-muted mb-3">
                        Selecione um ou mais editais e informe os CPFs separados por vírgula, espaço, quebra de linha ou ponto e vírgula.
                        Os convites serão gravados para o ano corrente.
                    </p>

                    <?= $this->Form->create(null, ['class' => 'row g-3']) ?>
                        <?= $this->Form->hidden('acao', ['value' => 'analisar']) ?>
                        <div class="col-12">
                            <label class="form-label d-block">Editais</label>
                            <div class="row">
                                <?php foreach ($editais as $id => $nome): ?>
                                    <div class="col-md-6 col-lg-4 mb-2">
                                        <div class="form-check">
                                            <?= $this->Form->checkbox("editais.$id", [
                                                'value' => $id,
                                                'checked' => in_array((int)$id, $dados['editais'] ?? [], true),
                                                'hiddenField' => false,
                                                'class' => 'form-check-input',
                                                'id' => 'convites-editais-' . $id,
                                            ]) ?>
                                            <label class="form-check-label" for="convites-editais-<?= (int)$id ?>">
                                                <?= h((string)$nome) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">
                                Os IDs selecionados serão salvos em ordem crescente na coluna <code>editais</code>, separados por vírgula.
                            </small>
                        </div>
                        <div class="col-12">
                            <?= $this->Form->control('cpfs', [
                                'label' => 'CPFs',
                                'type' => 'textarea',
                                'rows' => 5,
                                'value' => $dados['cpfs'] ?? '',
                                'class' => 'form-control',
                                'placeholder' => 'Ex.: 11111111111, 22222222222, 33333333333',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?= $this->Form->button('Analisar CPFs', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Avaliadores', 'action' => 'cadastroConvites'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if (!empty($resultado['processado'])): ?>
                        <hr class="my-4">
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <span class="badge bg-primary">CPFs informados: <?= (int)$resultado['totalInformados'] ?></span>
                            <span class="badge bg-success">Elegíveis: <?= (int)$resultado['totalElegiveis'] ?></span>
                            <span class="badge bg-danger">Inelegíveis: <?= (int)$resultado['totalInelegiveis'] ?></span>
                        </div>

                        <?php if (!empty($resultado['elegiveis'])): ?>
                            <div class="card border-success mb-4">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-3">Elegíveis para convite</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>CPF</th>
                                                    <th>Nome</th>
                                                    <th>Ano</th>
                                                    <th>Editais</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($resultado['elegiveis'] as $item): ?>
                                                    <tr>
                                                        <td><?= h((string)$item['cpf']) ?></td>
                                                        <td><?= h((string)$item['nome']) ?></td>
                                                        <td><?= h((string)$item['ano']) ?></td>
                                                        <td><?= h((string)$item['editais']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($resultado['inelegiveis'])): ?>
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h5 class="card-title text-danger mb-3">Inelegibilidades</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>CPF</th>
                                                    <th>Nome</th>
                                                    <th>Ano</th>
                                                    <th>Editais</th>
                                                    <th>Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($resultado['inelegiveis'] as $item): ?>
                                                    <tr>
                                                        <td><?= h((string)$item['cpf']) ?></td>
                                                        <td><?= h((string)($item['nome'] ?? 'Não localizado')) ?></td>
                                                        <td><?= h((string)($item['ano'] ?? date('Y'))) ?></td>
                                                        <td><?= h((string)($item['editais'] ?? '-')) ?></td>
                                                        <td><?= h((string)$item['motivo']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$resultado['confirmado'] && !empty($resultado['elegiveis'])): ?>
                            <div class="alert alert-warning mt-4">
                                Ao confirmar, serão criados convites apenas para os CPFs localizados e não repetidos.
                            </div>
                            <?= $this->Form->create(null, ['class' => 'd-flex flex-wrap gap-2']) ?>
                                <?= $this->Form->hidden('acao', ['value' => 'confirmar']) ?>
                                <?php foreach (($dados['editais'] ?? []) as $id): ?>
                                    <?= $this->Form->hidden('editais[]', ['value' => (int)$id]) ?>
                                <?php endforeach; ?>
                                <?= $this->Form->hidden('cpfs', ['value' => (string)($dados['cpfs'] ?? '')]) ?>
                                <?= $this->Form->button('Confirmar cadastro dos convites', [
                                    'class' => 'btn btn-danger',
                                    'onclick' => "return confirm('Confirma o cadastro massivo dos convites elegíveis?');",
                                ]) ?>
                            <?= $this->Form->end() ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
