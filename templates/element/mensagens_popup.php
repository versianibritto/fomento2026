<?php
$mensagensPopup = $mensagensPopup ?? [];
$popupId = $popupId ?? 'modalMensagensSistema';
if (empty($mensagensPopup)) {
    return;
}

$primeiraMensagem = $mensagensPopup[0] ?? null;
$htmlTitulo = trim((string)($primeiraMensagem->titulo ?? ''));
$htmlTexto = trim((string)($primeiraMensagem->testo ?? ''));
$imagemPrincipal = !empty($primeiraMensagem->imagem);
$tituloPrincipal = trim(strip_tags($htmlTitulo)) !== '' ? $htmlTitulo : 'Aviso';
$mensagemSomenteImagem = count($mensagensPopup) === 1
    && $imagemPrincipal
    && trim(strip_tags($htmlTitulo)) === ''
    && trim(strip_tags($htmlTexto)) === '';
?>
<div class="modal fade" id="<?= h($popupId) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog <?= $mensagemSomenteImagem ? 'modal-xl' : 'modal-lg modal-dialog-scrollable' ?> modal-dialog-centered">
        <div class="modal-content <?= $mensagemSomenteImagem ? 'border-0 bg-transparent shadow-none' : '' ?>">
            <div class="modal-header <?= $mensagemSomenteImagem ? 'border-0 position-absolute top-0 end-0 w-100 justify-content-end' : '' ?>">
                <?php if (!$mensagemSomenteImagem): ?>
                    <div class="modal-title mensagem-popup-titulo"><?= $tituloPrincipal ?></div>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body <?= $mensagemSomenteImagem ? 'p-0 text-center' : '' ?>">
                <?php foreach ($mensagensPopup as $index => $mensagem): ?>
                    <?php
                    $tituloHtml = trim((string)($mensagem->titulo ?? ''));
                    $textoHtml = trim((string)($mensagem->testo ?? ''));
                    $temTitulo = trim(strip_tags($tituloHtml)) !== '';
                    $temTexto = trim(strip_tags($textoHtml)) !== '';
                    ?>
                    <div class="<?= $index > 0 && !$mensagemSomenteImagem ? 'border-top pt-4 mt-4' : '' ?>">
                        <?php if ($index > 0 && $temTitulo): ?>
                            <div class="mb-2 mensagem-popup-titulo"><?= $tituloHtml ?></div>
                        <?php endif; ?>
                        <?php if ($temTexto): ?>
                            <div class="mb-3 mensagem-popup-texto"><?= $textoHtml ?></div>
                        <?php endif; ?>
                        <?php if (!empty($mensagem->imagem)): ?>
                            <div class="<?= $mensagemSomenteImagem ? '' : 'text-center' ?>">
                                <img
                                    src="/uploads/editais/<?= h($mensagem->imagem) ?>"
                                    alt="<?= h(trim(strip_tags($tituloHtml)) ?: 'Mensagem') ?>"
                                    class="<?= $mensagemSomenteImagem ? 'mensagem-popup-imagem-total' : 'img-fluid rounded border mensagem-popup-imagem' ?>"
                                >
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer <?= $mensagemSomenteImagem ? 'border-0 bg-transparent justify-content-center' : '' ?>">
                <button type="button" class="btn btn-sm px-3 py-1 mensagem-popup-btn-fechar" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<style>
#<?= h($popupId) ?> .mensagem-popup-titulo {
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.3;
}
#<?= h($popupId) ?> .mensagem-popup-texto {
    color: #495057;
}
#<?= h($popupId) ?> .mensagem-popup-btn-fechar {
    background: #6c757d;
    border-color: #6c757d;
    color: #fff;
}
#<?= h($popupId) ?> .mensagem-popup-btn-fechar:hover,
#<?= h($popupId) ?> .mensagem-popup-btn-fechar:focus {
    background: #5c636a;
    border-color: #565e64;
    color: #fff;
}
#<?= h($popupId) ?> .mensagem-popup-imagem {
    max-height: 70vh;
}
#<?= h($popupId) ?> .mensagem-popup-imagem-total {
    display: block;
    width: 100%;
    height: 82vh;
    object-fit: cover;
    border-radius: 1rem;
    background: #fff;
}
#<?= h($popupId) ?> .modal-header.position-absolute {
    z-index: 10;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById(<?= json_encode($popupId) ?>);
    if (!modalEl) {
        return;
    }

    setTimeout(function () {
        if (window.jQuery && jQuery.fn && jQuery.fn.modal) {
            jQuery(modalEl).modal('show');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }, 250);
});
</script>
