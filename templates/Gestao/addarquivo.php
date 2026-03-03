<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Gestão de Anexos #<?= h((string)($inscricao->id ?? '')) ?></h4>
        <a href="<?= $this->Url->build(['controller' => 'Padrao', 'action' => 'visualizar', (int)($inscricao->id ?? 0)]) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar para visualização
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Edital</div>
                    <div class="fw-semibold"><?= h((string)($inscricao->editai->nome ?? '-')) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Programa</div>
                    <div class="fw-semibold"><?= h((string)($inscricao->editai->programa->sigla ?? '-')) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold"><?= h((string)($inscricao->fase->nome ?? '-')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?= $this->Form->create(null, ['type' => 'file', 'id' => 'form-gestao-anexos']) ?>
                <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Anexo</th>
                                <th>Usuário</th>
                                <th>Data inclusão</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($anexosLista)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhum tipo de anexo configurado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($anexosLista as $anexo): ?>
                                <?php
                                    $tipoId = (int)($anexo['tipo_id'] ?? 0);
                                    $arquivo = (string)($anexo['arquivo'] ?? '');
                                    $usuarioNome = trim((string)($anexo['usuario_nome'] ?? ''));
                                    $dataInclusao = $anexo['data_inclusao'] ?? null;
                                    $dataInclusaoFmt = '-';
                                    if ($dataInclusao instanceof \DateTimeInterface) {
                                        $dataInclusaoFmt = (clone $dataInclusao)->modify('-3 hours')->format('d/m/Y H:i');
                                    } elseif (!empty($dataInclusao)) {
                                        $tsData = strtotime((string)$dataInclusao);
                                        $dataInclusaoFmt = $tsData ? date('d/m/Y H:i', strtotime('-3 hours', $tsData)) : (string)$dataInclusao;
                                    }
                                    $inputId = 'anexo-gestao-' . $tipoId;
                                ?>
                                <tr>
                                    <td><?= h((string)($anexo['tipo_nome'] ?? '')) ?></td>
                                    <td style="min-width: 280px;">
                                        <?php if ($arquivo !== ''): ?>
                                            <div class="small text-muted text-truncate"><?= h($arquivo) ?></div>
                                        <?php else: ?>
                                            <input
                                                id="<?= h($inputId) ?>"
                                                name="anexos[<?= $tipoId ?>]"
                                                type="file"
                                                class="form-control form-control-sm"
                                            >
                                        <?php endif; ?>
                                    </td>
                                    <td><?= h($usuarioNome !== '' ? $usuarioNome : '-') ?></td>
                                    <td><?= h($dataInclusaoFmt) ?></td>
                                    <td class="text-end">
                                        <?php if ($arquivo !== ''): ?>
                                            <a href="/uploads/anexos/<?= h($arquivo) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <label for="<?= h($inputId) ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Editar anexo">
                                                <i class="fa fa-edit"></i>
                                            </label>
                                            <input
                                                id="<?= h($inputId) ?>"
                                                name="anexos[<?= $tipoId ?>]"
                                                type="file"
                                                class="d-none gestao-editar-anexo"
                                                data-tipo="<?= $tipoId ?>"
                                            >
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <?= $this->Form->button('Salvar anexos enviados', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.gestao-editar-anexo').forEach(function (input) {
    input.addEventListener('change', function () {
        if (!this.files || this.files.length === 0) {
            return;
        }
        const form = document.getElementById('form-gestao-anexos');
        if (!form) {
            return;
        }
        const tipo = this.getAttribute('data-tipo') || '';
        const acao = form.querySelector('#anexo-acao');
        const anexoTipo = form.querySelector('#anexo-tipo');
        const alterarTipo = form.querySelector('#alterar-anexo-tipo');
        if (acao) {
            acao.value = 'alterar';
        }
        if (anexoTipo) {
            anexoTipo.value = tipo;
        }
        if (alterarTipo) {
            alterarTipo.value = tipo;
        }
        form.submit();
    });
});
</script>
