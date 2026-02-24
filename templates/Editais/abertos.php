<div class="container">
        <div class="row">
            <div class="col-12 d-flex gap-3">
                <div class="d-flex flex-column justify-content-center">
                    <h1 class="breadcrumbs-custom-title">Editais Abertos</h1>
                </div>
            </div>
        </div>
    </div>
<section class="section pt-5 bg-grey-100">
    <?php
        if($editais->all()->isEmpty()){
        ?>
            <tr>
                <td colspan="6"><div class="alert alert-info" style="text-align:center;">Nenhum edital com inscrição aberta no momento.</div></td>
            </tr>
        <?php
        }?>
</section>
<section class="section pt-5 bg-grey-100">
    <div class="container text-center text-lg-start mb-5">
        <div class="row row-30 row-xxl-40 row-offset-2">
            <?php
            foreach($editais as $e) {
            ?>
            <div class="col-xs-6 col-md-4">
                <div class="border">
                    <a href="/projetos/addsubprojeto/<?= $e->id?>">
                        <div class="editais box-simple">
                            <?=(date("Ymd", strtotime($e->fim_inscricao)) > date('Ymd') ? '<div class="inscricoes">Clique aqui para inscrever</div>' : '')?>
                            <div class="box-simple-title h4"><?=$e->nome?></div>
                            <div class="box-simple-time"><?=$e->unidade_id == null ? 'Toda a FIOCRUZ' : $e->unidade->sigla?></div>
                        </div>
                    </a>
                    <?php
                    if(date("Ymd", strtotime($e->inicio_vigencia)) > date("Ymd")) {
                    ?>
                    <div class="p-2">
                        <ul class="list-inline">
                            <?=$e->arquivo != null && $e->origem == "R" ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->arquivo . '" target="_blank">Regulamento</a></li>' : ''?>
                            <?=$e->arquivo != null && $e->origem <> "R" ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->arquivo . '" target="_blank">Download</a></li>' : ''?>
                            <?=$e->resultado_arquivo != null ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->resultado_arquivo . '" target="_blank">Resultado</a></li>' : ''?>
                            <?php
                            foreach($e->erratas as $errata) {
                            ?>
                            <li class="list-inline-item text-danger"><a href="/uploads/editais/<?=$errata->arquivo?>" target="_blank">Errata #<?=$errata->id?></a></li>' : ''?>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                    } else {
                    ?>
                    <div class="p-2 text-center">
                        PROCESSO FINALIZADO
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
</section>
