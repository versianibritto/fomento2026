<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $convites
 * @var array<int|string, int|string> $anos
 * @var array<string, string> $aceiteOptions
 * @var array<string, mixed> $filtros
 * @var array<int|string, string> $editais
 */
?>
<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h4 class="mb-1">Listagem de Convites para Avaliadores</h4>
                            <p class="text-muted mb-0">Convites cadastrados na tabela <code>convites</code>.</p>
                        </div>
                        <?= $this->Html->link(
                            'Novo cadastro massivo',
                            ['controller' => 'Avaliadores', 'action' => 'cadastroConvites'],
                            ['class' => 'btn btn-outline-primary btn-sm']
                        ) ?>
                    </div>

                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3 align-items-end mb-4',
                    ]) ?>
                        <div class="col-md-3">
                            <?= $this->Form->control('ano', [
                                'label' => 'Ano',
                                'options' => $anos,
                                'empty' => 'Todos',
                                'default' => $filtros['ano'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('aceite', [
                                'label' => 'Aceite',
                                'options' => $aceiteOptions,
                                'empty' => 'Todos',
                                'default' => $filtros['aceite'] ?? '',
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Avaliadores', 'action' => 'listaConvites'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if ($convites->count() === 0): ?>
                        <div class="alert alert-info mb-0">
                            Nenhum convite localizado com os filtros informados.
                        </div>
                    <?php else: ?>
                        <?php $this->Paginator->options(['url' => $this->request->getQueryParams()]); ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuário</th>
                                        <th>CPF</th>
                                        <th>Ano</th>
                                        <th>Editais</th>
                                        <th>Aceite</th>
                                        <th>Cadastrado por</th>
                                        <th>Criado em</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($convites as $convite): ?>
                                        <?php
                                        $aceiteValor = $convite->aceite;
                                        if ($aceiteValor === null) {
                                            $aceiteTexto = 'Não respondido';
                                            $aceiteClasse = 'secondary';
                                        } elseif ((int)$aceiteValor === 1) {
                                            $aceiteTexto = 'Aceito';
                                            $aceiteClasse = 'success';
                                        } else {
                                            $aceiteTexto = 'Recusado';
                                            $aceiteClasse = 'danger';
                                        }

                                        $editaisIds = array_values(array_filter(array_map('trim', explode(',', (string)($convite->editais ?? '')))));
                                        $editaisNomes = [];
                                        foreach ($editaisIds as $editaiId) {
                                            $idInt = (int)$editaiId;
                                            $editaisNomes[] = $editais[$idInt] ?? $editaiId;
                                        }
                                        ?>
                                        <tr>
                                            <td><?= (int)$convite->id ?></td>
                                            <td><?= h((string)($convite->usuario->nome ?? 'Não localizado')) ?></td>
                                            <td><?= h((string)($convite->usuario->cpf ?? '-')) ?></td>
                                            <td><?= h((string)($convite->ano ?? '-')) ?></td>
                                            <td>
                                                <div><?= h((string)($convite->editais ?? '-')) ?></div>
                                                <?php if (!empty($editaisNomes)): ?>
                                                    <small class="text-muted"><?= h(implode(' | ', $editaisNomes)) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-<?= h($aceiteClasse) ?>"><?= h($aceiteTexto) ?></span></td>
                                            <td><?= h((string)($convite->cadastrador->nome ?? '-')) ?></td>
                                            <td>
                                                <?= !empty($convite->created) ? h($convite->created->i18nFormat('dd/MM/yyyy HH:mm')) : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                            <?= $this->Paginator->prev('« Anterior', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= $this->Paginator->numbers([
                                'before' => '',
                                'after' => '',
                                'modulus' => 4,
                            ]) ?>
                            <?= $this->Paginator->next('Próxima »', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <span class="ms-2 text-muted">
                                <?= $this->Paginator->counter('Página {{page}} de {{pages}} | Total: {{count}}') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
