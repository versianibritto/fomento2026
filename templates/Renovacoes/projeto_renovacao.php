<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('renovacoes_steps', [
            'edital' => $edital,
            'current' => 'projetoRenovacao',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Dados do projeto do orientador</h3>
                <div class="fw-semibold">Inscrição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">
                    O projeto do orientador não pode ser alterado na renovação.<br>
                    Se houver alguma atualização dos documentos, podem ser inseridos neste formulário.
                </div>
            </div>

            <?= $this->Form->create($projetoSelecionado, ['type' => 'file', 'class' => 'row g-3', 'id' => 'projeto-form']) ?>
                <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
                <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                <?php
                    $naoInformado = 'Não informado';
                    $valorTitulo = trim((string)($projetoSelecionado->titulo ?? ''));
                    $valorGrandeArea = $grandeAreaSelecionada !== null && isset($grandesAreas[$grandeAreaSelecionada])
                        ? (string)$grandesAreas[$grandeAreaSelecionada]
                        : '';
                    $valorAreaCnpq = !empty($projetoSelecionado->area->nome)
                        ? (string)$projetoSelecionado->area->nome
                        : '';
                    $valorAreaFiocruz = $areaFiocruzSelecionada !== null && isset($areasFiocruz[$areaFiocruzSelecionada])
                        ? (string)$areasFiocruz[$areaFiocruzSelecionada]
                        : '';
                    $valorLinhaFiocruz = !empty($projetoSelecionado->linha->nome)
                        ? (string)$projetoSelecionado->linha->nome
                        : '';
                    $valorFinanciadores = trim((string)($projetoSelecionado->financiamento ?? ''));
                    $valorPalavras = trim((string)($projetoSelecionado->palavras_chaves ?? ''));
                    $valorResumo = trim((string)($projetoSelecionado->resumo ?? ''));
                    $rotuloAnexoTipo5 = 'Anexo do projeto';
                    foreach ($anexosTiposProjeto as $tipoProjetoInfo) {
                        if ((int)$tipoProjetoInfo->id === 5) {
                            $rotuloAnexoTipo5 = (string)$tipoProjetoInfo->nome;
                            break;
                        }
                    }
                ?>
                <div class="col-12">
                    <div class="card border mb-2">
                        <div class="card-header bg-light fw-semibold">Informações do projeto</div>
                        <div class="card-body">
                            <div class="row g-3">
                        <div class="col-12">
                            <div class="border rounded p-2 bg-light h-100">
                                <div class="small text-muted">Título do projeto</div>
                                <div><?= h($valorTitulo !== '' ? $valorTitulo : $naoInformado) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($podePreencherAreaCnpq)) : ?>
                                <?= $this->Form->control('grande_area_id_aux', [
                                    'label' => 'Grande área CNPQ',
                                    'type' => 'select',
                                    'options' => $grandesAreas,
                                    'empty' => ' - Selecione - ',
                                    'class' => 'form-select campo-vazio',
                                    'id' => 'grande-area-id',
                                ]) ?>
                            <?php else : ?>
                                <div class="border rounded p-2 bg-light h-100">
                                    <div class="small text-muted">Grande área CNPQ</div>
                                    <div><?= h($valorGrandeArea !== '' ? $valorGrandeArea : $naoInformado) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($podePreencherAreaCnpq)) : ?>
                                <?= $this->Form->control('area_id', [
                                    'label' => 'Área CNPQ',
                                    'type' => 'select',
                                    'options' => [],
                                    'empty' => ' - Selecione a grande área - ',
                                    'class' => 'form-select campo-vazio',
                                    'id' => 'area-id',
                                ]) ?>
                            <?php else : ?>
                                <div class="border rounded p-2 bg-light h-100">
                                    <div class="small text-muted">Área CNPQ</div>
                                    <div><?= h($valorAreaCnpq !== '' ? $valorAreaCnpq : $naoInformado) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($podePreencherLinhaFiocruz)) : ?>
                                <?= $this->Form->control('area_fiocruz_id_aux', [
                                    'label' => 'Área de pesquisa FIOCRUZ',
                                    'type' => 'select',
                                    'options' => $areasFiocruz,
                                    'empty' => ' - Selecione - ',
                                    'class' => 'form-select campo-vazio',
                                    'id' => 'area-fiocruz-id',
                                ]) ?>
                            <?php else : ?>
                                <div class="border rounded p-2 bg-light h-100">
                                    <div class="small text-muted">Área de pesquisa FIOCRUZ</div>
                                    <div><?= h($valorAreaFiocruz !== '' ? $valorAreaFiocruz : $naoInformado) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($podePreencherLinhaFiocruz)) : ?>
                                <?= $this->Form->control('linha_id', [
                                    'label' => 'Linha de pesquisa FIOCRUZ',
                                    'type' => 'select',
                                    'options' => [],
                                    'empty' => ' - Selecione a área - ',
                                    'class' => 'form-select campo-vazio',
                                    'id' => 'linha-id',
                                ]) ?>
                            <?php else : ?>
                                <div class="border rounded p-2 bg-light h-100">
                                    <div class="small text-muted">Linha de pesquisa FIOCRUZ</div>
                                    <div><?= h($valorLinhaFiocruz !== '' ? $valorLinhaFiocruz : $naoInformado) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-light h-100">
                                <div class="small text-muted">Instituições financiadoras</div>
                                <div><?= h($valorFinanciadores !== '' ? $valorFinanciadores : $naoInformado) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-light h-100">
                                <div class="small text-muted">Palavras-chave</div>
                                <div><?= h($valorPalavras !== '' ? $valorPalavras : $naoInformado) ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <?php if (!empty($podePreencherResumoProjeto)) : ?>
                                <?= $this->Form->control('resumo', [
                                    'label' => 'Resumo do projeto (máx. 4 mil caracteres)',
                                    'type' => 'textarea',
                                    'class' => 'form-control campo-vazio',
                                    'rows' => 5,
                                    'maxlength' => 4000,
                                    'required' => true,
                                    'value' => '',
                                ]) ?>
                            <?php else : ?>
                                <details class="border rounded p-2 bg-light">
                                    <summary class="fw-semibold">Resumo do projeto</summary>
                                    <div class="mt-2" style="white-space: pre-line;"><?= h($valorResumo !== '' ? $valorResumo : $naoInformado) ?></div>
                                </details>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="anexos-areas">
                                <?php if (empty($anexos[5])) : ?>
                                    <?= $this->Form->control('anexos[5]', [
                                        'type' => 'file',
                                        'label' => h($rotuloAnexoTipo5),
                                        'class' => 'form-control',
                                    ]) ?>
                                <?php else : ?>
                                    <label class="form-label d-block"><?= h($rotuloAnexoTipo5) ?></label>
                                    <div class="anexo-arquivo-atual">
                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                            <div class="small text-muted text-truncate">
                                                <?= h($anexos[5]) ?>
                                            </div>
                                            <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                <a href="/uploads/anexos/<?= h($anexos[5]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info border h-100 mb-0">
                                <?php if (!empty($anexos[5])) : ?>
                                    O arquivo existente não pode ser alterado, mas pode ser baixado clicando no ícone.
                                <?php else : ?>
                                    Este anexo será exigido e este campo não poderá ser alterado, mesmo na fase de Rascunho. Após inserido, não poderá ser alterado.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <h6 class="fw-semibold mb-1">Anexos</h6>
                    <p class="text-muted small mb-3">
                        Os anexos abaixo não são obrigatórios para concluir a inscrição no sistema, mas serão analisados na avaliação.
                        Se o seu projeto exigir algum desses documentos e ele não for apresentado, o avaliador poderá registrar ausência documental, com impacto no resultado (inclusive reprovação).
                        Como essa necessidade depende do conteúdo do projeto, a verificação é feita na avaliação humana.
                        <br>
                        Quando houver anexos mais recentes de inscrições anteriores, eles já são carregados nesta tela e podem ser baixados, atualizados ou excluídos.
                    </p>
                    <div class="row g-3 anexos-areas">
                        <?php foreach ($anexosTiposProjeto as $tipo) : ?>
                            <?php
                                $tipoId = (int)$tipo->id;
                                $rotulo = (string)$tipo->nome;
                                if ($tipoId === 5) {
                                    continue;
                                }
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
        optionDefault.textContent = ' - Selecione a grande área - ';
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
        optionDefault.textContent = ' - Selecione a área - ';
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
