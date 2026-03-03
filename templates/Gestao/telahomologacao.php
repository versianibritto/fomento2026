<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Homologação #<?= h($inscricao->id ?? '') ?></h4>
        <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listahomologacao']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <?php
        $naoInformado = '<span class="badge bg-danger">Não informado</span>';
        $formatDataAnexo = static function ($data): string {
            if ($data === null || $data === '') {
                return '';
            }
            if ($data instanceof \DateTimeInterface) {
                return (clone $data)->modify('-3 hours')->format('d/m/Y H:i');
            }
            $dataTexto = trim((string)$data);
            if ($dataTexto === '') {
                return '';
            }
            $timestamp = strtotime($dataTexto);
            return $timestamp ? date('d/m/Y H:i', strtotime('-3 hours', $timestamp)) : $dataTexto;
        };
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
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-semibold mb-3">Bolsista</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="text-muted small">Primeiro período</div>
                    <div class="fw-semibold"><?= h($primeiroPeriodoTexto) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Cota</div>
                    <div class="fw-semibold">
                        <?= isset($cotas[(string)($inscricao->cota ?? '')]) ? h($cotas[(string)$inscricao->cota]) : $naoInformado ?>
                    </div>
                </div>
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

            <hr class="my-4 border-0 border-top border-2 border-secondary-subtle opacity-100">

            <h5 class="fw-semibold mb-3">Coorientador</h5>
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
                <hr class="my-4 border-0 border-top border-2 border-secondary-subtle opacity-100">

                <h5 class="fw-semibold mb-3">Projeto</h5>
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

                <hr class="my-4 border-0 border-top border-2 border-secondary-subtle opacity-100">

                <h5 class="fw-semibold mb-3">Subprojeto</h5>
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

                <hr class="my-4 border-0 border-top border-2 border-secondary-subtle opacity-100">
            <?php endif; ?>

            <h5 class="fw-semibold mb-3">Anexos específicos da Inscrição</h5>
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

            <hr class="my-4 border-0 border-top border-2 border-secondary-subtle opacity-100">

            <?php if (!$homologacaoPermitida): ?>
                <div class="alert alert-danger mb-0 fw-semibold border-2">
                    Somente inscrições nas fases Finalizada, Homologada ou Não homologada podem ser homologadas ou não homologadas.
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
                        <button type="submit" class="btn btn-success"
                            onclick="return confirmarReavaliacao('form-homologar', 'Confirma a homologação desta inscrição?');">
                            Homologar
                        </button>
                    <?= $this->Form->end() ?>

                    <button type="button" class="btn btn-outline-danger" id="btn-mostrar-nao-homologar">
                        Não homologar
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
                                    type="submit"
                                    class="btn btn-danger"
                                    onclick="return confirmarReavaliacao('form-nao-homologar', 'Confirma a não homologação desta inscrição?');">
                                    Confirmar não homologação
                                </button>
                            </div>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const exigeConfirmacaoReavaliacao = <?= $exigeConfirmacaoReavaliacao ? 'true' : 'false' ?>;

    window.confirmarReavaliacao = function (formId, mensagemAcao) {
        const confirmouAcao = window.confirm(mensagemAcao);
        if (!confirmouAcao) {
            return false;
        }
        if (!exigeConfirmacaoReavaliacao) {
            return true;
        }
        const confirmouReavaliacao = window.confirm('Esta inscrição já havia sido verificada. Deseja realmente alterar a avaliação?');
        if (!confirmouReavaliacao) {
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

    const btn = document.getElementById('btn-mostrar-nao-homologar');
    const box = document.getElementById('box-nao-homologar');
    if (!btn || !box) {
        return;
    }
    btn.addEventListener('click', function () {
        const exibindo = box.style.display !== 'none';
        box.style.display = exibindo ? 'none' : 'block';
    });
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
