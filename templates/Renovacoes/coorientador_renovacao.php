<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('renovacoes_steps', [
            'edital' => $edital,
            'current' => 'coorientadorRenovacao',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Dados do coorientador</h3>
                <div class="fw-semibold">Inscrição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Rascunho: nenhum campo é obrigatório nesta etapa.</div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Dados pessoais</h4>
                    </div>
                    <?php
                    $cpfExib = '';
                    if (!empty($coorientadorUsuario?->cpf)) {
                        $digits = preg_replace('/\D+/', '', (string)$coorientadorUsuario->cpf);
                        $cpfExib = strlen($digits) === 11
                            ? substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2)
                            : $coorientadorUsuario->cpf;
                    }
                    $naoInformado = '<span class="badge bg-danger">Não informado</span>';
                    ?>
                    <div class="row g-3">
                        <?php if (empty($inscricao->coorientador)) : ?>
                            <div class="col-12">
                                <div class="alert alert-secondary mb-0">
                                    <div class="fw-semibold">Nenhum coorientador vinculado</div>
                                    <div class="small">Você pode incluir agora ou continuar o preenchimento e voltar depois.</div>
                                </div>
                            </div>
                        <?php else : ?>
                        <div class="col-md-4">
                            <div class="text-muted small">CPF</div>
                            <div class="fw-semibold"><?= $cpfExib ? h($cpfExib) : $naoInformado ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Nome</div>
                            <div class="fw-semibold"><?= !empty($coorientadorUsuario?->nome) ? h($coorientadorUsuario->nome) : $naoInformado ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">E-mail</div>
                            <div class="fw-semibold"><?= !empty($coorientadorUsuario?->email) ? h($coorientadorUsuario->email) : $naoInformado ?></div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(
                                    'Visualizar dados do coorientador',
                                    ['controller' => 'Users', 'action' => 'ver', (int)$inscricao->coorientador],
                                    ['class' => 'btn btn-outline-secondary btn-sm']
                                ) ?>
                                <?= $this->Html->link(
                                    'Editar dados do coorientador',
                                    ['controller' => 'Users', 'action' => 'editar', (int)$inscricao->coorientador],
                                    ['class' => 'btn btn-outline-primary btn-sm']
                                ) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <div class="border rounded bg-white p-3 p-md-4 mt-2">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <h5 class="mb-0">Ações do coorientador</h5>
                                    <span class="text-muted small">Informe o CPF para <?= empty($inscricao->coorientador) ? 'incluir' : 'alterar' ?> o coorientador.</span>
                                </div>

                                <?= $this->Form->create(null, ['class' => 'row g-2 align-items-end mb-3']) ?>
                                    <?= $this->Form->hidden('acao', ['value' => 'incluir_coorientador']) ?>
                                    <div class="col-md-7">
                                        <?= $this->Form->control('cpf_coorientador', [
                                            'label' => empty($inscricao->coorientador) ? 'CPF do coorientador' : 'Novo CPF do coorientador',
                                            'class' => 'form-control',
                                            'maxlength' => 25,
                                            'required' => true,
                                            'placeholder' => 'Somente números ou CPF formatado',
                                        ]) ?>
                                    </div>
                                    <div class="col-md-5 d-grid d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary"><?= empty($inscricao->coorientador) ? 'Incluir coorientador' : 'Alterar coorientador' ?></button>
                                    </div>
                                <?= $this->Form->end() ?>

                                <?php if (!empty($inscricao->coorientador)) : ?>
                                <div class="pt-2 border-top">
                                    <?= $this->Form->create(null, ['class' => 'd-grid d-md-flex justify-content-md-end']) ?>
                                        <?= $this->Form->hidden('acao', ['value' => 'excluir_coorientador']) ?>
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Deseja excluir o coorientador vinculado a esta inscrição?');">Excluir coorientador</button>
                                    <?= $this->Form->end() ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <h4 class="mb-1">Anexos</h4>
                    <p class="text-muted small mt-0 mb-4">
                        Envie os documentos por etapa. Itens com arquivo já enviado podem ser baixados, alterados ou excluídos.<br>
                        Todos os anexos disponibilizados nesta tela serão exigidos na finalização da inscrição.<br>
                        Sempre que alterar um coorientador, verifique se os anexos estão de acordo com a atualização do coorientador.
                    </p>
                    <div class="alert alert-info border mb-4">
                        <div class="fw-semibold mb-2">Termo de consentimento do coorientador</div>
                        <div class="small mb-2">
                            Para o anexo de termo de consentimento, siga esta sequência:
                        </div>
                        <ol class="small mb-2 ps-3">
                            <li>Baixe o modelo disponibilizado para este edital.</li>
                            <li>Preencha os dados solicitados.</li>
                            <li>Colete a(s) assinatura(s) necessária(s).</li>
                            <li>Retorne a esta tela e faça o upload do termo assinado.</li>
                            <li>É permitida assinatura eletrônica do Gov.br.</li>
                        </ol>
                        <?php if (!empty($edital->modelo_cons_coor)) : ?>
                            <a href="/uploads/editais/<?= h($edital->modelo_cons_coor) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-download me-1"></i>Baixar modelo do termo de consentimento do coorientador
                            </a>
                        <?php else : ?>
                            <div class="small text-danger fw-semibold">Modelo de consentimento do coorientador não cadastrado para este edital.</div>
                        <?php endif; ?>
                    </div>
                    <?= $this->Form->create(null, ['type' => 'file']) ?>
                        <?= $this->Form->hidden('acao', ['value' => 'salvar_anexos']) ?>
                        <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
                        <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                        <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                        <fieldset class="anexos-areas">
                            <div class="row g-3">
                                <?php foreach ($anexosTiposC as $tipo) : ?>
                                    <?php
                                        $tipoId = (int)$tipo->id;
                                        $rotulo = (string)$tipo->nome;
                                    ?>
                                    <div class="col-md-6">
                                        <?php if (empty($anexos[$tipoId])) : ?>
                                            <?= $this->Form->control("anexos[$tipoId]", [
                                                'type' => 'file',
                                                'label' => h($rotulo),
                                                'class' => 'form-control',
                                            ]) ?>
                                        <?php else : ?>
                                            <label class="form-label d-block"><?= h($rotulo) ?></label>
                                            <div class="anexo-arquivo-atual">
                                                <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                    <div class="small text-muted text-truncate">
                                                        <?= h($anexos[$tipoId]) ?>
                                                    </div>
                                                    <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                        <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                            <i class="fa fa-download"></i>
                                                        </a>
                                                        <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                            <i class="fa fa-edit"></i>
                                                        </label>
                                                        <button
                                                            type="button"
                                                            class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                            onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)"
                                                            title="Excluir"
                                                        >
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input
                                                    id="anexo-<?= (int)$tipoId ?>"
                                                    name="anexos[<?= (int)$tipoId ?>]"
                                                    type="file"
                                                    class="d-none anexo-file"
                                                    data-tipo="<?= (int)$tipoId ?>"
                                                >
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Salvar rascunho e continuar</button>
                                </div>
                            </div>
                        </fieldset>
                    <?= $this->Form->end() ?>
                </div>
            </div>
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
