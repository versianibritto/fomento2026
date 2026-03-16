<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Solicitar substituição</h4>
                    <div class="text-muted">Inscrição #<?= (int)$inscricao->id ?></div>
                </div>
                <?= $this->Html->link('Voltar', ['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="small text-muted">Bolsista atual</div>
                    <div class="fw-semibold"><?= h((string)($bolsistaNome ?: '-')) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Orientador</div>
                    <div class="fw-semibold"><?= h((string)($orientadorNome ?: '-')) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Edital</div>
                    <div class="fw-semibold"><?= h((string)($edital->nome ?? '-')) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Programa</div>
                    <div class="fw-semibold"><?= h((string)($programaNome ?: '-')) ?></div>
                </div>
            </div>

            <?= $this->Form->create(null, ['class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('motivo_cancelamento_id', [
                        'label' => 'Motivo da substituição',
                        'options' => $motivos,
                        'empty' => 'Selecione',
                        'class' => 'form-select',
                        'required' => true,
                        'value' => $substituicao->motivo_cancelamento_id ?? null,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('justificativa_substituicao', [
                        'type' => 'textarea',
                        'label' => 'Justificativa da substituição',
                        'class' => 'form-control',
                        'rows' => 5,
                        'required' => true,
                        'value' => (string)($substituicao->justificativa ?? ''),
                        'placeholder' => 'Descreva o motivo da substituição com pelo menos 20 caracteres.',
                    ]) ?>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <?= $this->Html->link('Cancelar', ['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Form->button('Salvar e continuar', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
