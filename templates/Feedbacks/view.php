<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Post / Feedback</h4>
        <?= $this->Html->link('Voltar', ['action' => 'index'], ['class'=>'btn btn-outline-secondary btn-sm']) ?>
    </div>

    <?php
        $tipoBadge = 'bg-info';
        $tipoTexto = 'Resposta';
        if ($feedback->tipo === 'M') { $tipoBadge = 'bg-success'; $tipoTexto = 'Comentário'; }
        if ($feedback->tipo === 'S') { $tipoBadge = 'bg-primary'; $tipoTexto = 'Sugestão'; }
        if ($feedback->tipo === 'C') { $tipoBadge = 'bg-warning'; $tipoTexto = 'Crítica'; }
        if ($feedback->tipo === 'R') { $tipoBadge = 'bg-danger'; $tipoTexto = 'Reclamação'; }
        $destino = ($feedback->destinatario=='T'?'Todos':($feedback->destinatario=='G'?'Gestão':($feedback->destinatario=='A'?'Administrativo':($feedback->destinatario=='I'?'T.I.':' - '))));
    ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <span class="badge <?= h($tipoBadge) ?>"><?= h($tipoTexto) ?></span>
                    <div class="mt-2">
                        <div class="text-muted small">Título</div>
                        <div class="fw-semibold"><?= $feedback->titulo ? h($feedback->titulo) : 'Não informado' ?></div>
                    </div>
                </div>
                <a class="btn btn-sm btn-primary" href="/feedbacks/responder/<?=$feedback->id?>">
                    <i class="fas fa-regular fa-comment me-1"></i> Responder
                </a>
            </div>

            <hr class="my-3">

            <div class="text-muted small">Feedback</div>
            <div><?= h($feedback->texto) ?></div>
        </div>
        <div class="card-footer">
            <div class="small">Destinatário: <?= h($destino) ?></div>
            <div class="small">Criada em: <?=date("d/m/Y \à\s H:i", strtotime($feedback->created))?></div>
            <div class="small">Autor: <?= h($feedback->usuario->nome) ?></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-semibold mb-3">Respostas</h5>
            <?php
                $renderRespostas = function (int $parentId, int $nivel = 0) use (&$renderRespostas, $repliesByParent) {
                    if (empty($repliesByParent[$parentId])) {
                        return;
                    }
                    foreach ($repliesByParent[$parentId] as $item) {
                        $autor = $item->usuario->nome ?? '';
                        $margin = $nivel * 24;
                        ?>
                        <div class="reply-item" style="margin-left: <?= (int)$margin ?>px;">
                            <div class="reply-meta">
                                <span class="fw-semibold"><?= h((string)$autor) ?></span>
                                <span class="text-muted">• <?= $item->created ? h($item->created->i18nFormat('dd/MM/YYYY HH:mm')) : '-' ?></span>
                            </div>
                            <div class="reply-text"><?= h((string)($item->texto ?? '')) ?></div>
                            <div class="reply-actions">
                                <a class="btn btn-sm btn-outline-primary" href="/feedbacks/responder/<?= (int)$item->id ?>">
                                    <i class="fas fa-regular fa-comment me-1"></i> Responder
                                </a>
                            </div>
                        </div>
                        <?php
                        $renderRespostas((int)$item->id, $nivel + 1);
                    }
                };
            ?>
            <?php if (empty($repliesByParent[(int)$feedback->id])) { ?>
                <div class="text-muted">Não há resposta registrada.</div>
            <?php } else { ?>
                <?php $renderRespostas((int)$feedback->id, 0); ?>
            <?php } ?>
        </div>
    </div>
</div>

<style>
    .reply-item {
        border-left: 3px solid #e3e6ea;
        padding-left: 14px;
        margin-bottom: 12px;
    }
    .reply-meta {
        font-size: 0.9rem;
        margin-bottom: 4px;
    }
    .reply-text {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 6px;
    }
    .reply-actions .btn {
        padding: 2px 10px;
    }
</style>
