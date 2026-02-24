<h3>Resultado da Busca de Usuários</h3>

<style>
/* Bolinhas para tipo de usuário empilhadas */
.tipo-usuario {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.tipo-usuario .linha-tipo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Bolinhas coloridas */
.bolinha {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
.bolinha-gestao { background-color: #198754; }    /* verde */
.bolinha-coord-unidade { background-color: #ffc107; } /* amarelo */
.bolinha-coord-programa { background-color: #0d6efd; } /* azul */

/* Botões de ação */
.btn-outline-blue {
    color: #0d6efd;
    border: 1px solid #0d6efd;
    background-color: #fff;
}
.btn-outline-blue:hover {
    background-color: #0d6efd;
    color: #fff;
}
</style>

<div class="mb-3 d-flex justify-content-between align-items-center">
    <?= $this->Html->link(
        '<i class="fa fa-search me-1"></i> Nova Busca',
        ['action' => 'index'],
        ['class' => 'btn btn-secondary', 'escape' => false]
    ) ?>

    <small class="text-muted">
        <?= $this->Paginator->counter() ?>
    </small>
</div>

<?php if (empty($usuarios) || $usuarios->count() === 0): ?>
    <div class="card text-center mb-3 border-primary" style="background-color: rgba(0,123,255,0.1);">
        <div class="card-body py-4">
            <i class="bi bi-exclamation-triangle-fill fs-2 text-primary mb-2"></i>
            <p class="mb-0 fw-semibold text-default">
                Nenhum registro encontrado. Tente outros filtros ou redefina sua busca.
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover my-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->Paginator->sort('Usuarios.nome', 'Usuário') ?></th>
                        <th class="d-none d-xl-table-cell"><?= $this->Paginator->sort('Unidades.sigla', 'Unidade') ?></th>
                        <th>Tipo de Usuário</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>
                            <strong>CPF</strong>
                            <?php
                            if ($this->request->getAttribute('identity')['yoda']) {
                                echo h($u->cpf);
                            } else {
                                echo substr($u->cpf, 0, 3) . '.***.***-' . substr($u->cpf, -2);
                            }
                            ?>
                            <br>
                            <?= h($u->nome) ?>
                        </td>

                        <td class="d-none d-xl-table-cell">
                            <?= $u->unidade->sigla ?? 'Não informado' ?>
                        </td>

                        <td>
                            <div class="tipo-usuario">
                                <?php if ($u->yoda): ?>
                                    <div class="linha-tipo">
                                        <span class="bolinha bolinha-gestao" title="Gestão Fomento"></span>
                                        <span>Gestão Fomento</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($u->jedi !== null && $u->jedi !== ''): ?>
                                    <div class="linha-tipo">
                                        <span class="bolinha bolinha-coord-unidade" title="Coordenação de Unidade"></span>
                                        <span>Coord. Unidade</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($u->padauan !== null && $u->padauan !== ''): ?>
                                    <div class="linha-tipo">
                                        <span class="bolinha bolinha-coord-programa" title="Coordenação de Programa"></span>
                                        <span>Coord. Programa</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="text-center">
                            <?= $this->Html->link(
                                '<i class="fa fa-eye"></i>',
                                ['action' => 'ver', $u->id],
                                ['class' => 'btn btn-sm btn-outline-blue', 'escape' => false, 'title' => 'Visualizar']
                            ) ?>

                            <?php if ($this->request->getAttribute('identity')['yoda']): ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-edit"></i>',
                                    ['action' => 'editar', $u->id],
                                    ['class' => 'btn btn-sm btn-primary ms-1', 'escape' => false, 'title' => 'Editar']
                                ) ?>
                            <?php endif; ?>

                            <?= $this->Html->link(
                                '<i class="fa fa-info-circle"></i>',
                                ['controller' => 'Users', 'action' => 'gestao', $u->id],
                                ['class' => 'btn btn-sm btn-outline-secondary ms-1', 'escape' => false, 'title' => 'Detalhes']
                            ) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        <?= $this->Paginator->numbers([
            'class' => 'pagination',
            'modulus' => 5
        ]) ?>
    </div>
<?php endif; ?>
