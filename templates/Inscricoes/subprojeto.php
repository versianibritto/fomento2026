<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('inscricoes_steps', [
            'edital' => $edital,
            'current' => 'subprojeto',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Subprojeto do bolsista</h3>
                <div class="fw-semibold">Inscrição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Rascunho: nenhum campo é obrigatório nesta etapa.</div>
            </div>

            <?= $this->Form->create(null, ['type' => 'file', 'class' => 'row g-3']) ?>
                <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
                <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                <div class="col-12">
                    <h6 class="fw-semibold">Dados do subprojeto</h6>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('sp_titulo', [
                        'label' => 'Título do subprojeto',
                        'class' => 'form-control',
                        'maxlength' => 255,
                        'value' => $inscricao->sp_titulo ?? null,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('sp_resumo', [
                        'label' => 'Resumo do subprojeto',
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 4,
                        'maxlength' => 4000,
                        'value' => $inscricao->sp_resumo ?? null,
                    ]) ?>
                </div>
                <div class="col-12">
                    <h6 class="fw-semibold mb-1">Anexos</h6>
                    <p class="text-muted small mb-3">
                        Envie o anexo do subprojeto. Itens com arquivo já enviado podem ser baixados, alterados ou excluídos.
                    </p>
                    <div class="row g-3 anexos-areas">
                        <div class="col-md-6">
                            <?php if (empty($anexos[20])) : ?>
                                <?= $this->Form->control('anexos[20]', [
                                    'label' => 'Anexo do subprojeto completo (PDF)',
                                    'type' => 'file',
                                    'class' => 'form-control',
                                ]) ?>
                            <?php else : ?>
                                <label class="form-label d-block">Anexo do subprojeto completo (PDF)</label>
                                <div class="anexo-arquivo-atual">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                        <div class="small text-muted text-truncate">
                                            <?= h($anexos[20]) ?>
                                        </div>
                                        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                            <a href="/uploads/anexos/<?= h($anexos[20]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <label for="anexo-20" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                <i class="fa fa-edit"></i>
                                            </label>
                                            <button
                                                type="button"
                                                class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                onclick="confirmarExclusaoAnexo(20, this.form)"
                                                title="Excluir"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input
                                        id="anexo-20"
                                        name="anexos[20]"
                                        type="file"
                                        class="d-none anexo-file"
                                        data-tipo="20"
                                    >
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end align-items-center">
                    <?= $this->Form->button('Salvar Rascunho e Continuar', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
<style>
.anexos-areas .anexo-arquivo-atual {
    border: 1px solid #dfe3e7;
    border-radius: .5rem;
    background: #f8f9fa;
    padding: .5rem .75rem;
}
.anexos-areas .form-label {
    font-weight: 400;
    margin-bottom: .35rem;
}
</style>
<script>
document.querySelectorAll('.anexo-file').forEach(function (input) {
    input.addEventListener('change', function () {
        if (!this.files || this.files.length === 0) {
            return;
        }
        const form = this.closest('form');
        if (!form) {
            return;
        }
        const hidden = form.querySelector('#alterar-anexo-tipo');
        if (hidden) {
            hidden.value = this.getAttribute('data-tipo') || '';
        }
        const anexoAcao = form.querySelector('#anexo-acao');
        const anexoTipo = form.querySelector('#anexo-tipo');
        if (anexoAcao) {
            anexoAcao.value = 'alterar';
        }
        if (anexoTipo) {
            anexoTipo.value = this.getAttribute('data-tipo') || '';
        }
        form.submit();
    });
});

function confirmarExclusaoAnexo(tipoId, form) {
    if (!form) {
        return;
    }
    if (!confirm('Deseja excluir este anexo?')) {
        return;
    }
    const anexoAcao = form.querySelector('#anexo-acao');
    const anexoTipo = form.querySelector('#anexo-tipo');
    if (anexoAcao) {
        anexoAcao.value = 'excluir';
    }
    if (anexoTipo) {
        anexoTipo.value = String(tipoId || '');
    }
    form.submit();
}
</script>
