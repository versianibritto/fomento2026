<?php
/**
 * Template: Users/ver.php
 * Header fixo com nome do usuário (viewport)
 */
?>

<style>
.user-id-bar {
    position: fixed;
    top: 60px; /* altura da navbar */
    left: 266px; /* sidebar + respiro */
    right: 16px;
    z-index: 1050;
    background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e3e6ea;
    border-radius: .6rem;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    padding: .45rem .9rem;
    font-size: .9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}

.user-id-label {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
}

.user-id-main {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    flex-wrap: wrap;
}

.user-id-chip {
    background: #eef2f7;
    border: 1px solid #d7dee8;
    border-radius: 999px;
    padding: .1rem .5rem;
    font-size: .82rem;
    color: #4a5568;
}

@media print {
    .user-id-bar {
        position: static;
        box-shadow: none;
        border-color: #999;
    }
}

/* Ajuste do conteúdo para não ficar escondido pelo topo fixo */
.body-offset-top {
    padding-top: 84px;
}

body.sidebar-collapse .user-id-bar {
    left: 16px;
}
</style>

<!-- ================= IDENTIFICAÇÃO FIXA NO TOPO ================= -->
<div class="user-id-bar">
    <div class="user-id-label">Identificação</div>
    <div class="user-id-main">
        <strong><?= h($usuario->nome) ?></strong>
        <span class="user-id-chip">CPF: <?= h($usuario->cpf) ?></span>
    </div>
</div>

