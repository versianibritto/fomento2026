<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Editar Workshop</h3>
                <div class="fw-semibold">Registro #<?= (int)$workshop->id ?></div>
                <div class="text-muted mt-1">Atualize os dados permitidos desta Workshop.</div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <h4 class="mb-3">Vínculos da Workshop</h4>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Bolsista</div>
                            <div class="fw-semibold"><?= h((string)($workshop->usuario->nome ?? 'Não informado')) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Orientador</div>
                            <div class="fw-semibold"><?= h((string)($workshop->orientadore->nome ?? 'Não informado')) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Unidade</div>
                            <div class="fw-semibold"><?= h((string)($workshop->unidade->sigla ?? 'Não informada')) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Dados da Workshop</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="text-muted small">Edital</div>
                            <div class="fw-semibold"><?= h((string)($workshop->editai->nome ?? ('#' . (int)$workshop->editai_id))) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Data de apresentação</div>
                            <div class="fw-semibold">
                                <?= !empty($workshop->data_apresentacao)
                                    ? h($workshop->data_apresentacao->i18nFormat('dd/MM/YYYY'))
                                    : 'Não informada' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2 pt-2">
                <?= $this->Html->link('Voltar', ['controller' => 'Workshops', 'action' => 'ver', $workshop->id], ['class' => 'btn btn-outline-secondary px-4']) ?>
            </div>
        </div>
    </div>
</div>
