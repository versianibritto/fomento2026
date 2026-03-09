<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded">
                <h4 class="mb-2">Solicitar cancelamento da <?= strtoupper((string)($origemAtual ?? '')) === 'R' ? 'renovacao' : 'inscricao' ?> #<?= (int)$inscricao->id ?></h4>
                <div class="text-muted">Regra desta versao: cancelamento sem prazo, permitido somente para inscrições na fase 11.</div>
            </div>

            <div class="alert alert-warning">
                <strong>Atenção:</strong> use a justificativa para fundamentar o cancelamento.
                Se não anexar o relatório final neste momento, marque a opção correspondente.
            </div>

            <?= $this->Form->create($inscricao, ['type' => 'file', 'class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('motivo_cancelamento_id', [
                        'label' => 'Motivo do cancelamento',
                        'type' => 'select',
                        'options' => $motivos ?? [],
                        'empty' => ' - Selecione - ',
                        'class' => 'form-select',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12">
                    <?= $this->Form->control('justificativa_cancelamento', [
                        'label' => 'Justificativa do cancelamento',
                        'type' => 'textarea',
                        'rows' => 4,
                        'class' => 'form-control',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12">
                    <?= $this->Form->control('nao_enviar', [
                        'label' => 'não farei upload do relatorio final neste formulario',
                        'type' => 'checkbox',
                    ]) ?>
                </div>

                <div class="col-md-6">
                    <?= $this->Form->control('anexos[14]', [
                        'label' => 'Relatorio final (PDF)',
                        'type' => 'file',
                        'class' => 'form-control',
                    ]) ?>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <?= $this->Html->link('Voltar', ['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Form->button('Solicitar cancelamento', ['class' => 'btn btn-danger']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
