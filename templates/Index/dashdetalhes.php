<?php
    $usuarioId = $this->request->getAttribute('identity')->id;
    $tipo = strtoupper((string)($tipo ?? ''));
?>




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
                                    <?= !isset($i['bolsista'])
                                        ? '<strong><em>Não informado</em></strong>'
                                        : ($usuarioId == $i['bolsista']
                                            ? '<span class="badge bg-danger" title="Você Mesmo"><i class="fa fa-user"></i></span>'
                                            : h($i['nome_bolsista'] ?? '<strong><em>Não informado</em></strong>')
                                        ) 
                                    ?>
                                </td>

                                <td>
                                    <?= !isset($i['coorientador'])
                                        ? '<strong><em>Não informado</em></strong>'
                                        : ($usuarioId == $i['coorientador']
                                            ? '<span class="badge bg-danger" title="Você Mesmo"><i class="fa fa-user"></i></span>'
                                            : h($i['nome_coorientador'] ?? '<strong><em>Não informado</em></strong>')
                                        ) 
                                    ?>
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
                                                class="btn btn-sm btn-outline-warning mb-1">
                                                    Editar
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
                                                class="btn btn-sm btn-outline-success mb-1">
                                                    Termo
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
                                                class="btn btn-sm btn-outline-danger mb-1"
                                                onclick="return confirm('Deseja finalizar este projeto?');">
                                                    Finalizar
                                                </a>
                                            <?php endif; ?>
                                        
                                        <?php endif; ?>
                                        <?php if ($tipo === 'V' || $tipo === 'T'): ?>                                  
                                            <?php if (in_array($i['fase_id'], [11, 18, 19])): ?>
                                                <!-- CANCELAR -->
                                                <a href="<?= $this->Url->build([
                                                    'controller' => 'Padrao',
                                                    'action' => 'cancelar',
                                                    $i['id'],
                                                ]) ?>"
                                                class="btn btn-sm btn-outline-danger mb-1">
                                                    Cancelar
                                                </a>
                                            <?php endif; ?>

                                            <?php if (in_array($i['fase_id'], [11, 22, 16])): ?>
                                                <!-- Substituir -->
                                                <a href="<?= $this->Url->build([
                                                    'controller' => 'Padrao',
                                                    'action' => 'substituir',
                                                    $i['id'],
                                                ]) ?>"
                                                class="btn btn-sm btn-outline-success mb-1">
                                                    Substituir
                                                </a>
                                            <?php endif; ?>                             
                                        <?php endif; ?>
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
            class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-filter me-1"></i>
            Mostrar Inscrições <?= $tipo === 'A' ? 'VIGENTES' : 'EM ANDAMENTO' ?>
        </a>
    <?php else: ?>
        <div class="d-flex gap-2">
            <a href="<?= $this->Url->build(['action' => 'dashdetalhes', 'V']) ?>"
               class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-filter me-1"></i> Mostrar Vigentes
            </a>
            <a href="<?= $this->Url->build(['action' => 'dashdetalhes', 'A']) ?>"
               class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-filter me-1"></i> Mostrar Em Andamento
            </a>
        </div>
    <?php endif; ?>



</div>
