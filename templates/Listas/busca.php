<?php
$titulo = 'Listagem';
$tipoLabel = 'Listagem';
if ($tipo === 'V') {
    $tipoLabel = 'Vigentes';
} elseif ($tipo === 'A') {
    $tipoLabel = 'Andamentos';
} elseif ($tipo === 'T') {
    $tipoLabel = 'Todos';
}
$titulo = 'Filtros - ' . $tipoLabel;
?>

<div class="container mt-4">
    <style>
    .listas-filtros-card {
        border: 1px solid #e5e7eb;
        border-radius: .85rem;
    }
    .listas-filtros-topo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #eef1f4;
    }
    .listas-filtros-topo small {
        display: block;
        color: #6b7280;
    }
    .listas-filtros-acoes {
        display: flex;
        justify-content: flex-end;
        align-items: end;
    }
    @media (max-width: 767.98px) {
        .listas-filtros-topo {
            flex-direction: column;
            align-items: flex-start;
        }
        .listas-filtros-acoes {
            justify-content: stretch;
        }
        .listas-filtros-acoes .btn {
            width: 100%;
        }
    }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><?= h($titulo) ?></h4>
        <a href="<?= $this->Url->build(['controller' => 'Index', 'action' => 'dashboard']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <div class="card mb-3 shadow-sm listas-filtros-card">
        <div class="card-body">
            <?= $this->Form->create(null, [
                'type' => 'get',
                'url' => ['controller' => 'Listas', 'action' => 'resultado', $tipo ?: null],
                'class' => 'row g-3'
            ]) ?>
                <div class="col-12 listas-filtros-topo">
                    <div>
                        <div class="fw-semibold">Refinar listagem</div>
                        <small>Combine os filtros abaixo para encontrar os registros desejados.</small>
                    </div>
                    <span class="badge bg-light text-dark border"><?= h($tipoLabel) ?></span>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('programa', [
                        'label' => 'Programa',
                        'options' => $prog,
                        'empty' => 'Todos',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('fase_id', [
                        'label' => 'Fase',
                        'options' => $situacao ?? [],
                        'empty' => 'Todos',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('homologado', [
                        'label' => 'Homologação',
                        'options' => $homologacao ?? [],
                        'default' => 'T',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('origem', [
                        'label' => 'Origem',
                        'options' => $origem ?? [],
                        'empty' => 'Todas',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('cota', [
                        'label' => 'Cota',
                        'options' => $cotas ?? [],
                        'empty' => 'Todas',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('heranca', [
                        'label' => 'Herança',
                        'options' => $herancaOptions ?? [],
                        'default' => 'T',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-12 listas-filtros-acoes">
                    <?= $this->Form->button('Buscar', ['class' => 'btn btn-primary px-4']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
