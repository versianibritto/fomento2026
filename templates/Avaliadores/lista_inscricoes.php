<style>
    .avaliadores-inscricoes-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .avaliadores-inscricao-card {
        border: 1px solid #dfe5ec;
        border-radius: 0.9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    }
    .avaliadores-inscricao-card .card-body {
        padding: 1rem 1rem 0.9rem;
    }
    .avaliadores-inscricao-layout {
        display: grid;
        grid-template-columns: minmax(210px, 0.95fr) minmax(220px, 1fr) minmax(240px, 1.15fr) minmax(210px, 0.8fr);
        gap: 1rem;
        align-items: start;
    }
    .avaliadores-inscricao-topo {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .avaliadores-inscricao-meta {
        color: #667085;
        font-size: 0.92rem;
    }
    .avaliadores-inscricao-bloco {
        border-top: 1px solid #eef2f6;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
    }
    .avaliadores-inscricao-bloco strong {
        color: #344054;
    }
    .avaliadores-inscricao-nomes {
        white-space: pre-line;
        line-height: 1.45;
    }
    .avaliadores-inscricao-acoes {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    @media (max-width: 1200px) {
        .avaliadores-inscricao-layout {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 768px) {
        .avaliadores-inscricao-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Inscrições para Vinculação de Avaliadores</h4>
                    <p class="text-muted mb-3">
                        Listagem de inscrições dos editais de nova e renovação com avaliação aberta, indicando se já possuem avaliadores vinculados.
                    </p>

                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3 align-items-end mb-4',
                    ]) ?>
                        <div class="col-md-4">
                            <?= $this->Form->control('editai_id', [
                                'label' => 'Edital',
                                'options' => $editais,
                                'empty' => 'Todos os editais com avaliação aberta',
                                'default' => $filtros['editai_id'] ?? 0,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('status_vinculo', [
                                'label' => 'Situação de vínculo',
                                'options' => $statusOptions,
                                'empty' => 'Todas',
                                'default' => $filtros['status_vinculo'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('grandes_area_id', [
                                'label' => 'Grande área',
                                'options' => $grandesAreas,
                                'empty' => 'Todas',
                                'default' => $filtros['grandes_area_id'] ?? 0,
                                'class' => 'form-select',
                                'id' => 'grandes-area-id',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('area_id', [
                                'label' => 'Área do projeto',
                                'options' => $areas,
                                'empty' => 'Todas',
                                'default' => $filtros['area_id'] ?? 0,
                                'class' => 'form-select',
                                'id' => 'area-id',
                            ]) ?>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Avaliadores', 'action' => 'listaInscricoes'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if ($inscricoes->count() === 0): ?>
                        <div class="alert alert-info mb-0">
                            Nenhuma inscrição localizada com os filtros informados.
                        </div>
                    <?php else: ?>
                        <?php $this->Paginator->options(['url' => $this->request->getQueryParams()]); ?>

                        <div class="avaliadores-inscricoes-grid">
                            <?php foreach ($inscricoes as $inscricao): ?>
                                <?php
                                $totalAvaliadores = (int)($inscricao->total_avaliadores ?? 0);
                                $avaliadoresNomes = trim((string)($inscricao->avaliadores_nomes ?? ''));
                                $projetoTitulo = trim((string)($inscricao->projeto_titulo ?? ''));
                                if ($projetoTitulo === '') {
                                    $projetoTitulo = trim((string)($inscricao->sp_titulo ?? ''));
                                }
                                if ($projetoTitulo === '') {
                                    $projetoTitulo = 'Não informado';
                                }
                                $grandeAreaNome = trim((string)($inscricao->grande_area_nome ?? ''));
                                if ($grandeAreaNome === '') {
                                    $grandeAreaNome = 'Grande área não informada';
                                }
                                $areaNome = trim((string)($inscricao->area_nome ?? ''));
                                $statusClasse = 'secondary';
                                $statusTexto = 'Não vinculado';
                                if ($totalAvaliadores === 2) {
                                    $statusClasse = 'success';
                                    $statusTexto = 'Vinculado';
                                } elseif ($totalAvaliadores > 0) {
                                    $statusClasse = 'warning text-dark';
                                    $statusTexto = 'Vinculação parcial';
                                }
                                $origemTexto = strtoupper((string)($inscricao->origem ?? '')) === 'R' ? 'Renovação' : 'Nova';
                                ?>
                                <div class="avaliadores-inscricao-card card h-100">
                                    <div class="card-body">
                                        <div class="avaliadores-inscricao-topo">
                                            <div>
                                                <div class="fw-semibold">
                                                    Inscrição #<?= (int)$inscricao->id ?>
                                                </div>
                                                <div class="avaliadores-inscricao-meta">
                                                    <?= h((string)($inscricao->editai->nome ?? 'Edital não informado')) ?>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?= h($statusClasse) ?>"><?= h($statusTexto) ?></span>
                                                <div class="small text-muted mt-1">
                                                    <?= h($origemTexto) ?> | <?= $totalAvaliadores ?> avaliador(es)
                                                </div>
                                            </div>
                                        </div>

                                        <div class="avaliadores-inscricao-layout">
                                            <div>
                                                <strong>Bolsista:</strong>
                                                <div><?= h((string)($inscricao->bolsista_usuario->nome ?? 'Não informado')) ?></div>
                                                <div class="small text-muted">
                                                    CPF: <?= h((string)($inscricao->bolsista_usuario->cpf ?? 'Não informado')) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?php if (!empty($inscricao->bolsista_usuario->unidade->sigla)): ?>
                                                        Unidade: <?= h((string)$inscricao->bolsista_usuario->unidade->sigla) ?>
                                                    <?php else: ?>
                                                        Unidade não informada
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div>
                                                <strong>Orientador:</strong>
                                                <div><?= h((string)($inscricao->orientadore->nome ?? 'Não informado')) ?></div>
                                                <div class="small text-muted">
                                                    <?php if (!empty($inscricao->orientadore->vinculo->nome)): ?>
                                                        <?= h((string)$inscricao->orientadore->vinculo->nome) ?>
                                                    <?php else: ?>
                                                        Vínculo não informado
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?php if (!empty($inscricao->orientadore->unidade->sigla)): ?>
                                                        <?= h((string)$inscricao->orientadore->unidade->sigla) ?>
                                                    <?php else: ?>
                                                        Unidade não informada
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div>
                                                <strong>Projeto / Área:</strong>
                                                <div><?= h($projetoTitulo) ?></div>
                                                <div class="small text-muted">
                                                    <?= h($grandeAreaNome) ?>
                                                    <?php if ($areaNome !== ''): ?>
                                                        | <?= h($areaNome) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div>
                                                <strong>Avaliadores vinculados:</strong>
                                                <?php if ($avaliadoresNomes !== ''): ?>
                                                    <div class="avaliadores-inscricao-nomes">
                                                        <?= h(str_replace(' | ', "\n", $avaliadoresNomes)) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-muted">Nenhum avaliador vinculado até o momento.</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="avaliadores-inscricao-bloco avaliadores-inscricao-acoes">
                                            <?= $this->Html->link(
                                                'Abrir inscrição',
                                                ['controller' => 'Padrao', 'action' => 'visualizar', $inscricao->id],
                                                ['class' => 'btn btn-sm btn-outline-primary']
                                            ) ?>
                                            <?= $this->Html->link(
                                                $totalAvaliadores === 0 ? 'Vincular avaliadores' : 'Substituir avaliadores',
                                                ['controller' => 'Avaliadores', 'action' => 'vincularInscricao', $inscricao->id],
                                                ['class' => 'btn btn-sm btn-outline-secondary']
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-4 d-flex flex-wrap align-items-center gap-2">
                            <?= $this->Paginator->prev('« Anterior', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= $this->Paginator->numbers([
                                'before' => '',
                                'after' => '',
                                'modulus' => 4,
                            ]) ?>
                            <?= $this->Paginator->next('Próxima »', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <span class="ms-2 text-muted">
                                <?= $this->Paginator->counter('Página {{page}} de {{pages}} | Total: {{count}}') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('change', '#grandes-area-id', function () {
    const grandeAreaId = $(this).val();

    if (!grandeAreaId) {
        $('#area-id').html("<option value=''>Todas</option>");
        return;
    }

    $.ajax({
        type: 'POST',
        url: "<?= $this->Url->build(['controller' => 'Avaliadores', 'action' => 'buscaAreas']) ?>",
        data: { id: grandeAreaId },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')) ?>);
            $('#area-id').html("<option value=''>Carregando...</option>");
        },
        success: function(json) {
            let html = "<option value=''>Todas</option>";
            $.each(json, function(_, item) {
                html += `<option value="${item.id}">${item.nome}</option>`;
            });
            $('#area-id').html(html);
        },
        error: function() {
            $('#area-id').html("<option value=''>Erro ao carregar áreas</option>");
        }
    });
});
</script>
