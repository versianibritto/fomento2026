<section class="mt-n3">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0">Alterar quesito</h2>
                <?= $this->Html->link('Voltar', $this->request->referer(), ['class' => 'btn btn-outline-secondary']) ?>
            </div>
            <hr class="my-4">

            <?= $this->Form->create($quesito, ['class' => 'row g-3']) ?>
                <div class="col-12">
                    <div class="card bg-light border-0 p-3">
                        <div class="row g-3">
                            <div class="col-12">
                                <?= $this->Form->control('questao', [
                                    'label' => 'Questão',
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'rows' => 4,
                                    'maxlength' => 255,
                                    'required' => true
                                ]) ?>
                            </div>
                            <div class="col-12">
                                <?= $this->Form->control('prametros', [
                                    'label' => 'Parâmetros',
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'rows' => 4,
                                    'required' => true
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('limite_min', [
                                    'label' => 'Limite mínimo',
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'class' => 'form-control',
                                    'required' => true
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('limite_max', [
                                    'label' => 'Limite máximo',
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'class' => 'form-control',
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <?= $this->Form->button('Salvar alterações', ['class' => 'btn btn-success']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</section>
