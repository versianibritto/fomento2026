<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-1">Upload de Arquivos</h4>
                            <p class="text-muted mb-0">Envio para pastas internas de `webroot/uploads`.</p>
                        </div>
                        <?= $this->Html->link(
                            'Voltar',
                            ['controller' => 'Restrito', 'action' => 'index'],
                            ['class' => 'btn btn-outline-secondary btn-sm']
                        ) ?>
                    </div>

                    <?= $this->Form->create(null, ['type' => 'file']) ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <?= $this->Form->control('arquivo', [
                                'type' => 'file',
                                'label' => 'Arquivo',
                                'required' => true,
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $this->Form->control('pasta', [
                                'label' => 'Pasta de destino',
                                'empty' => 'Selecione',
                                'options' => $pastasUpload ?? [],
                                'required' => true,
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $this->Form->control('nome_arquivo', [
                                'label' => 'Nome do arquivo (opcional)',
                                'class' => 'form-control',
                                'placeholder' => 'exemplo.pdf',
                                'help' => 'Se vazio, o sistema gera um nome unico.',
                            ]) ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <?= $this->Form->button('Enviar arquivo', ['class' => 'btn btn-success']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
