<?php
$this->assign('title', 'Excluir inscricao');
?>
<section class="mt-n3">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">Desistir do processo</h4>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger mb-3">
                    <?php foreach ($erros as $erro): ?>
                        <div><?= h($erro) ?></div>
                    <?php endforeach; ?>
                </div>
                <?= $this->Html->link('Voltar', ['action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-outline-secondary']) ?>
            <?php else: ?>
                <?= $this->Form->create($inscricao) ?>
                    <?= $this->Form->control('id', ['type' => 'hidden', 'value' => (int)$inscricao->id]) ?>
                    <div class="mb-3">
                        <?= $this->Form->control('motivo_cancelamento_id', [
                            'label' => 'Motivo do cancelamento:',
                            'class' => 'form-control',
                            'options' => $motivos,
                            'empty' => '- Selecione -',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="mb-3">
                        <?= $this->Form->control('justificativa_cancelamento', [
                            'label' => 'Justificativa da exclusao:',
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="d-flex gap-2">
                        <?= $this->Form->button('Solicitar exclusao', ['class' => 'btn btn-danger']) ?>
                        <?= $this->Html->link('Voltar', ['action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </div>
    </div>
</section>
