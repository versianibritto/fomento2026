<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded">
                <h4 class="mb-2">Alterar fonte pagadora #<?= (int)$bolsista->id ?></h4>
                <div class="text-muted">Ação exclusiva da gestão.</div>
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

            <div class="alert alert-info">
                <?php
                    $fonteAtual = (string)($bolsista->tipo_bolsa ?? '');
                    $fonteNome = $fontes[$fonteAtual] ?? $fonteAtual;
                ?>
                <strong>Fonte atual:</strong> <?= h($fonteNome !== '' ? $fonteNome : '-') ?>
            </div>

            <?= $this->Form->create($bolsista, ['class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('tipo_bolsa', [
                        'label' => 'Nova fonte pagadora',
                        'type' => 'select',
                        'options' => $fontes ?? [],
                        'empty' => ' - Selecione - ',
                        'class' => 'form-select',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <?= $this->Html->link('Voltar', ['controller' => 'Padrao', 'action' => 'visualizar', (int)$bolsista->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Form->button('Salvar', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
