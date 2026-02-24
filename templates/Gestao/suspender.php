<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded">
                <h4 class="mb-2">Suspender bolsa #<?= (int)$bolsista->id ?></h4>
                <div class="text-muted">Apenas a gestão pode suspender bolsas.</div>
            </div>

            <?php if (!empty($erros)) : ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erros as $e) : ?>
                            <li><?= h($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?= $this->Form->create($bolsista, ['class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('licenca', [
                        'label' => 'Tipo de licença',
                        'type' => 'select',
                        'options' => $motivos ?? [],
                        'empty' => ' - Selecione - ',
                        'class' => 'form-select',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12">
                    <?= $this->Form->control('justificativa_cancelamento', [
                        'label' => 'Justificativa da suspensão',
                        'type' => 'textarea',
                        'rows' => 4,
                        'class' => 'form-control',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <?= $this->Html->link('Voltar', ['controller' => 'Projetos', 'action' => 'detalhesubprojeto', (int)$bolsista->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Form->button('Suspender', ['class' => 'btn btn-danger']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
