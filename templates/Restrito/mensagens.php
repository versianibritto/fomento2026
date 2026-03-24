<?php
$titulo = $isEdicao ? 'Editar Mensagem' : 'Cadastrar Mensagem';
$textoBotao = $isEdicao ? 'Salvar alterações' : 'Cadastrar mensagem';
$mensagemInicio = !empty($mensagem->inicio) ? $mensagem->inicio->format('Y-m-d\TH:i') : '';
$mensagemFim = !empty($mensagem->fim) ? $mensagem->fim->format('Y-m-d\TH:i') : '';
?>
<section class="mt-n3">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0"><?= h($titulo) ?></h2>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Lista de mensagens', ['controller' => 'Restrito', 'action' => 'mensagensLista'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-dark']) ?>
                </div>
            </div>
            <hr>

            <?= $this->Form->create($mensagem, ['type' => 'file', 'class' => 'row g-3']) ?>
                <?= $this->Form->hidden('remover_imagem', ['value' => '0', 'id' => 'mensagem-remover-imagem']) ?>
                <div class="col-md-8">
                    <?= $this->Form->control('titulo', [
                        'label' => 'Título',
                        'type' => 'textarea',
                        'rows' => 3,
                        'class' => 'form-control',
                        'required' => false,
                    ]) ?>
                    <div class="form-text">Aceita HTML. Ex.: &lt;strong&gt;, &lt;br&gt;, &lt;span&gt;.</div>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->control('tipo', [
                        'label' => 'Tipo',
                        'type' => 'select',
                        'options' => $tipos,
                        'empty' => false,
                        'class' => 'form-select',
                        'required' => true,
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('inicio', [
                        'label' => 'Início de exibição',
                        'type' => 'datetime-local',
                        'class' => 'form-control',
                        'value' => $mensagemInicio,
                        'templates' => ['inputContainer' => '{{content}}'],
                        'required' => false,
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('fim', [
                        'label' => 'Fim de exibição',
                        'type' => 'datetime-local',
                        'class' => 'form-control',
                        'value' => $mensagemFim,
                        'templates' => ['inputContainer' => '{{content}}'],
                        'required' => false,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('testo', [
                        'label' => 'Mensagem',
                        'type' => 'textarea',
                        'rows' => 6,
                        'class' => 'form-control',
                        'required' => false,
                    ]) ?>
                    <div class="form-text">O conteúdo é salvo com HTML e renderizado no popup.</div>
                </div>
                <div class="col-md-8">
                    <?php if (empty($mensagem->imagem)): ?>
                        <?= $this->Form->control('imagem', [
                            'label' => 'Imagem',
                            'type' => 'file',
                            'class' => 'form-control',
                            'accept' => '.jpg,.jpeg,.png,.gif,.webp,.svg',
                        ]) ?>
                        <div class="form-text">A imagem será gravada em webroot/uploads/editais e, se for a mensagem principal, ocupará o modal inteiro.</div>
                    <?php else: ?>
                        <label class="form-label d-block">Imagem</label>
                        <div class="manual-anexo-atual" id="mensagem-imagem-atual">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <img src="/uploads/editais/<?= h($mensagem->imagem) ?>" alt="Imagem atual" class="mensagem-preview">
                                    <div class="small text-muted text-break" id="mensagem-imagem-nome">
                                        <?= h($mensagem->imagem) ?>
                                    </div>
                                </div>
                                <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                                    <a href="/uploads/editais/<?= h($mensagem->imagem) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <label for="mensagem-imagem" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </label>
                                    <button type="button" id="mensagem-imagem-excluir" class="btn btn-light border btn-sm py-0 px-2" title="Excluir">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <input
                                id="mensagem-imagem"
                                name="imagem"
                                type="file"
                                class="d-none"
                                accept=".jpg,.jpeg,.png,.gif,.webp,.svg"
                            >
                        </div>
                        <div class="form-text" id="mensagem-imagem-ajuda">
                            Use editar para substituir a imagem atual.
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
.mensagem-preview {
    max-width: 120px;
    max-height: 80px;
    border-radius: .4rem;
    border: 1px solid #dfe3e7;
    object-fit: cover;
}
</style>
<script>
(function () {
    const imagemInput = document.getElementById('mensagem-imagem');
    const nomeImagem = document.getElementById('mensagem-imagem-nome');
    const ajudaImagem = document.getElementById('mensagem-imagem-ajuda');
    const removerImagemInput = document.getElementById('mensagem-remover-imagem');
    const botaoExcluirImagem = document.getElementById('mensagem-imagem-excluir');
    const blocoImagemAtual = document.getElementById('mensagem-imagem-atual');

    if (imagemInput && nomeImagem && ajudaImagem) {
        imagemInput.addEventListener('change', function () {
            if (!this.files || this.files.length === 0) {
                return;
            }
            if (removerImagemInput) {
                removerImagemInput.value = '0';
            }
            nomeImagem.textContent = this.files[0].name + ' (nova imagem selecionada)';
            ajudaImagem.textContent = 'Nova imagem selecionada. Clique em "' + <?= json_encode($textoBotao) ?> + '" para salvar.';
        });
    }

    if (botaoExcluirImagem && removerImagemInput && ajudaImagem && nomeImagem) {
        botaoExcluirImagem.addEventListener('click', function () {
            removerImagemInput.value = '1';
            if (imagemInput) {
                imagemInput.value = '';
            }
            nomeImagem.textContent = 'Imagem atual marcada para exclusão';
            ajudaImagem.textContent = 'Clique em "' + <?= json_encode($textoBotao) ?> + '" para excluir a imagem ao salvar.';
            if (blocoImagemAtual) {
                blocoImagemAtual.classList.add('border-danger');
            }
        });
    }
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.tinymce) {
        return;
    }

    tinymce.init({
        selector: '#titulo, #testo',
        height: 260,
        menubar: false,
        branding: false,
        plugins: 'lists link table code image autoresize',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
    });
});
</script>
