<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0">Deletar RAIC #<?= (int)$raic->id ?></h4>
        <?= $this->Html->link('Voltar', ['controller' => 'RaicNew', 'action' => 'ver', $raic->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Bolsista</strong><br>
                    <?= h((string)($raic->usuario->nome ?? 'Não informado')) ?>
                </div>
                <div class="col-md-6">
                    <strong>Orientador</strong><br>
                    <?= h((string)($raic->orientadore->nome ?? 'Não informado')) ?>
                </div>
                <div class="col-md-4">
                    <strong>Unidade</strong><br>
                    <?= h((string)($raic->unidade->sigla ?? 'Não informada')) ?>
                </div>
                <div class="col-md-8">
                    <strong>Edital</strong><br>
                    <?= h((string)($raic->editai->nome ?? ('#' . (int)$raic->editai_id))) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="alert alert-warning">
                A exclusão lógica da RAIC é irreversível pela interface. Informe a justificativa para registrar a ação.
            </div>

            <?= $this->Form->create(null) ?>
                <?= $this->Form->control('justificativa_cancelamento', [
                    'label' => 'Justificativa da exclusão',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 5,
                    'class' => 'form-control',
                ]) ?>

                <div class="mt-4 d-flex gap-2">
                    <?= $this->Form->button('Confirmar exclusão', ['class' => 'btn btn-danger']) ?>
                    <?= $this->Html->link('Cancelar', ['controller' => 'RaicNew', 'action' => 'ver', $raic->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
