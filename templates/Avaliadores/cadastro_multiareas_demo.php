<?php
/**
 * Tela demonstrativa sem integração de gravação.
 *
 * @var \App\View\AppView $this
 * @var array<int|string, string> $grandesAreas
 * @var array<int, array<int, string>> $areasPorGrandeArea
 */
$usuarioLogado = $this->request->getAttribute('identity');
$nomeUsuario = trim((string)($usuarioLogado->nome ?? $usuarioLogado['nome'] ?? 'avaliador'));
?>

<section class="mt-n3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h2 class="mb-0">Convite para Avaliador</h2>
            <div class="text-muted mt-1">Demonstração do fluxo de aceite ou recusa de convite.</div>
        </div>
        <a href="#" class="btn btn-outline-secondary" onclick="return false;">Voltar</a>
    </div>
    <hr>

    <form class="row g-3" onsubmit="return false;">
        <div class="col-12">
            <div class="alert alert-info border mb-0">
                <h4 class="alert-heading mb-2">Olá, Sr(a). <?= h($nomeUsuario) ?>!</h4>
                <p class="mb-2">
                    Você recebeu um convite para atuar como avaliador(a) nos processos de fomento.
                    Para confirmar o aceite, informe abaixo as grandes áreas e áreas em que deseja avaliar.
                </p>
                <p class="mb-0 text-danger fw-semibold">
                    (Dra Márcia, esta tela é apenas demonstrativa: os botões de aceite e recusa não gravam informações.)
                </p>
            </div>
        </div>

        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="areas-table" style="min-width: 900px;">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 280px;">Grande área</th>
                            <th style="min-width: 320px;">Área</th>
                            <th class="text-center" style="min-width: 70px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-index="0">
                            <td>
                                <select name="vinculos[0][grandes_area_id]" class="form-select grande-area-select" required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($grandesAreas as $id => $nome): ?>
                                        <option value="<?= (int)$id ?>"><?= h($nome) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="vinculos[0][area_id]" class="form-select area-select" required>
                                    <option value="">Selecione a grande área</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-row" disabled title="Remover">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary" id="add-area-row">Adicionar área</button>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-danger">Recusar convite</button>
                <button type="button" class="btn btn-success">Aceitar convite</button>
            </div>
        </div>
    </form>

    <div class="mt-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div>
                <h4 class="mb-0">Áreas já informadas</h4>
                <div class="text-muted mt-1">Tabela demonstrativa das áreas associadas ao usuário logado em convites anteriores.</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 260px;">Grande área</th>
                        <th style="min-width: 260px;">Área</th>
                        <th style="min-width: 130px;">Situação</th>
                        <th class="text-center" style="min-width: 160px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Ciências Biológicas e Biomédicas</td>
                        <td>Imunologia</td>
                        <td><span class="badge bg-success">Ativo</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm">Inativar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Saúde Coletiva</td>
                        <td>Epidemiologia</td>
                        <td><span class="badge bg-success">Ativo</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm">Inativar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Ciências Humanas e Sociais e Interdisciplinar</td>
                        <td>Políticas Públicas</td>
                        <td><span class="badge bg-secondary">Inativo</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-success btn-sm">Reativar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
(function() {
    const areasPorGrandeArea = <?= json_encode($areasPorGrandeArea, JSON_UNESCAPED_UNICODE) ?>;
    const tableBody = document.querySelector('#areas-table tbody');
    const addButton = document.getElementById('add-area-row');

    function areaOptions(grandeAreaId) {
        const areas = areasPorGrandeArea[grandeAreaId] || {};
        let html = '<option value="">Selecione</option>';
        Object.keys(areas).forEach(function(id) {
            html += `<option value="${id}">${areas[id]}</option>`;
        });
        return html;
    }

    function updateAreaSelect(row) {
        const grandeArea = row.querySelector('.grande-area-select');
        const area = row.querySelector('.area-select');
        if (!grandeArea || !area) {
            return;
        }
        area.innerHTML = grandeArea.value ? areaOptions(grandeArea.value) : '<option value="">Selecione a grande área</option>';
    }

    function updateRemoveButtons() {
        const buttons = tableBody.querySelectorAll('.remove-row');
        buttons.forEach(function(button, index) {
            button.disabled = buttons.length === 1 && index === 0;
        });
    }

    function buildRow(index) {
        const row = document.createElement('tr');
        row.setAttribute('data-index', index);
        row.innerHTML = `
            <td>
                <select name="vinculos[${index}][grandes_area_id]" class="form-select grande-area-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($grandesAreas as $id => $nome): ?>
                        <option value="<?= (int)$id ?>"><?= h($nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="vinculos[${index}][area_id]" class="form-select area-select" required>
                    <option value="">Selecione a grande área</option>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Remover">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        return row;
    }

    addButton.addEventListener('click', function() {
        const index = tableBody.querySelectorAll('tr').length;
        tableBody.appendChild(buildRow(index));
        updateRemoveButtons();
    });

    tableBody.addEventListener('change', function(event) {
        if (!event.target.classList.contains('grande-area-select')) {
            return;
        }
        const row = event.target.closest('tr');
        if (row) {
            updateAreaSelect(row);
        }
    });

    tableBody.addEventListener('click', function(event) {
        const button = event.target.closest('.remove-row');
        if (!button) {
            return;
        }
        const row = button.closest('tr');
        if (row) {
            row.remove();
            updateRemoveButtons();
        }
    });

    updateRemoveButtons();
})();
</script>
