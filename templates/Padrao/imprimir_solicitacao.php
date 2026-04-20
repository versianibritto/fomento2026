<style media="print">
.pagebreak { page-break-before: always; }
@page {
    size: auto;
    margin: 50px 0;
}
</style>
<?php
    $m = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
    $v = ['F'=>'Pesquisador 40h','C'=>'Celetista 40h','V'=>'Pesquisador Visitante', 'A'=>'Aluno de Pós-Doutorado', 'W' => 'Bolsista FIOTEC'];
    
    $situacao = [
        'I' => 'Inscri&ccedil;&atilde;o em andamento',
        'N' => 'Solicita&ccedil;&atilde;o realizada',
        'A' => 'Ativo',
        'O' => 'Renova&ccedil;&atilde;o em andamento',
        'R' => 'Renova&ccedil;&atilde;o',
        'F' => 'Renova&ccedil;&atilde;o Finalizada',
        'E' => 'Encerrado',
        'U' => 'Processo de substitui&ccedil;&atilde;o',
        'S' => 'Substituído',
        'X' => 'Reprovado',
        'C' => 'Cancelamento solicitado',
        'Q' => 'Cancelado',
        'L' => 'Finalizando bolsa', 
        'B' => 'Banco Reserva',
        'P' => 'Aprovado', 
        'W' => 'Cancelamento com intenção de substituição posterior',
        'Y' => 'Cancelado, aguardando substitui&ccedil;&atilde;o'
    ];
