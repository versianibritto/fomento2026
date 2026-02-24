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
            pb.programa as programa,
            pb.cota as cota,
            y.nome as nome_orientador,
            s.sigla as unidade_orientador,
            y.ano_conclusao as ano,
            y.lattes as lattes,
            y.sexo as sexo,
            pb.situacao as situacao,
            pb.deleted as deleted,
            pb.filhos_menor as filhos,
            pb.created as created
            



            from projeto_bolsistas pb
                left join usuarios y on y.id=pb.orientador
                    left join unidades s on s.id=y.unidade_id
                            
                                
                            
            where 
            pb.situacao='N' and pb.deleted=0 and year(pb.created)=2023";
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
        <th>Programa</th>
        <th>Cota</th>
        <th>nome_orientador</th>
        <th>unidade_orientador</th>
        <th>ano</th>
        <th>lattes</th>
        <th>sexo</th>
        <th>situacao</th>
        <th>deleted</th>
        <th>filhos</th>
        <th>created</th>
        

    </tr>";
    foreach($rows as $r){
        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['projeto_bolsista_id']."</td>
                    <td style='text-align:center'>".($r['programa']=='T'?'PIBITI':'PIBIC')."</td>
                    <td style='text-align:center'>".($r['cota']=='G'?'GERAL':($r['cota']=='N'?'Negros/Pardos':($r['cota']=='T'?'Transexual':'Deficiente')))."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".$r['unidade_orientador']."</td>
                    <td style='text-align:center'>".($r['ano']===null?'N/A':(number_format(($r['ano']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['lattes']===null?'N/A':(utf8_decode($r['lattes'])))."</td>
                    <td style='text-align:center'>".$r['sexo']."</td>
                    <td style='text-align:center'>".$r['situacao']."</td>
                    <td style='text-align:center'>".$r['deleted']."</td>
                    <td style='text-align:center'>".$r['filhos']."</td>
                    <td style='text-align:center'>".$r['created']."</td>
                    
                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"ListaLattes.xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;