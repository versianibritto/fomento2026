<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Novo chamado</h4>
        <a href="/supote" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

        <div class="card shadow-sm">
        <div class="card-body">
            <?=$this->Form->create($chamado, ['class' => 'row g-3', 'type' => 'file'])?>
                <?php if (!empty($isTi)): ?>
                    <div class="col-12">
                        <div class="alert alert-info py-2 mb-0">
                            Para TI, é possível registrar o chamado em nome de outra pessoa.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <?=$this->Form->hidden('demandante', ['id' => 'demandante-id'])?>
                        <label for="demandante-busca" class="form-label">Demandante</label>
                        <input
                            type="text"
                            id="demandante-busca"
                            class="form-control usuario-autocomplete"
                            placeholder="Buscar por nome, CPF ou ID"
                            autocomplete="off"
                            required
                            data-hidden-target="demandante-id"
                            data-results-target="demandante-resultados"
                            data-selected-target="demandante-selecionado"
                        >
                        <div id="demandante-selecionado" class="form-text">Nenhum usuário selecionado.</div>
                        <div id="demandante-resultados" class="list-group position-relative mt-1"></div>
                    </div>

                    <div class="col-md-6">
                        <?=$this->Form->hidden('beneficiado', ['id' => 'beneficiado-id'])?>
                        <label for="beneficiado-busca" class="form-label">Beneficiado</label>
                        <input
                            type="text"
                            id="beneficiado-busca"
                            class="form-control usuario-autocomplete"
                            placeholder="Buscar por nome, CPF ou ID"
                            autocomplete="off"
                            required
                            data-hidden-target="beneficiado-id"
                            data-results-target="beneficiado-resultados"
                            data-selected-target="beneficiado-selecionado"
                        >
                        <div id="beneficiado-selecionado" class="form-text">Se não informar, o sistema usa o demandante.</div>
                        <div id="beneficiado-resultados" class="list-group position-relative mt-1"></div>
                    </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <?=$this->Form->control('categoria_id', [
                        'label' => 'Classificação',
                        'options' => $categorias,
                        'empty' => '- Selecione -',
                        'class' => 'form-select',
                        'required' => true
                    ])?>
                </div>

                <div class="col-12">
                    <?=$this->Form->control('texto', [
                        'label' => 'Descrição',
                        'type' => 'textarea',
                        'class' => 'form-control js-tinymce-suporte',
                        'rows' => 5,
                        'required' => false,
                        'data-editor-required' => '1'
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
                window.alert('Preencha a descrição antes de registrar o chamado.');
            }
        });
    });

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

    document.querySelectorAll('.usuario-autocomplete').forEach(function (input) {
        const hiddenTarget = document.getElementById(input.dataset.hiddenTarget);
        const resultsBox = document.getElementById(input.dataset.resultsTarget);
        const selectedBox = document.getElementById(input.dataset.selectedTarget);
        let timer = null;

        if (!hiddenTarget || !resultsBox || !selectedBox) {
            return;
        }

        input.addEventListener('input', function () {
            const termo = this.value.trim();
            hiddenTarget.value = '';
            if (termo.length < 2) {
                resultsBox.innerHTML = '';
                return;
            }

            clearTimeout(timer);
            timer = setTimeout(function () {
                fetch('/supote/busca-usuarios?q=' + encodeURIComponent(termo), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (itens) {
                        resultsBox.innerHTML = '';
                        if (!Array.isArray(itens) || itens.length === 0) {
                            const empty = document.createElement('div');
                            empty.className = 'list-group-item small text-muted';
                            empty.textContent = 'Nenhum usuário encontrado.';
                            resultsBox.appendChild(empty);
                            return;
                        }

                        itens.forEach(function (item) {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'list-group-item list-group-item-action';
                            button.textContent = item.label;
                            button.addEventListener('mousedown', function (event) {
                                event.preventDefault();
                                hiddenTarget.value = item.id;
                                input.value = item.label;
                                selectedBox.textContent = 'Selecionado: ' + item.label;
                                resultsBox.innerHTML = '';
                            });
                            resultsBox.appendChild(button);
                        });
                    })
                    .catch(function () {
                        resultsBox.innerHTML = '';
                    });
            }, 250);
        });

        input.addEventListener('blur', function () {
            setTimeout(function () {
                resultsBox.innerHTML = '';
            }, 150);
        });
    });
});
</script>
