<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Suporte técnico</h4>
        <a href="/supote/add" class="btn btn-success btn-sm">
            <i class="fa fa-plus me-1"></i> Novo chamado
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="fw-semibold">Filtros</div>
                <span class="text-muted small">Refine a lista de chamados</span>
            </div>
            <?=$this->Form->create(null, ['type' => 'get', 'class' => 'row g-3 align-items-end', 'id' => 'filtros-suporte-form'])?>
                <div class="col-md-4">
                    <?=$this->Form->control('categoria_id', [
                        'label' => 'Categoria',
                        'options' => $categorias,
                        'empty' => '- Todas -',
                        'class' => 'form-select',
                        'value' => $filtros['categoria_id'] ?? null
                    ])?>
                </div>
                <div class="col-md-4">
                    <?=$this->Form->control('status_id', [
                        'label' => 'Status',
                        'options' => $statusList,
                        'empty' => '- Todos -',
                        'class' => 'form-select',
                        'value' => $filtros['status_id'] ?? null
                    ])?>
                </div>
                <?php if ($isYoda): ?>
                    <div class="col-md-4">
                        <?=$this->Form->control('classificacao_final_id', [
                            'label' => 'Classificação final',
                            'options' => $classificacoesFinais,
                            'empty' => '- Todas -',
                            'class' => 'form-select',
                            'value' => $filtros['classificacao_final_id'] ?? null
                        ])?>
                    </div>
                <?php endif; ?>
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <?=$this->Form->button('Filtrar', ['class' => 'btn btn-primary'])?>
                        <a href="/supote" class="btn btn-outline-secondary">Limpar</a>
                    </div>
                    <?php if ($isTi): ?>
                        <?php
                            $params = $this->request->getQueryParams();
                            $params['export'] = 1;
                        ?>
                        <a href="<?= $this->Url->build(['controller' => 'Supote', 'action' => 'index', '?' => $params]) ?>" class="btn btn-success btn-sm" id="btn-exportar-suporte">
                            <i class="fa fa-file-excel me-1"></i> Exportar (filtros atuais)
                        </a>
                    <?php endif; ?>
                </div>
            <?=$this->Form->end()?>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Categoria</th>
                        <th>Usuário</th>
                        <th>Status</th>
                        <th>Última atualização</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chamados as $item): ?>
                        <tr>
                            <td><?= (int)$item->id ?></td>
                            <td><?= h($item->suporte_categoria->nome ?? '-') ?></td>
                            <td><?= h($item->usuario->nome ?? '-') ?></td>
                            <td><?= h($item->suporte_status->nome ?? '-') ?></td>
                            <td><?= $item->modified ? h($item->modified->i18nFormat('dd/MM/YYYY HH:mm')) : '-' ?></td>
                            <td class="text-end">
                                <a href="/supote/view/<?= (int)$item->id ?>?ramo=<?= (int)($item->ramo ?? $item->id) ?>" class="btn btn-sm btn-outline-info">Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($chamados->count() === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum chamado encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination mt-3">
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->prev('« Previous') ?>
        <?= $this->Paginator->next('Next »') ?>
        <?= $this->Paginator->counter() ?>
    </div>
</div>

<div class="modal fade" id="modal-exportar-suporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div class="fw-semibold">Gerando arquivo...</div>
                <div class="text-muted small">Isso pode levar alguns segundos.</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnExportar = document.getElementById('btn-exportar-suporte');
    if (!btnExportar) return;
    btnExportar.addEventListener('click', function () {
        const modalEl = document.getElementById('modal-exportar-suporte');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        setTimeout(() => {
            modal.hide();
        }, 8000);
    });
});
</script>
