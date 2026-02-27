
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .card-counter {
        cursor: pointer;
        transition: 0.25s;
        min-height: 150px;
    }
    .card-counter:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* EDITAIS */
    .card-edital {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-radius: 12px;
        transition: 0.25s;
        background: #ffffff;
    }
    .card-edital:hover {
        transform: translateY(-3px);
        box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    }

    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 9px;
        width: 100%;
        transition: 0.25s;
    }

    .btn-download {
        background: #e8f1ff;
        color: #004a99;
        border: 1px solid #bcd4ff;
    }
    .btn-download:hover {
        background: #d8e8ff;
    }

    .btn-inscrever {
        background: #e7f8ee;
        color: #137b37;
        border: 1px solid #b8eaca;
    }
    .btn-inscrever:hover {
        background: #d4f3e3;
    }

    /* Cards inferiores mesma altura */
    .card-equal {
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
</style>
</head>

<body class="bg-light p-4">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('modalAvisoInicial');
    if (!el) return;

    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();

    // 🔥 GARANTE limpeza total ao fechar
    el.addEventListener('hidden.bs.modal', function () {
        document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
    });
});
</script>

<!-- ===================== MODAL AUTOMÁTICO INICIAL ===================== -->
<?php if (
    ($dashboard['subst'] ?? 0) > 0
    || ($dashboard['cancel'] ?? 0) > 0
): ?>

<div class="modal fade" id="modalAvisoInicial" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Atenção!
                </h5>
            </div>

            <div class="modal-body">

                <?php if (($dashboard['subst'] ?? 0) > 0): ?>
                    <p class="mb-2">
                        🔄 Existem 
                        <strong><?= $dashboard['subst'] ?></strong>
                        substituição(ões) pendente(s) de análise.
                    </p>
                <?php endif; ?>

                <?php if (($dashboard['cancel'] ?? 0) > 0): ?>
                    <p class="mb-2">
                        ❌ Existem 
                        <strong><?= $dashboard['cancel'] ?></strong>
                        cancelamento(s) pendente(s) de análise.
                    </p>
                <?php endif; ?>

                <p class="text-muted mt-3 mb-0">
                    Essas pendências exigem sua atenção antes de prosseguir.
                </p>

            </div>

            

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(
        document.getElementById('modalAvisoInicial')
    );
    modal.show();
});
</script>

<?php endif; ?>


