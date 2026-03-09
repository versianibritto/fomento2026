<?php
  $uri = $_SERVER['REQUEST_URI'];
  $ini = $uri;
  $uri = preg_replace('/\W+/u', '', $uri);
  $titulo = ($titulo ?? $uri);
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <title>Fomento à Pesquisa :: <?= $titulo ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta name="description" content="Sistema de gerenciamento de bolsas e inscrições da Fiocruz" />
    <meta name="keywords" content="pibic, pibiti, bolsas, iniciação científica, iniciação tecnológica, Fiocruz, Formação continuada" />    
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <?= $this->Html->css(['bootstrap.min', 'styles'])?>
    <?= $this->Html->css('novo_layout')?>
    <?= $this->fetch('meta')?>    
  </head>
  <body>
  <?php
  if($ini == '/'){ ?>
    <header>
      <div id="topo"> 
        <?=$this->element('dev')?>
        <div class="preloader">
          <div class="preloader-body"></div>
        </div>    
        <div class="main">
          <div class="icones">
            <a href="/" title="Página inical">
              <img src="/img/logo_fomento.svg" class="logoFomento" />
              <img src="/img/logo_fiocruz.svg" class="logoFiocruz" />
            </a>
          </div>
        </div>
      </div>
      <div class="botoes">       
        <div class="main">
          <div class="botoesHome">
            <a href="/editais" title="Veja os editais.">  
              <div class="btEditais">
                <div><img src="/img/img_bt_editais.svg" class="" /></div>  
                <div>
                  Veja os editais abertos.<br>
                  Clique aqui.
                </div>
              </div>
            </a>
            <a href="/login" title="Faça o login.">
              <div class="btSistema">
                <div><img src="/img/img_bt_sistema.svg" class="" /></div>
                  <div>
                    Entre no<br>
                    sistema fomento.<br>
                    Faça seu login aqui.
                  </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </header>
  <?php } /*fim if*/ else { ?>
    <header>
      <div id="topoMenor"> 
        <?=$this->element('dev')?>
        <div class="preloader">
          <div class="preloader-body"></div>
        </div>    
        <div class="main">
          <div class="icones">
            <a href="/" title="Página inical">
              <img src="/img/logo_fomento.svg" class="logoFomento2" />
              <img src="/img/logo_fiocruz.svg" class="logoFiocruz2" />
            </a>
          </div>
        </div>
      </div>
       <?php if($ini != '/manutencao') { ?>
        <nav class="navPrincipal">
          <div class="barraNav">
            <div class="botoesNav">
                <a href="/" <?php if($uri == '') {echo 'class="botaoAtual"';}?>>Página inicial</a>
                <a href="/talentos" <?php if($uri == 'talentos') {echo 'class="botaoAtual"';}?>>Banco de talentos</a>
                <a href="/editais" <?php if($uri == 'editais') {echo 'class="botaoAtual"';}?>>Editais</a>
                <a href="/manuais" <?php if($uri == 'manuais') {echo 'class="botaoAtual"';}?>>Manuais</a>
                <a href="/revistas/apresentacao" <?php if($uri == 'revistasapresentacao') {echo 'class="botaoAtual"';}?>>Revistas</a>
            </div>
            <div class="dropBtn" onclick="dropFunction()"></div>
            <div id="drop" class="dropNav">
                <a href="/">Página inicial</a>
                <a href="/talentos">Banco de talentos</a>
                <a href="/editais">Editais</a>
                <a href="/manuais">Manuais</a>
                <a href="/revistas/apresentacao">Revistas</a>
            </div>
          </div>
          <div class="main">
            <a href="/login" title="Faça o login.">
              <div class="btSistema2">
                <div>
                  Entre no sistema fomento.<br>
                  Faça seu login aqui.
                </div>
              </div>
            </a>
          </div>
        </nav>
      <?php } ?>
    </header>
  <?php } ; //fim else ?>
    <main>
      <div class="page">
        <div class="main">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
        </div>
      </div>
    </main>
    <footer>
      <div class="linhaTopo"></div>
      <div id="rodape">
        <div class="main">
          <div class="rodapeGrade">
            <div class="rodapeItem">
              <address>
                <div class="rodapeTitulo">Endereço - Campus Sede</div>
                <div class="rodapeTexto">
                  Castelo Mourisco - Sala 13 <br>
                  Av. Brasil, 4365 - Manguinhos<br>
                  Rio de Janeiro - RJ - CEP: 21040-900<br>
                </div>
                <div class="rodapeTitulo">Atendimentos dos Programas</div>
                <div class="rodapeTexto">
                  VICE-PRESIDÊNCIA DE PESQUISA E<br>
                  COLEÇÕES BIOLÓGICAS<br>
                </div>
                <div class="rodapeTitulo">Telefone de contato</div>
                <div class="rodapeTexto">
                  +55 (21) 3885-1630<br><br>
                  <a href="/unidades"><b>Unidades | Coordenações</b></a>
                  <br><br>
                  <a href="/revistas/apresentacao"><b>Revista eletrônica</b></a>
                  <a href="/revistas/editorial"><b> Editorial</b></a> <br>
                  <a href="/revistas/regras"><b> Regras</b></a>
                  <a href="/revistas"><b> Edição atual / anteriores</b></a>               
                </div>
              </address>
            </div>
            <div class="rodapeItem tracejado">
              <div class="rodapeIcones">
                <img src="/img/logo_fomento.svg" />
                <img src="/img/logo_fiocruz.svg" />
                <img src="/img/logo_gov.svg" />
                <img src="/img/logo_sus.svg" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <?= $this->fetch('script') ?>
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
    <?= $this->Html->script(['core.min', 'scripts']) ?>
  </body>
</html>



<script>
function dropFunction() {
  document.getElementById("drop").classList.toggle("mostrar");
}
window.onclick = function(event) {
  if (!event.target.matches('.dropBtn')) {
    var dropdowns = document.getElementsByClassName("dropNav");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('mostrar')) {
        openDropdown.classList.remove('mostrar');
      }
    }
  }
}
</script>
