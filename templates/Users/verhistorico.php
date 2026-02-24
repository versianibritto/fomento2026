<?php
$contextoMap = [
    'E' => 'Edicao',
    'A' => 'Acesso login unico',
    'C' => 'Criacao',
    'P' => 'Perfil',
];

$origemAcessoMap = [
    'F' => 'Login unico Fiocruz',
    'G' => 'Gov.br',
];

$fieldLabels = [
    'nome' => 'Nome',
    'nome_social' => 'Nome Social',
    'cpf' => 'CPF',
    'data_nascimento' => 'Data de Nascimento',
    'documento' => 'Documento',
    'documento_numero' => 'Numero do Documento',
    'documento_emissor' => 'Orgao Emissor',
    'documento_uf_emissor' => 'UF Emissor',
    'documento_emissao' => 'Data de Emissao',
    'sexo' => 'Genero',
    'raca' => 'Raca',
    'deficiencia' => 'Deficiencia',
    'ic' => 'Edital Social',
    'lattes' => 'Lattes',
    'street_id' => 'Endereco (Street ID)',
    'numero' => 'Numero',
    'complemento' => 'Complemento',
    'escolaridade_id' => 'Escolaridade',
    'curso' => 'Curso',
    'ano_conclusao' => 'Ano de Conclusao',
    'em_curso' => 'Em Curso',
    'instituicao_curso' => 'Instituicao do Curso',
    'vinculo_id' => 'Vinculo',
    'unidade_id' => 'Unidade',
    'matricula_siape' => 'Siape',
    'departamento' => 'Departamento',
    'laboratorio' => 'Laboratorio',
    'telefone' => 'Telefone 1',
    'telefone_contato' => 'Telefone 2',
    'celular' => 'Celular',
    'whatsapp' => 'WhatsApp',
    'email' => 'Email',
    'email_alternativo' => 'Email Alternativo',
    'email_contato' => 'Email Contato',
    'padauan' => 'Coordenacao de Programas',
    'jedi' => 'Coordenacao de Unidade',
    'yoda' => 'Gestao Fomento',
    '_info' => 'Observacao',
];

$icMap = [
    'M' => 'IC Mare',
    'A' => 'IC Mata Atlantica',
    'I' => 'IC Manguinhos/ENSP',
];

$valueMaps = [
    'sexo' => $sexo ?? [],
    'raca' => $racas ?? [],
    'deficiencia' => $deficiencia ?? [],
    'documento' => $documentos ?? [],
    'unidade_id' => $unidades ?? [],
    'escolaridade_id' => $escolaridades ?? [],
    'vinculo_id' => $vinculos ?? [],
    'ic' => $icMap,
    'yoda' => [
        '1' => 'Sim',
        '0' => 'Nao',
    ],
];

$unidadesMap = $unidades ?? [];
$programaMap = $programa ?? [];

