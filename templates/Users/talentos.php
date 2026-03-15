<?php
$programaOptions = [
    'I' => 'IC Manguinhos/ENSP',
    'A' => 'IC Mata Atlântica',
    'M' => 'IC Maré',
    'G' => 'IC Indígena',
    'C' => 'IC Coleções Biológicas',
    'N' => 'Não me enquadro nestes editais',
];
?>

<style>
.talentos-card {
    border: 0;
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.talentos-hero {
    background: linear-gradient(135deg, #f4f7fb 0%, #ffffff 100%);
    border: 1px solid #e4ebf3;
    border-radius: 1rem;
    padding: 1.25rem;
}

.talentos-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: .25rem .65rem;
    font-size: .78rem;
    font-weight: 600;
    background: #eef4ff;
    color: #2f5ea6;
}

.talentos-meta {
    font-size: .86rem;
    color: #6c757d;
}

.talentos-empty {
    border: 1px dashed #c7d3e0;
    border-radius: 1rem;
    background: #f8fbff;
}
</style>

<section class="mt-n3">
    <div class="talentos-hero mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h3 class="mb-1">Banco de Talentos</h3>
                <div class="text-muted">Filtre candidatos por nome, curso e edital de interesse.</div>
            </div>
            <div class="talentos-meta">
                <?= $this->Paginator->counter('Total: {{count}} registro(s)') ?>
            </div>
        </div>
    </div>

    <div class="card talentos-card mb-3">
        <div class="card-body">
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Users', 'action' => 'talentos'],
                'class' => 'row g-3 align-items-end',
            ]) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('nome', [
                        'label' => 'Nome',
                        'class' => 'form-control',
                        'placeholder' => 'Parte do nome',
                        'maxlength' => 150,
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->control('curso', [
                        'label' => 'Curso',
                        'class' => 'form-control',
                        'placeholder' => 'Parte do curso',
                        'maxlength' => 45,
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->control('programa', [
                        'label' => 'Programa/Edital de interesse',
                        'options' => $programaOptions,
                        'empty' => '- Todos -',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <?= $this->Form->button('<i class="fa fa-search me-1"></i> Buscar', [
                        'class' => 'btn btn-primary',
                        'escapeTitle' => false,
                    ]) ?>
                    <?= $this->Html->link('Limpar filtros', ['controller' => 'Users', 'action' => 'talentos', true], [
                        'class' => 'btn btn-outline-secondary',
                    ]) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <?php if ($usuarios->count() === 0): ?>
        <div class="talentos-empty text-center p-4">
            <div class="fw-semibold mb-1">Nenhum candidato encontrado</div>
            <div class="text-muted">Refaça os filtros ou limpe a busca para visualizar todos os registros.</div>
        </div>
    <?php else: ?>
        <div class="card talentos-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuário</th>
                                <th>Curso</th>
                                <th class="d-none d-lg-table-cell">Gênero</th>
                                <th class="d-none d-lg-table-cell">Raça</th>
                                <th class="d-none d-lg-table-cell">Deficiência</th>
                                <th>Edital de interesse</th>
                                <th class="text-center" style="width: 90px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <?php
                                $nome = explode(' ', (string)$u->nome);
                                $nomeCurto = $nome[0] . ' ' . end($nome);
                                $programaLabel = !empty($u->ic) && isset($programaOptions[$u->ic]) ? $programaOptions[$u->ic] : 'Não informado';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= h(trim($nomeCurto)) ?></div>
                                    </td>
                                    <td><?= $u->curso === null || $u->curso === '' ? 'Não cadastrado' : h($u->curso) ?></td>
                                    <td class="d-none d-lg-table-cell"><?= $u->sexo === null || $u->sexo === '' ? 'Não cadastrado' : h($sexo[$u->sexo] ?? $u->sexo) ?></td>
                                    <td class="d-none d-lg-table-cell"><?= $u->raca === null || $u->raca === '' ? 'Não cadastrado' : h($racas[$u->raca] ?? $u->raca) ?></td>
                                    <td class="d-none d-lg-table-cell"><?= $u->deficiencia === null || $u->deficiencia === '' ? 'Não cadastrado' : h($deficiencia[$u->deficiencia] ?? $u->deficiencia) ?></td>
                                    <td>
                                        <?php if ($programaLabel === 'Não informado'): ?>
                                            <span class="text-muted"><?= h($programaLabel) ?></span>
                                        <?php else: ?>
                                            <span class="talentos-chip"><?= h($programaLabel) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $this->Html->link('<i class="fa fa-eye"></i>', ['action' => 'curriculo', $u->id], [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'escape' => false,
                                            'title' => 'Visualizar currículo',
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <div class="small text-muted">
                <?= $this->Paginator->counter('Página {{page}} de {{pages}}') ?>
            </div>
            <ul class="pagination mb-0">
                <?= $this->Paginator->first('<<') ?>
                <?= $this->Paginator->prev('<') ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next('>') ?>
                <?= $this->Paginator->last('>>') ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($ehTi)): ?>
        <div class="d-flex justify-content-end mt-3">
            <?= $this->Html->link('Exportar CSV', ['controller' => 'Users', 'action' => 'talentos', '?' => ['export' => 'csv']], [
                'class' => 'btn btn-sm btn-outline-secondary',
                'title' => 'Exportar extração completa do banco de talentos',
            ]) ?>
        </div>
    <?php endif; ?>
</section>
