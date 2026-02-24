<section>
    <div class="main ajuste">
        <h1 class="tituloEM">Editais</h1>
        <div class="barraTituloEM"></div>
        <div class="edital">
                <?php
                foreach($editais as $e) {
                ?>
                <div class="editalItem">
                    <div class="border">
                        <a href="">
                            <div class="editais box-simple">
                                <?=(((date("Ymd", strtotime($e->fim_inscricao)) > date('Ymd')) && (date("Ymd", strtotime($e->inicio_inscricao)) < date('Ymd'))) ? '<div class="inscricoes">Inscrições abertas</div>' : '')?>
                                <?=(((date("Ymd", strtotime($e->inicio_inscricao)) > date('Ymd'))) ? '<div class="inscricoes">Divulgação</div>' : '')?>

                                <div class="box-simple-title h4"><?=$e->nome?></div>
                                <div class="box-simple-time"><?=$e->unidades_permitidas == null ? 'Toda a FIOCRUZ' : 'Restrito a Unidades'?></div>
                            </div>
                        </a>
                    
                        <div class="p-2">
                            <ul class="list-inline">
                                <!--<?=$e->arquivo != null && $e->origem == "R" ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->arquivo . '" target="_blank">Regulamento</a></li>' : ''?>-->
                                <!--<?=$e->arquivo != null && $e->origem <> "R" ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->arquivo . '" target="_blank">Download</a></li>' : ''?>-->
                                <?=($e->origem=='V'?($e->arquivo != null ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->arquivo . '" target="_blank">Regulamento</a></li>' : ''):($e->arquivo != null ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->arquivo . '" target="_blank">Download</a></li>' : ''))?>
                                <?=($e->origem=='V'?($e->resultado_arquivo != null ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->resultado_arquivo . '" target="_blank">Modelo de relatório</a></li>' : ''):($e->resultado_arquivo != null ? '<li class="list-inline-item"><a href="/uploads/editais/' . $e->resultado_arquivo . '" target="_blank">Resultado</a></li>' : ''))?>
                                <?php
                                foreach($e->erratas as $errata) {
                                ?>
                                <li class="list-inline-item text-danger"><a href="/uploads/editais/<?=$errata->arquivo?>" target="_blank">Errata #<?=$errata->id?></a></li>
                                <?php
                                }
                                if((date('Ymd', strtotime($e->p_nova)) > date("Ymd")) && (date('Ymd', strtotime($e->inicio_inscricao)) < date("Ymd")) && ($usuario_logado != null) && ($e->origem!='V')) {
                                    $isPdj = ($e->programa && $e->programa->letra === 'J');
                                ?>
                                    <li class="list-inline-item text-danger bg-success rounded px-3">
                                        <a
                                        href="<?=($isPdj?('/pdj2025/verificavigente/'.$e->id):($e->origem == "N"?('/projetos/inscricao/'.$e->id):('projetos/listarenovar/'.$e->id)))?>"
                                        target="_blank" class="text-white">Inscreva-se
                                        </a>
                                    </li>                                
                                <?php    
                                }
                                ?>
                            </ul>
                        </div>
                        
                    </div>
                </div>
                <?php
                }
                ?>
        </div>
    </div>
</section>