$formatValue = function (string $field, mixed $value) use ($valueMaps, $unidadesMap, $programaMap): string {
    if ($value === null || $value === '') {
        return '<i class="badge bg-danger me-2">Nao Informado</i>';
    }

    if (is_string($value)) {
        if (in_array($field, ['jedi', 'padauan'], true)) {
            $items = array_filter(array_map('trim', explode(',', $value)));
            if (empty($items)) {
                return '<i class="badge bg-danger me-2">Nao Informado</i>';
            }

            $map = $field === 'jedi' ? $unidadesMap : $programaMap;
            $nomes = [];
            foreach ($items as $item) {
                $nomes[] = $map[$item] ?? $item;
            }

            return h(implode(', ', $nomes));
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}:\d{2})?$/', $value)) {
            $datePart = substr($value, 0, 10);
            [$y, $m, $d] = explode('-', $datePart);
            return h(sprintf('%02d/%02d/%04d', (int)$d, (int)$m, (int)$y));
        }
    }

    if (isset($valueMaps[$field])) {
        $map = $valueMaps[$field];
        $key = is_scalar($value) ? (string)$value : null;
        if ($key !== null && array_key_exists($key, $map)) {
            return h($map[$key]);
        }
    }

    if (is_array($value)) {
        return h(json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    return h((string)$value);
};

$paginationParams = array_filter([
    'contexto' => $contextoFilter ?? null,
    'mes' => $mes ?? null,
    'ano' => $ano ?? null,
]);
$this->Paginator->options(['url' => $paginationParams]);
?>

<style>
.user-id-bar {
    position: fixed;
    top: 60px;
    left: 266px;
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

.body-offset-top {
    padding-top: 84px;
}

body.sidebar-collapse .user-id-bar {
    left: 16px;
}
</style>

<div class="user-id-bar">
    <div class="user-id-label">Identificação</div>
    <div class="user-id-main">
        <strong><?= h($usuario->nome ?? 'Usuario') ?></strong>
        <span class="user-id-chip">CPF: <?= h($usuario->cpf ?? '-') ?></span>
    </div>
</div>

<div class="container-fluid p-1 pt-1 body-offset-top">
    <div class="col-12 mb-3">
        <?= $this->Form->create(null, ['type' => 'get']) ?>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <?= $this->Form->label('contexto', 'Filtrar por Acao') ?>
                <?= $this->Form->select(
                    'contexto',
                    ['' => 'Todas'] + $contextoMap,
                    ['class' => 'form-control', 'value' => $contextoFilter ?? '']
                ) ?>
            </div>
            <div class="col-md-2">
                <?= $this->Form->label('mes', 'Mes') ?>
                <?= $this->Form->select(
                    'mes',
                    [
                        '' => 'Todos',
                        '1' => 'Janeiro',
                        '2' => 'Fevereiro',
                        '3' => 'Março',
                        '4' => 'Abril',
                        '5' => 'Maio',
                        '6' => 'Junho',
                        '7' => 'Julho',
                        '8' => 'Agosto',
                        '9' => 'Setembro',
                        '10' => 'Outubro',
                        '11' => 'Novembro',
                        '12' => 'Dezembro',
                    ],
                    ['class' => 'form-control', 'value' => $mes ?? '']
                ) ?>
            </div>
            <div class="col-md-2">
                <?= $this->Form->label('ano', 'Ano') ?>
                <?php
                $anoAtual = (int)date('Y');
                $anos = [];
                for ($i = $anoAtual; $i >= $anoAtual - 10; $i--) {
                    $anos[$i] = $i;
                }
                ?>
                <?= $this->Form->select(
                    'ano',
                    ['' => 'Todos'] + $anos,
                    ['class' => 'form-control', 'value' => $ano ?? '']
                ) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link('Limpar', ['action' => 'verhistorico', $this->request->getParam('pass.0')], ['class' => 'btn btn-outline-secondary ms-2']) ?>
                <?php if (in_array($this->request->getAttribute('identity')['id'] ?? null, [1, 8088], true)): ?>
                    <?= $this->Form->button('<i class="fa fa-file-excel"></i>', [
                        'class' => 'btn btn-outline-secondary btn-sm ms-2',
                        'name' => 'acao',
                        'value' => 'excel',
                        'escapeTitle' => false,
                        'title' => 'Exportar Excel',
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <div class="text-primary fw-semibold">
                    Cada busca traz apenas os 30 registros mais recentes. Para consultas mais antigas, use os filtros de período.
                </div>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>

<?php if ($historicos->count() > 0): ?>
    <?php foreach ($historicos as $historico): ?>
            <?php
            $createdLabel = $historico->created
                ? $historico->created->i18nFormat('dd/MM/Y HH:mm:ss')
                : '-';
            $alteradorNome = $historico->alterador->nome ?? 'Sistema';
            $contextoLabel = $contextoMap[$historico->contexto] ?? $historico->contexto;
            $origemAcessoLabel = null;
            if ($historico->contexto === 'A' && !empty($historico->origem_acesso)) {
                $origemAcessoLabel = $origemAcessoMap[$historico->origem_acesso] ?? $historico->origem_acesso;
            }
            $diff = $historico->diff_json;
            if (is_string($diff)) {
                $diff = json_decode($diff, true) ?: [];
            }
            if (!is_array($diff)) {
                $diff = [];
            }
            ?>
            <div class="col-12">
                <div class="card card-primary card-outline mb-3">
                    <div class="card-body">
                        <h4 class="mt-2">Alteracoes em <?= h($createdLabel) ?></h4>
                        <div class="text-muted mb-3">
                            <span>Por: <?= h($alteradorNome) ?></span>
                            <span class="ms-2">Contexto: <?= h($contextoLabel) ?></span>
                            <?php if ($origemAcessoLabel): ?>
                                <span class="ms-2">Origem: <?= h($origemAcessoLabel) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($diff)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>De</th>
                                            <th>Para</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($diff as $campo => $valores): ?>
                                            <tr>
                                                <td><strong><?= h($fieldLabels[$campo] ?? $campo) ?></strong></td>
                                                <td><?= $formatValue($campo, $valores['de'] ?? null) ?></td>
                                                <td><?= $formatValue($campo, $valores['para'] ?? null) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert bg-warning">
                                Sem campos alterados registrados.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <?= $this->Paginator->prev('« Anterior') ?>
            <?= $this->Paginator->numbers([
                'first' => '<<',
                'last' => '>>',
            ]) ?>
            <?= $this->Paginator->next('Proxima »') ?>
            <span class="text-muted">
                <?= $this->Paginator->counter('Pagina {{page}} de {{pages}}') ?>
            </span>
        </div>
    </div>
<?php else: ?>
    <div class="col-md-12">
            <div class="card">
                <div class="bg-warning p-3">
                Nao ha registro de historico para este usuario com os filtros selecionados. Refaça os filtros e tente novamente
                </div>
            </div>
        </div>
<?php endif; ?>
