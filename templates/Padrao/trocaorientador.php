<?php
/**
 * @var \App\View\AppView $this
 * @var int $id
 */
print $this->Form->create(); ?>
    <div class="text-center p-5">
        <fieldset class="form-certificado">
            <h3>Troca de orientador</h3>
            <div class="text-left">
                <?= $this->Form->control('id', ['type' => 'hidden', 'value' => $id]) ?>
                <?= $this->Form->control('coorientador', [
                    'label' => 'Id do novo orientador:',
                    'type' => 'number',
                    'class' => 'form-control mb-3',
                ]) ?>
                <?= $this->Form->control('justificativa_cancelamento', [
                    'label' => 'Justificativa da alteracao:',
                    'type' => 'textarea',
                    'class' => 'form-control mb-3',
                    'required' => true,
                ]) ?>
                <?= $this->Form->button('Gravar', ['class' => 'btn btn-danger']) ?>
                <?= $this->Html->link('Voltar', $this->request->referer(), ['class' => 'btn btn-info pull-right']) ?>
            </div>
        </fieldset>
    </div>
<?php
print $this->Form->end();
?>
