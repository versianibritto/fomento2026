
<div class="container-fluid p-4 pt-1">
    <h2 class="mt-2">Olá, <?=$usuario_logado['nome']?></h2>
        <div class="bg-default px-5 p-3 rounded mb-2 text-center" >
            Escolha seu perfil para continuar o seu cadastro
        </div>
    
    <div class="col-12">
        <div class="card card-primary card-outline card-outline-tabs">
        <?=$this->Html->link(' Bolsista ', ['controller' => 'Users', 'action' => 'editar', 'B', $usuario_logado['id']], ['class' => 'btn btn-info mb-3'])?>
        <?=$this->Html->link(' Orientador / Coorientador ', ['controller' => 'Users', 'action' => 'editar', 'O', $usuario_logado['id']], ['class' => 'btn btn-info mb-3'])?>
        <?=$this->Html->link(' Administrativo ', ['controller' => 'Users', 'action' => 'editar', 'A', $usuario_logado['id']], ['class' => 'btn btn-info mb-3'])?>

        </div>
    </div>
</div>