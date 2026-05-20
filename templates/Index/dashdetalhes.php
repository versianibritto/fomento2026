<?php
    $usuarioId = $this->request->getAttribute('identity')->id;
    $tipo = strtoupper((string)($tipo ?? ''));
?>

<style>
    .dash-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem;
        max-width: 180px;
    }

    .dash-action-btn {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: .78rem;
        line-height: 1;
        padding: .34rem .55rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .08);
        white-space: nowrap;
        border-style: dashed;
    }

    .dash-switcher {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 999px;
        font-weight: 600;
        padding: .55rem .95rem;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .08);
    }

    .dash-action-desistir {
        background: #fff5f5;
        color: #b02a37;
    }

    .dash-action-desistir:hover,
    .dash-action-desistir:focus {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .dash-action-editar {
        background: #fff8e1;
        color: #8a5a00;
    }

    .dash-action-editar:hover,
    .dash-action-editar:focus {
        background: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }

    .dash-action-termo,
    .dash-action-substituir {
        background: #eef9f1;
        color: #146c43;
    }

    .dash-action-termo:hover,
    .dash-action-termo:focus,
    .dash-action-substituir:hover,
    .dash-action-substituir:focus {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }

    .dash-action-anexar,
    .dash-action-cancelar {
        background: #fff5f5;
        color: #b02a37;
    }

    .dash-action-anexar:hover,
    .dash-action-anexar:focus,
    .dash-action-cancelar:hover,
    .dash-action-cancelar:focus {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }
</style>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">
            <?= $tipo === 'V'
                ? 'Bolsas vigentes'
                : ($tipo === 'A' ? 'Inscrições em andamento' : 'Meus Bolsistas') ?>
        </h4>

        <a href="<?= $this->Url->build(['action' => 'dashboard']) ?>"
        class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar para o Dashboard
        </a>

    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="id">
                            Id <i class="fa fa-sort ms-1 text-muted"></i>
                        </th>

                        <th class="sortable" data-sort="orientador">
                            Orientador <i class="fa fa-sort ms-1 text-muted"></i>
                        </th>

                        <th class="sortable" data-sort="bolsista">
                            Bolsista <i class="fa fa-sort ms-1 text-muted"></i>
                        </th>
                        <th class="sortable" data-sort="coorientador">
                            Coorientador <i class="fa fa-sort ms-1 text-muted"></i>
                        </th>

                        <th class="sortable" data-sort="edital">
                            Edital <i class="fa fa-sort ms-1 text-muted"></i>
                        </th>

                        <th class="sortable" data-sort="status">
                            Status <i class="fa fa-sort ms-1 text-muted"></i>
                        </th>
                        <th>Ações do Orientador</th>


                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($detalhes)): ?>
                        <?php foreach ($detalhes as $i): ?>
                            <?php $controllerFluxo = trim((string)($i['controller'] ?? '')); ?>
                            <tr>
                                <td>
                                    <a href="<?= $this->Url->build([
                                        'controller' => 'Padrao',
                                        'action' => 'visualizar',
                                        $i['id'],
                                    ]) ?>"
                                    class="text-decoration-none d-inline-flex align-items-center gap-2 text-primary fw-bold">
                                        <i class="fa fa-eye"></i>
                                        <span>#<?= h($i['id']) ?></span>
                                    </a>
                                </td>

                                <td>
                                    <?= !isset($i['orientador'])
                                        ? '<strong><em>Não informado</em></strong>'
                                        : ($usuarioId == $i['orientador']
                                            ? '<span class="badge bg-danger" title="Você Mesmo"><i class="fa fa-user"></i></span>'
                                            : h($i['nome_orientador'] ?? '<strong><em>Não informado</em></strong>')
                                        ) 
                                    ?>
                                </td>

                                <td>
                                    <?php if (!isset($i['bolsista']) || (int)$i['bolsista'] <= 0): ?>
                                        <strong><em>Não informado</em></strong>
                                    <?php else: ?>
                                        <?php
                                            $bolsistaId = (int)$i['bolsista'];
                                            $nomeBolsista = trim((string)($i['nome_bolsista'] ?? ''));
                                            $nomeBolsista = $nomeBolsista !== '' ? $nomeBolsista : 'Não informado';
                                        ?>
                                        <?= $this->Html->link(
                                            $usuarioId === $bolsistaId ? 'Você' : $nomeBolsista,
                                            ['controller' => 'Users', 'action' => 'ver', $bolsistaId],
                                            [
                                                'class' => 'text-decoration-none',
                                                'title' => 'Visualizar os dados pessoais',
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (!isset($i['coorientador']) || (int)$i['coorientador'] <= 0): ?>
                                        <strong><em>Não informado</em></strong>
                                    <?php else: ?>
                                        <?php
                                            $coorientadorId = (int)$i['coorientador'];
                                            $nomeCoorientador = trim((string)($i['nome_coorientador'] ?? ''));
                                            $nomeCoorientador = $nomeCoorientador !== '' ? $nomeCoorientador : 'Não informado';
                                        ?>
                                        <?= $this->Html->link(
                                            $usuarioId === $coorientadorId ? 'Você' : $nomeCoorientador,
                                            ['controller' => 'Users', 'action' => 'ver', $coorientadorId],
                                            [
                                                'class' => 'text-decoration-none',
                                                'title' => 'Visualizar os dados pessoais',
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= h($i['nome_edital']) ?><br>
                                    <small class="text-muted">
                                        <?= h($i['nome_programa']) ?>
                                    </small>
                                </td>
                                <td>
                                    <?= h($i['nome_fase']) ?><br>
                                    <small class="text-muted">
                                        <?= $i['vigente']
                                        ? '<span class="badge bg-success">Vigente</span>'
                                        : '<span class="badge bg-secondary">Não Vigente</span>' 
                                        ?>                                
                                    </small>
                                
                                </td>
                                <td>
                                    <?php if ($usuarioId == $i['orientador']): ?>
                                        <div class="dash-actions">
                                        <?php
                                            $faseRegistro = (int)($i['fase_id'] ?? 0);
                                            $origemRegistro = strtoupper(trim((string)($i['origem'] ?? '')));
                                            $podeDesistirProcesso = $tipo === 'A'
                                                && $origemRegistro === 'N'
                                                && $faseRegistro < 8;
                                        ?>
                                        <?php if ($tipo === 'A' || $tipo === 'T'): ?>                                  
                                            <?php if (in_array($i['fase_id'], [1, 3])): ?>
                                                <!-- EDITAR -->
                                                <a href="<?= $this->Url->build([
                                                    'controller' => $controllerFluxo,
                                                    'action' => 'direcionarAcao',
                                                    $i['editai_id'],
                                                    $i['id'],
                                                    'E',
                                                ]) ?>"
                                                class="btn btn-sm btn-outline-warning dash-action-btn dash-action-editar">
                                                    <i class="fa fa-pen"></i>
                                                    <span>Editar Inscrição</span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (in_array($i['fase_id'], [1, 3])): ?>
                                                <!-- TERMO -->
                                                <a href="<?= $this->Url->build([
                                                    'controller' => $controllerFluxo,
                                                    'action' => 'direcionarAcao',
                                                    $i['editai_id'],
                                                    $i['id'],
                                                    'T',
                                                ]) ?>"
                                                class="btn btn-sm btn-outline-success dash-action-btn dash-action-termo">
                                                    <i class="fa fa-file-signature"></i>
                                                    <span>Gerar Termo</span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (in_array($i['fase_id'], [5])): ?>
                                                <!-- FINALIZAR -->
                                                <a href="<?= $this->Url->build([
                                                    'controller' => $controllerFluxo,
                                                    'action' => 'direcionarAcao',
                                                    $i['editai_id'],
                                                    $i['id'],
                                                    'F',
                                                ]) ?>"
                                                class="btn btn-sm btn-outline-danger dash-action-btn dash-action-anexar"
                                                onclick="return confirm('Deseja Anexar o Termo desta esta inscrição?');">
                                                    <i class="fa fa-upload"></i>
                                                    <span>Anexar Termo</span>
                                                </a>
                                            <?php endif; ?>
                                        
                                        <?php endif; ?>
                                        <?php if ($podeDesistirProcesso): ?>
                                            <?= $this->Form->postLink('Desistir do processo', [
                                                'controller' => 'Inscricoes',
                                                'action' => 'desistir',
                                                (int)$i['editai_id'],
                                                (int)$i['id'],
                                            ], [
                                                'class' => 'btn btn-sm btn-outline-danger dash-action-btn dash-action-desistir',
                                                'confirm' => 'Confirma a desistência do processo? Esta ação não poderá ser desfeita.',
                                            ]) ?>
                                        <?php endif; ?>
                                        <?php if ($tipo === 'V' || $tipo === 'T'): ?>                                  
                                            <?php if (in_array($i['fase_id'], [11, 18, 19])): ?>
                                                <!-- CANCELAR -->
                                                <a href="<?= $this->Url->build([
                                                    'controller' => 'Padrao',
                                                    'action' => 'cancelar',
                                                    $i['id'],
                                                ]) ?>"
                                                class="btn btn-sm btn-outline-danger dash-action-btn dash-action-cancelar">
                                                    <i class="fa fa-ban"></i>
                                                    <span>Cancelar</span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (in_array($i['fase_id'], [11, 22, 16])): ?>
                                                <!-- Substituir -->
                                                <a href="<?= $this->Url->build([
                                                    'controller' => 'Substituicoes',
                                                    'action' => 'iniciar',
                                                    $i['id'],
                                                ]) ?>"
                                                class="btn btn-sm btn-outline-success dash-action-btn dash-action-substituir">
                                                    <i class="fa fa-user-plus"></i>
                                                    <span>Substituir</span>
                                                </a>
                                            <?php endif; ?>                             
                                        <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                </td>

                            
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted fw-bold">
                                Nenhum registro encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <script>
                        document.querySelectorAll('.sortable').forEach((header, columnIndex) => {
                        let asc = true;

                        header.addEventListener('click', () => {
                            const table = header.closest('table');
                            const tbody = table.querySelector('tbody');
                            const rows = Array.from(tbody.querySelectorAll('tr'));

                            rows.sort((a, b) => {
                                let A = a.children[columnIndex].innerText.trim().toLowerCase();
                                let B = b.children[columnIndex].innerText.trim().toLowerCase();

                                // Se for número (#ID)
                                if (!isNaN(A.replace('#','')) && !isNaN(B.replace('#',''))) {
                                    A = Number(A.replace('#',''));
                                    B = Number(B.replace('#',''));
                                }

                                if (A < B) return asc ? -1 : 1;
                                if (A > B) return asc ? 1 : -1;
                                return 0;
                            });

                            asc = !asc;

                            // Reinsere as linhas ordenadas
                            rows.forEach(row => tbody.appendChild(row));
                        });
                    });
                    </script>

                </tbody>

            </table>

        </div>
    </div>
    <?php if ($tipo !== 'T'): ?>
        <a href="<?= $this->Url->build([
                'action' => 'dashdetalhes',
                $tipo === 'A' ? 'V' : 'A'
            ]) ?>"
            class="btn btn-outline-primary dash-switcher mt-3">
            <i class="fa fa-filter me-1"></i>
            Mostrar Inscrições <?= $tipo === 'A' ? 'VIGENTES' : 'EM ANDAMENTO' ?>
        </a>
    <?php else: ?>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="<?= $this->Url->build(['action' => 'dashdetalhes', 'V']) ?>"
               class="btn btn-outline-primary dash-switcher">
                <i class="fa fa-filter me-1"></i> Mostrar Vigentes
            </a>
            <a href="<?= $this->Url->build(['action' => 'dashdetalhes', 'A']) ?>"
               class="btn btn-outline-primary dash-switcher">
                <i class="fa fa-filter me-1"></i> Mostrar Em Andamento
            </a>
        </div>
    <?php endif; ?>



</div>
