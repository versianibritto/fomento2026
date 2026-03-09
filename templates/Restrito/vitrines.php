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
                <div class="col-md-3">
                    <?= $this->Form->control('inicio', [
                        'label' => 'Data de início',
                        'type' => 'datetime-local',
                        'class' => 'form-control',
                        'value' => !empty($vitrine->inicio) ? $vitrine->inicio->format('Y-m-d\TH:i') : '',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('fim', [
                        'label' => 'Data de fim',
                        'type' => 'datetime-local',
                        'class' => 'form-control',
                        'value' => !empty($vitrine->fim) ? $vitrine->fim->format('Y-m-d\TH:i') : '',
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
            <h4 class="mb-3">Cadastrar Erratas (PDF) por Vitrine</h4>
            <div class="alert alert-warning py-2">
                Novas erratas serão vinculadas apenas à <strong>vitrine</strong> (`vitrine_id`). O campo `editai_id` não será usado em novos cadastros.
            </div>
            <?= $this->Form->create(null, ['type' => 'file', 'url' => ['controller' => 'Restrito', 'action' => 'erratasVitrine'], 'class' => 'row g-3']) ?>
                <?= $this->Form->hidden('acao', ['value' => 'cadastrar_erratas_vitrine']) ?>
                <div class="col-md-5">
                    <?= $this->Form->control('vitrine_id', [
                        'label' => 'Vitrine',
                        'type' => 'select',
                        'options' => $vitrinesAtivas ?? [],
                        'empty' => 'Selecione',
                        'default' => !empty($preselectedErrataVitrineId) ? (int)$preselectedErrataVitrineId : null,
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="erratas-arquivos">Arquivos PDF (múltiplos)</label>
                    <input id="erratas-arquivos" type="file" name="erratas_arquivos[]" accept="application/pdf,.pdf" multiple class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <?= $this->Form->button('Cadastrar PDFs', ['class' => 'btn btn-primary w-100']) ?>
                </div>
            <?= $this->Form->end() ?>

            <hr>
            <h5 class="mb-3">Erratas recentes</h5>
            <?php if (empty($erratasRecentes) || $erratasRecentes->count() === 0): ?>
                <div class="alert alert-light border mb-0">Nenhuma errata cadastrada por vitrine.</div>
            <?php else: ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Vitrine</th>
                                <th>Arquivo</th>
                                <th style="width: 160px;">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($erratasRecentes as $errata): ?>
                                <tr>
                                    <td><?= (int)$errata->id ?></td>
                                    <td><?= !empty($errata->vitrine) ? ('#' . (int)$errata->vitrine->id . ' - ' . $this->Text->truncate(strip_tags((string)$errata->vitrine->nome), 70)) : '-' ?></td>
                                    <td>
                                        <?php if (!empty($errata->arquivo)): ?>
                                            <a href="/uploads/editais/<?= h($errata->arquivo) ?>" target="_blank"><?= h($errata->arquivo) ?></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($errata->created) ? h($errata->created->format('d/m/Y H:i')) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h4 class="mb-3">Listagem de Vitrines</h4>
            <div class="d-flex flex-wrap gap-2">
                <?= $this->Html->link('Abrir lista paginada', ['controller' => 'Restrito', 'action' => 'vitrinesLista'], ['class' => 'btn btn-outline-dark']) ?>
                <?= $this->Html->link('Editar/Excluir/Reativar vitrines', ['controller' => 'Restrito', 'action' => 'vitrinesLista'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>
</section>
