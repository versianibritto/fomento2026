<?php
    $usuarioId = $this->request->getAttribute('identity')->id;
    $tipo = strtoupper((string)($tipo ?? ''));
    $titulo = $tipo === 'C' ? 'Lista de Confirmação - Cancelamentos' : 'Lista de Confirmação - Substituições';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><?= h($titulo) ?></h4>

        <div class="d-flex gap-2">
            <?php if ($tipo === 'S'): ?>
                <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'C']) ?>"
                class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    Ver Cancelamentos
                </a>
            <?php else: ?>
                <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'S']) ?>"
                class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    Ver Substituições
                </a>
            <?php endif; ?>

            <a href="<?= $this->Url->build(['controller' => 'Index', 'action' => 'dashyoda']) ?>"
            class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i> Voltar para o Dashboard
            </a>
        </div>

    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="id">Inscrição <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th class="sortable" data-sort="bolsista">Bolsista Entrando <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <?php if ($tipo === 'S'): ?>
                            <th class="sortable" data-sort="bolsista-saindo">Bolsista Saindo <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <?php endif; ?>
                        <th class="sortable" data-sort="orientador">Orientador <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th class="sortable" data-sort="unidade">Unidade <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th class="sortable" data-sort="data">Data Solicitação <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th class="sortable" data-sort="cota">Cota <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $i): ?>
                            <tr>
                                <td>
                                    <a href="<?= $this->Url->build([
                                        'controller' => 'Padrao',
                                        'action' => 'visualizar',
                                        $i['id'],
                                    ]) ?>"
                                    target="_blank"
                                    class="text-decoration-none d-inline-flex align-items-center gap-2 text-primary fw-bold">
                                        <i class="fa fa-eye"></i>
                                        <span>#<?= h($i['id']) ?></span>
                                    </a>
                                </td>
                                <td>
                                    <?= h($i['bolsista_entrando'] ?? '-') ?>
                                </td>
                                <?php if ($tipo === 'S'): ?>
                                    <td><?= h($i['bolsista_saindo'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td>
                                    <?= h($i['orientador'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= h($i['unidade'] ?? '-') ?>
                                </td>
                                <td>
                                    <?php
                                        $data = $i['data_solicitacao'] ?? null;
                                        if ($data instanceof \Cake\I18n\FrozenTime) {
                                            echo h($data->i18nFormat('dd/MM/yyyy'));
                                        } elseif (!empty($data)) {
                                            $ts = strtotime((string)$data);
                                            echo h($ts ? date('d/m/Y', $ts) : (string)$data);
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $cota = (string)($i['cota'] ?? '');
                                        echo h($cotas[$cota] ?? ($cota !== '' ? $cota : '-'));
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($this->request->getAttribute('identity')['yoda'])): ?>
                                        <?php
                                            $isPdj = false;
                                            $programaId = (int)($i['programa_id'] ?? 0);
                                            if ($programaId === 1) {
                                                $isPdj = true;
                                            } else {
                                                $fonte = strtoupper((string)($i['fonte'] ?? ''));
                                                $nomePrograma = strtoupper((string)($i['nome_programa'] ?? ''));
                                                $siglaPrograma = strtoupper((string)($i['programa'] ?? ''));
                                                $isPdj = ($fonte === 'PDJ' || $nomePrograma === 'PDJ' || $siglaPrograma === 'J');
                                            }

                                            if ($tipo === 'S') {
                                                $link = ['controller' => 'Gestao', 'action' => 'confirmacao', $i['id']];
                                                $label = 'Confirmar';
                                                $btnClass = 'btn btn-xs btn-warning';
                                            } else {
                                                $link = ['controller' => 'Gestao', 'action' => 'confirmacao', $i['id']];
                                                $label = 'Confirmar';
                                                $btnClass = 'btn btn-xs btn-danger';
                                            }
                                        ?>
                                        <?= $this->Html->link($label, $link, ['class' => $btnClass]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $tipo === 'S' ? 8 : 7 ?>" class="text-center text-muted fw-bold">Nenhum registro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

                        if (!isNaN(A.replace('#','')) && !isNaN(B.replace('#',''))) {
                            A = Number(A.replace('#',''));
                            B = Number(B.replace('#',''));
                        }

                        if (A < B) return asc ? -1 : 1;
                        if (A > B) return asc ? 1 : -1;
                        return 0;
                    });

                    asc = !asc;

                    rows.forEach(row => tbody.appendChild(row));
                });
            });
            </script>
        </div>
    </div>

</div>
