<section>
    <div class="main ajuste">
        <h1 class="tituloEM">Unidades participantes</h1>
        <div class="barraTituloEM"></div>
    </div>
</section>

<section>
    <div class="main">
        <div class="blocoUnidades">

            <?php foreach ($unidades as $unidade): ?>
                <div class="unidade">
                    <p class="unidadeT">
                        <?= ($unidade->sigla. ' - '. $unidade->nome) ?>
                    </p>

                    <br>
                    <b>Coordenador(a):</b>
                    <?= (($unidade->coordenador!=null?$unidade->coordenadore->nome.($unidade->subcoordenador!=null?', '.$unidade->subcoordenadore->nome:''):'Não Informado')) ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>
