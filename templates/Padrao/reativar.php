<?php
if (sizeof($erros) > 0) {
    print '<h3 class="text-danger">Ação restrita</h3><ul>';
    foreach ($erros as $err) {
        print '<li class="text-danger">' . h($err) . '</li>';
    }
    print '</ul>';
} else {
    print $this->Form->create($inscricao, ['type' => 'file']); ?>
    <div class="text p-5">
        <fieldset class="form-certificado">
            <h3>Desfazer a substituição do bolsista</h3>
            <div class="text-left">
                <?= $this->Form->control('id', ['type' => 'hidden', 'value' => $inscricao->id]) ?>
                <?= $this->Form->control('motivo_cancelamento_id', [
                    'label' => 'Motivo da alteração:',
                    'class' => 'form-control mb-2',
                    'empty' => '- Selecione -',
                    'options' => $motivos,
                    'required' => true,
                ]) ?>
                <?= $this->Form->control('justificativa_cancelamento', [
                    'label' => 'Justificativa da alteração:',
                    'type' => 'textarea',
                    'class' => 'form-control mb-3',
                    'required' => true,
                ]) ?>
                <p></p>
                <?= $this->Form->button('Gravar', ['class' => 'btn btn-danger']) ?>
                <?= $this->Html->link('Voltar', ['action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-info pull-right']) ?>
            </div>
        </fieldset>
    </div>
    <?php
    print $this->Form->end();
}
?>
