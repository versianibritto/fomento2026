<?php
$banco = [

    'host' => 'localhost',
    'username' => 'root',
    'password' => 'Uaarae11@',
    'database' => 'pibicv3',

    /*
    'host' => '157.86.11.114',
    'username' => 'TST_PIBIC',
    'password' => '@123Pibic2023',
    'database' => 'TST_VPPCB_PIBIC',
    */
    
];

$query = "select 
            pb.id as projeto_bolsista_id,
            pb.resultado as resultado,
            pb.programa as programa,
            pb.apresentar_raic as apresentar_raic_1,
            pb.subprojeto_renovacao as subprojeto_renovacao,
            pb.justificativa_alteracao as justificativa_orientador_alteracao,
            pb.referencia_raic as projeto_raic_alterado,
            pb.cota as cota,
            j.titulo as projeto_orientador,
            y.nome as nome_orientador,
            s.sigla as unidade_orientador,
            pb.sp_titulo as subprojeto_bolsista,
            z.nome as nome_bolsista,
            p.id as raic_id,
            p.data_apresentacao as data_raic,
            av1.nome_avaliador as avaliador1,
            av1.destaque as destaque1,
            av1.indicado_premio_capes as indicado_premio_capes1,
            av1.avaliacao_alteracao_subprojeto as avaliacao_alteracao_subprojeto1,
            av1.nota as nota1,
            av1.anexou_parecer as anexou_parecer1,
            av1.observacao as observacao1,

            av2.nome_avaliador as avaliador2,
            av2.destaque as destaque2,
            av2.indicado_premio_capes as indicado_premio_capes2,
            av2.avaliacao_alteracao_subprojeto as avaliacao_alteracao_subprojeto2,
            av2.nota as nota2,
            av2.anexou_parecer as anexou_parecer2,
            av2.observacao as observacao2,


            av3.nome_avaliador as avaliador3,
            av3.destaque as destaque3,
            av3.indicado_premio_capes as indicado_premio_capes3,
            av3.avaliacao_alteracao_subprojeto as avaliacao_alteracao_subprojeto3,
            av3.nota as nota3,
            av3.anexou_parecer as anexou_parecer3,
            av3.observacao as observacao3




            from projeto_bolsistas pb
                left join raics p on p.projeto_bolsista_id=pb.id
                    left join projetos j on j.id=pb.projeto_id
                        left join usuarios y on y.id=pb.orientador
                        left join unidades s on s.id=y.unidade_id
                            left join usuarios z on z.id=pb.usuario_id
                            
                                left join (select 
                                            ab.bolsista as bolsista,
                                            u.nome as nome_avaliador,
                                            ab.destaque as destaque,
                                            ab.indicado_premio_capes as indicado_premio_capes,
                                            ab.observacao_alteracao as avaliacao_alteracao_subprojeto,
                                            ab.nota as nota,
                                            ab.parecer as anexou_parecer,
                                            ab.observacao as observacao

                                                from avaliador_bolsistas ab
                                                    left join avaliadors a on a.id=ab.avaliador_id
                                                    left join usuarios u on a.usuario_id=u.id
                                                where ab.ano='2023' and ab.deleted=0 and ab.tipo='V' and ab.deleted=0 and ab.ordem=1
                                            ) av1 on av1.bolsista=p.id
                                            
                                left join (select 
                                            ab.bolsista as bolsista,
                                            u.nome as nome_avaliador,
                                            ab.destaque as destaque,
                                            ab.indicado_premio_capes as indicado_premio_capes,
                                            ab.observacao_alteracao as avaliacao_alteracao_subprojeto,
                                            ab.nota as nota,
                                            ab.parecer as anexou_parecer,
                                            ab.observacao as observacao

                                                from avaliador_bolsistas ab
                                                    left join avaliadors a on a.id=ab.avaliador_id
                                                    left join usuarios u on a.usuario_id=u.id
                                                where ab.ano='2023' and ab.deleted=0 and ab.tipo='V' and ab.deleted=0 and ab.ordem=2
                                            ) av2 on av2.bolsista=p.id
                                            
                                    left join (select 
                                            ab.bolsista as bolsista,
                                            u.nome as nome_avaliador,
                                            ab.destaque as destaque,
                                            ab.indicado_premio_capes as indicado_premio_capes,
                                            ab.observacao_alteracao as avaliacao_alteracao_subprojeto,
                                            ab.nota as nota,
                                            ab.parecer as anexou_parecer,
                                            ab.observacao as observacao

                                                from avaliador_bolsistas ab
                                                    left join avaliadors a on a.id=ab.avaliador_id
                                                    left join usuarios u on a.usuario_id=u.id
                                                where ab.ano='2023' and ab.deleted=0 and ab.tipo='V' and ab.deleted=0 and ab.ordem=3
                                            ) av3 on av3.bolsista=p.id
                            
                            
                            
            where 
            pb.situacao='F' and pb.deleted=0 and year(pb.created)=2023";
