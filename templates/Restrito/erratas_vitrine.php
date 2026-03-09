<section class="mt-n3">
    <div class="card card-secondary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h4 class="mb-0">Cadastrar Erratas (PDF) por Vitrine</h4>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Lista de vitrines', ['controller' => 'Restrito', 'action' => 'vitrinesLista'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Html->link('Nova vitrine', ['controller' => 'Restrito', 'action' => 'vitrines'], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>

            <div class="alert alert-warning py-2">
                Novas erratas serão vinculadas apenas à <strong>vitrine</strong> (`vitrine_id`). O campo `editai_id` não será usado em novos cadastros.
            </div>

            <?= $this->Form->create(null, ['type' => 'file', 'class' => 'row g-3']) ?>
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
        </div>
    </div>
</section>
