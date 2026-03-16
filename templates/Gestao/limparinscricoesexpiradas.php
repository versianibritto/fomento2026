<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Deletar Inscrições Expiradas</h4>
        <a href="<?= $this->Url->build(['controller' => 'Restrito', 'action' => 'index']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar para Restritos
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="alert alert-danger border mb-0">
                <div class="fw-bold mb-1">Atenção à deleção sistêmica</div>
                <div>
                    Esta rotina irá deletar logicamente todas as inscrições em fase <strong>1, 2, 3 ou 5</strong> cujo edital já teve o período de inscrição encerrado.
                </div>
                <div class="mt-2">
                    A rotina também irá garantir <strong>vigente = 0</strong> e gravar histórico com a frase:
                    <br>
                    <code><?= h($fraseHistorico ?? '') ?></code>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="text-muted small">Inscrições aptas</div>
                    <div class="fs-4 fw-bold"><?= (int)($total ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <div class="fw-semibold">Prévia da rotina</div>
                    <div class="text-muted small">Revise os registros encontrados antes de confirmar.</div>
                </div>
                <?php if (!empty($total)): ?>
                    <?= $this->Form->create(null, ['class' => 'm-0']) ?>
                        <?= $this->Form->button('Executar deleção sistêmica', [
                            'class' => 'btn btn-danger',
                            'confirm' => 'Confirma a deleção sistêmica das inscrições expiradas?',
                        ]) ?>
                    <?= $this->Form->end() ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($inscricoes)): ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 sortable-table">
                        <thead>
                            <tr>
                                <th class="sortable">Inscrição</th>
                                <th class="sortable">Fase</th>
                                <th class="sortable">Edital</th>
                                <th class="sortable">Fim da inscrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscricoes as $item): ?>
                                <tr>
                                    <td>#<?= h((string)$item->id) ?></td>
                                    <td><?= h($faseLabels[(int)($item->fase_id ?? 0)] ?? (string)($item->fase_id ?? '-')) ?></td>
                                    <td><?= h((string)($item->editai->nome ?? '-')) ?></td>
                                    <td>
                                        <?= !empty($item->editai->fim_inscricao)
                                            ? h($item->editai->fim_inscricao->i18nFormat('dd/MM/yyyy HH:mm'))
                                            : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">Nenhuma inscrição apta para deleção sistêmica.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.sortable-table').forEach(function (table) {
    const headers = table.querySelectorAll('.sortable');
    headers.forEach(function (header, index) {
        let asc = true;
        header.style.cursor = 'pointer';
        header.addEventListener('click', function () {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort(function (a, b) {
                let A = a.children[index].innerText.trim().toLowerCase();
                let B = b.children[index].innerText.trim().toLowerCase();
                const nA = A.replace('#', '');
                const nB = B.replace('#', '');

                if (!isNaN(nA) && !isNaN(nB)) {
                    return asc ? nA - nB : nB - nA;
                }

                return asc ? A.localeCompare(B) : B.localeCompare(A);
            });

            asc = !asc;
            rows.forEach(function (row) {
                tbody.appendChild(row);
            });
        });
    });
});
</script>
