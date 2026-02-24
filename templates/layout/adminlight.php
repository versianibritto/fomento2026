<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords"
        content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="canonical" href="https://demo-basic.adminkit.io/pages-blank.html" />
    <title>Blank Page | AdminKit Demo</title>
    <link href="/css/admin.css" rel="stylesheet">
    <link href="/css/all.min.css" rel="stylesheet">
    <?=$this->Html->script("app")?>
    <?=$this->Html->script("jquery.min")?>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav navbar-align">
                        <li class="sidebar-item dropdown">
                            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#"
                                data-bs-toggle="dropdown">
                                <i class="align-middle" data-feather="settings"></i>
                            </a>
                            <a class="sidebar-link dropdown-toggle d-none d-sm-inline-block bg-white" href="#"
                                data-bs-toggle="dropdown">
                                <img src="/img/no-photo.gif" class="avatar img-fluid rounded me-1" style="border-radius:50px!important" alt="Charles Hall" /> <span class="text-dark"><?=$usuario_logado['nome']?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="/users/editar/<?=$usuario_logado['id']?>">
                                    <i class="align-middle me-1" data-feather="user"></i> Editar meus dados
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/users/logout">
                                    Sair
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="content">
                <div class="container-fluid p-0">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </main>
            <footer class="footer" style="padding:.5rem .875rem 0rem!important">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-10 text-start text-sm" style="font-size:10px!important;">
                            Layout modificado pela <a href="agenciam2u.com.br" target="_blank"><b>Agência M2U</b></a> a partir do modelo OpenSource 
                            <a class="text-muted" href="https://adminkit.io/"
                                    target="_blank"><strong>AdminKit</strong></a> - <a class="text-muted"
                                    href="https://adminkit.io/" target="_blank"><strong>Bootstrap Admin
                                        Template</strong></a> &copy; e gentilmente cedido para a Coordenação de Bolsas da VPPCB-FIOCRUZ
                        </div>
                        <div class="col-2 text-end">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a class="text-muted" href="https://adminkit.io/" target="_blank">Support</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
