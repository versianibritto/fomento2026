<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('inscricoes_steps', [
            'edital' => $edital,
            'current' => 'sumula',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Súmula do orientador</h3>
                <div class="fw-semibold">Inscrição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Rascunho: nenhum campo é obrigatório nesta etapa.</div>
            </div>

            <?= $this->Form->create(null, ['type' => 'file', 'class' => 'row g-3']) ?>
            <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
            <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
            <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
            <div class="alert alert-warning border border-warning-subtle mb-4 sumula-alerta">
                <div class="fw-semibold mb-1">Atenção ao preenchimento da súmula</div>
                <?php $programaId = (int)($edital->programa_id ?? 0); ?>
                <?php if ($programaId === 3) : ?>
                    <div>Orientadoras com filhos; orientadores recém-doutores; orientadores que tenham ingressado na Fiocruz por meio dos concursos de 2016 e 2024; ou orientadores vinculados às unidades de produção (FAR, BIO) e ao INCQS, considerar a produção científica e tecnológica dos últimos 8 anos.</div>
                    <div>Para os demais, será considerada a produção científica e tecnológica dos últimos 5 anos.</div>
                    <div>OBS. Detalhes no anexo II do Edital.</div>
                <?php else : ?>
                    <div>Orientadoras com filhos; Orientadores recém-doutores; ou orientadores que tenham ingressado na Fiocruz por meio dos concursos de 2016 e 2024, considerar a produção científica e tecnológica dos últimos 8 anos.</div>
                    <div>Para os demais, será considerada a produção científica e tecnológica dos últimos 5 anos.</div>
                    <div>OBS. Detalhes no anexo II do Edital</div>
                <?php endif; ?>
                <div class="mt-3">Todos os campos desta tela serão exigidos na finalização da inscrição.</div>
                <div>As súmulas que o orientador não possuir devem ser preenchidas obrigatoriamente com valor 0 (zero).</div>
                <?php $filhosValor = (int)($inscricao->filhos_menor ?? 0); ?>
                <?php $recemServidorValor = (int)($inscricao->recem_servidor ?? 0); ?>
                <?php $sexoLogado = strtoupper(trim((string)($this->request->getAttribute('identity')['sexo'] ?? ''))); ?>
                <div class="card border border-warning-subtle bg-white mt-3">
                    <div class="card-body py-3">
                        <?php if ($sexoLogado === 'F') : ?>
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    <label for="filhos-menor" class="form-label mb-0">Orientadora, possui filhos menores de 5 anos?</label>
                                </div>
                                <div class="col-md-6">
                                    <?= $this->Form->control('filhos_menor', [
                                        'label' => false,
                                        'id' => 'filhos-menor',
                                        'type' => 'select',
                                        'class' => 'form-select',
                                        'empty' => 'Selecione',
                                        'value' => $inscricao->filhos_menor ?? null,
                                        'options' => [
                                            '0' => 'Não possuo filhos, ou são maiores de 5 anos',
                                            '1' => 'Possuo um filho menor de 5 anos',
                                            '2' => 'Mais de um filho menor de 5 anos',
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                            <div class="row g-2 align-items-center mt-2 anexos-areas" id="bloco-anexo-filhos" style="display: <?= $filhosValor > 0 ? 'flex' : 'none' ?>;">
                                <div class="col-md-6">
                                    <label for="anexo-27" class="form-label mb-0"><?= h($anexoTipo27Nome ?? 'Anexo') ?></label>
                                </div>
                                <div class="col-md-6">
                                    <?php if (empty($anexoFilhosMenor->anexo)) : ?>
                                        <?= $this->Form->control('anexos.27', [
                                            'label' => false,
                                            'id' => 'anexo-27',
                                            'type' => 'file',
                                            'class' => 'form-control',
                                            'required' => false,
                                        ]) ?>
                                    <?php else : ?>
                                        <div class="anexo-arquivo-atual">
                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                <div class="small text-muted text-truncate">
                                                    <?= h($anexoFilhosMenor->anexo) ?>
                                                </div>
                                                <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                    <a href="/uploads/anexos/<?= h($anexoFilhosMenor->anexo) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                    <label for="anexo-27" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                        <i class="fa fa-edit"></i>
                                                    </label>
                                                    <button
                                                        type="button"
                                                        class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                        onclick="confirmarExclusaoAnexo(27, this.form)"
                                                        title="Excluir"
                                                    >
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <input
                                                id="anexo-27"
                                                name="anexos[27]"
                                                type="file"
                                                class="d-none anexo-file"
                                                data-tipo="27"
                                            >
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row g-2 align-items-center mt-2">
                            <div class="col-md-6">
                                <label for="ano-doutorado" class="form-label mb-0">Ano de conclusão do doutorado</label>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('ano_doutorado', [
                                    'label' => false,
                                    'id' => 'ano-doutorado',
                                    'type' => 'number',
                                    'class' => 'form-control',
                                    'min' => 1900,
                                    'max' => (int)date('Y'),
                                    'step' => 1,
                                    'value' => $inscricao->ano_doutorado ?? null,
                                ]) ?>
                            </div>
                        </div>
                        <div class="row g-2 align-items-center mt-2">
                            <div class="col-md-6">
                                <label for="recem-servidor" class="form-label mb-0">Você ingressou na Fiocruz por meio dos concursos de 2016 e 2024?</label>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('recem_servidor', [
                                    'label' => false,
                                    'id' => 'recem-servidor',
                                    'type' => 'select',
                                    'class' => 'form-select',
                                    'empty' => 'Selecione',
                                    'value' => $inscricao->recem_servidor ?? null,
                                    'options' => [
                                        '1' => 'Sim, incluirei o anexo do DO',
                                        '0' => 'Não',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                        <div class="row g-2 align-items-center mt-2 anexos-areas" id="bloco-anexo-recem-servidor" style="display: <?= $recemServidorValor === 1 ? 'flex' : 'none' ?>;">
                            <div class="col-md-6">
                                <label for="anexo-29" class="form-label mb-0"><?= h($anexoTipo29Nome ?? 'Anexo') ?></label>
                            </div>
                            <div class="col-md-6">
                                <?php if (empty($anexoRecemServidor->anexo)) : ?>
                                    <?= $this->Form->control('anexos.29', [
                                        'label' => false,
                                        'id' => 'anexo-29',
                                        'type' => 'file',
                                        'class' => 'form-control',
                                        'required' => false,
                                    ]) ?>
                                <?php else : ?>
                                    <div class="anexo-arquivo-atual">
                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                            <div class="small text-muted text-truncate">
                                                <?= h($anexoRecemServidor->anexo) ?>
                                            </div>
                                            <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                <a href="/uploads/anexos/<?= h($anexoRecemServidor->anexo) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                                <label for="anexo-29" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                    <i class="fa fa-edit"></i>
                                                </label>
                                                <button
                                                    type="button"
                                                    class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                    onclick="confirmarExclusaoAnexo(29, this.form)"
                                                    title="Excluir"
                                                >
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <input
                                            id="anexo-29"
                                            name="anexos[29]"
                                            type="file"
                                            class="d-none anexo-file"
                                            data-tipo="29"
                                        >
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <div class="col-12">
                    <?php if (!empty($sumulasEdital) && count($sumulasEdital) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 sumula-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Súmula</th>
                                        <th>Parâmetro</th>
                                        <th style="width: 140px;">Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sumulasEdital as $idx => $sumula) : ?>
                                        <?php $qtdSalva = $quantidadesSalvas[(int)$sumula->id] ?? null; ?>
                                        <tr>
                                            <td class="sumula-col-texto"><?= h($sumula->sumula) ?></td>
                                            <td class="sumula-col-texto"><?= h($sumula->parametro) ?></td>
                                            <td>
                                                <input type="hidden" name="sumula[<?= (int)$idx ?>][editais_sumula_id]" value="<?= (int)$sumula->id ?>">
                                                <input
                                                    type="number"
                                                    name="sumula[<?= (int)$idx ?>][quantidade]"
                                                    min="0"
                                                    max="50"
                                                    step="1"
                                                    class="form-control sumula-qtd<?= $qtdSalva === null ? ' sumula-qtd-vazio' : '' ?>"
                                                    value="<?= $qtdSalva !== null ? (int)$qtdSalva : '' ?>"
                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-warning mb-0">
                            Não há itens de súmula ativos configurados para este edital.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 d-flex justify-content-end align-items-center">
                    <?= $this->Form->button('Salvar Rascunho e Continuar', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
<style>
.sumula-table {
    table-layout: fixed;
    width: 100%;
}
.sumula-alerta {
    border: 1px solid #ffe69c;
    background: #fff8db;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, .15);
}
.sumula-col-texto {
    white-space: normal;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.sumula-qtd-vazio {
    border-color: #dc3545 !important;
    background-color: #fff5f5;
}
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
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('filhos-menor');
    const bloco = document.getElementById('bloco-anexo-filhos');
    const input = document.getElementById('anexo-27');
    const selectRecem = document.getElementById('recem-servidor');
    const blocoRecem = document.getElementById('bloco-anexo-recem-servidor');
    const inputRecem = document.getElementById('anexo-29');
    if (select && bloco) {
        const toggle = () => {
            const val = parseInt(select.value || '0', 10);
            bloco.style.display = val > 0 ? 'flex' : 'none';
            if (val <= 0 && input) {
                input.value = '';
            }
        };
        select.addEventListener('change', toggle);
        toggle();
    }
    const toggleRecem = () => {
        if (!selectRecem || !blocoRecem) {
            return;
        }
        const val = parseInt(selectRecem.value || '0', 10);
        blocoRecem.style.display = val === 1 ? 'flex' : 'none';
        if (val !== 1 && inputRecem) {
            inputRecem.value = '';
        }
    };
    if (selectRecem) {
        selectRecem.addEventListener('change', toggleRecem);
    }
    toggleRecem();
});

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