?>
<div style="padding:0px 70px 50px; text-align:justity !important;font-size:14px">
    <div>
        <?=$this->Html->image('logoNovo.svg',['style'=>'width:15%'])?>
    </div>
    <div>
        <p style="text-align:center"><?= $bol->editai->programa->sigla?> - CNPq/FIOCRUZ </p>
        <p style="text-align:center">Formul&aacute;rio de Solicita&ccedil;&atilde;o de Bolsas para o Per&iacute;odo
    <?=$bol->editai->inicio_vigencia==null?'':$bol->editai->inicio_vigencia->i18nFormat('dd/MM/YYYY');?> a <?=$bol->editai->fim_vigencia==null?'':$bol->editai->fim_vigencia->i18nFormat('dd/MM/YYYY');?></p>
    </div>
    
    <div style="background-color:#EEEEEE;padding:5px;text-align:right">
        Inscri&ccedil;&atilde;o número <b><?=$bol->id?></b>
    </div>
    <div style="height:30px"></div>
    <div style="text-align:justity !important">
        <p>Eu, 
            <strong><i><?=($bol->orientadore->nome_social==null?
                $bol->orientadore->nome:$bol->orientadore->nome_social);?>
            </i></strong>, portador<?=($bol->orientadore->sexo=='M'?'':'a');?> 
            do CPF de número <?=$bol->orientadore->cpf;?>, 
            registrado no Sistema de Gest&atilde;o de Bolsas da VPPCB-FIOCRUZ sob o número 
            <?=str_pad($bol->orientadore->id,5,'0',STR_PAD_LEFT);?>, 
            onde constam todos os meus dados pessoais e de contato, 
            informados por mim.
        </p>
        <p>
            Declaro que meu vínculo com a FIOCRUZ é de 
            <?=$bol->orientadore->vinculo->nome;?> 
            e que detenho a gradua&ccedil;&atilde;o de 
            <?=$bol->orientadore->escolaridade->nome;?>, 
            mínima exigida para a submiss&atilde;o de projetos conforme o Edital, 
            que foi lido e estou de pleno acordo.
        </p>

        <?=($projeto->financiamento==''?'':'<p>O projeto é co-financiando por '.strtoupper($projeto->financiamento).'</p>')?>
        <p>
            O projeto <strong>(<?=$projeto->titulo?>)</strong>
             apresentado solicita o/a bolsista descrito a seguir:
        </p>
        

        <ul>
          <?="<li> (".($bol->bolsista_anterior!=null?'Substitui&ccedil;&atilde;o à - '.$bol->bolsista_anterior.' - ':($bol->situacao == 'N' ? 'Nova - ': (($bol->situacao == 'R'||$bol->situacao == 'F') ? 'Renova&ccedil;&atilde;o - ' : ($bol->origem=='N'?'Bolsa Nova':($bol->origem=='R'?'Renova&ccedil;&atilde;o':'Em substitui&ccedil;&atilde;o a -'.$bol->bolsista_anterior))))).
            ($bol->created->i18nFormat('YYYY')).') '.
            ($bol->bolsista_usuario->nome_social==null?$bol->bolsista_usuario->nome:$bol->bolsista_usuario->nome_social)
            .', CPF '.$bol->bolsista_usuario->cpf.", estudante de ".(isset($bol->bolsista_usuario->curso)?$bol->bolsista_usuario->curso:'n&otilde;o informado')." em ".(
                ($bol->bolsista_usuario->instituicao_curso!=null)?((is_numeric($bol->bolsista_usuario->instituicao_curso))?$bol->bolsista_usuario->instituicao->sigla:$bol->bolsista_usuario->instituicao_curso):'n&otilde;o informado').", conforme Histórico Escolar e Comprovante de Matrícula anexo.</li>";?>
        </ul>
        <div style="height:15px"></div>
        <p>
            <b>DECLARA&Ccedil;&Otilde;ES NEGATIVAS DE V&Iacute;NCULO EMPREGAT&Iacute;CIO 
                E TERMOS DE COMPROMISSO
            </b>
        </p>
        
        <p></p>
        <p></p>
        <p align="justify">Eu, 
            <?=($bol->bolsista_usuario->nome_social==null?$bol->bolsista_usuario->nome:$bol->bolsista_usuario->nome_social)?>, 
            CPF n&ordm; <?=$bol->bolsista_usuario->cpf;?> 
            declaro estar apto(a) a participar
            do Programa Institucional de Bolsas de Inicia&ccedil;&atilde;o Cient&iacute;fica
            Fiocruz/CNPq, tendo em vista que n&atilde;o possuo nenhum v&iacute;nculo empregat&iacute;cio,
            nem outros trabalhos remunerados.<br>
            Declaro ainda, que uma vez comprovada a acumula&ccedil;&atilde;o desta com outros
            programas do CNPq, de outra ag&ecirc;ncia ou da pr&oacute;pria universidade,
            comprometo-me a devolver, em valores atualizados, as mensalidades recebidas
            indevidamente.
        </p>
        <p>
            Sendo assim me considero de acordo com o proposto pela GEST&Atilde;O 
            DA PROPRIEDADE INTELECTUAL, considerando que a FIOCRUZ &eacute; uma 
            institui&ccedil;&atilde;o p&uacute;blica
            diretamente vinculada ao Minist&eacute;rio da Sa&uacute;de, cuja miss&atilde;o
            &eacute; a gera&ccedil;&atilde;o, absor&ccedil;&atilde;o e difus&atilde;o de
            conhecimentos cient&iacute;ficos e tecnol&oacute;gicos em sa&uacute;de; 
            <br>
            Considerando que a FIOCRUZ, visando contribuir com a pol&iacute;tica nacional
            de sa&uacute;de p&uacute;blica, possui como pol&iacute;tica institucional a
            busca da prote&ccedil;&atilde;o legal dos resultados oriundos das suas atividades
            de pesquisas e desenvolvimento tecnol&oacute;gico; 
            <br>
            Considerando que a novidade &eacute; um dos requisitos necess&aacute;rios &agrave;
            prote&ccedil;&atilde;o dos resultados de pesquisas pelos institutos de propriedade
            industrial, e, por conseq&uuml;&ecirc;ncia, a sua manuten&ccedil;&atilde;o em
            sigilo at&eacute; a ado&ccedil;&atilde;o dos procedimentos legais pertinentes
            &eacute; indispens&aacute;vel para a obten&ccedil;&atilde;o da prote&ccedil;&atilde;o
            almejada; 
            <br>
            Considerando, ainda, o disciplinado pelo ordenamento jur&iacute;dico brasileiro,
            em especial pela Lei 9.279/96 (Lei de Propriedade Industrial), Lei 9.609/98
            (Lei de Programa de Computador), Lei 9.610/98 (Lei de Direitos Autorais), Decreto
            2.553/98 (que regulamenta sobre a premia&ccedil;&atilde;o aos inventores de
            institui&ccedil;&otilde;es p&uacute;blicas) e Lei 10.973/04 (Lei de regulamentada
            pelo Decreto n&ordm; 5.563, de 11 de outubro de 2005), pela Medida Provis&oacute;ria
            2.186/2001 e demais atos normativos emanados do Conselho de Gest&atilde;o do
            Patrim&ocirc;nio Gen&eacute;tico do Minist&eacute;rio do Meio Ambiente; 
            <br>
            <br>
            <br>
            Pelo presente TERMO DE COMPROMISSO, o signat&aacute;rio abaixo qualificado:
            <br>
            <br>
            1&ordm; Obriga-se a manter em sigilo de todas as informa&ccedil;&otilde;es obtidas
            em fun&ccedil;&atilde;o das atividades desempenhadas junto a FIOCRUZ, incluindo,
            mas n&atilde;o limitadas, &agrave;s informa&ccedil;&otilde;es t&eacute;cnicas
            e cient&iacute;ficas relativas a: projetos, resultados de pesquisas, opera&ccedil;&otilde;es,
            processos, produ&ccedil;&atilde;o, instala&ccedil;&otilde;es, equipamentos,
            habilidades especializadas, m&eacute;todos e metodologias, fluxogramas, componentes,
            f&oacute;rmulas, produtos, amostras, diagramas, desenhos, desenho de esquema
            industrial, patentes, segredos de neg&oacute;cio. Estas informa&ccedil;&otilde;es
            ser&atilde;o consideradas INFORMA&Ccedil;&Otilde;ES CONFIDENCIAIS. 
            <br>
            A obriga&ccedil;&atilde;o de sigilo assumida, por meio deste termo, n&atilde;o
            compreende informa&ccedil;&otilde;es que j&aacute; sejam de conhecimento p&uacute;blico
            ou se tornem publicamente dispon&iacute;veis por outra maneira que n&atilde;o
            uma revela&ccedil;&atilde;o n&atilde;o autorizada.
            <br>
            O sigilo imposto veda quaisquer formas de divulga&ccedil;&atilde;o das INFORMA&Ccedil;&Otilde;ES
            CONFIDENCIAIS, sejam atrav&eacute;s de artigos t&eacute;cnicos, relat&oacute;rios,
            publica&ccedil;&otilde;es, comunica&ccedil;&otilde;es verbais entre outras,
            salvo pr&eacute;via autoriza&ccedil;&atilde;o por escrito da FIOCRUZ, em conformidade
            com o disposto no art. 12 da Lei 10.973/2004, que disp&otilde;e: 
            <br>
            &#8220;&Eacute; vedado a dirigente, ao criador ou a qualquer servidor, militar,
            empregado ou prestador de servi&ccedil;os de ICT divulgar, noticiar ou publicar
            qualquer aspecto de cria&ccedil;&otilde;es de cujo desenvolvimento tenha participado
            diretamente ou tomado conhecimento por for&ccedil;a de suas atividades, sem
            antes obter expressa autoriza&ccedil;&atilde;o da ICT&#8221;. P&aacute;gina
            2 de 2 
            <br>
            A vig&ecirc;ncia da obriga&ccedil;&atilde;o de sigilo perdurar&aacute; at&eacute;
            que a informa&ccedil;&atilde;o tida como INFORMA&Ccedil;&Atilde;O CONFIDENCIAL
            seja licitamente tornada de conhecimento p&uacute;blico ou FIOCRUZ autorize
            por escrito a sua divulga&ccedil;&atilde;o, devendo ser observado os procedimentos
            institucionais estabelecidos para tanto. 
            <br>
        </p>
        
            
        <p>
            2&ordm; Obriga-se a n&atilde;o usar as INFORMA&Ccedil;&Otilde;ES CONFIDENCIAIS
            de forma distinta dos prop&oacute;sitos das atividades a serem desempenhadas
            junto a FIOCRUZ. 
            <br>
        </p>
        <p>
            3&ordm; Obriga-se a n&atilde;o enviar amostras de material biol&oacute;gico
            e/ou gen&eacute;tico, obtidas em fun&ccedil;&atilde;o das atividades desempenhadas
            junto a FIOCRUZ, a terceiros sem a pr&eacute;via autoriza&ccedil;&atilde;o por
            escrito da FIOCRUZ, devendo ser observado os procedimentos institucionais estabelecidos
            para tanto. 
            <br>
        </p>
        <p>
            4&ordm; Reconhece que, respeitado o direito de nomea&ccedil;&atilde;o a autoria
            (autor/inventor), os direitos de propriedade intelectual sobre os resultados
            porventura advindos da execu&ccedil;&atilde;o das atividades pelo signat&aacute;rio
            desempenhadas perante a FIOCRUZ pertencer&atilde;o exclusivamente a FIOCRUZ,
            ficando esta desde j&aacute; autorizada a requerer a prote&ccedil;&atilde;o
            pelos institutos de propriedade intelectual que julgar pertinente. Para tanto,
            se compromete em assinar todos os documentos que forem necess&aacute;rios para
            regularizar a titularidade da FIOCRUZ perante os institutos de propriedade intelectual,
            no Brasil e exterior. 
            <br>
        </p>
        <p>
            5&ordm; Reconhece que a inobserv&acirc;ncia das disposi&ccedil;&otilde;es aqui
            contidas sujeitar-lhe-&aacute; &agrave; aplica&ccedil;&atilde;o das san&ccedil;&otilde;es
            legais pertinentes, em especial &agrave;s san&ccedil;&otilde;es administrativas,
            al&eacute;m de ensejar responsabilidade em eventuais perdas e danos ocasionados
            a FIOCRUZ. 
            <br>
        </p>
            <br>
            <br>

            <!-- trecho das cotas-->
                <?php if($bol->cota=='N'){?>

                    <p><b>AUTODECLARA&Ccedil;&Atilde;O PARA PESSOAS NEGRAS</b></p>

                    <p align="justify">Eu, 
                        <?=($bol->bolsista_usuario->nome_social==null?$bol->bolsista_usuario->nome:$bol->bolsista_usuario->nome_social)?>, 
                        CPF n&ordm; 
                        <?=$bol->bolsista_usuario->cpf;?> 
                        me declaro ser pessoa preta ( ) parda ( ) e desejo concorrer &agrave;s vagas destinadas &agrave;s a&ccedil;&otilde;es afirmativas 
                        para pessoas negras, nos termos estabelecidos no processo de sele&ccedil;&atilde;o para ingresso no Programa Institucional 
                        de Bolsas de Inicia&ccedil;&atilde;o Cient&iacute;fica do Conselho Nacional de Desenvolvimento Cient&iacute;fico e 
                        Tecnol&oacute;gico (PIBIC/PIBITI/CNPq) da Funda&ccedil;&atilde;o Oswaldo Cruz. 
                        Declaro, ainda, que as informa&ccedil;&otilde;es aqui prestadas s&atilde;o de minha inteira responsabilidade, 
                        estando ciente de que, em caso de declara&ccedil;&atilde;o falsa, poderei ter como consequ&ecirc;ncia o meu desligamento 
                        do processo seletivo e san&ccedil;&otilde;es prescritas na legisla&ccedil;&atilde;o em vigor.
                        Concordo com a divulga&ccedil;&atilde;o de minha condi&ccedil;&atilde;o de optante por vagas destinadas a a&ccedil;&otilde;es 
                        afirmativas,  nos documentos e listas publicadas durante o processo seletivo.
                        
                        
                    </p>

                <?php }?>
                <?php if($bol->cota=='I'){?>

                    <p><b>AUTODECLARA&Ccedil;&Atilde;O PARA PESSOAS IND&Iacute;GENAS</b></p>

                    <p align="justify">Eu, 
                        <?=($bol->bolsista_usuario->nome_social==null?$bol->bolsista_usuario->nome:$bol->bolsista_usuario->nome_social)?>, 
                        CPF n&ordm; 
                        <?=$bol->bolsista_usuario->cpf;?> 
                        me declaro ser pessoa ind&iacute;gena e desejo concorrer &agrave;s vagas destinadas &agrave;s a&ccedil;&otilde;es afirmativas, 
                        nos termos estabelecidos no processo de sele&ccedil;&atilde;o para ingresso no Programa Institucional 
                        de Bolsas de Inicia&ccedil;&atilde;o Cient&iacute;fica do Conselho Nacional de Desenvolvimento Cient&iacute;fico e 
                        Tecnol&oacute;gico (PIBIC/PIBITI/CNPq) da Funda&ccedil;&atilde;o Oswaldo Cruz. 
                        Declaro, ainda, que as informa&ccedil;&otilde;es aqui prestadas s&atilde;o de minha inteira responsabilidade, 
                        estando ciente de que, em caso de declara&ccedil;&atilde;o falsa, poderei ter como consequ&ecirc;ncia o meu desligamento 
                        do processo seletivo e san&ccedil;&otilde;es prescritas na legisla&ccedil;&atilde;o em vigor.
                        Concordo com a divulga&ccedil;&atilde;o de minha condi&ccedil;&atilde;o de optante por vagas destinadas a a&ccedil;&otilde;es 
                        afirmativas,  nos documentos e listas publicadas durante o processo seletivo.
                        
                        
                    </p>

                <?php }?>

                <?php if($bol->cota == 'D'){?>

                    <p><b>AUTODECLARA&Ccedil;&Atilde;O PARA PESSOAS COM DEFICI&Ecirc;NCIA</b></p>

                    <p align="justify">Eu, 
                        <?=($bol->bolsista_usuario->nome_social==null?$bol->bolsista_usuario->nome:$bol->bolsista_usuario->nome_social)?>, 
                        CPF n&ordm; 
                        <?=$bol->bolsista_usuario->cpf;?> 
                        declaro que desejo me inscrever para concorrer &agrave;s vagas destinadas &agrave;s a&ccedil;&otilde;es afirmativas para pessoas com defici&ecirc;ncia, 
                        nos termos estabelecidos no processo de sele&ccedil;&atilde;o para ingresso no Programa Institucional 
                        de Bolsas de Inicia&ccedil;&atilde;o Cient&iacute;fica do Conselho Nacional de Desenvolvimento Cient&iacute;fico e 
                        Tecnol&oacute;gico (PIBIC/PIBITI/CNPq) da Funda&ccedil;&atilde;o Oswaldo Cruz. 
                        Declaro, ainda, que as informa&ccedil;&otilde;es aqui prestadas s&atilde;o de minha inteira responsabilidade, 
                        estando ciente de que, em caso de declara&ccedil;&atilde;o falsa, poderei ter como consequ&ecirc;ncia o meu desligamento 
                        do processo seletivo e san&ccedil;&otilde;es prescritas na legisla&ccedil;&atilde;o em vigor.
                        Concordo com a divulga&ccedil;&atilde;o de minha condi&ccedil;&atilde;o de optante por vagas destinadas a a&ccedil;&otilde;es 
                        afirmativas,  nos documentos e listas publicadas durante o processo seletivo.
                        
                        
                    </p>
                        <p><b><strong>Tipo de defici&ecirc;ncia</strong></b></p>
                        (  ) Defici&ecirc;ncia f&iacute;sica
                        (  ) Defici&ecirc;ncia auditiva
                        (  ) Defici&ecirc;ncia visual
                        (  ) Defici&ecirc;ncia intelectual
                        (  ) Defici&ecirc;ncia m&uacute;ltipla
                        (  ) Transtorno do Espectro Autista


                    <p>

                    </p>
                <?php } ?>
                <?php if($bol->cota=='T'){?>

                    <p><b>AUTODECLARA&Ccedil;&Atilde;O PARA PESSOAS TRANS</b></p>

                    <p align="justify">Eu, 
                        <?=($bol->bolsista_usuario->nome_social==null?$bol->bolsista_usuario->nome:$bol->bolsista_usuario->nome_social)?>, 
                        CPF n&ordm; 
                        <?=$bol->bolsista_usuario->cpf;?> 
                        me declaro ser pessoa trans (transexual, travesti ou transg&ecirc;nero) e confirmo a minha op&ccedil;&atilde;o 
                        em concorrer &agrave;s vagas reservadas para estudantes trans, 
                        nos termos estabelecidos no processo de sele&ccedil;&atilde;o para ingresso no Programa Institucional 
                        de Bolsas de Inicia&ccedil;&atilde;o Cient&iacute;fica do Conselho Nacional de Desenvolvimento Cient&iacute;fico e 
                        Tecnol&oacute;gico (PIBIC/PIBITI/CNPq) da Funda&ccedil;&atilde;o Oswaldo Cruz. 
                        Declaro, ainda, que as informa&ccedil;&otilde;es aqui prestadas s&atilde;o de minha inteira responsabilidade, 
                        estando ciente de que, em caso de declara&ccedil;&atilde;o falsa, poderei ter como consequ&ecirc;ncia o meu desligamento 
                        do processo seletivo e san&ccedil;&otilde;es prescritas na legisla&ccedil;&atilde;o em vigor.
                        Concordo com a divulga&ccedil;&atilde;o de minha condi&ccedil;&atilde;o de optante por vagas destinadas a a&ccedil;&otilde;es 
                        afirmativas,  nos documentos e listas publicadas durante o processo seletivo.
                        
                        
                    </p>

                <?php }?>
            <!-- fim trecho das cotas-->

            
            <table style="width:100%">
                <tr>
                    <td style="width:50%">
                        <br><br>
                        Local: __________________________________  em  ____/____/_______<br><br><br>
                        _____________________________________________________ <br>
                        Nome: 
                        <?=($bol->bolsista_usuario->nome_social==null?$bol->bolsista_usuario->nome:$bol->bolsista_usuario->nome_social);?><br>
                        Identidade: <?=$bol->bolsista_usuario->documento_numero;?><br>
                        CPF: <?=$bol->bolsista_usuario->cpf;?><br>
                        Profiss&atilde;o: BOLSISTA<br>
                        Telefone: <?=$bol->bolsista_usuario->telefone;?><br>
                        V&iacute;nculo com a FIOCRUZ: BOLSISTA<br>
                        Atividades desenvolvidas junto a FIOCRUZ: BOLSISTA<br>
                    </td>
                   
                    <td style="width:50%">
                        <br><br>
                        <?php 
                        if($teste_idade<18){?>

                            Local: __________________________________  em  ____/____/_______<br><br><br>
                            _____________________________________________________ <br>
                            Nome Legível do Responsável/representante legal: <br>
                            Identidade: <br>
                            CPF: <br><br><br>
                        <?php }?>
                    </td>
                </tr>
            </table>

            
        <br><br><br>
        
        <p>Submetido o projeto, aceitamos e concordamos com os seguintes 
            <b>TERMOS DE COMPROMISSO</b>:
        </p>
        <p align="justify">
            <strong><br>CONDI&Ccedil;&Otilde;ES GERAIS<br></strong>
            <br><br>
            1. Ao aceitar a concess&atilde;o, caso a bolsa seja aprovada, compromete-se
            o benefici&aacute;rio a dedicar-se, com exclusividade, &agrave;s atividades
            pertinentes &agrave; bolsa concedida.
            <br>
            <br>
            2. Confirma tamb&eacute;m ter sido informado pelo orientador sobre: (a) as normas
            de biosseguran&ccedil;a da Institui&ccedil;&atilde;o; (b) os aspectos &eacute;ticos
            da pesquisa em desenvolvimento; (c) em caso de pesquisa com gera&ccedil;&atilde;o
            de produtos pass&iacute;veis de registro de patente, estar a par e compromissado
            com os termos de sigilo. 
            <br>
            <br>
            3. Compromete-se ainda o benefici&aacute;rio a:
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;a) estar regularmente matriculado em curso de gradua&ccedil;&atilde;o;
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;b) apresentar excelente rendimento acad&ecirc;mico e n&atilde;o ter reprova&ccedil;&atilde;o
            em disciplinas afins com as atividades do projeto de pesquisa e nem ser do c&iacute;rculo
            familiar do orientador;
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;c) n&atilde;o possuir v&iacute;nculo empregat&iacute;cio nem receber sal&aacute;rio
            ou remunera&ccedil;&atilde;o decorrente do exerc&iacute;cio de atividades de
            qualquer natureza, inclusive os de est&aacute;gio remunerado, durante a vig&ecirc;ncia
            da bolsa;
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;d) dedicar-se integralmente &agrave;s atividades acad&ecirc;micas e de pesquisa,
            em ritmo compat&iacute;vel com as atividades exigidas pelo curso durante o ano
            letivo, e de forma intensificada durante as f&eacute;rias letivas;
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;e) n&atilde;o se afastar da institui&ccedil;&atilde;o em que desenvolve seu
            projeto de pesquisa, exceto para a realiza&ccedil;&atilde;o de pesquisa de campo,
            participa&ccedil;&atilde;o em evento cient&iacute;fico ou est&aacute;gio de
            pesquisa, por per&iacute;odo limitado e com autoriza&ccedil;&atilde;o do orientador.
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;f) apresentar, ap&oacute;s 6 (seis) meses de vig&ecirc;ncia do per&iacute;odo
            da bolsa, relat&oacute;rio de pesquisa, contendo resultados parciais;
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;g) apresentar os resultados parciais ou finais da pesquisa, sob a forma de exposi&ccedil;&otilde;es
            orais e pain&eacute;is, acompanhado de um relat&oacute;rio de pesquisa com reda&ccedil;&atilde;o
            cient&iacute;fica, que permita verificar o acesso a m&eacute;todos e processos
            cient&iacute;ficos.
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;h) estar recebendo apenas esta modalidade de bolsa, sendo vedada a acumula&ccedil;&atilde;o
            desta com a de outros programas do CNPq, de outra ag&ecirc;ncia ou da pr&oacute;pria
            institui&ccedil;&atilde;o;
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;i) devolver ao CNPq, em valores atualizados, a(s) mensalidade(s) recebidas indevidamente,
            caso os requisitos e compromissos estabelecidos acima n&atilde;o sejam cumpridos.
            <br><br>
            4. Os trabalhos publicados em decorr&ecirc;ncia das atividades apoiadas pelo
            CNPq dever&atilde;o, necessariamente, fazer refer&ecirc;ncia ao apoio recebido,
            com as seguintes express&otilde;es:
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;a) Se publicado individualmente: &quot;O presente trabalho foi realizado com
            o apoio do Conselho Nacional de Desenvolvimento Cient&iacute;fico e Tecnol&oacute;gico
            &#8211; CNPq &#8211; Brasil&quot;.
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;b) Se publicado em co-autoria: &quot;Bolsista do CNPq &#8211; Brasil&quot;
            <br><br>
            5. O CNPq poder&aacute; cancelar ou suspender a bolsa quando constatada infring&ecirc;ncia
            a quaisquer das condi&ccedil;&otilde;es constantes deste Termo das normas aplic&aacute;veis
            a esta concess&atilde;o, sem preju&iacute;zo da aplica&ccedil;&atilde;o dos
            dispositivos legais que disciplinam o ressarcimento dos recursos.
            <br><br>
            6. A concess&atilde;o objeto do presente instrumento n&atilde;o gera v&iacute;nculo
            de qualquer natureza ou rela&ccedil;&atilde;o de trabalho, constituindo doa&ccedil;&atilde;o,
            com encargos, feita ao benefici&aacute;rio.
            <br><br>
            7. O benefici&aacute;rio e o orientador manifestam sua integral e incondicional
            concord&acirc;ncia com os termos da concess&atilde;o, comprometendo-se a cumprir
            fielmente as condi&ccedil;&otilde;es expressas neste instrumento e as normas
            que lhe s&atilde;o aplic&aacute;veis: Resolu&ccedil;&atilde;o Normativa 017/2006
            do Programa Institucional de Bolsas de Inicia&ccedil;&atilde;o Cient&iacute;fica.
    </p>
    <p></p>
    <p></p>
    <?php if($bol->situacao=='F'){?>

        <?php if($bol->created->i18nFormat('YYYY')==2023){?>
            <?php if($bol->revista_orientador==1){?>
                <?php if($bol->revista_bolsista==1){?>
                    <p>
                    <b>Conforme informado na inscri&ccedil;&atilde;o fica autorizada pelo(a) orientador(a) 
                    <?= $bol->orientadore->nome_social ? $bol->orientadore->nome_social : $bol->orientadore->nome?> 
                    e pelo(a) bolsista <?=$bol->bolsista_usuario->nome_social ? $bol->bolsista_usuario->nome_social : $bol->bolsista_usuario->nome?>
                    a publica&ccedil;&atilde;o do resumo do subprojeto cadastrado nesta submiss&atilde;o na revista eletrônica do 
                    PIBIC/PIBITI Fiocruz, na próxima edi&ccedil;&atilde;o, condicionada a aprova&ccedil;&atilde;o do bolsista 
                    neste processo seletivo.</b>&nbsp;&nbsp;
                    </p>
                <?php }?>
            <?php }?>
            <?php if($bol->revista_orientador==1){?>
                <?php if($bol->revista_bolsista==0){?>
                    <p>
                    <b>Conforme informado na inscri&ccedil;&atilde;o 
                    o(a)bolsista <?=$bol->bolsista_usuario->nome_social ? $bol->bolsista_usuario->nome_social : $bol->bolsista_usuario->nome?> N&Atilde;O autoriza
                    a publica&ccedil;&atilde;o do resumo do subprojeto cadastrado nesta submiss&atilde;o na revista eletrônica do 
                    PIBIC/PIBITI Fiocruz, na próxima edi&ccedil;&atilde;o.</b>&nbsp;&nbsp;
                    </p>
                <?php }?>
            <?php }?>
            <?php if($bol->revista_orientador==0){?>
                    <p>
                    <b>Conforme informado na inscri&ccedil;&atilde;o o(a) orientador(a) 
                    <?= $bol->orientadore->nome_social ? $bol->orientadore->nome_social : $bol->orientadore->nome?>  N&Atilde;O autoriza
                    a publica&ccedil;&atilde;o do resumo do subprojeto cadastrado nesta submiss&atilde;o na revista eletrônica do 
                    PIBIC/PIBITI Fiocruz, na próxima edi&ccedil;&atilde;o.</b>&nbsp;&nbsp;
                    </p>
            <?php }?>

        <?php }?>
        <?php if($bol->created->i18nFormat('YYYY')!=2023){?>
            <?php if($bol->autorizacao==1){?>
                    <p>
                    <b>Conforme informado na inscri&ccedil;&atilde;o fica autorizada pelo(a) orientador(a) 
                    <?= $bol->orientadore->nome_social ? $bol->orientadore->nome_social : $bol->orientadore->nome?> 
                    
                    a publica&ccedil;&atilde;o do resumo do subprojeto cadastrado nesta submiss&atilde;o na revista eletrônica do 
                    PIBIC/PIBITI Fiocruz na edi&ccedil;&atilde;o Zero.</b>&nbsp;&nbsp;
                    </p>
            <?php }?>
            

        <?php }?>
    <?php }?>

        
    
    

        
    <p></p>
    <p></p>
    <p>Rio de Janeiro, 
        <?=$bol->created->i18nFormat('d');?> 
        de 
        <?=$m[$bol->created->i18nFormat('M')];?> 
        de 
        <?=$bol->created->i18nFormat('YYYY');?>
    </p><br />

    
    <?php
    //if($bol->situacao=='N'||$bol->situacao=='O'||$bol->situacao=='U'){
        print '<p>______________________________________________________________________<br />'. 
        ( $bol->bolsista_usuario->nome_social ? $bol->bolsista_usuario->nome_social : $bol->bolsista_usuario->nome) .
        ' - CPF '.$bol->bolsista_usuario->cpf.'<br />Bolsista</p>';
    //}
    
    ?>
    <?php 
        if($teste_idade<18){?>

            <p></p><br><br><br><br>
            ________________________________________________________________________ <br>
            Nome Legível do Responsável/representante legal: <br>
            CPF: <br><br><br>
        <?php }?>
    <p></p>
    <p style="margin-top:50px"></p>
    <?='<p>________________________________________________________________________<br />'. 
    ( $bol->orientadore->nome_social ? $bol->orientadore->nome_social : $bol->orientadore->nome ).
    ' - CPF '.$bol->orientadore->cpf.'<br />Orientador</p>';?>
    <p></p>
    <p style="margin-top:50px"></p>
    <?='<p>________________________________________________________________________<br />
    Carimbo e nome do Coordenador-PIBIC/PIBITI da Unidade</p>';?>
</div>
<br><br><br><br>
<div class="pagebreak"></div>
<div style="padding:0px 70px 50px; text-align:justity !important;font-size:14px">
    <div>
        <?=$this->Html->image('logoNovo.svg',['style'=>'width:15%'])?>
    </div>
    <div>
        <p style="text-align:center"><?=($bol->programa=='P'?'PIBIC':($bol->programa=='T'?'PIBIC':'Iniciação Científica'))?>-CNPq / FIOCRUZ </p>
        <p style="text-align:center">Declara&ccedil;&atilde;o de comprometimento da atualiza&ccedil;&atilde;o da produ&ccedil;&atilde;o intelectual no Repositório Intelectual, ARCA</p>
    </div>
    <div>
    TEXTO DO Declara&ccedil;&atilde;o de comprometimento da atualiza&ccedil;&atilde;o da produ&ccedil;&atilde;o intelectual no Repositório Intelectual, ARCA
    </div>
    <p></p>
    <p style="margin-top:50px"></p>
    <?='<p>________________________________________________________________________<br />'.$bol->orientadore->nome.' - CPF '.$bol->orientadore->cpf.'<br />Orientador</p>';?>
    <p></p>
</div>
