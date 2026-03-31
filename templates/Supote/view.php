<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Chamado #<?= (int)$chamado->id ?></h4>
        <a href="/supote" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light">
            <div class="fw-semibold">Resumo do chamado</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Categoria</div>
                    <div class="fw-semibold"><?= h($chamado->suporte_categoria->nome ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold"><?= h($chamado->suporte_status->nome ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Usuário</div>
                    <div class="fw-semibold"><?= h($chamado->usuario->nome ?? '-') ?></div>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-muted small">Descrição</div>
            <div class="suporte-texto-html"><?= (string)($chamado->texto ?? '') ?></div>
            <div class="mt-3">
                <?php if (!empty($chamado->anexo_1)): ?>
                    <a href="/uploads/anexos/<?= h($chamado->anexo_1) ?>" class="btn btn-sm btn-outline-secondary me-1" target="_blank">Anexo 1</a>
                <?php endif; ?>
                <?php if (!empty($chamado->anexo_2)): ?>
                    <a href="/uploads/anexos/<?= h($chamado->anexo_2) ?>" class="btn btn-sm btn-outline-secondary me-1" target="_blank">Anexo 2</a>
                <?php endif; ?>
                <?php if (!empty($chamado->anexo_3)): ?>
                    <a href="/uploads/anexos/<?= h($chamado->anexo_3) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Anexo 3</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
        $identity = $this->request->getAttribute('identity');
        $isTi = in_array((int)($identity['id'] ?? 0), [1, 8088], true);
    ?>

    <?php if ($isTi): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light">
                <div class="fw-semibold">Gestão do status</div>
            </div>
            <div class="card-body">
                <?php if ((int)$chamado->status_id === 1): ?>
                    <?=$this->Form->create(null, ['url' => ['action' => 'alterarStatus', $chamado->id]])?>
                        <?=$this->Form->hidden('status_id', ['value' => 2])?>
                        <?=$this->Form->button('Colocar em análise', ['class' => 'btn btn-warning'])?>
                    <?=$this->Form->end()?>
                <?php else: ?>
                <h6 class="fw-semibold mb-2">Atualizar status</h6>
                <?php
                    $statusOptions = [];
                    foreach ($statusList as $statusId => $statusNome) {
                        if ((int)$statusId > (int)$chamado->status_id) {
                            $statusOptions[$statusId] = $statusNome;
                        }
                    }
                ?>
                <?=$this->Form->create(null, ['url' => ['action' => 'alterarStatus', $chamado->id], 'type' => 'file', 'id' => 'form-alterar-status'])?>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <?=$this->Form->control('status_id', [
                                'label' => 'Status',
                                'options' => $statusOptions,
                                'empty' => '- Selecione -',
                                'class' => 'form-select',
                                'value' => null
                            ])?>
                        </div>
                        <div class="col-md-6 status-finalizacao d-none">
                            <?=$this->Form->control('classificacao_final_id', [
                                'label' => 'Classificação final',
                                'options' => $classificacoesFinais,
                                'empty' => '- Selecione -',
                                'class' => 'form-select',
                                'value' => $chamado->classificacao_final_id
                            ])?>
                        </div>
                        <div class="col-12 status-mensagem d-none">
                            <?=$this->Form->control('texto', [
                                'label' => 'Mensagem para o cliente',
                                'type' => 'textarea',
                                'class' => 'form-control js-tinymce-suporte',
                                'rows' => 4,
                                'required' => false
                            ])?>
                        </div>
                        <div class="col-12 status-mensagem d-none">
                            <div class="text-muted small mb-2">Você pode anexar até 3 arquivos.</div>
                            <div class="row g-2">
                                <div class="col-md-4 suporte-anexo-resposta">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <?=$this->Form->control('anexo_1', ['label' => 'Anexo 1', 'type' => 'file', 'class' => 'form-control'])?>
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next-resposta" data-next="2" title="Adicionar outro anexo">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 suporte-anexo-resposta d-none" data-slot="2">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <?=$this->Form->control('anexo_2', ['label' => 'Anexo 2', 'type' => 'file', 'class' => 'form-control'])?>
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next-resposta" data-next="3" title="Adicionar outro anexo">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 suporte-anexo-resposta d-none" data-slot="3">
                                    <?=$this->Form->control('anexo_3', ['label' => 'Anexo 3', 'type' => 'file', 'class' => 'form-control'])?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <?=$this->Form->button('Atualizar', ['class' => 'btn btn-primary'])?>
                        </div>
                    </div>
                <?=$this->Form->end()?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="fw-semibold">Respostas</div>
        </div>
        <div class="card-body">
            <?php
                $renderRespostas = function (int $parentId, int $nivel = 0) use (&$renderRespostas, $repliesByParent) {
                    if (empty($repliesByParent[$parentId])) {
                        return;
                    }
                    foreach ($repliesByParent[$parentId] as $item) {
                        $autor = $item->usuario->nome ?? '';
                        $margin = $nivel * 24;
                        ?>
                        <div class="reply-item" style="margin-left: <?= (int)$margin ?>px;">
                            <div class="reply-meta">
                                <span class="fw-semibold"><?= h((string)$autor) ?></span>
                                <span class="text-muted">• <?= $item->created ? h($item->created->subHours(3)->i18nFormat('dd/MM/yyyy HH:mm')) : '-' ?></span>
                            </div>
                            <div class="reply-text suporte-texto-html"><?= (string)($item->texto ?? '') ?></div>
                            <?php if (!empty($item->anexo_1) || !empty($item->anexo_2) || !empty($item->anexo_3)): ?>
                                <div class="mt-2">
                                    <?php if (!empty($item->anexo_1)): ?>
                                        <a href="/uploads/anexos/<?= h($item->anexo_1) ?>" class="btn btn-sm btn-outline-secondary me-1" target="_blank">Anexo 1</a>
                                    <?php endif; ?>
                                    <?php if (!empty($item->anexo_2)): ?>
                                        <a href="/uploads/anexos/<?= h($item->anexo_2) ?>" class="btn btn-sm btn-outline-secondary me-1" target="_blank">Anexo 2</a>
                                    <?php endif; ?>
                                    <?php if (!empty($item->anexo_3)): ?>
                                        <a href="/uploads/anexos/<?= h($item->anexo_3) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Anexo 3</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $renderRespostas((int)$item->id, $nivel + 1);
                    }
                };
            ?>
            <?php if (empty($repliesByParent[(int)$chamado->id])) { ?>
                <div class="text-muted">Não há respostas registradas.</div>
            <?php } else { ?>
                <?php $renderRespostas((int)$chamado->id, 0); ?>
            <?php } ?>
        </div>
    </div>

    <?php
        $podeReabrir = $chamado->finalizado !== null;
    ?>

    <?php if ($podeReabrir): ?>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-light">
                <div class="fw-semibold">Reabrir chamado</div>
            </div>
            <div class="card-body">
                <?=$this->Form->create(null, ['url' => ['action' => 'reabrir', $chamado->id], 'type' => 'file'])?>
                    <div class="mb-3">
                        <?=$this->Form->control('texto', [
                            'label' => 'Mensagem',
                            'type' => 'textarea',
                            'class' => 'form-control js-tinymce-suporte',
                            'rows' => 4,
                            'required' => false,
                            'data-editor-required' => '1'
                        ])?>
                    </div>
                    <div class="text-muted small mb-2">Você pode anexar até 3 arquivos.</div>
                    <div class="row g-2">
                        <div class="col-md-4 suporte-anexo-resposta">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <?=$this->Form->control('anexo_1', ['label' => 'Anexo 1', 'type' => 'file', 'class' => 'form-control'])?>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next-resposta" data-next="2" title="Adicionar outro anexo">
                                    +
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 suporte-anexo-resposta d-none" data-slot="2">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <?=$this->Form->control('anexo_2', ['label' => 'Anexo 2', 'type' => 'file', 'class' => 'form-control'])?>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next-resposta" data-next="3" title="Adicionar outro anexo">
                                    +
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 suporte-anexo-resposta d-none" data-slot="3">
                            <?=$this->Form->control('anexo_3', ['label' => 'Anexo 3', 'type' => 'file', 'class' => 'form-control'])?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?=$this->Form->button('Reabrir', ['class' => 'btn btn-warning'])?>
                    </div>
                <?=$this->Form->end()?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$isTi && (int)$chamado->status_id === 3): ?>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-light">
                <div class="fw-semibold">Responder</div>
            </div>
            <div class="card-body">
                <?=$this->Form->create(null, ['url' => ['action' => 'responder', $chamado->id], 'type' => 'file'])?>
                    <div class="mb-3">
                        <?=$this->Form->control('texto', [
                            'label' => 'Mensagem',
                            'type' => 'textarea',
                            'class' => 'form-control js-tinymce-suporte',
                            'rows' => 4,
                            'required' => false,
                            'data-editor-required' => '1'
                        ])?>
                    </div>
                    <div class="text-muted small mb-2">Você pode anexar até 3 arquivos.</div>
                    <div class="row g-2">
                        <div class="col-md-4 suporte-anexo-resposta">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <?=$this->Form->control('anexo_1', ['label' => 'Anexo 1', 'type' => 'file', 'class' => 'form-control'])?>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next-resposta" data-next="2" title="Adicionar outro anexo">
                                    +
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 suporte-anexo-resposta d-none" data-slot="2">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <?=$this->Form->control('anexo_2', ['label' => 'Anexo 2', 'type' => 'file', 'class' => 'form-control'])?>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-4 btn-add-next-resposta" data-next="3" title="Adicionar outro anexo">
                                    +
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 suporte-anexo-resposta d-none" data-slot="3">
                            <?=$this->Form->control('anexo_3', ['label' => 'Anexo 3', 'type' => 'file', 'class' => 'form-control'])?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?=$this->Form->button('Responder', ['class' => 'btn btn-primary'])?>
                    </div>
                <?=$this->Form->end()?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-light">
            <div class="fw-semibold">Histórico de status</div>
        </div>
        <div class="card-body">
            <?php if (empty($historico) || count($historico) === 0): ?>
                <div class="text-muted">Nenhuma alteração de status registrada.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Usuário</th>
                                <th>Status anterior</th>
                                <th>Status novo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $item): ?>
                                <tr>
                                    <td><?= $item->created ? h($item->created->subHours(3)->i18nFormat('dd/MM/yyyy HH:mm')) : '-' ?></td>
                                    <td><?= h($item->usuario->nome ?? '-') ?></td>
                                    <td><?= h($item->status_anterior->nome ?? '-') ?></td>
                                    <td><?= h($item->status_novo->nome ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .suporte-texto-html p:last-child {
        margin-bottom: 0;
    }
    .reply-item {
        border-left: 3px solid #e3e6ea;
        padding-left: 14px;
        margin-bottom: 12px;
    }
    .reply-meta {
        font-size: 0.9rem;
        margin-bottom: 4px;
    }
    .reply-text {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 6px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.tinymce) {
        tinymce.init({
            selector: '.js-tinymce-suporte',
            height: 280,
            menubar: false,
            branding: false,
            plugins: 'lists link table code image autoresize',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            setup: function (editor) {
                editor.on('change input undo redo', function () {
                    editor.save();
                });
            }
        });
    }

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (window.tinymce) {
                tinymce.triggerSave();
            }

            let hasError = false;
            form.querySelectorAll('textarea.js-tinymce-suporte[data-editor-required="1"]').forEach(function (textarea) {
                const plainText = textarea.value.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/gi, ' ').trim();
                if (plainText === '') {
                    hasError = true;
                    textarea.classList.add('is-invalid');
                } else {
                    textarea.classList.remove('is-invalid');
                }
            });

            if (hasError) {
                event.preventDefault();
                window.alert('Preencha a mensagem antes de enviar.');
            }
        });
    });

    document.querySelectorAll('.btn-add-next-resposta').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const next = this.getAttribute('data-next');
            if (!next) return;
            const alvo = document.querySelector(`.suporte-anexo-resposta[data-slot="${next}"]`);
            if (alvo) {
                alvo.classList.remove('d-none');
                this.disabled = true;
            }
        });
    });
    const formAlterar = document.getElementById('form-alterar-status');
    const statusSelect = formAlterar ? formAlterar.querySelector('select[name="status_id"]') : null;
    const blocoMensagem = document.querySelectorAll('.status-mensagem');
    const blocoFinalizacao = document.querySelectorAll('.status-finalizacao');
    if (statusSelect) {
        const textoField = formAlterar.querySelector('textarea[name="texto"]');
        const anexoFields = formAlterar.querySelectorAll('input[type="file"]');
        const toggleStatus = () => {
            const val = parseInt(statusSelect.value, 10);
            const precisaMensagem = val === 3 || val === 5;
            const precisaFinalizacao = val === 5;
            blocoMensagem.forEach((el) => el.classList.toggle('d-none', !precisaMensagem));
            blocoFinalizacao.forEach((el) => el.classList.toggle('d-none', !precisaFinalizacao));
            if (textoField) {
                textoField.required = precisaMensagem;
            }
            anexoFields.forEach((el) => {
                el.disabled = !precisaMensagem;
            });
        };
        statusSelect.addEventListener('change', toggleStatus);
        toggleStatus();
    }
});
</script>
