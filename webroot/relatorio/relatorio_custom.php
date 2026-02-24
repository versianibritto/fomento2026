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
            u.nome as nome_avaliador,
            ab.destaque as destaque,
            ab.indicado_premio_capes as indicado_premio_capes,
            ab.observacao_alteracao as avaliacao_alteracao_subprojeto,
            ab.nota as nota,
            ab.parecer as anexou_parecer
            from projeto_bolsistas pb
                left join raics p on p.projeto_bolsista_id=pb.id
                    left join projetos j on j.id=pb.projeto_id
                        left join usuarios y on y.id=pb.orientador
                        left join unidades s on s.id=y.unidade_id
                            left join usuarios z on z.id=pb.usuario_id
                                left join avaliador_bolsistas ab on (ab.bolsista=p.id and ab.ano='2023' and ab.deleted=0 and ab.tipo='V' and ab.deleted=0)
                                    left join avaliadors a on a.id=ab.avaliador_id
                                    left join usuarios u on a.usuario_id=u.id
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
        <th>Resultado</th>
        <th>Apresentou Raic?</th>
        <th>Subprojeto Novo?</th>
        <th>Justificativa Orientador altera&Ccedil;&Atilde;o</th>
        <th>Dados apresentados</th>
        <th>cota</th>
        <th>projeto_orientador</th>
        <th>nome_orientador</th>
        <th>unidade_orientador</th>
        <th>subprojeto_bolsista</th>
        <th>nome_bolsista</th>
        <th>raic_id</th>
        <th>data_raic</th>
        <th>nome_avaliador</th>
        <th>destaque</th>
        <th>indicado_premio_capes</th>
        <th>avaliacao_alteracao_subprojeto</th>
        <th>nota</th>
        <th>anexou_parecer</th>
    </tr>";
    foreach($rows as $r){
        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['projeto_bolsista_id']."</td>
                    <td style='text-align:center'>".($r['resultado']=='T'?'Aprova&Ccedil;&Atilde;o Autom&Aacute;tica':' - ')."</td>
                    <td style='text-align:center'>".($r['apresentar_raic_1']?'Apresentou RAIC':'N&Atilde;O apresentou Raic')."</td>
                    <td style='text-align:center'>".($r['subprojeto_renovacao']=='D'?'Cadastrado Novo Sunprojeto para Renova&Ccedil;&Atilde;o':'Manteve subprojeto Original')."</td>
                    <td style='text-align:right'>".utf8_decode($r['justificativa_orientador_alteracao'])."</td>
                    <td style='text-align:center'>".($r['projeto_raic_alterado']===null?'N&Atilde;o se aplica':($r['projeto_raic_alterado']==1?'Apresentou projeto original':'Apresentado Novo Projeto'))."</td>
                    <td style='text-align:center'>".($r['cota']=='G'?'GERAL':($r['cota']=='N'?'Negros/Pardos':($r['cota']=='T'?'Transexual':'Deficiente')))."</td>
                    <td style='text-align:right'>".utf8_decode($r['projeto_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".$r['unidade_orientador']."</td>
                    <td style='text-align:right'>".utf8_decode($r['subprojeto_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_bolsista'])."</td>
                    <td style='text-align:center'>".$r['raic_id']."</td>
                    <td style='text-align:center'>".$r['data_raic']."</td>

                    
                    <td style='text-align:center'>".utf8_decode($r['nome_avaliador'])."</td>
                    <td style='text-align:center'>".($r['destaque']===null?'N/A':($r['destaque']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:center'>".($r['indicado_premio_capes']===null?'N/A':($r['indicado_premio_capes']==1?'Sim':'N&Atilde;o'))."</td>
                    <td style='text-align:right'>".($r['avaliacao_alteracao_subprojeto']===null?'N/A':(utf8_decode($r['avaliacao_alteracao_subprojeto'])))."</td>
                    <td style='text-align:center'>".($r['nota']===null?'N/A':(number_format(($r['nota']),1,',','.')))."</td>
                    <td style='text-align:center'>".($r['anexou_parecer']===null?'N/A':($r['anexou_parecer']=='I'?'N&Atilde;o se aplica':($r['anexou_parecer']=='N'?'É necess&Aacute;rio mas n&Atilde;o anexou':'Anexou')))."</td>
                    
                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"NotasRaic.xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;