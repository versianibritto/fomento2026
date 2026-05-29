<style>
    .homologacao-separador {
        border: 0;
        border-top: 4px solid #6c757d;
        opacity: 1;
        margin: 2.75rem 0;
    }

    .homologacao-bloco-titulo {
        padding-top: .25rem;
        margin-bottom: 1.25rem;
    }

    .homologacao-status-card {
        background: #fff;
        border: 1px solid #d7dde4;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .homologacao-status-card.status-homologado {
        background: #f0fdf4;
        border-color: #86efac;
    }

    .homologacao-status-card.status-nao-homologado {
        background: #fef2f2;
        border-color: #fca5a5;
    }

    .homologacao-status-card.status-homologado-pendencia {
        background: #fffbeb;
        border-color: #fcd34d;
    }

    .homologacao-status-card.status-nao-verificado {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .homologacao-acao-ti-discreta {
        margin-top: 8rem;
        opacity: .45;
    }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Homologação #<?= h($inscricao->id ?? '') ?></h4>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= $this->Url->build(['controller' => 'Padrao', 'action' => 'visualizar', (int)($inscricao->id ?? 0)]) ?>"
               class="btn btn-outline-primary btn-sm rounded-pill px-3"
               target="_blank"
               rel="noopener">
                <i class="fa fa-eye me-1"></i> Visualizar inscrição
            </a>
            <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listahomologacao']) ?>"
               class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <?php
        $naoInformado = '<span class="badge bg-danger">Não informado</span>';
        $formatDataAnexo = static function ($data): string {
            if ($data === null || $data === '') {
                return '';
            }
            if ($data instanceof \DateTimeInterface) {
                return \DateTimeImmutable::createFromInterface($data)->format('d/m/Y H:i');
            }
            $dataTexto = trim((string)$data);
            if ($dataTexto === '') {
                return '';
            }
            $timestamp = strtotime($dataTexto);
            return $timestamp ? date('d/m/Y H:i', $timestamp) : $dataTexto;
        };
        $tipoAnexoOcultoId = 13;
        $ocultarTipoAnexo = static function ($anexos) use ($tipoAnexoOcultoId): array {
            if (!is_iterable($anexos)) {
                return [];
            }
            $anexosFiltrados = [];
            foreach ($anexos as $anexo) {
                if ((int)($anexo['tipo_id'] ?? 0) === $tipoAnexoOcultoId) {
                    continue;
                }
                $anexosFiltrados[] = $anexo;
            }
            return $anexosFiltrados;
        };
        $formatarFilhosMenores = static function ($valor): string {
            if ($valor === null || $valor === '') {
                return '';
            }
            $valorInt = (int)$valor;
            if ($valorInt === 0) {
                return 'Não';
            }
            if ($valorInt === 1) {
                return 'Sim (1 filho)';
            }
            return 'Sim (' . $valorInt . ' filhos)';
        };
        $formatarSimNao = static function ($valor): string {
            if ($valor === null || $valor === '') {
                return '';
            }
            return ((int)$valor === 1) ? 'Sim' : 'Não';
        };
        $homologadoValor = strtoupper(trim((string)($inscricao->homologado ?? '')));
        $homologadoTexto = match ($homologadoValor) {
            'S' => 'Sim',
            'P' => 'Homologado com pendência',
            'N' => 'Não',
            default => 'Não verificado',
        };
        $homologadoBadge = match ($homologadoValor) {
            'S' => 'bg-success',
            'P' => 'bg-warning text-dark',
            'N' => 'bg-danger',
            default => 'bg-secondary',
        };
        $homologadoStatusCardClasse = match ($homologadoValor) {
            'S' => 'status-homologado',
            'P' => 'status-homologado-pendencia',
            'N' => 'status-nao-homologado',
            default => 'status-nao-verificado',
        };
        $homologadoDataTexto = $formatDataAnexo($inscricao->homologado_data ?? null);
        $homologadoUsuarioTexto = trim((string)($inscricao->homologador->nome ?? ''));
        $homologadoJustificativaTexto = trim((string)($inscricao->homologado_justificativa ?? ''));
        $programaHomologacaoId = (int)($inscricao->editai->programa_id ?? 0);
        $exibirPrimeiroPeriodoHomologacao = $programaHomologacaoId !== 1;
        $exibirFilhosMenorBolsistaHomologacao = $programaHomologacaoId === 1;
        $identityHomologacao = $this->request->getAttribute('identity');
        $ehYodaHomologacao = is_array($identityHomologacao)
            ? !empty($identityHomologacao['yoda'])
            : !empty($identityHomologacao->yoda);
        $podeEditarCamposHomologacao = $ehYodaHomologacao
            && empty($inscricao->deleted)
            && in_array((int)($inscricao->fase_id ?? 0), [4, 15], true)
            && $homologadoValor !== 'S';
        $identityHomologacaoId = is_array($identityHomologacao)
            ? (int)($identityHomologacao['id'] ?? 0)
            : (int)($identityHomologacao->id ?? 0);
        $ehTiHomologacao = in_array($identityHomologacaoId, [1, 8088], true);
        $origemTermo = strtoupper(trim((string)($inscricao->origem ?? '')));
        $controllerTermo = trim((string)($inscricao->editai->controller ?? ''));
        if ($controllerTermo === '') {
            $controllerTermo = $origemTermo === 'R' ? 'Renovacoes' : 'Inscricoes';
        }
        $actionTermo = in_array($controllerTermo, ['Renovacoes', 'PdjRenovacoes'], true) || $origemTermo === 'R'
            ? 'baixarTermoRenovacao'
            : 'baixarTermo';
        $anexosB = $ocultarTipoAnexo($anexosB ?? []);
        $anexosC = $ocultarTipoAnexo($anexosC ?? []);
        $anexosP = $ocultarTipoAnexo($anexosP ?? []);
        $anexosS = $ocultarTipoAnexo($anexosS ?? []);
        $anexosI = $ocultarTipoAnexo($anexosI ?? []);
        $anexosOrientador = [];
        $anexosInscricao = [];
        foreach ($anexosI as $anexoI) {
            if (in_array((int)($anexoI['tipo_id'] ?? 0), [27, 29], true)) {
                $anexosOrientador[] = $anexoI;
                continue;
            }
            $anexosInscricao[] = $anexoI;
        }
        $anexosI = $anexosInscricao;
    ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Edital</div>
                    <div class="fw-semibold"><?= h($inscricao->editai->nome ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Programa</div>
                    <div class="fw-semibold"><?= h($inscricao->editai->programa->sigla ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold"><?= h($inscricao->fase->nome ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Orientador</div>
                    <div class="fw-semibold"><?= !empty($inscricao->orientadore->nome) ? h($inscricao->orientadore->nome) : $naoInformado ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Unidade</div>
                    <div class="fw-semibold"><?= !empty($inscricao->orientadore->unidade->sigla) ? h($inscricao->orientadore->unidade->sigla) : $naoInformado ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Vínculo</div>
                    <div class="fw-semibold"><?= !empty($inscricao->orientadore->vinculo->nome) ? h($inscricao->orientadore->vinculo->nome) : $naoInformado ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Possui filhos</div>
                    <div class="fw-semibold">
                        <?php
                            $filhosValor = $inscricao->filhos_menor;
                            $filhosTexto = 'Não informado';
                            if ($filhosValor !== null && $filhosValor !== '') {
                                $filhosInt = (int)$filhosValor;
                                if ($filhosInt === 0) {
                                    $filhosTexto = 'Não';
                                } elseif ($filhosInt === 1) {
                                    $filhosTexto = 'Sim (1 filho)';
                                } elseif ($filhosInt > 1) {
                                    $filhosTexto = 'Sim (' . $filhosInt . ' filhos)';
                                }
                            }
                        ?>
                        <?= $filhosTexto === 'Não informado' ? $naoInformado : h($filhosTexto) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Ano do doutorado</div>
                    <div class="fw-semibold">
                        <?= !empty($inscricao->ano_doutorado) ? h($inscricao->ano_doutorado) : $naoInformado ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Recém servidor</div>
                    <div class="fw-semibold">
                        <?php
                            $recemServidorVal = $inscricao->recem_servidor;
                            $recemServidorTexto = 'Não informado';
                            if ($recemServidorVal !== null && $recemServidorVal !== '') {
                                $recemServidorTexto = ((int)$recemServidorVal === 1) ? 'Sim' : 'Não';
                            }
                        ?>
                        <?= $recemServidorTexto === 'Não informado' ? $naoInformado : h($recemServidorTexto) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Bolsista</div>
                    <div class="fw-semibold"><?= !empty($inscricao->bolsista_usuario->nome) ? h($inscricao->bolsista_usuario->nome) : $naoInformado ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Coorientador</div>
                    <div class="fw-semibold"><?= !empty($inscricao->coorientadore->nome) ? h($inscricao->coorientadore->nome) : $naoInformado ?></div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light border-top">
            <div class="homologacao-status-card <?= h($homologadoStatusCardClasse) ?>">
                <div class="row g-3 align-items-start">
                    <div class="col-md-4">
                        <div class="text-muted small">Homologado</div>
                        <div class="fw-semibold">
                            <span class="badge <?= h($homologadoBadge) ?>"><?= h($homologadoTexto) ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Data da última alteração</div>
                        <div class="fw-semibold"><?= $homologadoDataTexto !== '' ? h($homologadoDataTexto) : $naoInformado ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Alterado por</div>
                        <div class="fw-semibold"><?= $homologadoUsuarioTexto !== '' ? h($homologadoUsuarioTexto) : $naoInformado ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Justificativa</div>
                        <div class="fw-semibold"><?= $homologadoJustificativaTexto !== '' ? nl2br(h($homologadoJustificativaTexto)) : '<span class="text-muted">-</span>' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-semibold homologacao-bloco-titulo">Bolsista</h5>
            <div class="row g-3 mb-4">
                <?php if ($exibirPrimeiroPeriodoHomologacao): ?>
                    <div class="col-md-4">
                        <div class="text-muted small d-flex align-items-center gap-2">
                            <span>Primeiro período</span>
                            <?php if ($podeEditarCamposHomologacao): ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-edit"></i>',
                                    ['controller' => 'Gestao', 'action' => 'editarCampoHomologacao', (int)($inscricao->id ?? 0), 'primeiro_periodo'],
                                    ['class' => 'btn btn-primary btn-sm py-0 px-2', 'escape' => false, 'title' => 'Editar primeiro período']
                                ) ?>
                            <?php endif; ?>
                        </div>
                        <div class="fw-semibold"><?= h($primeiroPeriodoTexto) ?></div>
                    </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <div class="text-muted small d-flex align-items-center gap-2">
                        <span>Cota</span>
                        <?php if ($podeEditarCamposHomologacao): ?>
                            <?= $this->Html->link(
                                '<i class="fa fa-edit"></i>',
                                ['controller' => 'Gestao', 'action' => 'editarCampoHomologacao', (int)($inscricao->id ?? 0), 'cota'],
                                ['class' => 'btn btn-primary btn-sm py-0 px-2', 'escape' => false, 'title' => 'Editar cota']
                            ) ?>
                        <?php endif; ?>
                    </div>
                    <div class="fw-semibold">
                        <?= isset($cotas[(string)($inscricao->cota ?? '')]) ? h($cotas[(string)$inscricao->cota]) : $naoInformado ?>
                    </div>
                </div>
                <?php if ($exibirFilhosMenorBolsistaHomologacao): ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Filhos menores de 8 anos da bolsista</div>
                        <div class="fw-semibold">
                            <?php $filhosMenorBolsistaTexto = $formatarFilhosMenores($inscricao->filhos_menor_bolsista ?? null); ?>
                            <?= $filhosMenorBolsistaTexto !== '' ? h($filhosMenorBolsistaTexto) : $naoInformado ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <h6 class="fw-semibold mb-2">Anexos do bloco Bolsista</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Regra</th>
                            <th class="text-center">Status</th>
                            <th>Anexo</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($anexosB)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum anexo obrigatório para esta inscrição.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($anexosB as $anexo): ?>
                            <tr>
                                <td><?= h($anexo['tipo_nome']) ?></td>
                                <td>
                                    <?= !empty($anexo['regras']) ? h(implode(' ', $anexo['regras'])) : 'Obrigatório' ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $statusRegra = (string)($anexo['status_regra'] ?? 'Não aplicável');
                                        $badgeClass = 'bg-light text-secondary border fw-normal';
                                        if ($statusRegra === 'Obrigatório') {
                                            $badgeClass = 'bg-light text-danger border border-danger fw-normal';
                                        } elseif ($statusRegra === 'Condicional') {
                                            $badgeClass = 'bg-light text-warning border fw-normal';
                                        }
                                    ?>
                                    <span class="badge <?= h($badgeClass) ?>"><?= h($statusRegra) ?></span>
                                </td>
                                <td>
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= h($anexo['arquivo']) ?>
                                        <?php
                                            $usuarioAnexo = trim((string)($anexo['usuario_nome'] ?? ''));
                                            $dataAnexoFmt = $formatDataAnexo($anexo['data_inclusao'] ?? null);
                                        ?>
                                        <div class="small text-muted">
                                            <?= $usuarioAnexo !== '' ? 'Incluído por ' . h($usuarioAnexo) : 'Usuário não informado' ?>
                                            <?= $dataAnexoFmt !== '' ? ' em ' . h($dataAnexoFmt) : '' ?>
                                        </div>
                                    <?php elseif ($statusRegra === 'Obrigatório'): ?>
                                        <?= $naoInformado ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= $this->Form->create(null, [
                                            'type' => 'file',
                                            'url' => ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)($inscricao->id ?? 0)],
                                            'class' => 'd-inline-flex align-items-center gap-1',
                                            'data-no-loading' => '1',
                                        ]) ?>
                                            <?= $this->Form->hidden('anexo_acao', ['value' => 'alterar']) ?>
                                            <?= $this->Form->hidden('anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <?php $inputIdAnexoB = 'editar-anexo-B-' . (int)($anexo['tipo_id'] ?? 0); ?>
                                            <label for="<?= h($inputIdAnexoB) ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar anexo">
                                                <i class="fa fa-edit"></i>
                                            </label>
                                            <input
                                                id="<?= h($inputIdAnexoB) ?>"
                                                name="anexos[<?= (int)($anexo['tipo_id'] ?? 0) ?>]"
                                                type="file"
                                                class="d-none homologacao-anexo-file"
                                                data-tipo="<?= (int)($anexo['tipo_id'] ?? 0) ?>"
                                            >
                                        <?= $this->Form->end() ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr class="homologacao-separador">

            <h5 class="fw-semibold homologacao-bloco-titulo">Orientador / Coorientador</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="text-muted small">Orientador</div>
                    <div class="fw-semibold">
                        <?php if ($orientadorNome !== ''): ?>
                            <?= h($orientadorNome) ?>
                            <?php if ($orientadorServidor === 1): ?>
                                <span class="text-muted">(Servidor)</span>
                            <?php endif; ?>
                            <?php if ($orientadorVinculoNome !== ''): ?>
                                <span class="text-muted">(<?= h($orientadorVinculoNome) ?>)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?= $naoInformado ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Coorientador</div>
                    <div class="fw-semibold">
                        <?php if ($coorientadorNome !== ''): ?>
                            <?= h($coorientadorNome) ?>
                            <?php if ($coorientadorServidor === 1): ?>
                                <span class="text-muted">(Servidor)</span>
                            <?php endif; ?>
                            <?php if ($coorientadorVinculoNome !== ''): ?>
                                <span class="text-muted">(<?= h($coorientadorVinculoNome) ?>)</span>
                            <?php endif; ?>
                        <?php elseif ($coorientadorObrigatorioServidor): ?>
                            <span class="badge bg-light text-danger border border-danger fw-normal">Coorientador não informado? obrigatório um coorientador servidor</span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Filhos menores de 8 anos da orientadora</div>
                    <div class="fw-semibold">
                        <?php $filhosMenorOrientadorTexto = $formatarFilhosMenores($inscricao->filhos_menor ?? null); ?>
                        <?= $filhosMenorOrientadorTexto !== '' ? h($filhosMenorOrientadorTexto) : $naoInformado ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Recém-servidor do orientador</div>
                    <div class="fw-semibold">
                        <?php $recemServidorTextoBloco = $formatarSimNao($inscricao->recem_servidor ?? null); ?>
                        <?= $recemServidorTextoBloco !== '' ? h($recemServidorTextoBloco) : $naoInformado ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($errosCoorientador)): ?>
                <div class="alert alert-danger py-2 px-3 mb-3">
                    <?php foreach ($errosCoorientador as $erro): ?>
                        <?php
                            $erroTexto = (string)$erro;
                            $erroDestaque = in_array($erroTexto, [
                                'Coorientador informado com vínculo não servidor.',
                                'Anexo obrigatório do coorientador pendente: Termo consentimento - Coorientador.',
                            ], true);
                        ?>
                        <?php if ($erroDestaque): ?>
                            <div class="fw-semibold text-danger-emphasis border-start border-3 border-danger ps-2 mb-1"><?= h($erroTexto) ?></div>
                        <?php else: ?>
                            <div class="small"><?= h($erroTexto) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h6 class="fw-semibold mb-2">Anexos do bloco Orientador</h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Regra</th>
                            <th class="text-center">Status</th>
                            <th>Anexo</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($anexosOrientador)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum anexo obrigatório para o orientador.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($anexosOrientador as $anexo): ?>
                            <tr>
                                <td><?= h($anexo['tipo_nome']) ?></td>
                                <td><?= !empty($anexo['regras']) ? h(implode(' ', $anexo['regras'])) : 'Obrigatório' ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-danger border border-danger fw-normal"><?= h($anexo['status_regra']) ?></span>
                                </td>
                                <td>
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= h($anexo['arquivo']) ?>
                                        <?php
                                            $usuarioAnexo = trim((string)($anexo['usuario_nome'] ?? ''));
                                            $dataAnexoFmt = $formatDataAnexo($anexo['data_inclusao'] ?? null);
                                        ?>
                                        <div class="small text-muted">
                                            <?= $usuarioAnexo !== '' ? 'Incluído por ' . h($usuarioAnexo) : 'Usuário não informado' ?>
                                            <?= $dataAnexoFmt !== '' ? ' em ' . h($dataAnexoFmt) : '' ?>
                                        </div>
                                    <?php else: ?>
                                        <?= $naoInformado ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= $this->Form->create(null, [
                                            'type' => 'file',
                                            'url' => ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)($inscricao->id ?? 0)],
                                            'class' => 'd-inline-flex align-items-center gap-1',
                                            'data-no-loading' => '1',
                                        ]) ?>
                                            <?= $this->Form->hidden('anexo_acao', ['value' => 'alterar']) ?>
                                            <?= $this->Form->hidden('anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <?php $inputIdAnexoOrientador = 'editar-anexo-O-' . (int)($anexo['tipo_id'] ?? 0); ?>
                                            <label for="<?= h($inputIdAnexoOrientador) ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar anexo">
                                                <i class="fa fa-edit"></i>
                                            </label>
                                            <input
                                                id="<?= h($inputIdAnexoOrientador) ?>"
                                                name="anexos[<?= (int)($anexo['tipo_id'] ?? 0) ?>]"
                                                type="file"
                                                class="d-none homologacao-anexo-file"
                                                data-tipo="<?= (int)($anexo['tipo_id'] ?? 0) ?>"
                                            >
                                        <?= $this->Form->end() ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h6 class="fw-semibold mb-2">Anexos do bloco Coorientador</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th class="text-center">Status</th>
                            <th>Anexo</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$coorientadorInformado): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Sem coorientador vinculado.</td>
                            </tr>
                        <?php elseif (empty($anexosC)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Nenhum tipo de anexo de coorientador configurado.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($anexosC as $anexo): ?>
                            <tr>
                                <td><?= h($anexo['tipo_nome']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-danger border border-danger fw-normal"><?= h($anexo['status_regra']) ?></span>
                                </td>
                                <td>
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= h($anexo['arquivo']) ?>
                                        <?php
                                            $usuarioAnexo = trim((string)($anexo['usuario_nome'] ?? ''));
                                            $dataAnexoFmt = $formatDataAnexo($anexo['data_inclusao'] ?? null);
                                        ?>
                                        <div class="small text-muted">
                                            <?= $usuarioAnexo !== '' ? 'Incluído por ' . h($usuarioAnexo) : 'Usuário não informado' ?>
                                            <?= $dataAnexoFmt !== '' ? ' em ' . h($dataAnexoFmt) : '' ?>
                                        </div>
                                    <?php else: ?>
                                        <?= $naoInformado ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= $this->Form->create(null, [
                                            'type' => 'file',
                                            'url' => ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)($inscricao->id ?? 0)],
                                            'class' => 'd-inline-flex align-items-center gap-1',
                                            'data-no-loading' => '1',
                                        ]) ?>
                                            <?= $this->Form->hidden('anexo_acao', ['value' => 'alterar']) ?>
                                            <?= $this->Form->hidden('anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <?php $inputIdAnexoC = 'editar-anexo-C-' . (int)($anexo['tipo_id'] ?? 0); ?>
                                            <label for="<?= h($inputIdAnexoC) ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar anexo">
                                                <i class="fa fa-edit"></i>
                                            </label>
                                            <input
                                                id="<?= h($inputIdAnexoC) ?>"
                                                name="anexos[<?= (int)($anexo['tipo_id'] ?? 0) ?>]"
                                                type="file"
                                                class="d-none homologacao-anexo-file"
                                                data-tipo="<?= (int)($anexo['tipo_id'] ?? 0) ?>"
                                            >
                                        <?= $this->Form->end() ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php /* Blocos Projeto e Subprojeto comentados temporariamente */ ?>
            <?php if (false): ?>
                <hr class="homologacao-separador">

                <h5 class="fw-semibold homologacao-bloco-titulo">Projeto</h5>
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <div class="text-muted small">ID</div>
                        <div class="fw-semibold"><?= !empty($projetoAtual->id ?? null) ? h($projetoAtual->id) : '<span class="text-muted">-</span>' ?></div>
                    </div>
                    <div class="col-md-10">
                        <div class="text-muted small">Título</div>
                        <div class="fw-semibold"><?= !empty($projetoAtual->titulo ?? null) ? h($projetoAtual->titulo) : $naoInformado ?></div>
                    </div>
                </div>

                <h6 class="fw-semibold mb-2">Anexos do bloco Projeto</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th class="text-center">Status</th>
                                <th>Anexo</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($anexosP)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nenhum tipo de anexo de projeto configurado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($anexosP as $anexo): ?>
                                <tr>
                                    <td><?= h($anexo['tipo_nome']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-danger border border-danger fw-normal"><?= h($anexo['status_regra']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($anexo['arquivo'] !== ''): ?>
                                            <?= h($anexo['arquivo']) ?>
                                            <?php
                                                $usuarioAnexo = trim((string)($anexo['usuario_nome'] ?? ''));
                                                $dataAnexoFmt = $formatDataAnexo($anexo['data_inclusao'] ?? null);
                                            ?>
                                            <div class="small text-muted">
                                                <?= $usuarioAnexo !== '' ? 'Incluído por ' . h($usuarioAnexo) : 'Usuário não informado' ?>
                                                <?= $dataAnexoFmt !== '' ? ' em ' . h($dataAnexoFmt) : '' ?>
                                            </div>
                                        <?php else: ?>
                                            <?= $naoInformado ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($anexo['arquivo'] !== ''): ?>
                                            <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr class="homologacao-separador">

                <h5 class="fw-semibold homologacao-bloco-titulo">Subprojeto</h5>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="text-muted small">Título</div>
                        <div class="fw-semibold"><?= $subprojetoTitulo !== '' ? h($subprojetoTitulo) : $naoInformado ?></div>
                    </div>
                </div>

                <h6 class="fw-semibold mb-2">Anexos do bloco Subprojeto</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th class="text-center">Status</th>
                                <th>Anexo</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($anexosS)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nenhum tipo de anexo de subprojeto configurado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($anexosS as $anexo): ?>
                                <tr>
                                    <td><?= h($anexo['tipo_nome']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-danger border border-danger fw-normal"><?= h($anexo['status_regra']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($anexo['arquivo'] !== ''): ?>
                                            <?= h($anexo['arquivo']) ?>
                                            <?php
                                                $usuarioAnexo = trim((string)($anexo['usuario_nome'] ?? ''));
                                                $dataAnexoFmt = $formatDataAnexo($anexo['data_inclusao'] ?? null);
                                            ?>
                                            <div class="small text-muted">
                                                <?= $usuarioAnexo !== '' ? 'Incluído por ' . h($usuarioAnexo) : 'Usuário não informado' ?>
                                                <?= $dataAnexoFmt !== '' ? ' em ' . h($dataAnexoFmt) : '' ?>
                                            </div>
                                        <?php else: ?>
                                            <?= $naoInformado ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($anexo['arquivo'] !== ''): ?>
                                            <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr class="homologacao-separador">
            <?php endif; ?>

            <hr class="homologacao-separador">

            <h5 class="fw-semibold homologacao-bloco-titulo">Anexos específicos da Inscrição</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Regra</th>
                            <th class="text-center">Status</th>
                            <th>Anexo</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($anexosI)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum anexo obrigatório para este bloco.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($anexosI as $anexo): ?>
                            <tr>
                                <td><?= h($anexo['tipo_nome']) ?></td>
                                <td><?= !empty($anexo['regras']) ? h(implode(' ', $anexo['regras'])) : 'Obrigatório' ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-danger border border-danger fw-normal"><?= h($anexo['status_regra']) ?></span>
                                </td>
                                <td>
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= h($anexo['arquivo']) ?>
                                        <?php
                                            $usuarioAnexo = trim((string)($anexo['usuario_nome'] ?? ''));
                                            $dataAnexoFmt = $formatDataAnexo($anexo['data_inclusao'] ?? null);
                                        ?>
                                        <div class="small text-muted">
                                            <?= $usuarioAnexo !== '' ? 'Incluído por ' . h($usuarioAnexo) : 'Usuário não informado' ?>
                                            <?= $dataAnexoFmt !== '' ? ' em ' . h($dataAnexoFmt) : '' ?>
                                        </div>
                                    <?php else: ?>
                                        <?= $naoInformado ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($anexo['arquivo'] !== ''): ?>
                                        <?= $this->Form->create(null, [
                                            'type' => 'file',
                                            'url' => ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)($inscricao->id ?? 0)],
                                            'class' => 'd-inline-flex align-items-center gap-1',
                                            'data-no-loading' => '1',
                                        ]) ?>
                                            <?= $this->Form->hidden('anexo_acao', ['value' => 'alterar']) ?>
                                            <?= $this->Form->hidden('anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => (int)($anexo['tipo_id'] ?? 0)]) ?>
                                            <a href="/uploads/anexos/<?= h($anexo['arquivo']) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <?php $inputIdAnexoI = 'editar-anexo-I-' . (int)($anexo['tipo_id'] ?? 0); ?>
                                            <label for="<?= h($inputIdAnexoI) ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar anexo">
                                                <i class="fa fa-edit"></i>
                                            </label>
                                            <input
                                                id="<?= h($inputIdAnexoI) ?>"
                                                name="anexos[<?= (int)($anexo['tipo_id'] ?? 0) ?>]"
                                                type="file"
                                                class="d-none homologacao-anexo-file"
                                                data-tipo="<?= (int)($anexo['tipo_id'] ?? 0) ?>"
                                            >
                                        <?= $this->Form->end() ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr class="homologacao-separador">

            <?php if (!$homologacaoPermitida): ?>
                <div class="alert alert-danger mb-0 fw-semibold border-2">
                    Somente inscrições na fase Finalizada podem seguir para homologação neste fluxo.
                </div>
            <?php else: ?>
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)($inscricao->id ?? 0)],
                        'class' => 'd-inline',
                        'id' => 'form-homologar',
                    ]) ?>
                        <?= $this->Form->hidden('acao_homologacao', ['value' => 'homologar']) ?>
                        <?= $this->Form->hidden('confirmou_reavaliacao', ['value' => $exigeConfirmacaoReavaliacao ? '0' : '1']) ?>
                        <button type="button" class="btn btn-success" id="btn-homologar">
                            Homologar
                        </button>
                    <?= $this->Form->end() ?>

                    <button type="button" class="btn btn-outline-danger" id="btn-mostrar-nao-homologar">
                        Não homologar
                    </button>

                    <button type="button" class="btn btn-warning fw-semibold text-dark border-warning" id="btn-mostrar-homologar-pendencia">
                        Homologar com pendência
                    </button>
                </div>

                <div id="box-nao-homologar" class="card border-danger-subtle mt-3" style="display: <?= $motivoNaoHomologacao !== '' ? 'block' : 'none' ?>;">
                    <div class="card-body">
                        <?= $this->Form->create(null, [
                            'url' => ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)($inscricao->id ?? 0)],
                            'id' => 'form-nao-homologar',
                        ]) ?>
                            <?= $this->Form->hidden('acao_homologacao', ['value' => 'nao_homologar']) ?>
                            <?= $this->Form->hidden('confirmou_reavaliacao', ['value' => $exigeConfirmacaoReavaliacao ? '0' : '1']) ?>
                            <div class="mb-2">
                                <label for="motivo-nao-homologar" class="form-label mb-1">Motivo da não homologação</label>
                                <textarea
                                    id="motivo-nao-homologar"
                                    name="motivo_nao_homologacao"
                                    class="form-control"
                                    rows="3"
                                    minlength="20"
                                    required><?= h($motivoNaoHomologacao ?? '') ?></textarea>
                                <div class="form-text">Mínimo de 20 caracteres.</div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    id="btn-confirmar-nao-homologar">
                                    Confirmar não homologação
                                </button>
                            </div>
                        <?= $this->Form->end() ?>
                    </div>
                </div>

                <div id="box-homologar-pendencia" class="card border-warning-subtle mt-3" style="display: <?= !empty($motivoHomologacaoPendencia) ? 'block' : 'none' ?>;">
                    <div class="card-body">
                        <?= $this->Form->create(null, [
                            'url' => ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)($inscricao->id ?? 0)],
                            'id' => 'form-homologar-pendencia',
                        ]) ?>
                            <?= $this->Form->hidden('acao_homologacao', ['value' => 'homologar_pendencia']) ?>
                            <?= $this->Form->hidden('confirmou_reavaliacao', ['value' => $exigeConfirmacaoReavaliacao ? '0' : '1']) ?>
                            <div class="mb-2">
                                <label for="motivo-homologar-pendencia" class="form-label mb-1">Pendência da homologação</label>
                                <textarea
                                    id="motivo-homologar-pendencia"
                                    name="motivo_homologacao_pendencia"
                                    class="form-control"
                                    rows="3"
                                    minlength="20"
                                    required><?= h($motivoHomologacaoPendencia ?? '') ?></textarea>
                                <div class="form-text">Mínimo de 20 caracteres.</div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button
                                    type="button"
                                    class="btn btn-warning"
                                    id="btn-confirmar-homologar-pendencia">
                                    Confirmar homologação com pendência
                                </button>
                            </div>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($ehTiHomologacao): ?>
                <div class="d-flex justify-content-end homologacao-acao-ti-discreta">
                    <?= $this->Html->link(
                        '<i class="fa fa-download me-1"></i> Baixar termo',
                        [
                            'controller' => $controllerTermo,
                            'action' => $actionTermo,
                            (int)($inscricao->editai->id ?? 0),
                            (int)($inscricao->id ?? 0),
                        ],
                        [
                            'class' => 'btn btn-outline-secondary btn-sm',
                            'escape' => false,
                            'target' => '_blank',
                            'rel' => 'noopener',
                        ]
                    ) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const exigeConfirmacaoReavaliacao = <?= $exigeConfirmacaoReavaliacao ? 'true' : 'false' ?>;
    const mensagemReavaliacaoHomologar = 'Esta inscrição já possui homologação registrada. Se continuar, o resultado será sobrescrito. Deseja continuar com a homologação?';
    const mensagemReavaliacaoNaoHomologar = 'Esta inscrição já possui homologação registrada. Se continuar, o resultado será sobrescrito. Deseja continuar com a não homologação?';
    const mensagemReavaliacaoHomologarPendencia = 'Esta inscrição já possui homologação registrada. Se continuar, o resultado será sobrescrito. Deseja continuar com a homologação com pendência?';
    const box = document.getElementById('box-nao-homologar');
    const boxPendencia = document.getElementById('box-homologar-pendencia');
    const btnMostrarNaoHomologar = document.getElementById('btn-mostrar-nao-homologar');
    const btnMostrarHomologarPendencia = document.getElementById('btn-mostrar-homologar-pendencia');
    const btnHomologar = document.getElementById('btn-homologar');
    const textareaMotivo = document.getElementById('motivo-nao-homologar');
    const textareaPendencia = document.getElementById('motivo-homologar-pendencia');
    const formHomologar = document.getElementById('form-homologar');
    const formNaoHomologar = document.getElementById('form-nao-homologar');
    const formHomologarPendencia = document.getElementById('form-homologar-pendencia');
    const btnConfirmarNaoHomologar = document.getElementById('btn-confirmar-nao-homologar');
    const btnConfirmarHomologarPendencia = document.getElementById('btn-confirmar-homologar-pendencia');

    const atualizarBoxNaoHomologar = function (mostrar) {
        if (!box) {
            return;
        }
        box.style.display = mostrar ? 'block' : 'none';
        if (textareaMotivo) {
            textareaMotivo.required = mostrar;
            if (!mostrar) {
                textareaMotivo.setCustomValidity('');
            }
        }
        if (btnMostrarNaoHomologar) {
            btnMostrarNaoHomologar.textContent = mostrar ? 'Cancelar não homologação' : 'Não homologar';
        }
    };

    const atualizarBoxHomologarPendencia = function (mostrar) {
        if (!boxPendencia) {
            return;
        }
        boxPendencia.style.display = mostrar ? 'block' : 'none';
        if (textareaPendencia) {
            textareaPendencia.required = mostrar;
            if (!mostrar) {
                textareaPendencia.setCustomValidity('');
            }
        }
        if (btnMostrarHomologarPendencia) {
            btnMostrarHomologarPendencia.textContent = mostrar ? 'Cancelar homologação com pendência' : 'Homologar com pendência';
        }
    };

    const executarConfirmacaoReavaliacao = function (formId, mensagemAcao) {
        let mensagemConfirmacao = mensagemAcao;
        if (exigeConfirmacaoReavaliacao) {
            if (formId === 'form-homologar') {
                mensagemConfirmacao = mensagemReavaliacaoHomologar;
            } else if (formId === 'form-homologar-pendencia') {
                mensagemConfirmacao = mensagemReavaliacaoHomologarPendencia;
            } else {
                mensagemConfirmacao = mensagemReavaliacaoNaoHomologar;
            }
        }

        const confirmouAcao = window.confirm(mensagemConfirmacao);
        if (!confirmouAcao) {
            return false;
        }
        const form = document.getElementById(formId);
        if (form) {
            const inputConfirmacao = form.querySelector('input[name="confirmou_reavaliacao"]');
            if (inputConfirmacao) {
                inputConfirmacao.value = '1';
            }
        }
        return true;
    };

    window.confirmarReavaliacao = executarConfirmacaoReavaliacao;

    if (btnHomologar && formHomologar) {
        btnHomologar.addEventListener('click', function () {
            atualizarBoxNaoHomologar(false);
            atualizarBoxHomologarPendencia(false);
            if (!executarConfirmacaoReavaliacao('form-homologar', 'Confirma a homologação desta inscrição?')) {
                return;
            }
            formHomologar.submit();
        });
    }

    if (btnConfirmarNaoHomologar && formNaoHomologar) {
        btnConfirmarNaoHomologar.addEventListener('click', function () {
            atualizarBoxHomologarPendencia(false);
            if (textareaMotivo && !textareaMotivo.checkValidity()) {
                textareaMotivo.reportValidity();
                return;
            }
            if (!executarConfirmacaoReavaliacao('form-nao-homologar', 'Confirma a não homologação desta inscrição?')) {
                return;
            }
            formNaoHomologar.submit();
        });
    }

    if (btnConfirmarHomologarPendencia && formHomologarPendencia) {
        btnConfirmarHomologarPendencia.addEventListener('click', function () {
            atualizarBoxNaoHomologar(false);
            if (textareaPendencia && !textareaPendencia.checkValidity()) {
                textareaPendencia.reportValidity();
                return;
            }
            if (!executarConfirmacaoReavaliacao('form-homologar-pendencia', 'Confirma a homologação com pendência desta inscrição?')) {
                return;
            }
            formHomologarPendencia.submit();
        });
    }

    if (btnMostrarNaoHomologar && box) {
        btnMostrarNaoHomologar.addEventListener('click', function () {
            const exibindo = box.style.display !== 'none';
            atualizarBoxHomologarPendencia(false);
            atualizarBoxNaoHomologar(!exibindo);
        });
    }

    if (btnMostrarHomologarPendencia && boxPendencia) {
        btnMostrarHomologarPendencia.addEventListener('click', function () {
            const exibindo = boxPendencia.style.display !== 'none';
            atualizarBoxNaoHomologar(false);
            atualizarBoxHomologarPendencia(!exibindo);
        });
    }

    atualizarBoxNaoHomologar(box && box.style.display !== 'none');
    atualizarBoxHomologarPendencia(boxPendencia && boxPendencia.style.display !== 'none');
});

document.querySelectorAll('.homologacao-anexo-file').forEach(function (input) {
    input.addEventListener('change', function () {
        if (!this.files || this.files.length === 0) {
            return;
        }
        const form = this.closest('form');
        if (!form) {
            return;
        }
        form.submit();
    });
});
</script>
