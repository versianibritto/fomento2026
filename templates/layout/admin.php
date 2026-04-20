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
    <style>
        .global-submit-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .global-submit-overlay .spinner {
            width: 56px;
            height: 56px;
            border: 4px solid #d0d7de;
            border-top-color: #0d6efd;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .global-submit-overlay .label {
            margin-top: 12px;
            font-size: 0.95rem;
            color: #2b2f33;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .campo-vazio {
            border-color: #dc3545 !important;
            background-color: #fff5f5 !important;
        }
    </style>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="global-submit-overlay" id="globalSubmitOverlay">
        <div class="d-flex flex-column align-items-center">
            <div class="spinner"></div>
            <div class="label">Processando...</div>
        </div>
    </div>
    <div class="wrapper">
        <nav id="sidebar" class="sidebar js-sidebar">
            <div class="sidebar-content js-simplebar">
                <a class="sidebar-brand" href="/dashboard">
                    <span class="align-middle">
                        <img src="/img/logoNovo.svg" alt="Logo novo" />
                    </span>
                </a>
                <ul class="sidebar-nav">
                    <li class="sidebar-item">
                        <a href="/" class="sidebar-link" style="background-color:#1a8cd1;color:#fff;">
                            Voltar à Home Page
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= ($usuario_logado['yoda'] ?? false) ? '/index/dashyoda' : '/dashboard' ?>" class="sidebar-link">
                            Resumo
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/feedbacks" class="sidebar-link">
                            Feedback
                            <span class="badge bg-danger rounded-pill ms-2"><?= (int)($feedbackCount ?? 0) ?></span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/supote" class="sidebar-link">
                            Suporte
                            <?php if ((int)($suporteNotifTotal ?? 0) > 0): ?>
                                <i class="bi bi-bell-fill text-danger ms-2"></i>
                            <?php endif; ?>
                            <?php if ((int)($suporteNotifDuvidaCount ?? 0) > 0): ?>
                                <span
                                    class="badge bg-warning text-dark rounded-pill ms-2 text-decoration-none"
                                    title="Chamados em dúvida (status 3)"
                                    role="button"
                                    style="cursor: pointer;"
                                    data-target-url="/supote?status_id=3">
                                    D: <?= (int)$suporteNotifDuvidaCount ?>
                                </span>
                            <?php endif; ?>
                            <?php if ((int)($suporteNotifFinalizadoCount ?? 0) > 0): ?>
                                <span
                                    class="badge bg-success rounded-pill ms-1 text-decoration-none"
                                    title="Chamados finalizados (status 5)"
                                    role="button"
                                    style="cursor: pointer;"
                                    data-target-url="/supote?status_id=5">
                                    F: <?= (int)$suporteNotifFinalizadoCount ?>
                                </span>
                            <?php endif; ?>
                            <?php if ((int)($suporteNotifNovaCount ?? 0) > 0): ?>
                                <span
                                    class="badge bg-primary rounded-pill ms-1 text-decoration-none"
                                    title="Chamados novos"
                                    role="button"
                                    style="cursor: pointer;"
                                    data-target-url="/supote?status_id=<?= (int)($suporteStatusNovoId ?? 0) ?>">
                                    N: <?= (int)$suporteNotifNovaCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index/dashdetalhes/T" class="sidebar-link <?=(($this->request->getParam('controller') == 'Index' && $this->request->getParam('action') == 'dashdetalhes') ? 'active' : '')?>">
                            <?php if ((int)($usuario_logado['escolaridade_id'] ?? 0) < 10) { ?>
                                Minhas Bolsas
                            <?php } else { ?>
                                Meus Bolsistas
                            <?php } ?>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/raic-new/painel" class="sidebar-link <?=(($this->request->getParam('controller') == 'RaicNew' && $this->request->getParam('action') == 'painel') ? 'active' : '')?>">
                            Raic
                        </a>
                    </li>
                    <?php if ((int)($usuario_logado['escolaridade_id'] ?? 0) >= 10) { ?>
                        <?php /*
                        <li class="sidebar-item">
                            <a href="/avaliadors/minhas_avaliacoes" class="sidebar-link <?=(($this->request->getParam('action') == 'minhasAvaliacoes') ? 'active' : '')?>">
                                Minhas Avaliações
                            </a>
                        </li>
                        */ ?>
                        <!--<li class="sidebar-item">
                            <a href="/users/meus-documentos" class="sidebar-link <?=(($this->request->getParam('action') == 'meusDocumentos') ? 'active' : '')?>">
                                Meus Documentos
                            </a>
                        </li>-->
                        <!--<li class="sidebar-item">
                            <a href="/aulas/direciona" class="sidebar-link <?=(($this->request->getParam('controller') == 'Aulas' && $this->request->getParam('action') == 'liberadas') ? 'active' : '')?>">
                                Sala de aula / Cursos
                            </a>
                        </li>-->
                        <!--<li class="sidebar-item">
                            <a href="/aulas/cursos" class="sidebar-link <?=(($this->request->getParam('controller') == 'Aulas' && $this->request->getParam('action') == 'cursos') ? 'active' : '')?>">
                                Cursos
                            </a>
                        </li>-->
                    
                        <?php if ((int)($usuario_logado['yoda'] ?? 0) === 1 || (int)($usuario_logado['escolaridade_id'] ?? 0) === 10) { ?>
                            <li class="sidebar-header">
                                Banco de talentos
                            </li>
                            <li class="sidebar-item">
                                <a href="/users/talentos/1" class="sidebar-link">
                                    * Busca de Talentos
                                </a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                    <!--
                    <li class="sidebar-item">
                        <a href="/talentos/curriculo" class="sidebar-link">
                            Meu currículo
                        </a>
                    </li>
                    -->
                    
                    <?php
                    if($usuario_logado['yoda'] || $usuario_logado['jedi']!=null || $usuario_logado['padauan']!=null) {
                    ?>
                    <li class="sidebar-header">
                        Administração
                    </li>
                    <li class="sidebar-item">
                        <a href="/users/index" class="sidebar-link">
                            * Usuários
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/listas/busca/V" class="sidebar-link">
                            * Bolsas ativas
                            <span class="badge badge-info right">IC: <?=$atv?></span>
                            <span class="badge badge-info right">PDJ:<?=$atv_pdj?></span>

                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/listas/busca/A" class="sidebar-link">
                           * Inscrições em andamento
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/listas/busca/T" class="sidebar-link">
                           * Lista por Status
                        </a>
                    </li>
                    
                    
                    <li class="sidebar-item">
                        <a href="/listas/limpar/buscaMensal/listamensal" class="sidebar-link">
                           * Lista Egressos/Ativados
                        </a>
                    </li>
                    <!--
                    <li class="sidebar-item">
                        <a href="/listas/limpar/buscaRaics/raics" class="sidebar-link">
                           * Lista RAICS (Agendamento)
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/projetos/voluntarias" class="sidebar-link">
                            * Cadastrar Raic de Outras Agências de Fomento
                        <span class="badge badge-info right"></span>
                        </a>
                    </li>

                    
                    <li class="sidebar-item">
                        <a href="/avaliadors/limpar/buscaAvaliadoresRaic/avaliadoresraic" class="sidebar-link">
                           * Cadastrar - Lista de Avaliadores RAIC

                        </a>
                    </li>
                    -->
                    <!-- aqui
                    <li class="sidebar-item">
                        <a href="/avaliadores/cadastro-raic" class="sidebar-link <?=(($this->request->getParam('controller') == 'Avaliadores' && $this->request->getParam('action') == 'cadastroRaic') ? 'active' : '')?>">
                           * Cadastro Massivo Avaliadores RAIC
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/avaliadores/lista-raic" class="sidebar-link <?=(($this->request->getParam('controller') == 'Avaliadores' && $this->request->getParam('action') == 'listaRaic') ? 'active' : '')?>">
                           * Lista de Avaliadores RAIC
                        </a>
                    </li>
                            -->
                            

                    <?php if (!empty($usuario_logado['yoda']) || !empty($usuario_logado['jedi'])): ?>
                        <li class="sidebar-item">
                            <a href="/listas/busca-raic" class="sidebar-link <?=(($this->request->getParam('controller') == 'Listas' && in_array($this->request->getParam('action'), ['buscaRaic', 'resultadoRaic'], true)) ? 'active' : '')?>">
                                Listagem Raic
                            </a>
                        </li>
                    <?php endif; ?>

                    
                    
                                      
                    <!--
                    <li class="sidebar-header">
                        Avaliações
                    </li>
                    <li class="sidebar-item">
                        <a href="/avaliadors/listaavaliadoresraic/1" class="sidebar-link">
                            Lançamento Notas RAIC
                            <span class="badge badge-info right"></span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/avaliadors/limpar/buscaAvnova/listaavaliadoresnova" class="sidebar-link">

                            Lançamento Notas NOVA
                            <span class="badge badge-info right"></span>
                        </a>
                    </li>
                    -->
                    <?php
                    }
                    ?>
                    <?php if($usuario_logado['yoda']) { ?>
                        <li class="sidebar-header">
                            Restrito à Gestão
                        </li>
                        <!--
                        <li class="sidebar-item">
                            <a href="/grafico/inscricoes-em-andamento" class="sidebar-link">
                                * Painel / Gráficos
                            </a>
                        </li>
                            -->
                        <li class="sidebar-item">
                            <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'S']) ?>" class="sidebar-link">
                                * Substituições
                                <span class="badge badge-warning right">IC: <?=$subs?></span>
                                <span class="badge badge-warning right">PDJ: <?=$subs_pdj?></span>

                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="<?= $this->Url->build(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'C']) ?>" class="sidebar-link">
                                * Cancelamentos
                                <span class="badge badge-danger right">IC: <?=$canc?></span>
                                <span class="badge badge-danger right">PDJ: <?=$canc_pdj?></span>

                            </a>
                        </li>
                       
                        
                        <!--
                        <li class="sidebar-item">
                            <a href="/editais/lista" class="sidebar-link">
                                Editais
                                <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/manuais/lista" class="sidebar-link">
                                Manuais
                                <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/revistas/ver" class="sidebar-link">
                                Revistas
                                <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        -->
                        <li class="sidebar-item">
                            <a href="/gestao/lancarresultados" class="sidebar-link <?=(($this->request->getParam('controller') == 'Gestao' && $this->request->getParam('action') == 'lancarresultados') ? 'active' : '')?>">
                            * Lançar resultados
                            </a>
                        </li>
                        <!-- aqui
                        <li class="sidebar-item">
                            <a href="/avaliadores/cadastro-nova" class="sidebar-link <?=(($this->request->getParam('controller') == 'Avaliadores' && $this->request->getParam('action') == 'cadastroNova') ? 'active' : '')?>">
                               * Cadastro Massivo Avaliadores Editais
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/avaliadores/lista-nova" class="sidebar-link <?=(($this->request->getParam('controller') == 'Avaliadores' && $this->request->getParam('action') == 'listaNova') ? 'active' : '')?>">
                               * Lista de Avaliadores Editais
                            </a>
                        </li>
                         <li class="sidebar-item">
                            <a href="/avaliadores/lista-inscricoes" class="sidebar-link <?=(($this->request->getParam('controller') == 'Avaliadores' && $this->request->getParam('action') == 'listaInscricoes') ? 'active' : '')?>">
                               * Vinculação de Avaliadores IC
                            </a>
                        </li>
                        -->
                        <!--
                        <li class="sidebar-item">
                            <a href="/projetos/listasemresultado" class="sidebar-link">
                                Cadastrar resultado - ICs
                            <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/pdj/listasemresultadopdj" class="sidebar-link">
                                Cadastrar resultado - PDJ
                            <span class="badge badge-info right"></span>
                            </a>
                        </li>
                            -->
                        <?php if ((int)($usuario_logado['yoda'] ?? 0) === 1) { ?>
                        <li class="sidebar-item">
                            <a href="/gestao/vigencias/E" class="sidebar-link <?=(($this->request->getParam('controller') == 'Gestao' && $this->request->getParam('action') == 'vigencias' && (($this->request->getParam('pass')[0] ?? '') === 'E')) ? 'active' : '')?>">
                                Encerrar Bolsas
                            <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/gestao/vigencias/A" class="sidebar-link <?=(($this->request->getParam('controller') == 'Gestao' && $this->request->getParam('action') == 'vigencias' && (($this->request->getParam('pass')[0] ?? '') === 'A')) ? 'active' : '')?>">
                                Ativar Bolsas
                            <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        <?php } ?>
                       <!--
                        <li class="sidebar-item">
                        <a href="/projetos/limpar/buscaPrograma/avaliarnovas" class="sidebar-link">
                                Vincular Avaliador Bolsa Nova
                                <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                        <a href="/listas/limpar/buscaAva/listaavaliacoes" class="sidebar-link">
                            * Lista das Avaliações
                                <span class="badge badge-info right"></span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/avaliadors/limpar/buscaAvaliadoresNova/avaliadoresnova" class="sidebar-link">
                            * Lista de Avaliadores Nova por Edital
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/avaliadors/addAvaliadorMassivo" class="sidebar-link">
                            * Cadastro Massivo de Avaliadores Nova
                            </a>
                        </li>
                            -->
                         <li class="sidebar-item">
                            
                        </li>
                         <li class="sidebar-item">
                            
                        </li>
                        <!--
                        <li class="sidebar-header">
                            Workshop
                        </li>
                        <li class="sidebar-item">
                            <a href="/listas/limpar/buscaBanca/listabancas" class="sidebar-link">
                            * Listagem de Bancas
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="/listas/limpar/buscaWork/workshop" class="sidebar-link">
                            * Listagem de Workshops
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <br><br><br><br><br><br><br>
                        </li>
                       
                            -->
                        
                    <?php }?>

                    
                </ul>
            </div>
        </nav>
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
    <script>
        (function () {
            document.addEventListener('click', function (event) {
                var alvo = event.target && event.target.closest ? event.target.closest('[data-target-url]') : null;
                if (!alvo) return;
                event.preventDefault();
                event.stopPropagation();
                var destino = alvo.getAttribute('data-target-url');
                if (destino) {
                    window.location.href = destino;
                }
            }, true);
        })();
    </script>
    <script>
        (function () {
            var overlay = document.getElementById('globalSubmitOverlay');
            if (!overlay) return;
            var loadingTimer = null;

            function showOverlay() {
                overlay.style.display = 'flex';
            }
            function hideOverlay() {
                overlay.style.display = 'none';
                if (loadingTimer) {
                    clearTimeout(loadingTimer);
                    loadingTimer = null;
                }
            }

            document.addEventListener('submit', function (event) {
                var form = event.target;
                if (!form || form.tagName !== 'FORM') return;
                if (form.getAttribute('data-no-loading') === '1') return;
                var method = (form.getAttribute('method') || 'get').toLowerCase();
                if (method === 'get') return;

                showOverlay();
                var submits = form.querySelectorAll('button[type=\"submit\"], input[type=\"submit\"]');
                submits.forEach(function (btn) {
                    btn.setAttribute('disabled', 'disabled');
                });
            }, true);

            document.addEventListener('click', function (event) {
                var link = event.target && event.target.closest ? event.target.closest('a.js-loading-link') : null;
                if (!link) return;
                if (link.getAttribute('data-no-loading') === '1') return;
                showOverlay();
                // Downloads podem não navegar de fato; evita overlay travado.
                loadingTimer = setTimeout(hideOverlay, 4000);
            }, true);

            window.addEventListener('pageshow', hideOverlay);
        })();
    </script>
    <script>
        (function () {
            var LIMITE_BYTES = 2 * 1024 * 1024; // 2MB
            var MENSAGEM = 'Arquivo maior que 2Mb.  Selecione outro pois este tamanho não é permitido.';

            document.addEventListener('change', function (event) {
                var input = event.target;
                if (!input || input.tagName !== 'INPUT' || input.type !== 'file') {
                    return;
                }
                if (!input.files || !input.files.length) {
                    return;
                }
                for (var i = 0; i < input.files.length; i++) {
                    if (input.files[i].size > LIMITE_BYTES) {
                        alert(MENSAGEM);
                        input.value = '';
                        return;
                    }
                }
            }, true);
        })();
    </script>
    <script>
        (function () {
            var path = (window.location && window.location.pathname) ? window.location.pathname.toLowerCase() : '';
            if (path.indexOf('/inscricoes/') === -1) {
                return;
            }

            function isCampoElegivel(el) {
                if (!el || !el.tagName) return false;
                var tag = el.tagName.toUpperCase();
                if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA') return false;
                if (el.disabled) return false;
                var nome = String(el.name || '').toLowerCase();
                var id = String(el.id || '').toLowerCase();
                if (nome === 'cpf_bolsista' || id === 'cpf-bolsista') return false;
                var type = (el.type || '').toLowerCase();
                if (type === 'file' || type === 'hidden' || type === 'checkbox' || type === 'radio' || type === 'submit' || type === 'button') {
                    return false;
                }
                return true;
            }

            function campoVazio(el) {
                return String(el.value || '').trim() === '';
            }

            function aplicar(el) {
                if (!isCampoElegivel(el)) return;
                el.classList.toggle('campo-vazio', campoVazio(el));
            }

            function aplicarTodos() {
                document.querySelectorAll('input,select,textarea').forEach(aplicar);
            }

            document.addEventListener('input', function (event) {
                aplicar(event.target);
            }, true);
            document.addEventListener('change', function (event) {
                aplicar(event.target);
            }, true);

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', aplicarTodos);
            } else {
                aplicarTodos();
            }
        })();
    </script>
    <?= $this->fetch('script') ?>
</body>

</html>
