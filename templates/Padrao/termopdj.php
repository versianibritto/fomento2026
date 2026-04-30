<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $inscricao
 * @var \Cake\ORM\Query|\Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $doc_bolsista
 */
?>
<style media="print">
.pagebreak { page-break-before: always; }
@page {
    size: auto;
    margin: 50px 0;
}
</style>
<?php
    $m = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
    $cota=[
        'G'=>['Geral'],
        'I'=>['pessoas Indígena'],
        'N'=>['Pessoas Negras (Pretos/Pardos)'],
        'T'=>['Pessoas Trans'],
        'D'=>['Pessoas com deficiência'],
    ];
    $area=[
        '10'=>['Ciências Biológicas e Biomédicas'],
        '11'=>['Ciências Humanas e Sociais e Interdisciplinar'],
        '12'=>['Saúde Coletiva'],
    ]
?>
<div style="padding:0px 70px 50px; text-align:justity !important;font-size:14px">
    <div>
        <?=$this->Html->image('logoNovo.svg',['style'=>'width:20%'])?>
    </div>
    <div style="background-color:#EEEEEE;padding:5px;text-align:right">
        Inscri&ccedil;&atilde;o número <b><?=$inscricao->id.'-'.date('Ymd', strtotime($inscricao->created))?></b>
    </div>
    <div style="height:30px"></div>
    <div>
        <h1><?=$inscricao->projeto->titulo?></h1>
        <strong>Edital: <?=$inscricao->editai->nome?></strong>
        <p>Inscri&ccedil;&atilde;o número <b><?=$inscricao->id.'-'.date('Ymd', strtotime($inscricao->created))?></b></p>
        <p>Cota <b><?=$inscricao->cota==null?'Não Informado': $cota[$inscricao->cota][0]?></b></p>       
        <p>Área de Pesquisa: <strong><?=$area[$inscricao->area_id][0]?></strong></p>
        <br><br><br><br><br>
        
        
        
        <h2>1. Identificação do Proponente</h2>
        <p>Proponente / Coordenador: <strong><?=$inscricao->usuario->nome?></strong></p>
        <p>Matrícula Siape: <strong><?=$inscricao->usuario->matricula_siape?></strong></p>
        <p>CPF: <strong><?=$inscricao->usuario->cpf?></strong></p>
        <p>Email Institucional: <strong><?=$inscricao->usuario->email?></strong></p>
        <p>Email Alternativo: <strong><?=($inscricao->usuario->email_alternativo==null?'Não informado':$inscricao->usuario->email_alternativo)?></strong></p>
        <p>Unidade: <strong><?=$inscricao->usuario->unidade->sigla?></strong></p>
        <p>Laboratorio / Deartamento: <strong><?=$inscricao->usuario->laboratorio.' / '.$inscricao->usuario->departamento?></strong></p>
        <p>Telefone: <strong><?=($inscricao->usuario->telefone==null?'Não Informado':$inscricao->usuario->telefone)?></strong></p>
        <p>Celular: <strong><?=($inscricao->usuario->celular==null?'Não Informado':$inscricao->usuario->celular)?></strong></p>
        <br><br><br><br><br>


        <h2>2. Identificação do Candidato</h2>   
        <p>Candidato / Bolsista: <strong><?=$inscricao->candidato->nome?></strong></p>
        <p>CPF: <strong><?=$inscricao->candidato->cpf?></strong></p>
        <p>Email Institucional: <strong><?=$inscricao->candidato->email?></strong></p>
        <p>Email Alternativo: <strong><?=($inscricao->candidato->email_alternativo==null?'Não informado':$inscricao->candidato->email_alternativo)?></strong></p>
        <p>Telefone: <strong><?=($inscricao->candidato->telefone==null?'Não Informado':$inscricao->candidato->telefone)?></strong></p>
        <p>Celular: <strong><?=($inscricao->candidato->celular==null?'Não Informado':$inscricao->candidato->celular)?></strong></p>
        <br><br><br><br><br>


        <h2>3. Detalhamento do Projeto</h2>   
        <p><strong><?=($inscricao->projeto->titulo)?></strong></p>
        <p><?=($inscricao->projeto->resumo)?></p>
        <br><br><br><br><br>


    <div>
        <?=$this->Html->image('logoNovo.svg',['style'=>'width:20%'])?>
    </div>
    <div style="background-color:#EEEEEE;padding:5px;text-align:right">
        Inscri&ccedil;&atilde;o número <b><?=$inscricao->id.'-'.date('Ymd', strtotime($inscricao->created))?></b>
    </div>
    <div style="height:30px"></div>
    <div>
    <h2 style="width:100%;text-align:center">DECLARAÇÃO DE RESPONSABILIDADE DO ORIENTADOR</h2>
    <h3 style="width:100%;text-align:center">DECLARAÇÃO</h3>
    <p>Eu, <strong><?=strtoupper($inscricao->usuario->nome)?></strong>, portador do CPF <strong><?=$inscricao->usuario->cpf?></strong>, pesquisador (<?=$inscricao->usuario->vinculo->nome?>) do  Departamento <strong><?=$inscricao->usuario->departamento?></strong>, Laboratório <strong><?=$inscricao->usuario->laboratorio?></strong>, tendo o conhecimento da Legislação Nacional de Biossegurança, da Resolução 196/96 do Conselho Nacional de Saúde  e das Normas de Trabalho com Animais de Laboratório e/ou pesquisas com seres humanos, declaro para os devidos fins, que estou ciente de minhas responsabilidades em providenciar treinamento no uso de equipamentos de proteção coletiva e individual ao Bolsista de Pós-Doutorado Júnior: <strong><?=strtoupper($inscricao->candidato->nome)?></strong> portador do CPF <strong><?=$inscricao->candidato->cpf?></strong>, relacionados aos riscos físicos, químicos, biológicos, ergonômicos e de acidentes (NR-15).</p>
    <p>Declaro também ter informado o estudante sobre os aspectos éticos da pesquisa e sobre o termo de sigilo, em caso de pesquisa com geração de patente.</p>
    <p>Rio de Janeiro, <?=date('d', strtotime($inscricao->modified))?> de <?=$m[date('n', strtotime($inscricao->modified))]?> de <?=date('Y', strtotime($inscricao->modified))?></p>
    <br><br><br><br><br>
    ___________________________________________________________________________<br />
    <?=strtoupper($inscricao->usuario->nome)?><br />
    (Assinatura do Pesquisador)   
    <br><br><br><br><br>
    ___________________________________________________________________________<br />
    <?=strtoupper($inscricao->candidato->nome)?><br />
    (Assinatura do Candidato)  
    <br><br><br><br><br>
     
    </div>
