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
            av.id as avaliador_id, 
            v.nome as nome_avaliador,
            v.email as email_avaliador,
            av.situacao as situacao,
            av.nota as nota, 
            av.observacao as observacao, 
            av.bolsista as inscricao, 
            t.sigla as unidade_avaliador,
            u.nome as bolsista, 
            o.nome as orientador, 
            s.sigla as unidade_orientador

            from avaliador_bolsistas av 
            left join avaliadors a on a.id=av.avaliador_id
            left join usuarios v on v.id=a.usuario_id
            left join unidades t on v.unidade_id=t.id
            left join projeto_bolsistas r on r.id=av.bolsista
            left join usuarios u on u.id=r.usuario_id
            left join usuarios o on o.id=r.orientador
            left join unidades s on s.id=o.unidade_id
where av.ano=2024 and av.deleted=0 and av.tipo='N' and r.deleted=0";
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
        
        <th>Av_id</th>
        <th>nome_avaliador</th>
        <th>email avaliador</th>
        <th>situacao da nota</th>
        <th>nota</th>
        <th>obs</th>
        <th>inscricao</th>
        <th>unidade_avaliador</th>
        <th>Bolsista</th>
        <th>orientador</th>
        <th>unidade_orientador</th>
    </tr>";
    foreach($rows as $r){
        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['avaliador_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_avaliador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['email_avaliador'])."</td>
                    <td style='text-align:center'>".($r['situacao']=='F'?'Notas Lançadas':'Aguardando')."</td>
                    <td style='text-align:center'>".($r['nota']===null?'N/A':(number_format(($r['nota']),1,',','.')))."</td>
                    <td style='text-align:right'>".($r['observacao']===null?'N/A':(utf8_decode($r['observacao'])))."</td>
                    <td style='text-align:center'>".$r['inscricao']."</td>
                    <td style='text-align:center'>".$r['unidade_avaliador']."</td>
                    <td style='text-align:center'>".utf8_decode($r['bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['orientador'])."</td>
                    <td style='text-align:center'>".$r['unidade_orientador']."</td>
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