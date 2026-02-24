<h3 class="mb-4">Consulta Gerencial</h3>

<!-- ===============================
 BLOCO 0 — CONSULTAS RÁPIDAS
================================ -->
<div class="card mb-3">
    <div class="card-body">

        <div class="fw-semibold mb-2">
            Consultas rápidas
        </div>

        <div class="d-flex flex-wrap gap-2">

            <a
                href="<?= $this->Url->build([
                    'controller' => 'Users',
                    'action' => 'gestao',
                    $usuario->id,
                    '?' => ['preset' => 'orientacoes_vigentes', 'dominio' => 'I']
                ]) ?>"
                class="btn btn-outline-primary btn-sm">
                Orientações vigentes
            </a>

            <a
                href="<?= $this->Url->build([
                    'controller' => 'Users',
                    'action' => 'gestao',
                    $usuario->id,
                    '?' => ['preset' => 'bolsistas_vigentes', 'dominio' => 'I']
                ]) ?>"
                class="btn btn-outline-primary btn-sm">
                Bolsistas vigentes
            </a>

            <a
                href="<?= $this->Url->build([
                    'controller' => 'Users',
                    'action' => 'gestao',
                    $usuario->id,
                    '?' => ['preset' => 'avaliacoes_pendentes', 'dominio' => 'A']
                ]) ?>"
                class="btn btn-outline-primary btn-sm">
                Avaliações pendentes
            </a>

            <a
                href="<?= $this->Url->build([
                    'controller' => 'Users',
                    'action' => 'gestao',
                    $usuario->id,
                    '?' => ['preset' => 'egressos', 'dominio' => 'I']
                ]) ?>"
                class="btn btn-outline-secondary btn-sm">
                Egressos
            </a>

            <a
                href="<?= $this->Url->build([
                    'controller' => 'Users',
                    'action' => 'gestao',
                    $usuario->id,
                    '?' => ['preset' => 'suspensos', 'dominio' => 'I']
                ]) ?>"
                class="btn btn-outline-secondary btn-sm">
                Suspensos
            </a>

        </div>

        <small class="text-muted d-block mt-2">
            As consultas rápidas ignoram os filtros abaixo.
        </small>

    </div>
</div>


<form method="get">

<!-- ===============================
 BLOCO 1 — DADOS USUÁRIO
================================ -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex align-items-center">
            <div class="me-3 text-muted">
                <i class="bi bi-person-circle fs-4"></i>
            </div>

            <div>
                <div class="fw-bold">
                    <?= h($usuario->nome) ?>
                </div>
                <small class="text-muted">
                    ID do usuário: <?= h($usuario->id) ?>
                </small>
            </div>
        </div>
    </div>
</div>


<!-- ===============================
 BLOCO 2 — TIPO DE CONSULTA
================================ -->
<div class="card mb-3">
    <div class="card-header fw-bold">
        Tipo de consulta
    </div>
    <div class="card-body">

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipo" value="inscricao">
            <label class="form-check-label">Inscrição</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipo" value="avaliacao">
            <label class="form-check-label">Avaliação</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipo" value="raic">
            <label class="form-check-label">RAIC</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipo" value="workshop">
            <label class="form-check-label">Workshop</label>
        </div>

    </div>
</div>


<!-- ===============================
 BLOCO 3 e 4 — FILTROS TRANSVERSAIS = INSCRIÇÃO
================================ -->
<div id="linha-filtros" class="row g-2 d-none">

    <!-- PAPEL -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Papel</div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="papel[]" value="orientador">
                    <label class="form-check-label">Orientador</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="papel[]" value="bolsista">
                    <label class="form-check-label">Bolsista</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="papel[]" value="coorientador">
                    <label class="form-check-label">Coorientador</label>
                </div>
            </div>
        </div>
    </div>

    <!-- PROGRAMA -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Programa</div>
            <div class="card-body">
                <?php foreach ($programas as $p): ?>
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               name="programa[]"
                               value="<?= $p->id ?>">
                        <label class="form-check-label">
                            <?= h($p->sigla) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- SITUAÇÃO -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Situação</div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="situacao[]" value="vigente">
                    <label class="form-check-label">Vigentes</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="situacao[]" value="egresso">
                    <label class="form-check-label">Egressos</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="situacao[]" value="nao_efetivado">
                    <label class="form-check-label">Não efetivados</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="situacao[]" value="suspenso">
                    <label class="form-check-label">Suspensos</label>
                </div>

                <hr class="my-2">

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="incluir_deletados" value="1">
                    <label class="form-check-label">
                        Incluir deletados
                    </label>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- ===============================
 BLOCO 5 — AÇÕES
================================ -->
<div class="d-flex justify-content-end gap-2 mb-4 mt-3">
    <button type="submit"
            name="acao"
            value="buscar"
            class="btn btn-primary">
        Buscar
    </button>

    <button type="submit"
            name="acao"
            value="excel"
            class="btn btn-outline-success">
        Exportar Excel
    </button>
</div>

</form>

<?php if ($resultados !== null): ?>

    <div class="card mt-4">

        <div class="card-header fw-bold">
            Resultado da consulta
        </div>

        <div class="card-body p-0">

            <?php if ($resultados !== null && $resultados->isEmpty()): ?>

                <div class="p-4 text-muted text-center">
                    Nenhum registro encontrado para os critérios informados.
                </div>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Programa</th>
                                <th>Papel</th>
                                <th>Situação</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($resultados as $r): ?>
                            <tr>
                                <td><?= h($r->id) ?></td>
                                <td><?= h($r->programa_sigla ?? '-') ?></td>
                                <td><?= h($r->papel ?? '-') ?></td>
                                <td><?= h($r->situacao ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

<?php endif; ?>



<script>
document.addEventListener('DOMContentLoaded', function () {

    const linhaFiltros = document.getElementById('linha-filtros');

    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', function () {

            if (['inscricao', 'raic', 'workshop'].includes(this.value)) {
                linhaFiltros.classList.remove('d-none');
            } else {
                linhaFiltros.classList.add('d-none');
            }

        });
    });

});
</script>
