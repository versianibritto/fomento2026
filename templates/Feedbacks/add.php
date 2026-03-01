<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Novo Feedback</h4>
        <?= $this->Html->link('Voltar', $this->request->referer(), ['class'=>'btn btn-outline-secondary btn-sm']) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?=$this->Form->create($feedback, ['class' => 'row g-3'])?>
                <div class="col-md-6">
                    <?=$this->Form->control('tipo',[
                        'label'=>'Tipo de Feedback',
                        'class'=>'form-select',
                        'empty'=>'- Selecione -',
                        'options' => ['C'=>'Crítica', 'S'=>'Sugestão', 'M'=>'Comentário', 'R'=>'Reclamação'],
                        'required'=>true
                    ])?>
                </div>
                <?=$this->Form->hidden('destinatario', ['value' => 'A'])?>
                <div class="col-12">
                    <?=$this->Form->control('titulo', [
                        'label' => 'Título (máx. 150 caracteres)',
                        'type' => 'text',
                        'class'=>'form-control',
                        'required' => true,
                        'maxlength'=>150
                    ])?>
                </div>
                <div class="col-12">
                    <?=$this->Form->control('texto', [
                        'label' => 'Feedback',
                        'type' => 'textarea',
                        'class'=>'form-control',
                        'required' => true,
                        'rows' => 5
                    ])?>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <?=$this->Form->button('Gravar', ['class'=>'btn btn-success'])?>
                </div>
            <?=$this->Form->end()?>
        </div>
    </div>
</div>
