<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('inscricoes_steps', [
            'edital' => $edital,
            'current' => 'projeto',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Dados do projeto do orientador</h3>
                <div class="fw-semibold">Inscricao - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Projeto novo para esta inscricao.</div>
            </div>

            <?= $this->Form->create($projetoSelecionado, ['type' => 'file', 'class' => 'row g-3', 'id' => 'projeto-form']) ?>
                <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
                <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                <?php
                    $valorTitulo = trim((string)($projetoSelecionado->titulo ?? ''));
                    $valorGrandeArea = $grandeAreaSelecionada !== null ? (string)$grandeAreaSelecionada : '';
                    $valorAreaCnpq = isset($projetoSelecionado->area_id) && $projetoSelecionado->area_id !== null ? (string)$projetoSelecionado->area_id : '';
                    $valorAreaFiocruz = $areaFiocruzSelecionada !== null ? (string)$areaFiocruzSelecionada : '';
                    $valorLinhaFiocruz = isset($projetoSelecionado->linha_id) && $projetoSelecionado->linha_id !== null ? (string)$projetoSelecionado->linha_id : '';
                    $valorFinanciadores = trim((string)($projetoSelecionado->financiamento ?? ''));
                    $valorPalavras = trim((string)($projetoSelecionado->palavras_chaves ?? ''));
                    $valorResumo = trim((string)($projetoSelecionado->resumo ?? ''));
                ?>
                <div class="col-12">
                    <?= $this->Form->control('titulo', [
                        'label' => 'Titulo do projeto',
                        'class' => 'form-control' . ($valorTitulo === '' ? ' campo-vazio' : ''),
                        'required' => true,
                        'maxlength' => 255,
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('grande_area_id', [
                        'label' => 'Grande area CNPQ',
                        'class' => 'form-select' . ($valorGrandeArea === '' ? ' campo-vazio' : ''),
                        'options' => $grandesAreas ?? [],
                        'empty' => ' - Selecione - ',
                        'value' => $grandeAreaSelecionada ?? null,
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('area_id', [
                        'label' => 'Area CNPQ',
                        'class' => 'form-select' . ($valorAreaCnpq === '' ? ' campo-vazio' : ''),
                        'options' => $areas ?? [],
                        'empty' => ' - Selecione a grande area - ',
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('area_fiocruz_id', [
                        'label' => 'Area de pesquisa FIOCRUZ',
                        'class' => 'form-select' . ($valorAreaFiocruz === '' ? ' campo-vazio' : ''),
                        'options' => $areasFiocruz ?? [],
                        'empty' => ' - Selecione - ',
                        'value' => $areaFiocruzSelecionada ?? null,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('linha_id', [
                        'label' => 'Linha de pesquisa FIOCRUZ',
                        'class' => 'form-select' . ($valorLinhaFiocruz === '' ? ' campo-vazio' : ''),
                        'options' => $linhas ?? [],
                        'empty' => ' - Selecione a area - ',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('financiadores', [
                        'label' => 'Instituicoes financiadoras, se houver',
                        'type' => 'text',
                        'class' => 'form-control' . ($valorFinanciadores === '' ? ' campo-vazio' : ''),
                        'maxlength' => 255,
                        'value' => $projetoSelecionado->financiamento ?? null,
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('palavras_chaves', [
                        'label' => 'Palavras-chave, se houver',
                        'type' => 'text',
                        'class' => 'form-control' . ($valorPalavras === '' ? ' campo-vazio' : ''),
                        'maxlength' => 255,
                        'value' => $projetoSelecionado->palavras_chaves ?? null,
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $this->Form->control('resumo', [
                        'label' => 'Resumo do Projeto (max 4mil caracteres)',
                        'type' => 'textarea',
                        'class' => 'form-control' . ($valorResumo === '' ? ' campo-vazio' : ''),
                        'rows' => 6,
                        'maxlength' => 4000,
                    ]) ?>
                </div>
                <div class="col-12 mt-3">
                    <h6 class="fw-semibold mb-1">Anexos</h6>
                    <p class="text-muted small mb-3">
                        Envie os documentos do bloco de projeto. Itens com arquivo ja enviado podem ser baixados, alterados ou excluidos.
                    </p>
                    <div class="row g-3 anexos-areas">
                        <?php foreach ($anexosTiposProjeto as $tipo) : ?>
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
                </div>

                <div class="col-12 d-flex justify-content-end align-items-center mt-3">
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
document.addEventListener('DOMContentLoaded', function () {
    var areasPorGrandeArea = <?= json_encode($areasPorGrandeArea ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var grandeAreaSelect = document.getElementById('grande-area-id');
    var areaCnpqSelect = document.getElementById('area-id');
    var linhasPorArea = <?= json_encode($linhasPorAreaFiocruz ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var areaFiocruzSelect = document.getElementById('area-fiocruz-id');
    var linhaSelect = document.getElementById('linha-id');

    function aplicarFiltroAreaCnpq() {
        if (!grandeAreaSelect || !areaCnpqSelect) {
            return;
        }
        var grandeAreaId = grandeAreaSelect.value || '0';
        var atual = areaCnpqSelect.value || '';
        var opcoes = areasPorGrandeArea[grandeAreaId] || [];

        areaCnpqSelect.innerHTML = '';
        var optionDefault = document.createElement('option');
        optionDefault.value = '';
        optionDefault.textContent = ' - Selecione a grande area - ';
        areaCnpqSelect.appendChild(optionDefault);

        opcoes.forEach(function (item) {
            var option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.nome;
            if (String(item.id) === atual) {
                option.selected = true;
            }
            areaCnpqSelect.appendChild(option);
        });
    }

    function aplicarFiltroLinha() {
        if (!areaFiocruzSelect || !linhaSelect) {
            return;
        }
        var areaId = areaFiocruzSelect.value || '0';
        var atual = linhaSelect.value || '';
        var opcoes = linhasPorArea[areaId] || [];

        linhaSelect.innerHTML = '';
        var optionDefault = document.createElement('option');
        optionDefault.value = '';
        optionDefault.textContent = ' - Selecione a area - ';
        linhaSelect.appendChild(optionDefault);

        opcoes.forEach(function (item) {
            var option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.nome;
            if (String(item.id) === atual) {
                option.selected = true;
            }
            linhaSelect.appendChild(option);
        });
    }

    function anexarValidadorArquivo2MB(el) {
        if (!el) {
            return;
        }
        el.addEventListener('change', function () {
            if (!this.files || !this.files[0]) {
                return;
            }
            if (this.files[0].size > 2097152) {
                alert('Arquivo maior que 2Mb. Selecione outro arquivo.');
                this.value = '';
            }
        });
    }

    if (grandeAreaSelect) {
        grandeAreaSelect.addEventListener('change', aplicarFiltroAreaCnpq);
        aplicarFiltroAreaCnpq();
    }

    if (areaFiocruzSelect) {
        areaFiocruzSelect.addEventListener('change', aplicarFiltroLinha);
        aplicarFiltroLinha();
    }

    document.querySelectorAll('input[type="file"]').forEach(anexarValidadorArquivo2MB);

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
