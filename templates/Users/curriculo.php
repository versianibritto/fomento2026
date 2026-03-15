<?php
$programaMap = [
    'I' => 'IC Manguinhos/ENSP',
    'A' => 'IC Mata Atlântica',
    'M' => 'IC Maré',
    'G' => 'IC Indígena',
    'C' => 'IC Coleções Biológicas',
    'N' => 'Não me enquadro nestes editais',
];

$valorOuPadrao = static function ($valor, ?callable $formatter = null): string {
    if ($valor === null || $valor === '') {
        return '<span class="badge bg-danger">Não informado</span>';
    }

    if ($formatter !== null) {
        return (string)$formatter($valor);
    }

    return h((string)$valor);
};
?>

<style>
.curriculo-hero {
    background: linear-gradient(135deg, #f4f7fb 0%, #ffffff 100%);
    border: 1px solid #e4ebf3;
    border-radius: 1rem;
    padding: 1.25rem;
}

.curriculo-card {
    border: 0;
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.curriculo-section-title {
    font-size: .78rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: .85rem;
}

.curriculo-label {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-bottom: .2rem;
}

.curriculo-value {
    font-weight: 600;
    color: #1f2937;
    word-break: break-word;
}

.curriculo-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: .25rem .65rem;
    font-size: .78rem;
    font-weight: 600;
    background: #eef4ff;
    color: #2f5ea6;
}
</style>

<section class="mt-n3">
    <div class="curriculo-hero mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h3 class="mb-1">Currículo do Candidato</h3>
                <div class="text-muted">Resumo acadêmico e dados de contato do registro selecionado.</div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-light text-dark border">
                    Cadastro:
                    <?= !empty($usuario->created)
                        ? $usuario->created->i18nFormat('dd/MM/yyyy')
                        : 'Não informado' ?>
                </span>
                <span class="badge bg-light text-dark border">
                    Última atualização:
                    <?= !empty($usuario->last_data_update_date)
                        ? $usuario->last_data_update_date->i18nFormat('dd/MM/yyyy')
                        : 'Perfil não foi completado/atualizado' ?>
                </span>
                <?= $this->Html->link('Voltar', ['controller' => 'Users', 'action' => 'talentos'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <div class="card curriculo-card mb-3">
        <div class="card-body">
            <div class="curriculo-section-title">Resumo</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="curriculo-label">Nome</div>
                    <div class="curriculo-value"><?= h((string)$usuario->nome) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="curriculo-label">Lattes</div>
                    <div class="curriculo-value">
                        <?php if (empty($usuario->lattes)): ?>
                            <span class="badge bg-danger">Não informado</span>
                        <?php else: ?>
                            <a href="<?= h((string)$usuario->lattes) ?>" target="_blank" rel="noopener noreferrer">
                                <?= h((string)$usuario->lattes) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="curriculo-label">Curso</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($usuario->curso) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="curriculo-label">Instituição de Ensino</div>
                    <div class="curriculo-value">
                        <?= $valorOuPadrao($usuario->instituicao->sigla ?? null) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">Escolaridade</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($usuario->escolaridade->nome ?? null) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">Conclusão</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($usuario->ano_conclusao) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">Edital de Interesse</div>
                    <div class="curriculo-value">
                        <?php if (!empty($usuario->ic) && isset($programaMap[$usuario->ic])): ?>
                            <span class="curriculo-chip"><?= h($programaMap[$usuario->ic]) ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger">Não informado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card curriculo-card">
        <div class="card-body">
            <div class="curriculo-section-title">Contato e Perfil</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="curriculo-label">E-mail</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($usuario->email) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">E-mail Alternativo</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($usuario->email_alternativo) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">E-mail de Contato</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($usuario->email_contato) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">Gênero</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($sexo[$usuario->sexo] ?? null) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">Raça</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($racas[$usuario->raca] ?? null) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="curriculo-label">Deficiência</div>
                    <div class="curriculo-value"><?= $valorOuPadrao($deficiencia[$usuario->deficiencia] ?? null) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
