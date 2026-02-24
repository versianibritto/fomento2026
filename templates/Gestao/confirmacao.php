<?= $this->Form->create(); ?>
<div class="text-center p-5">
    <fieldset class="form-certificado">
        <h3>
            <?= $modo === 'S' ? 'Confirma Substituição' : 'Confirma Cancelamento' ?>
            :<?= h((string)$bolsista->id) ?>
        </h3>

        <div class="text-left">
            <div class="row">
                <div class="col-md-12">
                    <?php
                        if ($modo === 'S') {
                            echo $this->Form->control('data_inicio', [
                                'label' => 'Data de Implementação da bolsa',
                                'type' => 'date',
                                'class' => 'form-control datepicker',
                                'required' => true,
                            ]);
                        } else {
                            echo $this->Form->control('data_fim', [
                                'label' => 'Data de Finalização da bolsa',
                                'type' => 'date',
                                'class' => 'form-control datepicker',
                                'required' => true,
                            ]);
                        }
                    ?>
                </div>
            </div>

            <p></p>
            <?=
                $this->Form->button(
                    $modo === 'S' ? ' Confirmar substituição ' : ' Confirmar cancelamento ',
                    ['class' => 'btn btn-primary']
                )
            ?>
            <?= $this->Html->link('Voltar', $this->request->referer(), ['class' => 'btn btn-danger pull-right']); ?>
        </div>
    </fieldset>
</div>
<?= $this->Form->end(); ?>
