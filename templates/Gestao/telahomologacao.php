<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Homologação</h4>
        <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listahomologacao']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="mb-2"><strong>Inscrição:</strong> #<?= h($inscricao->id ?? '') ?></p>
            <p class="mb-2"><strong>Edital:</strong> <?= h($inscricao->editai->nome ?? '-') ?></p>
            <p class="mb-0 text-muted">Tela de homologação em construção.</p>
        </div>
    </div>
</div>
