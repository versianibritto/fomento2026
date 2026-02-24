<?php
$palette = [
    '#0d6efd',
    '#198754',
    '#dc3545',
    '#fd7e14',
    '#6f42c1',
    '#20c997',
    '#0dcaf0',
    '#adb5bd',
    '#6610f2',
    '#ffc107',
];
$colorMap = [];
$colorIndex = 0;
foreach ($datasets as $ds) {
    $colorMap[$ds['label']] = $palette[$colorIndex % count($palette)];
    $colorIndex++;
}
?>

<div class="container-fluid p-1 pt-1">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="fw-semibold text-primary mb-1">
                Gráfico de Inscrições por Edital (Bloco I)
            </h4>
            <div class="text-muted">
                Cada edital possui uma barra por fase em andamento.
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <?= $this->Form->create(null, ['type' => 'get']) ?>
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <?= $this->Form->label('edital_id', 'Edital') ?>
                    <?= $this->Form->select('edital_id', ['' => 'Todos'] + $editaisList, [
                        'class' => 'form-control',
                        'value' => $editalId ?? '',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->label('fase_id', 'Fase') ?>
                    <?= $this->Form->select('fase_id', ['' => 'Todas'] + $fasesList, [
                        'class' => 'form-control',
                        'value' => $faseId ?? '',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->label('programa_id', 'Programa') ?>
                    <?= $this->Form->select('programa_id', ['' => 'Todos'] + $programasList, [
                        'class' => 'form-control',
                        'value' => $programaId ?? '',
                    ]) ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Limpar', ['action' => 'graficosInscricoes'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
                </div>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <canvas id="graficoInscricoes" height="120"></canvas>
        </div>
    </div>
</div>

<script>
    (function () {
        var labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
        var datasets = <?= json_encode($datasets, JSON_UNESCAPED_UNICODE) ?>;
        var colors = <?= json_encode($colorMap, JSON_UNESCAPED_UNICODE) ?>;

        datasets = datasets.map(function (ds) {
            var color = colors[ds.label] || '#0d6efd';
            return {
                label: ds.label,
                data: ds.data,
                backgroundColor: color,
                borderColor: color,
                borderWidth: 1
            };
        });

        var ctx = document.getElementById('graficoInscricoes');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    })();
</script>
