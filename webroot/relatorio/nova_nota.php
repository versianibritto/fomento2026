<?php
$banco = [

    'host' => 'localhost',
    'username' => 'root',
    'password' => 'segredo',
    'database' => 'fomento',

    /*
    'host' => '157.86.11.114',
    'username' => 'TST_PIBIC',
    'password' => '@123Pibic2024',
    'database' => 'TST_VPPCB_PIBIC',
    */
    
];

$query = "select 
            pb.id as projeto_bolsista_id,
            pb.programa as programa,
            pb.cota as cota,
            pb.projeto_id as projeto_id,
            y.nome as nome_orientador,
            y.sexo as sexo,
            pb.filhos_menor as filhos_menor,
            pb.pontos_orientador as pontos_orientador,
            s.sigla as unidade_orientador,
            z.nome as nome_bolsista,
            av1.nome_avaliador as avaliador1,
            av1.nota as nota1,
            av1.anexou_parecer as anexou_parecer1,
            av1.observacao as observacao1,

            av2.nome_avaliador as avaliador2,
            av2.nota as nota2,
            av2.anexou_parecer as anexou_parecer2,
            av2.observacao as observacao2

            from projeto_bolsistas pb
                    left join projetos j on j.id=pb.projeto_id
                        left join usuarios y on y.id=pb.orientador
                        left join unidades s on s.id=y.unidade_id
                            left join usuarios z on z.id=pb.usuario_id
                            
                                left join (select 
                                            ab.bolsista as bolsista,
                                            u.nome as nome_avaliador,
                                            ab.nota as nota,
                                            ab.parecer as anexou_parecer,
                                            ab.observacao as observacao

                                                from avaliador_bolsistas ab
                                                    left join avaliadors a on a.id=ab.avaliador_id
                                                    left join usuarios u on a.usuario_id=u.id
                                                where ab.ano='2024' and ab.deleted=0 and ab.tipo='N' and ab.deleted=0 and ab.ordem=1
                                            ) av1 on av1.bolsista=pb.id
                                            
                                left join (select 
                                            ab.bolsista as bolsista,
                                            u.nome as nome_avaliador,
                                            ab.nota as nota,
                                            ab.parecer as anexou_parecer,
                                            ab.observacao as observacao

                                                from avaliador_bolsistas ab
                                                    left join avaliadors a on a.id=ab.avaliador_id
                                                    left join usuarios u on a.usuario_id=u.id
                                                where ab.ano='2024' and ab.deleted=0 and ab.tipo='N' and ab.deleted=0 and ab.ordem=2
                                            ) av2 on av2.bolsista=pb.id
                                            
                                    
                            
                            
                            
            where 
            pb.situacao='N' and pb.deleted=0 and pb.editai_id=34";
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
        <th>nome_orientador</th>
        <th>projeto</th>
        <th>sexo</th>
        <th>filhos</th>
        <th>unidade_orientador</th>
        <th>nome_bolsista</th>
        <th>cota</th>
        <th>Programa</th>

        <th>nome_avaliador1</th>
        <th>observacao1</th>

        <th>nome_avaliador2</th>
        <th>observacao2</th>

        <th>Nota máxima</th>
        <th>Nota Mínima</th>
        <th>Discrepância</th>
        <th>lattes</th>

        <th>nota1</th>
        <th>nota2</th>

        <th>Media</th>
        <th>Somatorio com lattes</th>


        <th>anexou_parecer1</th>
        <th>anexou_parecer2</th>

      

    </tr>";
    foreach($rows as $r){
        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['projeto_bolsista_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".$r['projeto_id']."</td>
                    <td style='text-align:center'>".($r['sexo']=='F'?'FEM':($r['sexo']=='M'?'Masc':'Não declarado'))."</td>
                    <td style='text-align:center'>".($r['filhos_menor']===null?'N/A':$r['filhos_menor'])."</td>
                    <td style='text-align:center'>".$r['unidade_orientador']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_bolsista'])."</td>
                    <td style='text-align:center'>".($r['cota']=='G'?'GERAL':($r['cota']=='N'?'Negros/Pardos':($r['cota']=='T'?'Transexual':'Deficiente')))."</td>
                    <td style='text-align:center'>".($r['programa']=='T'?'PIBITI':'PIBIC')."</td>
                    
                    <td style='text-align:center'>".utf8_decode($r['avaliador1'])."</td>
                    <td style='text-align:right'>".($r['observacao1']===null?'N/A':(utf8_decode($r['observacao1'])))."</td>

                    <td style='text-align:center'>".utf8_decode($r['avaliador2'])."</td>
                    <td style='text-align:right'>".($r['observacao2']===null?'N/A':(utf8_decode($r['observacao2'])))."</td>

                    <td style='text-align:center'></td>
                    <td style='text-align:center'></td>
                    <td style='text-align:center'></td>
                    <td style='text-align:center'>".($r['pontos_orientador']===null?'N/A':(number_format(($r['pontos_orientador']),1,',','.')))."</td>
               
                    <td style='text-align:center'>".($r['nota1']===null?'N/A':(number_format(($r['nota1']),1,',','.')))."</td>
                    <td style='text-align:center'>".($r['nota2']===null?'N/A':(number_format(($r['nota2']),1,',','.')))."</td>

                    <td style='text-align:center'></td>
                    <td style='text-align:center'></td>

                    <td style='text-align:center'>".($r['anexou_parecer1']===null?'N/A':($r['anexou_parecer1']=='I'?'N&Atilde;o se aplica':($r['anexou_parecer1']=='N'?'É necess&Aacute;rio mas n&Atilde;o anexou':'Anexou')))."</td>
                    <td style='text-align:center'>".($r['anexou_parecer2']===null?'N/A':($r['anexou_parecer2']=='I'?'N&Atilde;o se aplica':($r['anexou_parecer2']=='N'?'É necess&Aacute;rio mas n&Atilde;o anexou':'Anexou')))."</td>



                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"NotasNovaOrdem.xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;