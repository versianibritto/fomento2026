<style>
.grafico-header {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    align-items: center;
    justify-content: space-between;
}

.grafico-area {
    height: clamp(240px, 45vh, 360px);
}

.grafico-filter-list {
    max-height: clamp(90px, 18vh, 140px);
    overflow-y: auto;
}
</style>

<div class="grafico-header mb-2">
    <div>
        <h4 class="text-primary mb-1">
            Inscrições dos Editais em Andamento 
        </h4>
        <div class="text-muted fw-semibold">
            Apenas editais com fim de vigência maior que hoje são plotados.
        </div>
    </div>
    <?= $this->Html->link(
        '<i class="fa fa-arrow-left me-2"></i> Voltar ao Painel',
        ['controller' => 'Index', 'action' => 'dashyoda'],
        ['class' => 'btn btn-outline-secondary btn-sm', 'escape' => false]
    ) ?>
</div>

<?php if (!empty($programasList) || !empty($fasesList)): ?>
    <div class="card mb-2">
        <div class="card-body py-2">
            <?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query']]) ?>
            <div class="row g-2 align-items-center mb-2">
                <div class="col-12 col-lg-6">
                    <div class="fw-semibold">Programas</div>
                </div>
                <div class="col-12 col-lg-6 d-flex justify-content-lg-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="select-all-programas">
                        Selecionar todos
                    </button>
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary btn-sm']) ?>
                    <?= $this->Html->link(
                        'Limpar',
                        ['controller' => 'Grafico', 'action' => 'inscricoesEmAndamento'],
                        ['class' => 'btn btn-outline-secondary btn-sm']
                    ) ?>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <div class="border rounded p-2 bg-light grafico-filter-list">
                        <?= $this->Form->control('programa_id', [
                            'type' => 'select',
                            'multiple' => 'checkbox',
                            'options' => $programasList,
                            'value' => $programaId ?? [],
                            'val' => $programaId ?? [],
                            'label' => false,
                            'class' => 'form-check-input me-1',
                            'templates' => [
                                'nestingLabel' => '<label class="form-check-label ms-1">{{input}}{{text}}</label>',
                                'checkboxWrapper' => '<div class="form-check me-3 mb-2 d-inline-flex align-items-center">{{label}}</div>',
                            ],
                        ]) ?>
                    </div>
                </div>
                <?php /* <div class="col-12 mt-2">
                    <?= $this->Form->label('fase_id', 'Status') ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Selecione um ou mais status.</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="select-all-status">
                            Selecionar todos os status
                        </button>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <?= $this->Form->control('fase_id', [
                            'type' => 'select',
                            'multiple' => 'checkbox',
                            'options' => $fasesList ?? [],
                            'value' => $faseId ?? [],
                            'val' => $faseId ?? [],
                            'label' => false,
                            'class' => 'form-check-input me-1',
                            'templates' => [
                                'nestingLabel' => '<label class="form-check-label ms-1">{{input}}{{text}}</label>',
                                'checkboxWrapper' => '<div class="form-check me-3 mb-2 d-inline-flex align-items-center">{{label}}</div>',
                            ],
                        ]) ?>
                    </div>
                </div> */ ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
<?php endif; ?>

<script>
    (function () {
        var btn = document.getElementById('select-all-programas');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var checks = document.querySelectorAll('input[name="programa_id[]"]');
            var allChecked = true;
            checks.forEach(function (chk) {
                if (!chk.checked) {
                    allChecked = false;
                }
            });
            checks.forEach(function (chk) {
                chk.checked = !allChecked;
            });
        });
    })();
</script>

<?php /* <script>
    (function () {
        var btn = document.getElementById('select-all-status');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var checks = document.querySelectorAll('input[name="fase_id[]"]');
            var allChecked = true;
            checks.forEach(function (chk) {
                if (!chk.checked) {
                    allChecked = false;
                }
            });
            checks.forEach(function (chk) {
                chk.checked = !allChecked;
            });
        });
    })();
</script> */ ?>

<style>
.grafico-side {
    max-height: clamp(240px, 45vh, 360px);
    overflow-y: auto;
}

.grafico-side-card {
    width: 100%;
    max-width: 220px;
}
</style>

<div class="row g-3">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body grafico-area">
                <canvas id="inscricoes"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-2 d-flex justify-content-end">
        <div class="card h-100 grafico-side-card">
            <div class="card-body grafico-side">
                <div class="fw-semibold text-muted mb-2">Total de inscrições</div>
                <?php foreach($programaTotais as $programa => $totalPrograma): ?>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="fw-semibold"><?= h((string)$programa) ?></span>
                        <span class="text-primary fw-semibold"><?= h($totalPrograma) ?></span>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    </div>
</div>


<script src="<?= $this->Url->webroot('js/chart.min.js'); ?>"></script>




<script>
    var labels = <?php echo json_encode($chartLabels ?? []); ?>;
    var datasets = <?php echo json_encode($chartDatasets ?? []); ?>;

    var palette = {
        I: ['#0a2a4a', '#12385d', '#1a4770', '#235682', '#2b6594'],
        F: ['#1f5d34', '#266b3e', '#2d7948'],
        H: ['#134422', '#1a562b', '#226734'],
        R: ['#4b5563', '#5f6672', '#727986']
    };
    var paletteIndex = { I: 0, F: 0, H: 0, R: 0 };

    datasets = datasets.map(function (ds) {
        var match = ds.label.match(/\(([IFHR])\)\s*$/);
        var bloco = match ? match[1] : 'I';
        var colors = palette[bloco] || palette.I;
        var idx = paletteIndex[bloco] || 0;
        var color = colors[idx % colors.length];
        paletteIndex[bloco] = idx + 1;
        return {
            label: ds.label,
            data: ds.data,
            backgroundColor: color,
            borderColor: color,
            borderWidth: 1
        };
    });

    var insc = document.getElementById('inscricoes').getContext('2d');
    var graph = new Chart(insc, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    backgroundColor: 'white'
                }
            }
        }
    });
</script>
