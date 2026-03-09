<?php
$titulo = $isEdicao ? 'Editar Vitrine' : 'Cadastrar Vitrine';
$textoBotao = $isEdicao ? 'Salvar alterações' : 'Cadastrar vitrine';
?>
<section class="mt-n3">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0"><?= h($titulo) ?></h2>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Nova vitrine', ['controller' => 'Restrito', 'action' => 'vitrines'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
            <hr>
            <div class="alert alert-info py-2">
                Os campos <strong>Nome</strong> e <strong>Observações</strong> aceitam HTML e serão gravados como informado.
            </div>

            <?= $this->Form->create($vitrine, ['type' => 'file', 'class' => 'row g-3']) ?>
                <?= $this->Form->hidden('acao', ['value' => 'salvar']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('divulgacao', [
                        'label' => 'Data de divulgação',
                        'type' => 'datetime-local',
                        'class' => 'form-control',
                        'value' => !empty($vitrine->divulgacao) ? $vitrine->divulgacao->format('Y-m-d\TH:i') : '',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('anexo_edital', ['label' => 'Anexo edital', 'type' => 'file', 'class' => 'form-control']) ?>
                    <?php if (!empty($vitrine->anexo_edital)): ?>
                        <div class="small mt-1">Atual: <a href="/uploads/editais/<?= h($vitrine->anexo_edital) ?>" target="_blank"><?= h($vitrine->anexo_edital) ?></a></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('anexo_resultado', ['label' => 'Anexo resultado', 'type' => 'file', 'class' => 'form-control']) ?>
                    <?php if (!empty($vitrine->anexo_resultado)): ?>
                        <div class="small mt-1">Atual: <a href="/uploads/editais/<?= h($vitrine->anexo_resultado) ?>" target="_blank"><?= h($vitrine->anexo_resultado) ?></a></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('anexo_resultado_recurso', ['label' => 'Anexo resultado recurso', 'type' => 'file', 'class' => 'form-control']) ?>
                    <?php if (!empty($vitrine->anexo_resultado_recurso)): ?>
                        <div class="small mt-1">Atual: <a href="/uploads/editais/<?= h($vitrine->anexo_resultado_recurso) ?>" target="_blank"><?= h($vitrine->anexo_resultado_recurso) ?></a></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('anexo_modelo_relatorio', ['label' => 'Anexo modelo relatório', 'type' => 'file', 'class' => 'form-control']) ?>
                    <?php if (!empty($vitrine->anexo_modelo_relatorio)): ?>
                        <div class="small mt-1">Atual: <a href="/uploads/editais/<?= h($vitrine->anexo_modelo_relatorio) ?>" target="_blank"><?= h($vitrine->anexo_modelo_relatorio) ?></a></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('anexo_modelo_consentimento', ['label' => 'Anexo modelo consentimento', 'type' => 'file', 'class' => 'form-control']) ?>
                    <?php if (!empty($vitrine->anexo_modelo_consentimento)): ?>
                        <div class="small mt-1">Atual: <a href="/uploads/editais/<?= h($vitrine->anexo_modelo_consentimento) ?>" target="_blank"><?= h($vitrine->anexo_modelo_consentimento) ?></a></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('nome', [
                        'label' => 'Nome (aceita HTML)',
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 4,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('obs', [
                        'label' => 'Observações (aceita HTML)',
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 4,
                    ]) ?>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <?= $this->Form->button($textoBotao, ['class' => 'btn btn-success']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="card card-secondary card-outline">
        <div class="card-body">
            <h4 class="mb-3">Vitrines cadastradas</h4>
            <?php if (empty($vitrines) || $vitrines->count() === 0): ?>
                <div class="alert alert-warning mb-0">Nenhuma vitrine cadastrada.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nome</th>
                                <th>Divulgação</th>
                                <th style="width: 190px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vitrines as $item): ?>
                                <tr>
                                    <td><?= (int)$item->id ?></td>
                                    <td><?= !empty($item->nome) ? $this->Text->truncate(strip_tags((string)$item->nome), 100) : '-' ?></td>
                                    <td><?= !empty($item->divulgacao) ? h($item->divulgacao->format('d/m/Y H:i')) : '-' ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <?= $this->Html->link('Editar', ['controller' => 'Restrito', 'action' => 'vitrines', (int)$item->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                            <?= $this->Form->postLink(
                                                'Excluir',
                                                ['controller' => 'Restrito', 'action' => 'vitrines', (int)$item->id],
                                                [
                                                    'class' => 'btn btn-outline-danger btn-sm',
                                                    'confirm' => 'Confirma a exclusão da vitrine #' . (int)$item->id . '?',
                                                    'data' => ['acao' => 'deletar', 'id' => (int)$item->id],
                                                ]
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
