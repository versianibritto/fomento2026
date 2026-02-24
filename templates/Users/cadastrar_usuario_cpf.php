<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-3">Pré-cadastro de usuário</h4>
            <p class="text-muted mb-3">Informe o CPF para validar se o usuário já existe na base antes de abrir o cadastro.</p>

            <?= $this->Form->create(null, ['class' => 'row g-3']) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('cpf', [
                        'label' => 'CPF',
                        'class' => 'form-control',
                        'required' => true,
                        'maxlength' => 25,
                    ]) ?>
                </div>
                <?php if (empty($isTiForm)) : ?>
                    <div class="col-md-4">
                        <?= $this->Form->control('papel', [
                            'label' => 'Papel',
                            'class' => 'form-select',
                            'options' => [
                                'B' => 'Bolsista',
                                'C' => 'Coorientador',
                            ],
                            'value' => $papelForm ?? 'B',
                        ]) ?>
                    </div>
                <?php else : ?>
                    <?= $this->Form->hidden('papel', ['value' => 'B']) ?>
                <?php endif; ?>

                <?= $this->Form->hidden('inscricao_id', ['value' => $inscricaoForm ?? 0]) ?>
                <?= $this->Form->hidden('edital_id', ['value' => $editalForm ?? 0]) ?>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Validar e continuar</button>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
