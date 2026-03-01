<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Feedbacks</h4>
        <?php if(!$this->request->getAttribute('identity')['yoda']){?>
            <a class="btn btn-success btn-sm" href="/feedbacks/add">
                <i class="fa fa-plus me-1"></i> Registrar feedback
            </a>
        <?php }?>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="fw-semibold mb-1">Atenção ao uso deste canal</div>
            <div class="text-muted">
                <div>Este espaço é destinado a <strong>sugestões</strong>, <strong>comentários</strong> e <strong>críticas</strong>.</div>
                <div>As mensagens são direcionadas à <strong>gestão</strong>.</div>
                <div>
                    Para <strong>erros de sistema</strong>, utilize o
                    <a href="<?= $this->Url->build(['controller' => 'Suporte', 'action' => 'index']) ?>" class="text-decoration-none fw-semibold">Suporte</a>
                    para encaminhamento direto à TI.
                </div>
            </div>
        </div>
    </div>

    <?php if($this->request->getAttribute('identity')['yoda']){?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <?=$this->Form->create(null,['url'=>['controller'=>'Feedbacks','action'=>'index'], 'class' => 'row g-2 align-items-end']);?>
                    <div class="col-md-4">
                        <?=$this->Form->control('tipo',[
                            'label'=>'Tipo',
                            'options'=>['C'=>'Crítica', 'M'=>'Comentário', 'S'=>'Sugestão', 'R'=>'Reclamação', 'P'=>'Respostas'],
                            'empty'=>'- Todos -',
                            'class'=>'form-select'
                        ])?>
                    </div>
                    <div class="col-md-4">
                        <?=$this->Form->control('destinatario',[
                            'label'=>'Destinatário',
                            'options'=>['G'=>'Gestão','A'=>'ADM', 'T'=>'Todos', 'I'=>'T.I.'],
                            'empty'=>'- Todos -',
                            'class'=>'form-select'
                        ])?>
                    </div>
                    <div class="col-md-4">
                        <?=$this->Form->control('situacao',[
                            'label'=>'Status',
                            'options'=>['N'=>'Nova','R'=>'Respondida'],
                            'empty'=>'- Todos -',
                            'class'=>'form-select'
                        ])?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-3">
                            <i class="fa fa-search me-2"></i> Buscar
                        </button>
                    </div>
                <?=$this->Form->end()?>
            </div>
        </div>
    <?php }?>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Usuário</th>
                        <th>Data</th>
                        <th>Para</th>
                        <th>Título</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($feedbacks as $b):
                        $nome = explode(" ", $b->usuario->nome);
                        $tipoBadge = 'bg-info';
                        $tipoTexto = 'Resposta';
                        if ($b->tipo === 'M') { $tipoBadge = 'bg-success'; $tipoTexto = 'Comentário'; }
                        if ($b->tipo === 'S') { $tipoBadge = 'bg-primary'; $tipoTexto = 'Sugestão'; }
                        if ($b->tipo === 'C') { $tipoBadge = 'bg-warning'; $tipoTexto = 'Crítica'; }
                        if ($b->tipo === 'R') { $tipoBadge = 'bg-danger'; $tipoTexto = 'Reclamação'; }
                        $destino = ($b->destinatario=='T'?'Todos':($b->destinatario=='G'?'Gestão':($b->destinatario=='A'?'Adm':($b->destinatario=='I'?'T.I.':' - '))));
                        $status = ($b->situacao=='N'?'Nova':($b->situacao=='R'?'Respondida':''));
                        $titulo = $b->titulo ? (strlen($b->titulo)>20 ? substr($b->titulo, 0, 20).' (...)' : $b->titulo) : '-';
                    ?>
                    <tr>
                        <td><span class="badge <?= h($tipoBadge) ?>"><?= h($tipoTexto) ?></span></td>
                        <td><?= h($nome[0] . ' ' . end($nome)) ?></td>
                        <td><small><?=($b->created==null?' - x - ':$b->created->i18nFormat('dd/MM/YYYY'))?></small></td>
                        <td><small><?= h($destino) ?></small></td>
                        <td>
                            <?php if(!$b->titulo && $b->parent_id!=null){ ?>
                                <a class="btn btn-sm btn-outline-info" href="/feedbacks/view/<?=$b->parent_id?>">Ver post original</a>
                            <?php } else { ?>
                                <?= h($titulo) ?>
                            <?php } ?>
                        </td>
                        <td><small><?= h($status) ?></small></td>
                        <td class="text-end">
                            <?php
                                $ramoAtual = (int)($b->ramo ?? $b->id);
                                $qtdNovas = $novasRespostasPorRamo[$ramoAtual] ?? 0;
                            ?>
                            <?=$this->Html->link('Ver respostas', ['controller'=>'feedbacks','action' => 'view', $b->id, '?' => ['ramo' => $ramoAtual]], ['class' => 'btn btn-sm btn-outline-info', 'escape' => false])?>
                            <?php if($qtdNovas > 0){?>
                                <span class="badge bg-danger ms-1"><?= (int)$qtdNovas ?></span>
                            <?php }?>
                            <?php if($b->origem!='R'){?>
                                <a class="btn btn-sm btn-primary ms-1" href="/feedbacks/responder/<?=$b->id?>"><i class="fas fa-regular fa-comment"></i></a>
                            <?php }?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($feedbacks->count() === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Nenhum feedback encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination mt-3">
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->prev('« Previous') ?>
        <?= $this->Paginator->next('Next »') ?>
        <?= $this->Paginator->counter() ?>
    </div>
</div>