<div class="container-fluid p-1 pt-1 body-offset-top">

    <div class="d-flex flex-wrap align-items-baseline justify-content-between">
        <h4 class="mt-2">Dados do Usuário</h4>
        <div class="mt-2">
            <span class="badge bg-light text-dark border">
                Última atualização:
                <?= !empty($usuario->last_data_update_date)
                    ? $usuario->last_data_update_date->i18nFormat('dd/MM/yyyy HH:mm')
                    : 'Não Informado' ?>
            </span>
        </div>
    </div>

    <!-- ================= AÇÕES ADMIN ================= -->
    <?php if (!empty($this->request->getAttribute('identity')['yoda'])): ?>
        <div class="row mb-3">
            <div class="col-12 d-flex flex-wrap gap-2">
                <?= $this->Html->link(
                    '<i class="fas fa-edit me-1"></i> Editar',
                    ['controller' => 'Users', 'action' => 'editar', $usuario->id],
                    ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                ) ?>

                <?= $this->Html->link(
                    '<i class="fas fa-history me-1"></i> Histórico de Alterações',
                    ['controller' => 'Users', 'action' => 'verhistorico', $usuario->id],
                    ['class' => 'btn btn-sm btn-info', 'escape' => false]
                ) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-body">

                <!-- ================= IDENTIFICAÇÃO ================= -->
                <h6 class="text-primary mb-2">Identificação</h6>

                <div class="row">
                    <div class="col-md-6">
                        <strong>Nome:</strong> <?= h($usuario->nome) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Nome Social:</strong>
                        <?= !empty($usuario->nome_social)
                            ? h($usuario->nome_social)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>CPF:</strong> <?= h($usuario->cpf) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Data de Nascimento:</strong>
                        <?= !empty($usuario->data_nascimento)
                            ? $usuario->data_nascimento->i18nFormat('dd/MM/yyyy')
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <hr>

                <!-- ================= DOCUMENTAÇÃO ================= -->
                <h6 class="text-primary mb-2">Documentação</h6>

                <div class="row">
                    <div class="col-md-6">
                        <strong>Documento:</strong>
                        <?php
                        if (!empty($usuario->documento) && isset($documentos[$usuario->documento])) {
                            echo h($documentos[$usuario->documento]);
                            if (!empty($usuario->documento_numero)) {
                                echo ' - ' . h($usuario->documento_numero);
                            }
                        } else {
                            echo '<span class="badge bg-danger">Não Informado</span>';
                        }
                        ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Órgão Emissor:</strong>
                        <?= !empty($usuario->documento_emissor)
                            ? h($usuario->documento_emissor)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>UF Emissor:</strong>
                        <?= !empty($usuario->documento_uf_emissor)
                            ? h($usuario->documento_uf_emissor)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Data de Emissão:</strong>
                        <?= !empty($usuario->documento_emissao)
                            ? $usuario->documento_emissao->i18nFormat('dd/MM/yyyy')
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <hr>

                <!-- ================= PERFIL ================= -->
                <h6 class="text-primary mb-2">Perfil Sociodemográfico</h6>

                <div class="row">
                    <div class="col-md-6">
                        <strong>Gênero:</strong>
                        <?= (!empty($usuario->sexo) && isset($sexo[$usuario->sexo]))
                            ? h($sexo[$usuario->sexo])
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Raça:</strong>
                        <?= (!empty($usuario->raca) && isset($racas[$usuario->raca]))
                            ? h($racas[$usuario->raca])
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Deficiência:</strong>
                        <?= (!empty($usuario->deficiencia) && isset($deficiencia[$usuario->deficiencia]))
                            ? h($deficiencia[$usuario->deficiencia])
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Edital Social:</strong>
                        <?= !empty($usuario->ic)
                            ? ($usuario->ic === 'M' ? 'IC Maré'
                                : ($usuario->ic === 'A' ? 'IC Mata Atlântica'
                                : ($usuario->ic === 'I' ? 'IC Manguinhos/ENSP'
                                : 'Não se enquadra')))
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <hr>

                <!-- ================= FORMAÇÃO ================= -->
                <h6 class="text-primary mb-2">Formação e Vínculo</h6>

                <div class="row">
                    <div class="col-md-6">
                        <strong>Escolaridade:</strong>
                        <?= !empty($usuario->escolaridade)
                            ? h($usuario->escolaridade->nome)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Curso:</strong>
                        <?= !empty($usuario->curso)
                            ? h($usuario->curso)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Ano de Conclusão:</strong>
                        <?= !empty($usuario->ano_conclusao)
                            ? h($usuario->ano_conclusao)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Vínculo:</strong>
                        <?= !empty($usuario->vinculo)
                            ? h($usuario->vinculo->nome)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <hr>

                <!-- ================= CONTATO ================= -->
                <h6 class="text-primary mb-2">Contato</h6>

                <div class="row">       
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <?= !empty($usuario->email)
                            ? h($usuario->email)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Email Alternativo:</strong>
                        <?= !empty($usuario->email_alternativo)
                            ? h($usuario->email_alternativo)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div> 
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Email Contato:</strong>
                        <?= !empty($usuario->email_contato)
                            ? h($usuario->email_contato)
                            : '<span class="badge bg-danger">Não Informado</span>' ?>
                    </div>
                </div>

                <hr>

                <!-- ================= PERFIS ================= -->
                <h6 class="text-primary mb-2">Perfis de Acesso</h6>

                <div class="row">
                    <div class="col-md-4">
                        <strong>Gestão Fomento:</strong>
                        <?= !empty($usuario->yoda)
                            ? '<span class="badge bg-success">Sim</span>'
                            : '<span class="badge bg-danger">Não</span>' ?>
                    </div>

                    <div class="col-md-4">
                        <strong>Coordenação de Unidade:</strong>
                        <?= !empty($usuario->jedi)
                            ? '<span class="badge bg-success">Sim</span>'
                            : '<span class="badge bg-danger">Não</span>' ?>

                        <?php if (!empty($unidades)): ?>
                            <ul class="mt-1">
                                <?php foreach ($unidades as $uni): ?>
                                    <li><?= h($uni->sigla) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <strong>Coordenação de Programa:</strong>
                        <?= !empty($programas)
                            ? '<span class="badge bg-success">Sim</span>'
                            : '<span class="badge bg-danger">Não</span>' ?>

                        <?php if (!empty($programas)): ?>
                            <ul class="mt-1">
                                <?php foreach ($programas as $prog): ?>
                                    <li><?= h($prog->sigla) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ================= GERENCIAR PERFIS (SÓ VOCÊ) ================= -->
    <?php if (in_array($this->request->getAttribute('identity')['id'], [1, 8088])): ?>
        <div class="row mt-5">
            <div class="col-12 d-flex justify-content-end gap-2">
                <?= $this->Html->link(
                    '<i class="fas fa-user-shield me-1"></i> Gerenciar Perfis',
                    ['controller' => 'Users', 'action' => 'addperfil', $usuario->id],
                    ['class' => 'btn btn-sm btn-outline-success', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-key me-1"></i> Ver Acessos',
                    ['controller' => 'Users', 'action' => 'veracessos', $usuario->id],
                    ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-bug me-1"></i> Erros do Usuário',
                    ['controller' => 'Restrito', 'action' => 'erros', '?' => ['usuario_id' => $usuario->id]],
                    ['class' => 'btn btn-sm btn-outline-danger', 'escape' => false]
                ) ?>
            </div>
        </div>
    <?php endif; ?>

</div>
<?php
$comoBolsista = [];
$comoOrientador = [];
$comoCoorientador = [];

foreach ($bolsas as $i) {
    if ($i['bolsista'] == $usuario->id) {
        $comoBolsista[] = $i;
    }
    if ($i['orientador'] == $usuario->id) {
        $comoOrientador[] = $i;
    }
    if (!empty($i['coorientador']) && $i['coorientador'] == $usuario->id) {
        $comoCoorientador[] = $i;
    }
}

$ehAdmin = in_array($this->request->getAttribute('identity')['id'], [1, 8088]);

function bolinhaEu() {
    return '<span class="badge bg-danger rounded-circle" style="width:10px;height:10px;display:inline-block"></span>';
}

function badgeVigente($vigente) {
    return $vigente
        ? '<span class="badge bg-success">Vigente</span>'
        : '<span class="badge bg-secondary">Não vigente</span>';
}

function renderTabela($lista, $titulo, $badge, $ehAdmin, $view, $usuarioId, $collapseId) {
?>
<div class="card shadow-sm mb-4">

    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div class="fw-semibold">
            <?= $badge ?> <?= h($titulo) ?>
        </div>
        <button class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="collapse"
                data-bs-target="#<?= $collapseId ?>">
            Expandir
        </button>
    </div>

    <div class="collapse" id="<?= $collapseId ?>">
        <div class="card-body p-0">

            <?php if (count($lista) > 0): ?>
                <div style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 sortable-table">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 3;">
                            <tr>
                                <th class="sortable">ID</th>
                                <th class="sortable">Orientador</th>
                                <th class="sortable">Bolsista</th>
                                <th class="sortable">Coorientador</th>
                                <th class="sortable">Edital</th>
                                <th class="sortable">Fase</th>
                                <th class="sortable">Vigente</th>
                                <th class="sortable">Início</th>
                                <th class="sortable">Fim</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($lista as $i): ?>
                            <?php
                                $inicio = $i['data_inicio']
                                    ? (new \Cake\I18n\FrozenTime($i['data_inicio']))->modify('+3 hours')
                                    : null;

                                $fim = $i['data_fim']
                                    ? (new \Cake\I18n\FrozenTime($i['data_fim']))->modify('+3 hours')
                                    : null;
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= $view->Url->build([
                                        'controller' => 'Padrao',
                                        'action' => 'visualizar',
                                        $i['id'],
                                    ]) ?>" class="fw-bold text-decoration-none">
                                        #<?= h($i['id']) ?>
                                    </a>
                                </td>

                                <td class="text-center">
                                    <?= $usuarioId == $i['orientador']
                                        ? bolinhaEu()
                                        : h($i['nome_orientador'] ?? '—') ?>
                                </td>

                                <td class="text-center">
                                    <?= $usuarioId == $i['bolsista']
                                        ? bolinhaEu()
                                        : h($i['nome_bolsista'] ?? '—') ?>
                                </td>

                                <td class="text-center">
                                    <?= $usuarioId == $i['coorientador']
                                        ? bolinhaEu()
                                        : h($i['nome_coorientador'] ?? '—') ?>
                                </td>

                                <td>
                                    <?= h($i['nome_edital']) ?><br>
                                    <small class="text-muted"><?= h($i['nome_programa']) ?></small>
                                </td>

                                <td>
                                    <?= h($i['nome_fase']) ?>
                                </td>

                                <td>
                                    <?= badgeVigente($i['vigente']) ?>
                                </td>

                                <td>
                                    <?= $inicio
                                        ? $inicio->i18nFormat('dd/MM/yyyy')
                                        : '<span class="badge bg-warning text-dark">Não implementado</span>' ?>
                                </td>

                                <td>
                                    <?= $fim
                                        ? $fim->i18nFormat('dd/MM/yyyy')
                                        : '--' ?>
                                </td>

                                <td class="text-end">
                                    <?php if ($ehAdmin && isset($i['ativo']) && (int)$i['ativo'] === 0): ?>
                                        <i class="fa fa-ban text-danger"
                                        title="Registro inativo / deletado"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-3 text-center text-muted">
                    Nenhum registro encontrado.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php
}
?>

<?php
renderTabela($comoBolsista, 'Atua como Bolsista', '<span class="badge bg-primary me-1">B</span>', $ehAdmin, $this, $usuario->id, 'bolsista');
renderTabela($comoOrientador, 'Atua como Orientador', '<span class="badge bg-info me-1">O</span>', $ehAdmin, $this, $usuario->id, 'orientador');
renderTabela($comoCoorientador, 'Atua como Coorientador', '<span class="badge bg-secondary me-1">C</span>', $ehAdmin, $this, $usuario->id, 'coorientador');
?>

<script>
document.querySelectorAll('.sortable-table').forEach(table => {
    const headers = table.querySelectorAll('.sortable');
    headers.forEach((header, index) => {
        let asc = true;
        header.addEventListener('click', () => {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let A = a.children[index].innerText.trim().toLowerCase();
                let B = b.children[index].innerText.trim().toLowerCase();

                const nA = A.replace('#','');
                const nB = B.replace('#','');

                if (!isNaN(nA) && !isNaN(nB)) {
                    return asc ? nA - nB : nB - nA;
                }

                return asc ? A.localeCompare(B) : B.localeCompare(A);
            });

            asc = !asc;
            rows.forEach(r => tbody.appendChild(r));
        });
    });
});
</script>
