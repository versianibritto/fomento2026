<?php
$naoInformado = '<span class="badge bg-danger">Não informado</span>';
$fmtDateTime = function ($dt) use ($naoInformado) {
    return $dt ? $dt->i18nFormat('dd/MM/Y HH:mm:ss') : $naoInformado;
};

$origemLabel = (!empty($edital->origem) && isset($origem[$edital->origem]))
    ? h($origem[$edital->origem])
    : $naoInformado;

if ($edital->visualizar === 'E') {
    $visualizacaoLabel = '<span class="badge bg-success">Externo</span>';
} elseif ($edital->visualizar === 'I') {
    $visualizacaoLabel = '<span class="badge bg-danger">Interno</span>';
} else {
    $visualizacaoLabel = $naoInformado;
}

$programaNome = null;
if (!empty($edital->programa) && !empty($edital->programa->nome)) {
    $programaNome = $edital->programa->nome;
} elseif (!empty($edital->programa_id) && !empty($programas[$edital->programa_id])) {
    $programaNome = $programas[$edital->programa_id];
}
$programaLabel = $programaNome ? h($programaNome) : $naoInformado;

$usuarioNome = !empty($edital->usuario) ? h($edital->usuario->nome) : '** Cadastro em banco';
$createdLabel = $edital->created
    ? $edital->created->i18nFormat('dd/MM/Y HH:mm:ss')
    : 'Legado';
$modifiedLabel = $edital->modified
    ? $edital->modified->i18nFormat('dd/MM/Y HH:mm:ss')
    : 'Legado';
?>

