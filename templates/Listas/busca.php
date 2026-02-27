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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><?= h($titulo) ?></h4>
        <a href="<?= $this->Url->build(['controller' => 'Index', 'action' => 'dashboard']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <?= $this->Form->create(null, [
                'type' => 'get',
                'url' => ['controller' => 'Listas', 'action' => 'resultado', $tipo ?: null],
                'class' => 'row g-2 align-items-end'
            ]) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('programa', [
                        'label' => 'Programa',
                        'options' => $prog,
                        'empty' => 'Todos',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->control('fase_id', [
                        'label' => 'Fase',
                        'options' => $situacao ?? [],
                        'empty' => 'Todos',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <?= $this->Form->button('Buscar', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
