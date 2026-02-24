<?php
$tipo_participacao = [
        'P' => 'Palestrante/Orador/Apresentador', 
        'O' => 'Ouvinte/Visitante', 
        'G' => 'Organizador',
        'M' => 'Mediator'
    ];

$idioma = [
        'N' => 'Nativo', 
        'F' => 'Fluente', 
        'A' => 'Avançado', 
        'I' => 'Intermediário', 
        'B' => 'Básico', 
        'T' => 'Instrumental (Só lê e escreve)'
    ];    
    $nome=explode(" ", $usuario->nome);
?>
<div class="container-fluid p-4 pt-1">
    <h2 class="mt-2">Dados do Bolsista</h2>
        
    
    <div class="col-12">
        <div class="card card-primary card-outline card-outline-tabs">
            
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    <div class="tab-pane fade show active" id="custom-tabs-four-home" role="tabpanel" aria-labelledby="custom-tabs-four-home-tab">
                        <div class="alert bg-secondary">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <span class="d-block"><strong>Nome: </strong><span><?=$usuario->nome ?></span></span>
                                        <!--<span class="d-block"><strong>Cursando: </strong><span><?=$usuario->curso==null?'Não informado':$usuario->curso?></span></span>
                                        <span class="d-block"><strong>Instituição de ensino: </strong><span><?=$usuario->instituicao_curso==''?'Não Informado':$usuario->instituicao->sigla?></span></span>
                                        <span class="d-block"><strong>Período: </strong><span><?=($usuario->periodo == null ? 'Não informado' : $usuario->periodo)?></span></span>
                                        <span class="d-block"><strong>Ano de Conclusão: </strong><span><?=($usuario->ano_conclusao == null ? 'Não informado' : $usuario->ano_conclusao)?></span></span>-->
                                        <span class="d-block"><strong>Lattes: 
                                            <?php if($usuario->lattes == null){ ?>
                                                </strong><span>Não informado</span></span>
                                            <?php } ?>
                                            <?php if($usuario->lattes != null){ 
                                                print '<a  href="'.$usuario->lattes.'">'.$usuario->lattes.'</a>';
                                            } ?>



                                        <!--<span class="d-block"><strong>Sexo: </strong><span><?=($usuario->sexo==null?'Não Informado':$sexo[$usuario->sexo])?></span></span>
                                        <span class="d-block"><strong>Raça: </strong><span><?=($usuario->raca==null?'Não Informado':$racas[$usuario->raca])?></span></span>
                                        <span class="d-block"><strong>Deficiência: </strong><span><?=($usuario->deficiencia==null?'Não Informado':$deficiencia[$usuario->deficiencia])?></span></span>
                                        <span class="d-block"><strong>Candidato a Edital Social: </strong><span><?=($usuario->ic==null?'Não Informado':($usuario->ic=='M'?'IC Maré':($usuario->ic=='A'?'IC Mata Atlântica':($usuario->ic=='I'?'IC Manguinhos/ENSP':'Não se enquadra nos ICs Sociais'))))?></span></span>-->
                                        <span class="d-block"><strong>Email: </strong><span><?=$usuario->email==null?'Não informado':$usuario->email?></span></span>
                                        <span class="d-block"><strong>Email Alternativo: </strong><span><?=$usuario->email_alternativo==null?'Não informado':$usuario->email_alternativo?></span></span>

                                    </div>
                                    
                                </div>
                            </div>                        
                        </div>
                        
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <?=$this->Html->link(' Voltar ', ['controller' => 'Users', 'action' => 'talentos'], ['class' => 'btn btn-danger'])?>
</div>