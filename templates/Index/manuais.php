
<section>
    <div class="main ajuste">
        <h1 class="tituloEM">Manuais</h1>
        <div class="barraTituloEM"></div>
    </div>
</section>
<section>
    <div class="main">
        <div class="manual">
            <?php
            foreach($manuais as $m) {
            ?>
            <div class="manualItem">
                <div class="border">
                        <div class="editais box-simple">
                            <div class="box-simple-title mt-2 h4"><?=$m->nome?></div>
                        </div>
                    <div class="p-2">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                            <?=$m->arquivo !=null ? '<li class="list-inline-item"><a href="/uploads/editais/' . $m->arquivo . '" target="_blank">Download</a></li>' : ''?>

                            </li>
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