try
{
    $PDO = new PDO('mysql:host='.$banco['host'].';dbname='.$banco['database'], $banco['username'], $banco['password']);
}
catch ( PDOException $e )
{
    echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
}
$PDO->exec("set names utf8");
$result = $PDO->query( $query );
$rows = $result->fetchAll();
$arquivo = "
<table>
    <tr>
        
        <th>Projeto_bolsista_id</th>
        <th>nome_bolsista</th>
        <th>unidade_orientador</th>
        <th>nome_orientador</th>
        <th>Programa</th>
        <th>nome_avaliador1</th>
        <th>nota1</th>
        <th>observacao1</th>

        <th>nome_avaliador2</th>
        <th>nota2</th>
        <th>observacao2</th>

        <th>nome_avaliador3</th>
        <th>nota3</th>
        <th>observacao3</th>

        <th>cota</th>
        <th>Somatorio</th>
        <th>Media</th>
        <th>Nota máxima</th>
        <th>Nota Mínima</th>
        <th>Discrepância</th>



        <th>Resultado</th>
        <th>Apresentou Raic?</th>
        <th>Subprojeto Novo?</th>
        <th>Justificativa Orientador altera&Ccedil;&Atilde;o</th>
        <th>Dados apresentados</th>


        <th>raic_id</th>
        <th>data_raic</th>
        <th>subprojeto_bolsista</th>
        <th>projeto_orientador</th>

    
        <th>destaque1</th>
        <th>indicado_premio_capes1</th>
        <th>avaliacao_alteracao_subprojeto1</th>
        <th>anexou_parecer1</th>

        <th>destaque2</th>
        <th>indicado_premio_capes2</th>
        <th>avaliacao_alteracao_subprojeto2</th>
        <th>anexou_parecer2</th>

        <th>destaque3</th>
        <th>indicado_premio_capes3</th>
        <th>avaliacao_alteracao_subprojeto3</th>
        <th>anexou_parecer3</th>

    </tr>";
    foreach($rows as $r){
        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['projeto_bolsista_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_bolsista'])."</td>
                    <td style='text-align:center'>".$r['unidade_orientador']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".($r['programa']=='T'?'PIBITI':'PIBIC')."</td>
                    <td style='text-align:center'>".utf8_decode($r['avaliador1'])."</td>
                    <td style='text-align:center'>".($r['nota1']===null?'N/A':(number_format(($r['nota1']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['observacao1']===null?'N/A':(utf8_decode($r['observacao1'])))."</td>
                    <td style='text-align:center'>".utf8_decode($r['avaliador2'])."</td>
                    <td style='text-align:center'>".($r['nota2']===null?'N/A':(number_format(($r['nota2']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['observacao2']===null?'N/A':(utf8_decode($r['observacao2'])))."</td>
                    <td style='text-align:center'>".utf8_decode($r['avaliador3'])."</td>
                    <td style='text-align:center'>".($r['nota3']===null?'N/A':(number_format(($r['nota3']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['observacao3']===null?'N/A':(utf8_decode($r['observacao3'])))."</td>
                    <td style='text-align:center'>".($r['cota']=='G'?'GERAL':($r['cota']=='N'?'Negros/Pardos':($r['cota']=='T'?'Transexual':'Deficiente')))."</td>
                    <td style='text-align:center'></td>
                    <td style='text-align:center'></td>
                    <td style='text-align:center'></td>
                    <td style='text-align:center'></td>
                    <td style='text-align:center'></td>





                    <td style='text-align:center'>".($r['resultado']=='T'?'Aprova&Ccedil;&Atilde;o Autom&Aacute;tica':' - ')."</td>
                    <td style='text-align:center'>".($r['apresentar_raic_1']?'Apresentou RAIC':'N&Atilde;O apresentou Raic')."</td>
                    <td style='text-align:center'>".($r['subprojeto_renovacao']=='D'?'Cadastrado Novo Sunprojeto para Renova&Ccedil;&Atilde;o':'Manteve subprojeto Original')."</td>
                    <td style='text-align:right'>".utf8_decode($r['justificativa_orientador_alteracao'])."</td>
                    <td style='text-align:center'>".($r['projeto_raic_alterado']===null?'N&Atilde;o se aplica':($r['projeto_raic_alterado']==1?'Apresentou projeto original':'Apresentado Novo Projeto'))."</td>

                    <td style='text-align:center'>".$r['raic_id']."</td>
                    <td style='text-align:center'>".$r['data_raic']."</td>
                    <td style='text-align:right'>".utf8_decode($r['subprojeto_bolsista'])."</td>
                    <td style='text-align:right'>".utf8_decode($r['projeto_orientador'])."</td>

                    <td style='text-align:center'>".($r['destaque1']===null?'N/A':($r['destaque1']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:center'>".($r['indicado_premio_capes1']===null?'N/A':($r['indicado_premio_capes1']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:right'>".($r['avaliacao_alteracao_subprojeto1']===null?'N/A':(utf8_decode($r['avaliacao_alteracao_subprojeto1'])))."</td>
                    <td style='text-align:center'>".($r['anexou_parecer1']===null?'N/A':($r['anexou_parecer1']=='I'?'N&Atilde;o se aplica':($r['anexou_parecer1']=='N'?'É necess&Aacute;rio mas n&Atilde;o anexou':'Anexou')))."</td>

                    

                    <td style='text-align:center'>".($r['destaque2']===null?'N/A':($r['destaque2']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:center'>".($r['indicado_premio_capes2']===null?'N/A':($r['indicado_premio_capes2']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:right'>".($r['avaliacao_alteracao_subprojeto2']===null?'N/A':(utf8_decode($r['avaliacao_alteracao_subprojeto2'])))."</td>
                    <td style='text-align:center'>".($r['anexou_parecer2']===null?'N/A':($r['anexou_parecer2']=='I'?'N&Atilde;o se aplica':($r['anexou_parecer2']=='N'?'É necess&Aacute;rio mas n&Atilde;o anexou':'Anexou')))."</td>

                    
                    
                    <td style='text-align:center'>".($r['destaque3']===null?'N/A':($r['destaque3']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:center'>".($r['indicado_premio_capes3']===null?'N/A':($r['indicado_premio_capes3']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:right'>".($r['avaliacao_alteracao_subprojeto3']===null?'N/A':(utf8_decode($r['avaliacao_alteracao_subprojeto3'])))."</td>
                    <td style='text-align:center'>".($r['anexou_parecer3']===null?'N/A':($r['anexou_parecer3']=='I'?'N&Atilde;o se aplica':($r['anexou_parecer3']=='N'?'É necess&Aacute;rio mas n&Atilde;o anexou':'Anexou')))."</td>

                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"NotasRaicOrdem.xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;