<?php
$titulo = $isEdicao ? 'Editar Manual' : 'Cadastrar Manual';
$textoBotao = $isEdicao ? 'Salvar alterações' : 'Cadastrar manual';
?>
<section class="mt-n3">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0"><?= h($titulo) ?></h2>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Lista de manuais', ['controller' => 'Restrito', 'action' => 'manuaisLista'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-dark']) ?>
                </div>
            </div>
            <hr>

            <?= $this->Form->create($manual, ['type' => 'file', 'class' => 'row g-3']) ?>
                <div class="col-md-8">
                    <?= $this->Form->control('nome', [
                        'label' => 'Nome do manual',
                        'class' => 'form-control',
                        'required' => true,
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->control('restrito', [
                        'label' => 'Restrito',
                        'type' => 'select',
                        'options' => [0 => 'Não', 1 => 'Sim'],
                        'empty' => false,
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-8">
                    <?php if (empty($manual->arquivo)): ?>
                        <?= $this->Form->control('arquivo', [
                            'label' => 'Anexo',
                            'type' => 'file',
                            'class' => 'form-control',
                        ]) ?>
                    <?php else: ?>
                        <label class="form-label d-block">Anexo</label>
                        <div class="manual-anexo-atual" id="manual-anexo-atual">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <div class="small text-muted text-break" id="manual-anexo-nome">
                                    <?= h($manual->arquivo) ?>
                                </div>
                                <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                                    <a href="/uploads/editais/<?= h($manual->arquivo) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <label for="manual-arquivo" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </label>
                                </div>
                            </div>
                            <input
                                id="manual-arquivo"
                                name="arquivo"
                                type="file"
                                class="d-none"
                            >
                        </div>
                        <div class="form-text" id="manual-anexo-ajuda">
                            Use editar para substituir o arquivo atual.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <?= $this->Form->button($textoBotao, ['class' => 'btn btn-success']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</section>
<style>
.manual-anexo-atual {
    border: 1px solid #dfe3e7;
    border-radius: .5rem;
    background: #f8f9fa;
    padding: .5rem .75rem;
}
</style>
<script>
(function () {
    const arquivoInput = document.getElementById('manual-arquivo');
    const nomeAnexo = document.getElementById('manual-anexo-nome');
    const ajudaAnexo = document.getElementById('manual-anexo-ajuda');

    if (arquivoInput && nomeAnexo && ajudaAnexo) {
        arquivoInput.addEventListener('change', function () {
            if (!this.files || this.files.length === 0) {
                return;
            }
            nomeAnexo.textContent = this.files[0].name + ' (novo arquivo selecionado)';
            ajudaAnexo.textContent = 'Novo arquivo selecionado. Clique em "' + <?= json_encode($textoBotao) ?> + '" para salvar.';
        });
    }
})();
</script>
