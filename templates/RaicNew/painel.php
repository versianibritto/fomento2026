<?php
$identityRaicPainel = $this->request->getAttribute('identity');
$usuarioId = (int)($identityRaicPainel->id ?? 0);
$tiposBolsa = [
    'R' => 'Renovação',
    'V' => '**Raics de Outras Agencias',
    'Z' => 'Raics de Outras Agencias',
];
?>

<div class="p-3">
    <?php if ($minha->count() > 0): ?>
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">RAIC</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-sort="id">
                                    Inscrição <i class="fa fa-sort ms-1 text-muted"></i>
                                </th>
                                <th class="sortable" data-sort="bolsista">
                                    Bolsista <i class="fa fa-sort ms-1 text-muted"></i>
                                </th>
                                <th class="sortable" data-sort="orientador">
                                    Orientador <i class="fa fa-sort ms-1 text-muted"></i>
                                </th>
                                <th class="sortable" data-sort="projeto">
                                    Projeto <i class="fa fa-sort ms-1 text-muted"></i>
                                </th>
                                <th class="sortable" data-sort="tipo_bolsa">
                                    Tipo <i class="fa fa-sort ms-1 text-muted"></i>
                                </th>
                                <th class="sortable" data-sort="data">
                                    Data Apresentação <i class="fa fa-sort ms-1 text-muted"></i>
                                </th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($minha as $bol): ?>
                                <?php
                                $nomeCompleto = trim((string)($bol->orientadore->nome ?? ''));
                                $partesNome = $nomeCompleto !== '' ? preg_split('/\s+/', $nomeCompleto) : [];
                                $nomeResumo = !empty($partesNome)
                                    ? $partesNome[0] . ' ' . end($partesNome)
                                    : 'não informado';
                                $anoCertificado = !empty($bol->data_apresentacao)
                                    ? $bol->data_apresentacao->format('Y')
                                    : (!empty($bol->editai->fim_vigencia) ? date('Y', strtotime((string)$bol->editai->fim_vigencia)) : date('Y'));
                                ?>
                                <tr>
                                    <td>
                                        <?= $this->Html->link(
                                            '#' . h((string)($bol->id ?? '')),
                                            ['controller' => 'RaicNew', 'action' => 'ver', $bol->id],
                                            ['class' => 'fw-bold text-primary text-decoration-none']
                                        ) ?>
                                    </td>
                                    <td>
                                        <?php if ((int)($bol->usuario_id ?? 0) === $usuarioId): ?>
                                            <span class="badge bg-danger rounded-circle d-inline-block"
                                                title="Você"
                                                style="width:10px;height:10px;padding:0;vertical-align:middle;"></span>
                                        <?php else: ?>
                                            <?= h((string)($bol->usuario->nome ?? 'não informado')) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int)($bol->orientador ?? 0) === $usuarioId): ?>
                                            <span class="badge bg-danger rounded-circle d-inline-block"
                                                title="Você"
                                                style="width:10px;height:10px;padding:0;vertical-align:middle;"></span>
                                        <?php else: ?>
                                            <?= h($nomeResumo) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= h(!empty($bol->projeto_orientador) ? (string)$bol->projeto_orientador : 'N/A') ?></td>
                                    <td><?= h($tiposBolsa[strtoupper((string)($bol->tipo_bolsa ?? ''))] ?? ((string)($bol->tipo_bolsa ?? 'não informado') !== '' ? (string)$bol->tipo_bolsa : 'não informado')) ?></td>
                                    <td>
                                        <?php if (!empty($bol->data_apresentacao) && $bol->data_apresentacao instanceof \Cake\I18n\Date): ?>
                                            <?= h($bol->data_apresentacao->i18nFormat('dd/MM/YYYY')) ?>
                                        <?php elseif (!empty($bol->data_apresentacao)): ?>
                                            <?= h(date('d/m/Y', strtotime((string)$bol->data_apresentacao))) ?>
                                        <?php else: ?>
                                            Não Marcada
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                                            <?php if (strtoupper((string)($bol->presenca ?? '')) === 'S' && (int)($bol->usuario_id ?? 0) === $usuarioId): ?>
                                                <?= $this->Html->link(
                                                    '<i class="fas fa-file"></i> Certificado',
                                                    ['controller' => 'Certificados', 'action' => 'ver', $bol->id, 'R', $anoCertificado],
                                                    ['class' => 'btn btn-info btn-xs', 'target' => '_blank', 'escape' => false]
                                                ) ?>
                                            <?php elseif (strtoupper((string)($bol->presenca ?? '')) === 'S'): ?>
                                                <span class="text-success small">Certificado liberado</span>
                                            <?php else: ?>
                                                <span class="text-muted small">Certificado não liberado</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-0">Nenhum registro de RAIC encontrado para o usuário logado.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

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

            if (!isNaN(A.replace('#', '')) && !isNaN(B.replace('#', ''))) {
                A = parseInt(A.replace('#', ''), 10);
                B = parseInt(B.replace('#', ''), 10);
            }

            if (A < B) {
                return asc ? -1 : 1;
            }
            if (A > B) {
                return asc ? 1 : -1;
            }
            return 0;
        });

        asc = !asc;
        rows.forEach((row) => tbody.appendChild(row));
    });
});
</script>
