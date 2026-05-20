<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Banca> $bancas
 * @var array<int|string, string> $grandesAreas
 * @var array<string, string> $periodos
 * @var array<string, mixed> $filtros
 */
$bancas = is_array($bancas) ? $bancas : $bancas->toArray();
?>

<div class="container-fluid p-1 pt-1">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Bancas Workshop</h4>
                    <div class="text-muted">Cadastro das bancas do Workshop.</div>
                </div>
                <?= $this->Html->link('Nova banca', ['controller' => 'Workshops', 'action' => 'adicionarBanca'], ['class' => 'btn btn-primary']) ?>
            </div>

            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3 align-items-end mb-4']) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('grandes_areas_id', [
                        'label' => 'Grande área',
                        'options' => $grandesAreas,
                        'empty' => 'Todas',
                        'value' => $filtros['grandes_areas_id'] ?? 0,
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('data', [
                        'label' => 'Data',
                        'type' => 'date',
                        'value' => $filtros['data'] ?? '',
                        'class' => 'form-control',
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->control('periodo', [
                        'label' => 'Período',
                        'options' => $periodos,
                        'empty' => 'Todos',
                        'value' => $filtros['periodo'] ?? '',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Html->link('Limpar', ['controller' => 'Workshops', 'action' => 'bancas'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= $this->Form->end() ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Grande área</th>
                            <th>Data</th>
                            <th>Período</th>
                            <th>Edital</th>
                            <th>Avaliadores</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bancas as $banca): ?>
                            <?php
                            $avaliadores = [];
                            foreach (($banca->banca_usuarios ?? []) as $vinculo) {
                                $nome = trim((string)($vinculo->avaliador->usuario->nome ?? ''));
                                $avaliadores[] = $nome !== '' ? $nome : ('Avaliador #' . (int)($vinculo->avaliador_id ?? 0));
                            }
                            ?>
                            <tr>
                                <td><?= (int)$banca->id ?></td>
                                <td><?= h((string)($banca->nome ?? '')) ?></td>
                                <td><?= h((string)($banca->grandes_area->nome ?? 'Não informada')) ?></td>
                                <td><?= !empty($banca->data) ? h($banca->data->i18nFormat('dd/MM/YYYY')) : 'Não informada' ?></td>
                                <td><?= h($periodos[(string)($banca->periodo ?? '')] ?? 'Não informado') ?></td>
                                <td><?= h((string)($banca->editai->nome ?? 'Não informado')) ?></td>
                                <td><?= $avaliadores !== [] ? h(implode(', ', $avaliadores)) : 'Nenhum' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bancas)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Nenhuma banca cadastrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
