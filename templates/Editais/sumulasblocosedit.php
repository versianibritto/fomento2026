<section class="mt-n3">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0">Editar bloco de súmula</h2>
                <?= $this->Html->link('Voltar', $this->request->referer(), ['class' => 'btn btn-outline-secondary']) ?>
            </div>
            <hr class="my-4">

            <?= $this->Form->create($bloco, ['class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('nome', [
                        'label' => 'Nome do bloco',
                        'class' => 'form-control',
                        'maxlength' => 45,
                        'required' => true,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->button('Salvar', ['class' => 'btn btn-success']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</section>