</div>
<br><br><br><br>
<div class="pagebreak"></div>
<div style="padding:0px 70px 50px; text-align:justity !important;font-size:14px">
    <div>
        <?=$this->Html->image('logoNovo.svg',['style'=>'width:20%'])?>
    </div>
    <div>
        <h2 style="width:100%;text-align:center">TERMO DE COMPROMISSO</h2>
        <h3 style="width:100%;text-align:center">GESTÃO DA PROPRIEDADE INTELECTUAL</h3>
        <p>Considerando que a FIOCRUZ é uma instituição pública diretamente vinculada ao Ministério da Saúde, cuja missão é a geração, absorção e difusão de conhecimentos científicos e tecnológicos em saúde; </p>
        <p>Considerando que a FIOCRUZ, visando contribuir com a política nacional de saúde pública, possui como política institucional a busca da proteção legal dos resultados oriundos das suas atividades de pesquisas e desenvolvimento tecnológico; </p>
        <p>Considerando que a novidade é um dos requisitos necessários à proteção dos resultados de pesquisas pelos institutos de propriedade industrial, e, por conseqüência, a sua manutenção em sigilo até a adoção dos procedimentos legais pertinentes é indispensável para a obtenção da proteção almejada; </p>
        <p>Considerando, ainda, o disciplinado pelo ordenamento jurídico brasileiro, em especial pela Lei 9.279/96 (Lei de Propriedade Industrial), Lei 9.609/98 (Lei de Programa de Computador), Lei 9.610/98 (Lei de Direitos Autorais), Decreto 2.553/98 (que regulamenta sobre a premiação aos inventores de instituições públicas) e Lei 10.973/04 (Lei de regulamentada pelo Decreto nº 5.563, de 11 de outubro de 2005), pela Medida Provisória 2.186/2001 e demais atos normativos emanados do Conselho de Gestão do Patrimônio Genético do Ministério do Meio Ambiente; </p>
        <p>Pelo presente TERMO DE COMPROMISSO, o signatário abaixo qualificado: </p>
        <p>1º Obriga-se a manter em sigilo todas as informações obtidas em função das atividades desempenhadas junto a FIOCRUZ, incluindo, mas não limitadas, às informações técnicas e científicas relativas a: projetos, resultados de pesquisas, operações, processos, produção, instalações, equipamentos, habilidades especializadas, métodos e metodologias, fluxogramas, componentes, fórmulas, produtos, amostras, diagramas, desenhos, desenho de esquema industrial, patentes, segredos de negócio. Estas informações serão consideradas INFORMAÇÕES CONFIDENCIAIS. </p>
        <p>A obrigação de sigilo assumida, por meio deste termo, não compreende informações que já sejam de conhecimento público ou se tornem publicamente disponíveis por outra maneira que não uma revelação não autorizada. </p>
        <p>O sigilo imposto veda quaisquer formas de divulgação das INFORMAÇÕES CONFIDENCIAIS, sejam através de artigos técnicos, relatórios, publicações, comunicações verbais entre outras, salvo prévia autorização por escrito da FIOCRUZ, em conformidade com o disposto no art. 12 da Lei 10.973/2004, que dispõe: </p>
        <p>“É vedado a dirigente, ao criador ou a qualquer servidor, militar, empregado ou prestador de serviços de ICT divulgar, noticiar ou publicar qualquer aspecto de criações de cujo desenvolvimento tenha participado diretamente ou tomado conhecimento por força de suas atividades, sem antes obter expressa autorização da ICT”.</p>

        <p>A vigência da obrigação de sigilo perdurará até que a informação tida como INFORMAÇÃO CONFIDENCIAL seja licitamente tornada de conhecimento público ou FIOCRUZ autorize por escrito a sua divulgação, devendo ser observado os procedimentos institucionais estabelecidos para tanto. </p>
        <p>2º Obriga-se a não usar as INFORMAÇÕES CONFIDENCIAIS de forma distinta dos propósitos das atividades a serem desempenhadas junto a FIOCRUZ. </p>
        <p>3º Obriga-se a não enviar amostras de material biológico e/ou genético, obtidas em função das atividades desempenhadas junto a FIOCRUZ, a terceiros sem a prévia autorização por escrito da FIOCRUZ, devendo ser observado os procedimentos institucionais estabelecidos para tanto. </p>
        <p>4º Reconhece que, respeitado o direito de nomeação a autoria (autor/inventor), os direitos de propriedade intelectual sobre os resultados porventura advindos da execução das atividades pelo signatário desempenhadas perante a FIOCRUZ pertencerão exclusivamente a FIOCRUZ, ficando esta desde já autorizada a requerer a proteção pelos institutos de propriedade intelectual que julgar pertinente. Para tanto, se compromete em assinar todos os documentos que forem necessários para regularizar a titularidade da FIOCRUZ perante os institutos de propriedade intelectual, no Brasil e exterior. </p>
        <p>5º Reconhece que a inobservância das disposições aqui contidas sujeitar-lhe-á à aplicação das sanções legais pertinentes, em especial às sanções administrativas, além de ensejar responsabilidade em eventuais perdas e danos ocasionados a FIOCRUZ. </p>
        
        <p>Rio de Janeiro, <?=date('d', strtotime($inscricao->modified))?> de <?=$m[date('n', strtotime($inscricao->modified))]?> de <?=date('Y', strtotime($inscricao->modified))?></p>
        <br><br><br><br><br>

        ___________________________________________________________________________<br />
    <?=strtoupper($inscricao->usuario->nome)?><br />
    (Assinatura do Pesquisador)  
        <p></p>
        <strong>Nome: </strong> <?=$inscricao->usuario->nome ?> <br />
        <strong>Identidade: </strong> <?=$inscricao->usuario->documento_numero ?>/<?=$inscricao->usuario->documento_emissor ?><br />
        <strong>CPF: </strong> <?=$inscricao->usuario->cpf ?> <br />
        <strong>Matrícula Siape: </strong> <?=$inscricao->usuario->matricula_siape ?> <br />
        <strong>Endereço: </strong> <?=$inscricao->usuario->street->nome . ', ' . $inscricao->usuario->numero . ' - ' . $inscricao->usuario->street->district->nome . ', '  . $inscricao->usuario->street->district->city->nome?> <br />
        <strong>Telefone: </strong> <?=$inscricao->usuario->telefone ?? 'Não informado'?> <br />
        <strong>Vínculo com a FIOCRUZ: </strong> <?=$inscricao->usuario->vinculo->nome ?><br />
        <!--<strong>Atividades  </strong> <?=$inscricao->usuario->id ?>desenvolvidas junto a FIOCRUZ: <br />-->
        <strong>Unidade: </strong> <?=$inscricao->usuario->unidade->sigla ?> <br />
        <strong>Departamento: </strong> <?=$inscricao->usuario->departamento ?? 'Não informado'  ?> <br />
        <strong>Laboratório: </strong> <?=$inscricao->usuario->laboratorio ?? 'Não informado' ?>  
        <br><br><br><br><br>
        ___________________________________________________________________________<br />
    <?=strtoupper($inscricao->candidato->nome)?><br />
    (Assinatura do Candidato)  
        <p></p>
        <strong>Nome: </strong> <?=$inscricao->candidato->nome ?> <br />
        <strong>Identidade: </strong> <?=$inscricao->candidato->documento_numero ?>/<?=$inscricao->candidato->documento_emissor ?><br />
        <strong>CPF: </strong> <?=$inscricao->candidato->cpf ?> <br />
        <strong>Telefone: </strong> <?=$inscricao->candidato->telefone ?? 'Não informado'?> <br />
       
    </div>
</div>
