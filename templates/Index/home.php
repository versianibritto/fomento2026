<h1 class="hidden">Fomento à Pesquisa : Fundação Oswaldo Cruz</h1>
<section>
    <div class="main">
        <div class="conteudo">
            <h2>Programas de Fomento à Pesquisa</h2>
            <p>
                A Fiocruz fomenta a geração de conhecimento de excelência e a formação e qualificação de recursos
                humanos em saúde por meio da VPPCB, a partir de propostas de projetos de pesquisa em fase inicial
                de desenvolvimento e pela inserção de novos pesquisadores, inclusive pela implementação de novas áreas.
            </p>
            <p>
                As ações desse eixo concentram-se em programas de incentivo como o Pós-Doutorado Júnior (PDJ),
                Programa de Bolsas de Iniciação Científica (Pibic), Programa de Bolsas de Iniciação Tecnológica
                e Inovação (Pibiti) . O financiamento é feito por meio de editais de concorrência pública e 
                seleção de projetos por mérito.
            </p>
            <div class="programas">
                <a href="/programas/PDJ" title="Programa PDJ.">  
                    <div class="btPdj">
                        <div><img src="/img/bt_pdj.svg" class="" /></div>  
                        <div>
                            <div class="programaTitulo">
                                Doutores <br>
                                Programa Pós-Doutorado Júnior
                            </div>
                        </div>
                    </div>
                </a>
                <a href="/programas/IC" title="Programas de Iniciação Ciêntífica.">
                    <div class="btIcs">
                        <div><img src="/img/bt_ics.svg" class="" /></div>
                        <div>
                            <div class="programaTitulo">
                                Graduandos<br>
                                Programas de Iniciação Ciêntífica
                            </div>
                            <div class="programaDescricao">(ICs, PIBIC, PIBITI)</div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="duvida">
                <a href="/manuais" title="Manuais">
                    <div><img src="/img/img_bt_duvida.svg" class="" /></div>  
                        <div>
                            <div class="duvidaTexto">
                                Tem alguma dúvida <br>
                                de como utilizar o sistema? <br>
                                Veja os nossos manuais.
                            </div>
                    </div>
                </a>
            </div>
           
        </div>
    </div>
    <?= $this->element('mensagens_popup', [
        'mensagensPopup' => $mensagensPopup ?? [],
        'popupId' => 'modalMensagensExternas',
    ]) ?>
</section>