<div class="container-fluid p-1 pt-1">
    <div class="d-flex flex-wrap align-items-baseline justify-content-between">
        <div>
            <h2 class="mt-2 mb-1">Edital #<?= h($edital->id) ?></h2>
            <div class="text-muted"><?= h($edital->nome ?? 'Não informado') ?></div>
        </div>
        <div class="mt-2 d-flex flex-wrap gap-2">
            <?= $visualizacaoLabel ?>
            <span class="badge bg-<?= $edital->deleted ? 'secondary' : 'success' ?>">
                <?= $edital->deleted ? 'Inativo' : 'Ativo' ?>
            </span>
        </div>
    </div>

    <?php if (in_array($this->request->getAttribute('identity')['id'], [1, 8088], true)) { ?>
        <div class="row mb-3 mt-2">
            <div class="col-12 d-flex flex-wrap gap-2">
                <?= $this->Html->link(
                    '<i class="fas fa-edit me-1"></i> Editar',
                    ['controller' => 'Editais', 'action' => 'gravar', $edital->id],
                    ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-calendar-plus me-1"></i> Cadastrar prazos',
                    ['controller' => 'Editais', 'action' => 'prazos', $edital->id],
                    ['class' => 'btn btn-sm btn-secondary', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-list-alt me-1"></i> Cadastrar Quesitos',
                    ['controller' => 'Editais', 'action' => 'quesitosadd', $edital->id],
                    ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-clipboard-list me-1"></i> Cadastrar Sumulas',
                    ['controller' => 'Editais', 'action' => 'sumulasadd', $edital->id],
                    ['class' => 'btn btn-sm btn-secondary', 'escape' => false]
                ) ?>
            </div>
        </div>
    <?php } ?>

    <div class="card card-primary card-outline">
        <div class="card-body">
            <h5 class="text-primary-emphasis mb-2 fw-semibold">
                <i class="fas fa-info-circle me-2"></i>Dados gerais
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <strong>Programa:</strong> <?= $programaLabel ?>
                </div>
                <div class="col-md-6">
                    <strong>Ingresso:</strong> <?= $origemLabel ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Visualização:</strong> <?= $visualizacaoLabel ?>
                </div>
                <div class="col-md-6">
                    <strong>Link:</strong> <?= !empty($edital->link) ? h($edital->link) : $naoInformado ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Evento:</strong> <?= !empty($edital->evento) ? h($edital->evento) : $naoInformado ?>
                </div>
                <div class="col-md-6">
                    <strong>Limitar ano de doutorado:</strong>
                    <?= $edital->limitaAnoDoutorado === null ? 'Não limitar' : h($edital->limitaAnoDoutorado) ?>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-primary-emphasis mb-2 fw-semibold">
                <i class="fas fa-paperclip me-2"></i>Arquivos
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <strong>Edital:</strong>
                    <?php if ($edital->arquivo) { ?>
                        <a href="/uploads/editais/<?= h($edital->arquivo) ?>" target="_blank"><?= h($edital->arquivo) ?></a>
                    <?php } else { ?>
                        <?= $naoInformado ?>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <strong>Resultado:</strong>
                    <?php if ($edital->resultado_arquivo) { ?>
                        <a href="/uploads/editais/<?= h($edital->resultado_arquivo) ?>" target="_blank"><?= h($edital->resultado_arquivo) ?></a>
                        <?= $this->Html->link(
                            'Alterar / excluir',
                            ['controller' => 'Editais', 'action' => 'lancarresultado', $edital->id],
                            ['class' => 'btn btn-sm btn-outline-primary ms-2']
                        ) ?>
                    <?php } else { ?>
                        <?= $this->Html->link(
                            'Cadastrar resultado',
                            ['controller' => 'Editais', 'action' => 'lancarresultado', $edital->id],
                            ['class' => 'btn btn-sm btn-outline-primary']
                        ) ?>
                    <?php } ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6 mb-2">
                    <strong>Modelo consentimento bolsista:</strong>
                    <?php if ($edital->modelo_cons_bols) { ?>
                        <a href="/uploads/editais/<?= h($edital->modelo_cons_bols) ?>" target="_blank"><?= h($edital->modelo_cons_bols) ?></a>
                    <?php } else { ?>
                        <?= $naoInformado ?>
                    <?php } ?>
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Modelo consentimento coorientador:</strong>
                    <?php if ($edital->modelo_cons_coor) { ?>
                        <a href="/uploads/editais/<?= h($edital->modelo_cons_coor) ?>" target="_blank"><?= h($edital->modelo_cons_coor) ?></a>
                    <?php } else { ?>
                        <?= $naoInformado ?>
                    <?php } ?>
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Modelo relatório bolsista:</strong>
                    <?php if ($edital->modelo_relat_bols) { ?>
                        <a href="/uploads/editais/<?= h($edital->modelo_relat_bols) ?>" target="_blank"><?= h($edital->modelo_relat_bols) ?></a>
                    <?php } else { ?>
                        <?= $naoInformado ?>
                    <?php } ?>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-primary-emphasis mb-2 fw-semibold">
                <i class="fas fa-calendar-alt me-2"></i>Prazos
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <strong>Divulgação:</strong> <?= $fmtDateTime($edital->divulgacao) ?>
                </div>
                <div class="col-md-6">
                    <strong>Resultado:</strong> <?= $fmtDateTime($edital->resultado) ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Início das Inscrições:</strong> <?= $fmtDateTime($edital->inicio_inscricao) ?>
                </div>
                <div class="col-md-6">
                    <strong>Fim das Inscrições:</strong> <?= $fmtDateTime($edital->fim_inscricao) ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Início da Avaliação:</strong> <?= $fmtDateTime($edital->inicio_avaliar) ?>
                </div>
                <div class="col-md-6">
                    <strong>Fim da Avaliação:</strong> <?= $fmtDateTime($edital->fim_avaliar) ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Início do Recurso:</strong> <?= $fmtDateTime($edital->inicio_recurso) ?>
                </div>
                <div class="col-md-6">
                    <strong>Fim do Recurso:</strong> <?= $fmtDateTime($edital->fim_recurso) ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Início da Vigência:</strong> <?= $fmtDateTime($edital->inicio_vigencia) ?>
                </div>
                <div class="col-md-6">
                    <strong>Fim da Vigência:</strong> <?= $fmtDateTime($edital->fim_vigencia) ?>
                </div>
            </div>


            <hr class="my-4">

            <hr>

            <h5 class="text-primary-emphasis mb-2 fw-semibold">
                <i class="fas fa-lock me-2"></i>Restrições
            </h5>
            <div class="row">
                <div class="col-md-3">
                    <strong>Unidades restritas:</strong>
                    <div class="mt-1">
                        <?php if (empty($edital->unidades_permitidas)) { ?>
                            <span class="badge bg-secondary">Não</span>
                        <?php } else { ?>
                            <?php foreach ($unidades as $uni) { ?>
                                <span class="badge bg-light text-dark border me-1 mb-1"><?= h($uni->sigla) ?></span>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <strong>Vínculos restritos:</strong>
                    <div class="mt-1">
                        <?php if (empty($edital->vinculos_permitidos)) { ?>
                            <span class="badge bg-secondary">Não</span>
                        <?php } else { ?>
                            <?php foreach ($vinculos as $vn) { ?>
                                <span class="badge bg-light text-dark border me-1 mb-1"><?= h($vn->nome) ?></span>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <strong>Escolaridade restrita:</strong>
                    <div class="mt-1">
                        <?php if (empty($edital->escolaridades_permitidas)) { ?>
                            <span class="badge bg-secondary">Não</span>
                        <?php } else { ?>
                            <?php foreach ($escolaridades as $esc) { ?>
                                <span class="badge bg-light text-dark border me-1 mb-1"><?= h($esc->nome) ?></span>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <strong>CPFs permitidos (curadores):</strong>
                    <div class="mt-1">
                        <?php if (empty($edital->cpf_permitidos)) { ?>
                            <span class="badge bg-secondary">Não</span>
                        <?php } else { ?>
                            <?php
                            $cpfs = preg_split('/[\s,;]+/', (string)$edital->cpf_permitidos, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($cpfs as $cpf) {
                                echo '<span class="badge bg-light text-dark border me-1 mb-1">' . h($cpf) . '</span>';
                            }
                            ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <hr>

            <div class="text-muted small">
                Cadastrado em <?= h($createdLabel) ?> por <?= $usuarioNome ?>
                <span class="mx-2">|</span>
                Última alteração: <?= h($modifiedLabel) ?>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h5 class="text-primary-emphasis mb-0 fw-semibold">
                    <i class="fas fa-list-alt me-2"></i>Quesitos de Avaliação
                </h5>
                <?= $this->Html->link(
                    '<i class="fas fa-list-alt me-1"></i> Cadastrar Quesitos',
                    ['controller' => 'Editais', 'action' => 'quesitosadd', $edital->id],
                    ['class' => 'btn btn-sm btn-outline-info', 'escape' => false]
                ) ?>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle sortable-table">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" style="width: 45px;">#</th>
                            <th class="sortable">Quesito</th>
                            <th class="sortable">Parâmetros</th>
                            <th class="sortable" style="width: 90px;">Limites</th>
                            <th class="sortable" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($edital->questions)) { ?>
                            <?php foreach ($edital->questions as $index => $quesito) { ?>
                                <tr>
                                    <td><?= $quesito->id ?></td>
                                    <td><?= ($index + 1 . ' - ' . $quesito->questao) ?></td>
                                    <td><?= ($quesito->prametros) ?></td>
                                    <td><?= ($quesito->limite_min) . ' - ' . ($quesito->limite_max) ?></td>
                                    <td class="text-end">
                                        <?php if (in_array($this->request->getAttribute('identity')['id'], [1, 8088], true)) { ?>
                                            <?php if ($quesito->deleted == 0) { ?>
                                                <?= $this->Html->link(
                                                    '<i class="fas fa-edit"></i>',
                                                    ['action' => 'quesitosedit', $quesito->id],
                                                    ['class' => 'btn btn-outline-primary btn-sm me-1', 'escape' => false, 'title' => 'Editar']
                                                ) ?>
                                                <?= $this->Form->postLink(
                                                    '<i class="fas fa-trash"></i>',
                                                    ['action' => 'quesitosdelete', $quesito->id],
                                                    ['confirm' => 'Tem certeza que deseja excluir este quesito?', 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Excluir']
                                                ) ?>
                                            <?php } else { ?>
                                                <span class="text-muted">Inativo</span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-muted">Nenhum quesito cadastrado.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h5 class="text-primary-emphasis mb-0 fw-semibold">
                    <i class="fas fa-clipboard-list me-2"></i>Sumulas
                </h5>
                <?= $this->Html->link(
                    '<i class="fas fa-clipboard-list me-1"></i> Cadastrar Sumulas',
                    ['controller' => 'Editais', 'action' => 'sumulasadd', $edital->id],
                    ['class' => 'btn btn-sm btn-outline-info', 'escape' => false]
                ) ?>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle sortable-table">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" style="width: 45px;">#</th>
                            <th class="sortable" style="width: 220px;">Bloco</th>
                            <th class="sortable">Sumula</th>
                            <th class="sortable">Parametro</th>
                            <th class="sortable" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($edital->editais_sumulas)) { ?>
                            <?php foreach ($edital->editais_sumulas as $sumula) { ?>
                                <?php
                                $blocoNome = $sumula->editais_sumulas_bloco ? $sumula->editais_sumulas_bloco->nome : '-';
                                ?>
                                <tr>
                                    <td><?= $sumula->id ?></td>
                                    <td><?= h($blocoNome) ?></td>
                                    <td><?= h($sumula->sumula) ?></td>
                                    <td><?= h($sumula->parametro) ?></td>
                                    <td class="text-end">
                                        <?php if (in_array($this->request->getAttribute('identity')['id'], [1, 8088], true)) { ?>
                                            <?php if (empty($sumula->deleted)) { ?>
                                                <?= $this->Html->link(
                                                    '<i class="fas fa-edit"></i>',
                                                    ['action' => 'sumulasedit', $sumula->id],
                                                    ['class' => 'btn btn-outline-primary btn-sm me-1', 'escape' => false, 'title' => 'Editar']
                                                ) ?>
                                                <?= $this->Form->postLink(
                                                    '<i class="fas fa-trash"></i>',
                                                    ['action' => 'sumulasdelete', $sumula->id],
                                                    ['confirm' => 'Tem certeza que deseja excluir esta súmula?', 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Excluir']
                                                ) ?>
                                            <?php } else { ?>
                                                <span class="text-muted">Inativo</span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-muted">Nenhuma súmula cadastrada.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h5 class="text-primary-emphasis mb-0 fw-semibold">
                    <i class="fas fa-clock me-2"></i>Prazos cadastrados
                </h5>
                <?= $this->Html->link(
                    '<i class="fas fa-calendar-plus me-1"></i> Cadastrar prazos',
                    ['controller' => 'Editais', 'action' => 'prazos', $edital->id],
                    ['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false]
                ) ?>
            </div>
            <?php if (!empty($prazos) && count($prazos) > 0) { ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle sortable-table">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable">ID</th>
                                <th class="sortable">Tipo</th>
                                <th class="sortable">Início</th>
                                <th class="sortable">Fim</th>
                                <th class="sortable">Usuário</th>
                                <th class="sortable">Inscrição</th>
                                <th class="sortable">Tabela</th>
                                <th class="sortable text-end">Ativo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prazos as $prazo) { ?>
                                <?php
                                $tipoLabel = $prazo->editais_wk ? $prazo->editais_wk->nome : ($prazo->tabela ?? '-');
                                $usuarioLabel = $prazo->usuario ? $prazo->usuario->nome : '-';
                                $tabelaLabel = '-';
                                if (!empty($prazo->inscricao) && !empty($prazo->tabela)) {
                                    $tabelaLabel = $prazo->tabela === 'I' ? 'IC' : ($prazo->tabela === 'J' ? 'PDJ' : $prazo->tabela);
                                }
                                ?>
                                <tr>
                                    <td><?= h($prazo->id) ?></td>
                                    <td><?= h($tipoLabel) ?></td>
                                    <td><?= $fmtDateTime($prazo->inicio) ?></td>
                                    <td><?= $fmtDateTime($prazo->fim) ?></td>
                                    <td><?= h($usuarioLabel) ?></td>
                                    <td><?= !empty($prazo->inscricao) ? h($prazo->inscricao) : '-' ?></td>
                                    <td><?= h($tabelaLabel) ?></td>
                                    <td class="text-end">
                                        <?php if ($prazo->deleted !== null && $prazo->deleted !== '') { ?>
                                            <?= $fmtDateTime($prazo->deleted) ?>
                                        <?php } else { ?>
                                            <span class="visually-hidden">Excluir</span>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-trash"></i>',
                                                ['action' => 'prazodelete', $prazo->id],
                                                ['confirm' => 'Tem certeza que deseja excluir o prazo #' . $prazo->id . '?', 'class' => 'btn btn-sm btn-outline-danger', 'escape' => false, 'title' => 'Excluir']
                                            ) ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <span class="text-muted">Nenhum prazo cadastrado.</span>
            <?php } ?>
        </div>
    </div>

</div>

<script>
document.querySelectorAll('.sortable-table').forEach(table => {
    const headers = table.querySelectorAll('.sortable');
    headers.forEach((header, index) => {
        let asc = true;
        header.addEventListener('click', () => {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let A = a.children[index].innerText.trim().toLowerCase();
                let B = b.children[index].innerText.trim().toLowerCase();

                const nA = A.replace('#','');
                const nB = B.replace('#','');

                if (!isNaN(nA) && !isNaN(nB)) {
                    return asc ? nA - nB : nB - nA;
                }

                return asc ? A.localeCompare(B) : B.localeCompare(A);
            });

            asc = !asc;
            rows.forEach(r => tbody.appendChild(r));
        });
    });
});
</script>
