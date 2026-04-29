<style>
    .avaliadores-inscricoes-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .avaliadores-inscricao-card {
        border: 1px solid #9fb0c3;
        border-radius: 0.9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.09);
    }
    .avaliadores-inscricao-card--pendente-homologacao {
        border-color: #dc3545;
        background: linear-gradient(180deg, #fff5f5 0%, #fffafa 100%);
        box-shadow: 0 10px 22px rgba(220, 53, 69, 0.12);
    }
    .avaliadores-inscricao-card .card-body {
        padding: 1rem 1rem 0.9rem;
    }
    .avaliadores-inscricao-layout {
        display: grid;
        grid-template-columns: minmax(280px, 1.2fr) minmax(220px, 0.9fr) minmax(280px, 1.15fr);
        gap: 1rem;
        align-items: start;
    }
    .avaliadores-inscricao-topo {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .avaliadores-inscricao-meta {
        color: #667085;
        font-size: 0.92rem;
    }
    .avaliadores-inscricao-topo-acoes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .avaliadores-inscricao-bloco {
        border: 1px solid #e7edf4;
        border-radius: 0.8rem;
        background: rgba(255, 255, 255, 0.85);
        padding: 0.85rem 0.9rem;
        min-height: 100%;
    }
    .avaliadores-inscricao-bloco-titulo {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #667085;
        margin-bottom: 0.55rem;
    }
    .avaliadores-inscricao-bloco strong {
        color: #344054;
    }
    .avaliadores-inscricao-linha {
        margin-bottom: 0.55rem;
        line-height: 1.35;
    }
    .avaliadores-inscricao-linha:last-child {
        margin-bottom: 0;
    }
    .avaliadores-inscricao-linha-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #667085;
        margin-bottom: 0.15rem;
    }
    .avaliadores-inscricao-linha-valor {
        color: #101828;
        font-weight: 500;
    }
    .avaliadores-inscricao-avaliadores {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }
    .avaliadores-inscricao-avaliador-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        padding: 0.45rem 0.55rem;
        border: 1px solid #e9eef5;
        border-radius: 0.65rem;
        background: #fff;
    }
    .avaliadores-inscricao-avaliador-nome {
        color: #101828;
        font-weight: 500;
    }
    .avaliadores-inscricao-avaliador-status {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .avaliadores-inscricao-status-bolinha {
        width: 0.8rem;
        height: 0.8rem;
        border-radius: 999px;
        display: inline-block;
    }
    .avaliadores-inscricao-status-bolinha--finalizado {
        background: #198754;
    }
    .avaliadores-inscricao-status-bolinha--aguardando {
        background: #f0ad4e;
    }
    .avaliadores-inscricao-status-bolinha--desvinculado {
        background: #dc3545;
    }
    .avaliadores-inscricao-acoes {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    @media (max-width: 1200px) {
        .avaliadores-inscricao-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .avaliadores-inscricao-topo {
            flex-direction: column;
        }
        .avaliadores-inscricao-topo-acoes {
            width: 100%;
            justify-content: flex-start;
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
                        Listagem de inscrições dos editais de nova e renovação com avaliação aberta.<br>
                        Listadas apenas inscrições finalizadas.<br>
                        Podem ser listadas inscrições homologadas, não homologadas e não verificadas.<br>
                        <strong class="text-danger">Inscrições não verificadas não poderão ser vinculadas.</strong>
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
                        <div class="col-md-4">
                            <?= $this->Form->control('homologado', [
                                'label' => 'Homologação',
                                'options' => $homologadoOptions,
                                'empty' => 'Todos',
                                'default' => $filtros['homologado'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $this->Form->control('status_vinculo', [
                                'label' => 'Situação de vínculo',
                                'options' => $statusOptions,
                                'empty' => 'Todas',
                                'default' => $filtros['status_vinculo'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="w-100"></div>
                        <div class="col-md-4">
                            <?= $this->Form->control('grandes_area_id', [
                                'label' => 'Grande área',
                                'options' => $grandesAreas,
                                'empty' => 'Todas',
                                'default' => $filtros['grandes_area_id'] ?? 0,
                                'class' => 'form-select',
                                'id' => 'grandes-area-id',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
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
                                ['controller' => 'Listas', 'action' => 'listaInscricoesAvaliadores'],
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
                                if ($areaNome === '') {
                                    $areaNome = 'Área não informada';
                                }
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
                                $homologadoValor = strtoupper((string)($inscricao->homologado ?? ''));
                                $homologadoPendente = $homologadoValor === '';
                                if ($homologadoValor === 'S') {
                                    $homologadoTexto = 'Homologado: Sim (S)';
                                    $homologadoClasse = 'success';
                                } elseif ($homologadoValor === 'N') {
                                    $homologadoTexto = 'Homologado: Não (N)';
                                    $homologadoClasse = 'danger';
                                } else {
                                    $homologadoTexto = 'Homologado: ainda não olhado';
                                    $homologadoClasse = 'danger';
                                }
                                $avaliadoresLista = [];
                                $avaliadoresStatusBruto = trim((string)($inscricao->avaliadores_status ?? ''));
                                if ($avaliadoresStatusBruto !== '') {
                                    $itensAvaliadores = array_values(array_filter(array_map('trim', explode(' | ', $avaliadoresStatusBruto))));
                                    foreach ($itensAvaliadores as $itemAvaliador) {
                                        $partesAvaliador = array_map('trim', explode('||', $itemAvaliador));
                                        $nomeAvaliador = (string)($partesAvaliador[0] ?? '');
                                        $situacaoAvaliador = (string)($partesAvaliador[1] ?? '');

                                        if ($nomeAvaliador === '') {
                                            continue;
                                        }

                                        $statusClasseAvaliador = 'aguardando';
                                        if ($situacaoAvaliador === 'F') {
                                            $statusClasseAvaliador = 'finalizado';
                                        }

                                        $avaliadoresLista[] = [
                                            'nome' => $nomeAvaliador,
                                            'status_classe' => $statusClasseAvaliador,
                                        ];
                                    }
                                }
                                ?>
                                <div class="avaliadores-inscricao-card card h-100<?= $homologadoPendente ? ' avaliadores-inscricao-card--pendente-homologacao' : '' ?>">
                                    <div class="card-body">
                                        <div class="avaliadores-inscricao-topo">
                                            <div>
                                                <div class="fw-semibold">
                                                    Inscrição #<?= (int)$inscricao->id ?>
                                                </div>
                                                <div class="avaliadores-inscricao-meta">
                                                    <?= h((string)($inscricao->editai->nome ?? 'Edital não informado')) ?>
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    <?= h($origemTexto) ?> | <?= h($statusTexto) ?>
                                                </div>
                                            </div>
                                            <div class="avaliadores-inscricao-topo-acoes">
                                                <span class="badge bg-<?= h($homologadoClasse) ?>"><?= h($homologadoTexto) ?></span>
                                                <span class="badge bg-<?= h($statusClasse) ?>"><?= h($statusTexto) ?></span>
                                                <?= $this->Html->link(
                                                    'Abrir inscrição',
                                                    ['controller' => 'Padrao', 'action' => 'visualizar', $inscricao->id],
                                                    ['class' => 'btn btn-sm btn-outline-primary']
                                                ) ?>
                                                <?= $this->Html->link(
                                                    $totalAvaliadores === 0 ? 'Vincular avaliadores' : 'Gerenciar avaliadores',
                                                    ['controller' => 'Avaliadores', 'action' => 'vincularInscricao', $inscricao->id],
                                                    ['class' => 'btn btn-sm btn-outline-secondary']
                                                ) ?>
                                            </div>
                                        </div>

                                        <div class="avaliadores-inscricao-layout">
                                            <div class="avaliadores-inscricao-bloco">
                                                <div class="avaliadores-inscricao-bloco-titulo">Pessoas</div>
                                                <div class="avaliadores-inscricao-linha">
                                                    <span class="avaliadores-inscricao-linha-label">Orientador</span>
                                                    <div class="avaliadores-inscricao-linha-valor">
                                                        <?= h((string)($inscricao->orientadore->nome ?? 'Não informado')) ?>
                                                    </div>
                                                </div>
                                                <div class="avaliadores-inscricao-linha">
                                                    <span class="avaliadores-inscricao-linha-label">Bolsista</span>
                                                    <div class="avaliadores-inscricao-linha-valor">
                                                        <?= h((string)($inscricao->bolsista_usuario->nome ?? 'Não informado')) ?>
                                                    </div>
                                                </div>
                                                <div class="avaliadores-inscricao-linha">
                                                    <span class="avaliadores-inscricao-linha-label">Coorientador</span>
                                                    <div class="avaliadores-inscricao-linha-valor">
                                                        <?= h((string)($inscricao->coorientadore->nome ?? 'Não informado')) ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="avaliadores-inscricao-bloco">
                                                <div class="avaliadores-inscricao-bloco-titulo">Áreas</div>
                                                <div class="avaliadores-inscricao-linha">
                                                    <span class="avaliadores-inscricao-linha-label">Grande área</span>
                                                    <div class="avaliadores-inscricao-linha-valor"><?= h($grandeAreaNome) ?></div>
                                                </div>
                                                <div class="avaliadores-inscricao-linha">
                                                    <span class="avaliadores-inscricao-linha-label">Área</span>
                                                    <div class="avaliadores-inscricao-linha-valor"><?= h($areaNome) ?></div>
                                                </div>
                                                <div class="avaliadores-inscricao-linha">
                                                    <span class="avaliadores-inscricao-linha-label">Projeto</span>
                                                    <div class="avaliadores-inscricao-linha-valor"><?= h($projetoTitulo) ?></div>
                                                </div>
                                            </div>

                                            <div class="avaliadores-inscricao-bloco">
                                                <div class="avaliadores-inscricao-bloco-titulo">Avaliadores vinculados</div>
                                                <?php if (!empty($avaliadoresLista)): ?>
                                                    <div class="avaliadores-inscricao-avaliadores">
                                                        <?php foreach ($avaliadoresLista as $avaliadorNome): ?>
                                                            <div class="avaliadores-inscricao-avaliador-item">
                                                                <span class="avaliadores-inscricao-avaliador-nome"><?= h((string)$avaliadorNome['nome']) ?></span>
                                                                <span class="avaliadores-inscricao-avaliador-status">
                                                                    <span class="avaliadores-inscricao-status-bolinha avaliadores-inscricao-status-bolinha--<?= h((string)$avaliadorNome['status_classe']) ?>"></span>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-muted">Nenhum avaliador vinculado até o momento.</div>
                                                <?php endif; ?>
                                            </div>
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
