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

            <?= $this->Form->create(null, ['class' => 'row g-3']) ?>
            <div class="alert alert-warning border border-warning-subtle mb-4 sumula-alerta">
                <div class="fw-semibold mb-1">Atenção ao preenchimento da súmula</div>
                <div>Orientadoras com filhos e orientadores egressos do concurso consideram 8 anos de produção. Os demais casos, 5 anos.</div>
                <div>Todos os campos desta tela serão exigidos na finalização da inscrição.</div>
                <div>As súmulas que o orientador não possuir devem ser preenchidas obrigatoriamente com valor 0 (zero).</div>
                <div class="row g-2 align-items-center mt-2">
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
</style>
