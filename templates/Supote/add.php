<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Novo chamado</h4>
        <a href="/supote" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?=$this->Form->create($chamado, ['class' => 'row g-3', 'type' => 'file'])?>
                <?php if (!$isYoda): ?>
                    <div class="col-md-6">
                        <?=$this->Form->control('categoria_id', [
                            'label' => 'Classificação',
                            'options' => $categorias,
                            'empty' => '- Selecione -',
                            'class' => 'form-select',
                            'required' => true
                        ])?>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <?=$this->Form->control('texto', [
                        'label' => 'Descrição',
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 5,
                        'required' => true
                    ])?>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Você pode anexar até 3 arquivos.</div>
                </div>
                <div class="col-md-4 suporte-anexo">
                    <div class="d-flex align-items-start gap-2">
                        <div class="flex-grow-1">
                            <?=$this->Form->control('anexo_1', [
                                'label' => 'Anexo 1',
                                'type' => 'file',
                                'class' => 'form-control'
                            ])?>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next" data-next="2" title="Adicionar outro anexo">
                            +
                        </button>
                    </div>
                </div>
                <div class="col-md-4 suporte-anexo d-none" data-slot="2">
                    <div class="d-flex align-items-start gap-2">
                        <div class="flex-grow-1">
                            <?=$this->Form->control('anexo_2', [
                                'label' => 'Anexo 2',
                                'type' => 'file',
                                'class' => 'form-control'
                            ])?>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next" data-next="3" title="Adicionar outro anexo">
                            +
                        </button>
                    </div>
                </div>
                <div class="col-md-4 suporte-anexo d-none" data-slot="3">
                    <?=$this->Form->control('anexo_3', [
                        'label' => 'Anexo 3',
                        'type' => 'file',
                        'class' => 'form-control'
                    ])?>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <?=$this->Form->button('Registrar', ['class'=>'btn btn-success'])?>
                </div>
            <?=$this->Form->end()?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-add-next').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const next = this.getAttribute('data-next');
            if (!next) return;
            const alvo = document.querySelector(`.suporte-anexo[data-slot="${next}"]`);
            if (alvo) {
                alvo.classList.remove('d-none');
                this.disabled = true;
            }
        });
    });
});
</script>