<!-- BOLSAS EM ANDAMENTO / HOMOLOGAÇÕES -->
<div class="card shadow-sm mb-4">

    <!-- Header geral -->
    <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-semibold text-primary mb-0">
            <i class="bi bi-award me-2"></i>Editais em Andamento
        </h5>
    </div>

    <div class="card-body">
        <div class="row g-4">

            <!-- ================= INSCRIÇÕES EM ANDAMENTO ================= -->
            <div class="col-md-6">
                <div class="border rounded p-4 h-100 bg-white">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold text-muted mb-0">
                            Inscrições em andamento
                        </h6>

                        <div class="d-flex gap-2">
                            <a href="<?= $this->Url->build(['controller' => 'Grafico', 'action' => 'inscricoesEmAndamento']) ?>"
                               class="btn btn-outline-secondary btn-sm"
                               target="_blank"
                               rel="noopener">
                                <i class="bi bi-bar-chart-line"></i> Gráficos
                            </a>

                            <a href="<?= $this->Url->build(['controller' => 'Listas', 'action' => 'resultado', 'A', '?' => ['acao' => 'excel']]) ?>"
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-file-earmark-excel"></i> Exportar
                            </a>
                        </div>
                    </div>

                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <h3 class="fw-bold text-danger mb-1">
                                <?= $dashboard['andamento'] ?? 0 ?>
                            </h3>
                            <small class="text-muted">Rascunho</small>
                        </div>

                        <div>
                            <h3 class="fw-bold text-warning mb-1">
                                <?= $dashboard['finalizadas'] ?? 0 ?>
                            </h3>
                            <small class="text-muted">Finalizadas</small>
                        </div>

                        <div>
                            <h3 class="fw-bold text-success mb-1">
                                <?= $dashboard['total'] ?? 0 ?>
                            </h3>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================= HOMOLOGAÇÕES ================= -->
            <div class="col-md-6">
                <div class="border rounded p-4 h-100 bg-white">

                    <!-- Header interno -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold text-muted mb-0">
                            Homologações
                        </h6>

                        <div class="d-flex gap-2">
                            <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listahomologacao']) ?>"
                            class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-check2-square me-1"></i>Homologar
                            </a>

                            <a href="<?= $this->Url->build(['action' => 'graficosHomologacoes']) ?>"
                            class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-bar-chart-line me-1"></i>Gráficos
                            </a>

                            <a href="<?= $this->Url->build([
                                'controller' => 'Gestao',
                                'action' => 'listahomologacao',
                                '?' => [
                                    'programa_id' => 0,
                                    'fase_id' => 0,
                                    'acao' => 'excel',
                                ],
                            ]) ?>"
                            class="btn btn-outline-success btn-sm">
                                <i class="bi bi-file-earmark-excel me-1"></i>Exportar
                            </a>
                        </div>
                    </div>

                    <!-- Métricas -->
                    <div class="d-flex justify-content-around text-center">

                        <!-- Falta / Total (AÇÃO CONTEXTUAL) -->
                        <div>
                            <h3 class="fw-bold text-warning mb-1">
                                <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listahomologacao']) ?>"
                                class="text-warning text-decoration-none">
                                    <?= ($dashboard['total']
                                        - $dashboard['aceito']
                                        - $dashboard['recusado']) ?? 0 ?>
                                    /
                                    <?= $dashboard['total'] ?? 0 ?>
                                </a>
                            </h3>
                            <small class="text-muted">Falta homologar</small>
                        </div>

                        <div>
                            <h3 class="fw-bold text-success mb-1">
                                <?= $dashboard['aceito'] ?? 0 ?>
                            </h3>
                            <small class="text-muted">Aceitas</small>
                        </div>

                        <div>
                            <h3 class="fw-bold text-danger mb-1">
                                <?= $dashboard['recusado'] ?? 0 ?>
                            </h3>
                            <small class="text-muted">Recusadas</small>
                        </div>

                    </div>

                </div>
            </div>


        </div>
    </div>
</div>

<!-- BOLSAS VIGENTES -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-semibold text-primary mb-0">
            <i class="bi bi-award me-2"></i>Bolsas vigentes
        </h5>

        
    </div>

    <div class="card-body">
        <div class="row g-4">

            <!-- Substituições -->
            <div class="col-md-4">
                <div class="border rounded p-4 text-center h-100 bg-white">
                    <h3 class="fw-bold text-warning mb-1">
                        <?= $dashboard['subst'] ?? 0 ?>
                    </h3>
                    <small class="text-muted d-block mb-3">
                        Substituições solicitadas
                    </small>

                    <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'S']) ?>"
                       class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Ver solicitações
                    </a>
                </div>
            </div>

            <!-- Cancelamentos -->
            <div class="col-md-4">
                <div class="border rounded p-4 text-center h-100 bg-white">
                    <h3 class="fw-bold text-danger mb-1">
                        <?= $dashboard['cancel'] ?? 0 ?>
                    </h3>
                    <small class="text-muted d-block mb-3">
                        Cancelamentos solicitados
                    </small>

                    <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'C']) ?>"
                       class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Ver solicitações
                    </a>
                </div>
            </div>

            <!-- Total vigentes -->
            <div class="col-md-4">
                <div class="border rounded p-4 text-center h-100 bg-white">
                    <h3 class="fw-bold text-success mb-1">
                        <?= $dashboard['bolsasAtivas'] ?? 0 ?>
                    </h3>
                    <small class="text-muted d-block mb-3">
                        Total de bolsas vigentes
                    </small>

                    <a href="<?= $this->Url->build(['controller' => 'Listas', 'action' => 'resultado', 'V', '?' => ['acao' => 'excel']]) ?>"
                       class="btn btn-outline-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                    </a>
                    <a href="<?= $this->Url->build(['action' => 'graficosHomologacoes']) ?>"
                            class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-bar-chart-line me-1"></i>Gráficos
                            </a>
                </div>
            </div>

        </div>
    </div>
</div>
