<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('renovacoes_steps', [
            'edital' => $edital,
            'current' => 'subprojetoRenovacao',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Subprojeto do bolsista</h3>
                <div class="fw-semibold">Inscricao - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Rascunho: nenhum campo e obrigatorio nesta etapa.</div>
            </div>

            <?= $this->Form->create(null, ['type' => 'file', 'class' => 'row g-3']) ?>
                <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
                <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                <?php
                    $modoAtual = strtoupper((string)($inscricao->subprojeto_renovacao ?? ''));
                    if (!in_array($modoAtual, ['I', 'D'], true)) {
                        $modoAtual = '';
                    }
                    $referenciaTitulo = trim((string)($referenciaSubprojeto->sp_titulo ?? ''));
                    $referenciaResumo = trim((string)($referenciaSubprojeto->sp_resumo ?? ''));
                    $referenciaAnexo = trim((string)($referenciaSubprojeto->anexo_subprojeto ?? ''));
                    $valorResumoRelatorio = trim((string)($inscricao->resumo_relatorio ?? ''));
                    $valorAutorizacao = in_array((string)($inscricao->autorizacao ?? ''), ['0', '1'], true)
                        ? (string)$inscricao->autorizacao
                        : '';
                    $valorJustificativaAlteracao = trim((string)($inscricao->justificativa_alteracao ?? ''));
                    $valorTituloSubprojeto = trim((string)($inscricao->sp_titulo ?? ''));
                    $valorResumoSubprojeto = trim((string)($inscricao->sp_resumo ?? ''));
                ?>
                <div class="col-12">
                    <h6 class="fw-semibold mb-1">Relatorio parcial</h6>
                </div>
                <div class="col-md-6">
                    <div class="anexos-areas">
                        <?php if (empty($anexos[13])) : ?>
                            <?= $this->Form->control('anexos[13]', [
                                'label' => 'Anexo do relatorio parcial (PDF)',
                                'type' => 'file',
                                'class' => 'form-control',
                            ]) ?>
                        <?php else : ?>
                            <label class="form-label d-block">Anexo do relatorio parcial (PDF)</label>
                            <div class="anexo-arquivo-atual">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                    <div class="small text-muted text-truncate">
                                        <?= h($anexos[13]) ?>
                                    </div>
                                    <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                        <a href="/uploads/anexos/<?= h($anexos[13]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        <label for="anexo-13" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                            <i class="fa fa-edit"></i>
                                        </label>
                                        <button
                                            type="button"
                                            class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                            onclick="confirmarExclusaoAnexo(13, this.form)"
                                            title="Excluir"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <input
                                    id="anexo-13"
                                    name="anexos[13]"
                                    type="file"
                                    class="d-none anexo-file"
                                    data-tipo="13"
                                >
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('autorizacao', [
                        'label' => 'Deseja autorizar publicacao em revista?',
                        'type' => 'select',
                        'options' => [
                            '1' => 'Sim',
                            '0' => 'Nao',
                        ],
                        'empty' => 'Selecione',
                        'class' => 'form-control' . ($valorAutorizacao === '' ? ' campo-vazio' : ''),
                        'value' => $valorAutorizacao !== '' ? $valorAutorizacao : null,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('resumo_relatorio', [
                        'label' => 'Resumo do relatorio (maximo de 4.000 caracteres)',
                        'type' => 'textarea',
                        'class' => 'form-control' . ($valorResumoRelatorio === '' ? ' campo-vazio' : ''),
                        'rows' => 4,
                        'maxlength' => 4000,
                        'value' => $inscricao->resumo_relatorio ?? null,
                    ]) ?>
                </div>
                <div class="col-12">
                    <div class="card border">
                        <div class="card-body py-3">
                            <div class="fw-semibold mb-2">Quer manter o subprojeto anterior ou cadastrar um novo?</div>
                            <div class="d-flex flex-column gap-2">
                                <label class="border rounded p-2 d-flex align-items-center gap-2 mb-0">
                                    <input type="radio" name="modo_subprojeto" value="I" <?= $modoAtual === 'I' ? 'checked' : '' ?> required>
                                    <span>Manter o subprojeto anterior</span>
                                </label>
                                <label class="border rounded p-2 d-flex align-items-center gap-2 mb-0">
                                    <input type="radio" name="modo_subprojeto" value="D" <?= $modoAtual === 'D' ? 'checked' : '' ?> required>
                                    <span>Cadastrar um novo subprojeto</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 modo-subprojeto-I">
                    <div class="card border">
                        <div class="card-header bg-light fw-semibold">Subprojeto da inscricao de referencia</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="small text-muted">Titulo</div>
                                    <div><?= h($referenciaTitulo !== '' ? $referenciaTitulo : 'Nao informado') ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="small text-muted">Resumo</div>
                                    <div style="white-space: pre-line;"><?= h($referenciaResumo !== '' ? $referenciaResumo : 'Nao informado') ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="small text-muted">Anexo do subprojeto</div>
                                    <?php if ($referenciaAnexo !== '') : ?>
                                        <div class="anexo-arquivo-atual mt-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                <div class="small text-muted text-truncate"><?= h($referenciaAnexo) ?></div>
                                                <a href="/uploads/anexos/<?= h($referenciaAnexo) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div>Nao informado</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 modo-subprojeto-D">
                    <h6 class="fw-semibold">Dados do Subprojeto</h6>
                </div>
                <div class="col-12 modo-subprojeto-D">
                    <?= $this->Form->control('justificativa_alteracao', [
                        'label' => 'Justificativa da alteracao',
                        'type' => 'textarea',
                        'class' => 'form-control' . ($valorJustificativaAlteracao === '' ? ' campo-vazio' : ''),
                        'rows' => 3,
                        'maxlength' => 4000,
                        'value' => $inscricao->justificativa_alteracao ?? null,
                    ]) ?>
                </div>
                <div class="col-12 modo-subprojeto-D">
                    <?= $this->Form->control('sp_titulo', [
                        'label' => 'Titulo do subprojeto',
                        'class' => 'form-control' . ($valorTituloSubprojeto === '' ? ' campo-vazio' : ''),
                        'maxlength' => 255,
                        'value' => $inscricao->sp_titulo ?? null,
                    ]) ?>
                </div>
                <div class="col-12 modo-subprojeto-D">
                    <?= $this->Form->control('sp_resumo', [
                        'label' => 'Resumo do subprojeto',
                        'type' => 'textarea',
                        'class' => 'form-control' . ($valorResumoSubprojeto === '' ? ' campo-vazio' : ''),
                        'rows' => 4,
                        'maxlength' => 4000,
                        'value' => $inscricao->sp_resumo ?? null,
                    ]) ?>
                </div>
                <div class="col-12 modo-subprojeto-D">
                    <h6 class="fw-semibold mb-1">Anexos</h6>
                    <p class="text-muted small mb-3">
                        Envie o anexo do subprojeto. Itens com arquivo ja enviado podem ser baixados, alterados ou excluidos.
                    </p>
                    <div class="row g-3 anexos-areas">
                        <div class="col-md-6">
                            <?php if (empty($anexos[20])) : ?>
                                <?= $this->Form->control('anexos[20]', [
                                    'label' => 'Anexo subprojeto completo (PDF)',
                                    'type' => 'file',
                                    'class' => 'form-control',
                                ]) ?>
                            <?php else : ?>
                                <label class="form-label d-block">Anexo subprojeto completo (PDF)</label>
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
.campo-vazio {
    border-color: #dc3545 !important;
    background-color: #fff5f5 !important;
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

function alternarModoSubprojeto() {
    const selecionado = document.querySelector('input[name="modo_subprojeto"]:checked');
    const valor = selecionado ? String(selecionado.value || '') : '';
    document.querySelectorAll('.modo-subprojeto-I').forEach(function (el) {
        el.classList.toggle('d-none', valor !== 'I');
    });
    document.querySelectorAll('.modo-subprojeto-D').forEach(function (el) {
        el.classList.toggle('d-none', valor !== 'D');
    });
}

document.querySelectorAll('input[name="modo_subprojeto"]').forEach(function (input) {
    input.addEventListener('change', alternarModoSubprojeto);
});
alternarModoSubprojeto();

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
