<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('renovacoes_steps', [
            'edital' => $edital,
            'current' => 'finalizarRenovacao',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Finalizar inscrição</h3>
                <div class="fw-semibold">Inscrição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Esta etapa finaliza sua inscrição.</div>
            </div>

            <div class="alert alert-warning border border-warning-subtle mb-4 sumula-alerta">
                <div class="fw-semibold mb-1">Atenção</div>
                <div>
                    O termo devidamente assinado, conforme edital, deverá ser anexado para finalizar.
                    Caso não tenha o arquivo do termo, é possível baixar clicando no botão abaixo.
                </div>
                <div class="mt-3">
                    <?= $this->Html->link('Baixar termo', ['controller' => 'Renovacoes', 'action' => 'baixarTermoRenovacao', $edital->id, $inscricao->id], ['class' => 'btn btn-outline-primary js-loading-link']) ?>
                </div>
            </div>

            <?= $this->Form->create(null, ['type' => 'file', 'class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('termo_assinado', [
                        'label' => 'Termo assinado (PDF)',
                        'type' => 'file',
                        'class' => 'form-control',
                    ]) ?>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <?= $this->Form->button('Finalizar', ['class' => 'btn btn-success']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
<style>
.sumula-alerta {
    border: 1px solid #ffe69c;
    background: #fff8db;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, .15);
}
</style>
