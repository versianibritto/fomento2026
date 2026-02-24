<?php
$banco = [

    'host' => 'localhost',
    'username' => 'root',
    'password' => 'uaarae11',
    'database' => 'fomento',

    /*
    'host' => '157.86.11.114',
    'username' => 'TST_PIBIC',
    'password' => '@123Pibic2024',
    'database' => 'TST_VPPCB_PIBIC',
    */
    
];
$hoje=date('Y_m_d_H:m');


$query = "select 
                r.id as raic_id, 
                r.usuario_id as bolsista_usuario_id, 
                u.nome as bolsista_nome, 
                u.email as bolsista_email, 
                u.telefone as telefone1_bolsista,
                u.telefone_contato as telefone2_bolsista,
                z.whatsapp as wapp_bolsista,
                r.orientador as orientador_usuario_id, 
                z.nome as orientador_nome, 
                z.email as orientador_email, 
                z.telefone as telefone1_orientador,
                z.telefone_contato as telefone2_orientador,
                z.whatsapp as wapp_orientador,
                s.sigla as unidade, 
                r.projeto_orientador as projeto_id, 
                a.nome as area,
                g.nome as grande_area,
                p.titulo as projeto_orientador, 
                r.titulo as projeto_bolsista,
                r.projeto_bolsista_id,
                pb.situacao,
                pb.deleted,
                pb.justificativa_cancelamento,                

                av1.indicado_premio_capes as indicado_premio_capes1,
                av1.nota as nota1,
                av1.observacao as observacao1,

                av2.indicado_premio_capes as indicado_premio_capes2,
                av2.nota as nota2,
                av2.observacao as observacao2,


                av3.indicado_premio_capes as indicado_premio_capes3,
                av3.nota as nota3,
                av3.observacao as observacao3


                from raics r
                left join usuarios u on u.id=r.usuario_id
                    left join usuarios z on z.id=r.orientador
                        left join unidades s on s.id=z.unidade_id
                            left join projetos p on p.id=r.projeto_orientador
                            left join areas a on a.id=p.area_id
                            left join grandes_areas g on g.id=a.grandes_area_id
                            left join projeto_bolsistas pb on r.projeto_bolsista_id=pb.id

            
                            
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
                                                where ab.ano='2024' and ab.deleted=0 and ab.tipo='V' and ab.deleted=0 and ab.ordem=1
                                            ) av1 on av1.bolsista=r.id
                                            
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
                                                where ab.ano='2024' and ab.deleted=0 and ab.tipo='V' and ab.deleted=0 and ab.ordem=2
                                            ) av2 on av2.bolsista=r.id
                                            
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
                                                where ab.ano='2024' and ab.deleted=0 and ab.tipo='V' and ab.deleted=0 and ab.ordem=3
                                            ) av3 on av3.bolsista=r.id
                            
                            
                            
            where 
            (av1.indicado_premio_capes=1 or av2.indicado_premio_capes=1 or av3.indicado_premio_capes=1) and r.editai_id in (22, 21)";
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
        <th>raic_id </th>
        <th>bolsista_nome </th>
        <th>bolsista_email </th>
        <th>telefone1_bolsista</th>
        <th>telefone2_bolsista</th>
        <th>wapp_bolsista</th>
        <th>orientador_nome </th>
        <th>orientador_email </th>
        <th>telefone1_orientador</th>
        <th>telefone2_orientador</th>
        <th>wapp_orientador</th>
        <th>unidade</th>
        <th>projeto_bolsista_id</th>

        <th>projeto_titulo</th>
        <th>projeto_area</th>
        <th>projeto_grande_area</th>
        <th>subprojeto_titulo</th>
        

        <th>deleted</th>
        <th>justificativa_cancelamento</th>
        <th>indicado_premio_capes1</th>
        <th>nota1</th>
        <th>observacao1</th>
        <th>indicado_premio_capes2</th>
        <th>nota2</th>
        <th>observacao2</th>
        <th>indicado_premio_capes3</th>
        <th>nota3</th>
        <th>observacao3</th>
    </tr>";
    foreach($rows as $r){
        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['raic_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['bolsista_nome'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['bolsista_email'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone1_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone2_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['wapp_bolsista'])."</td>

                    <td style='text-align:center'>".utf8_decode($r['orientador_nome'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['orientador_email'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone1_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone2_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['wapp_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['unidade'])."</td>
                    <td style='text-align:center'>".$r['projeto_bolsista_id']."</td>

                    <td style='text-align:right'>".($r['projeto_orientador']===null?'N/A':(utf8_decode($r['projeto_orientador'])))."</td>
                    <td style='text-align:right'>".($r['area']===null?'N/A':(utf8_decode($r['area'])))."</td>
                    <td style='text-align:right'>".($r['grande_area']===null?'N/A':(utf8_decode($r['grande_area'])))."</td>
                    <td style='text-align:right'>".($r['projeto_bolsista']===null?'N/A':(utf8_decode($r['projeto_bolsista'])))."</td>
                    





                    <td style='text-align:center'>".$r['deleted']."</td>
                    <td style='text-align:center'>".utf8_decode($r['justificativa_cancelamento'])."</td>

                    <td style='text-align:center'>".($r['indicado_premio_capes1']===null?'N/A':($r['indicado_premio_capes1']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:center'>".($r['nota1']===null?'N/A':(number_format(($r['nota1']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['observacao1']===null?'N/A':(utf8_decode($r['observacao1'])))."</td>

                    

                    <td style='text-align:center'>".($r['indicado_premio_capes2']===null?'N/A':($r['indicado_premio_capes2']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:center'>".($r['nota2']===null?'N/A':(number_format(($r['nota2']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['observacao2']===null?'N/A':(utf8_decode($r['observacao2'])))."</td>

                    
                    
                    <td style='text-align:center'>".($r['indicado_premio_capes3']===null?'N/A':($r['indicado_premio_capes3']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:center'>".($r['nota3']===null?'N/A':(number_format(($r['nota3']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['observacao3']===null?'N/A':(utf8_decode($r['observacao3'])))."</td>

                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"Premio_destaque".($hoje).".xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;