<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .card-counter {
        cursor: pointer;
        transition: 0.25s;
        min-height: 150px;
    }
    .card-counter:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* EDITAIS */
    .card-edital {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-radius: 12px;
        transition: 0.25s;
        background: #ffffff;
    }
    .card-edital:hover {
        transform: translateY(-3px);
        box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    }

    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 9px;
        width: 100%;
        transition: 0.25s;
    }

    .btn-download {
        background: #e8f1ff;
        color: #004a99;
        border: 1px solid #bcd4ff;
    }
    .btn-download:hover {
        background: #d8e8ff;
    }

    .btn-inscrever {
        background: #e7f8ee;
        color: #137b37;
        border: 1px solid #b8eaca;
    }
    .btn-inscrever:hover {
        background: #d4f3e3;
    }

    /* Cards inferiores mesma altura */
    .card-equal {
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    </style>
</head>

<body class="bg-light p-4">

<?= $this->element('mensagens_popup', [
    'mensagensPopup' => $mensagensPopup ?? [],
    'popupId' => 'modalMensagensInternas',
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var temModalMensagens = !!document.getElementById('modalMensagensInternas');

    if (temModalMensagens) {
        return;
    }

    setTimeout(function () {
        var modalInicial = document.getElementById('modalAvisoInicial');
        if (!modalInicial) {
            return;
        }
        if (window.jQuery && jQuery.fn && jQuery.fn.modal) {
            jQuery(modalInicial).modal('show');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalInicial).show();
        }
    }, 400);

    var modalEl = document.getElementById('modalAvisoInicial');
    if (!modalEl) {
        return;
    }

    modalEl.querySelectorAll('[data-modal-close="true"]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (window.jQuery && jQuery.fn && jQuery.fn.modal) {
                jQuery(modalEl).modal('hide');
                return;
            }
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                return;
            }
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        });
    });
});
</script>

<!-- ===================== MODAL AUTOMÁTICO INICIAL ===================== -->
<?php if (($dashboard['andamento'] ?? 0) && empty($mensagensPopup)) {?>
  <div class="modal fade" id="modalAvisoInicial">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

          <div class="modal-header bg-warning text-dark">
              <h5 class="modal-title fw-bold">
                  <i class="fa fa-exclamation-triangle"></i> Atenção!
              </h5>
              <button type="button" class="close" data-modal-close="true">&times;</button>
          </div>

          <div class="modal-body">
              <p class="lead">
                  Você possui inscrições em 'rascunho' que precisam ser finalizadas.
              </p>
              <p>
                  Fique atento aos prazos de encerramento para não perder a oportunidade.
              </p>
          </div>

          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-modal-close="true">Entendi</button>
          </div>

        </div>
    </div>
  </div>
<?php } ?>


<!-- ===================== CARDS DO DASHBOARD ===================== -->
    <div class="row g-4">

        <!-- INSCRIÇÕES EM ANDAMENTO -->
        <div class="col-md-3 col-sm-6">
            <div class="card card-counter card-equal shadow-sm text-center p-3" data-toggle="modal" data-target="#modalAndamento">
                <h6 class="text-muted mb-1">Em Andamento</h6>
                <h2 class="fw-bold text-primary"><?= ($dashboard['inscricoesTotal'] ?? 0) ?></h2>
                <a href="<?= $this->Url->build([
                    'controller' => 'Index',
                    'action' => 'dashdetalhes',
                    'A'
                ]) ?>"
                class="btn btn-outline-primary btn-sm mt-2">
                    Ver detalhes
                </a>
            </div>
        </div>

        <!-- BOLSAS VIGENTES -->
        <div class="col-md-3 col-sm-6">
            <div class="card card-counter shadow-sm text-center p-3">
                <h6 class="text-muted mb-1">Bolsas vigentes</h6>

                <h2 class="fw-bold text-success">
                    <?= $dashboard['bolsasAtivas'] ?? 0 ?>
                </h2>

               <a href="<?= $this->Url->build([
                    'controller' => 'Index',
                    'action' => 'dashdetalhes',
                    'V'
                ]) ?>"
                class="btn btn-outline-primary btn-sm mt-2">
                    Ver detalhes
                </a>


            </div>
        </div>


        <!-- AVALIAÇÕES -->
        <div class="col-md-3 col-sm-6">
            <div class="card card-counter card-equal shadow-sm text-center p-3" data-toggle="modal" data-target="#modalAvaliacoes">
                <h6 class="text-muted mb-1">Avaliações</h6>
                <h2 class="fw-bold text-warning"><?= h($dashboard['avaliacoes'] ?? 0) ?></h2>
                <button class="btn btn-outline-warning btn-sm mt-2">Ver detalhes</button>
            </div>
        </div>

        <!-- COORIENTAÇÕES -->
        <div class="col-md-3 col-sm-6">
            <div class="card card-counter card-equal shadow-sm text-center p-3" data-toggle="modal" data-target="#modalCoorientacoes">
                <h6 class="text-muted mb-1">Raics</h6>
                <h2 class="fw-bold text-danger"><?= h($dashboard['coorientacoes'] ?? 0) ?></h2>
                <button class="btn btn-outline-danger btn-sm mt-2">Ver detalhes</button>
            </div>
        </div>

    </div>


<!-- ===================== Editais ===================== -->

<div class="container">

    <div class="card shadow-sm mb-4">
    <div class="card-header bg-white border-0 pb-0">
        <h4 class="fw-bold text-primary">📢 Editais Abertos</h4>
    </div>

    <div class="card-body">
        <?php if ($editais->all()->isEmpty()): ?>
            <div class="alert alert-info text-center mb-0">Nenhum edital com inscrição aberta no momento.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Edital</th>
                            <th>Inscrições até</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($editais as $edital): ?>
                            <?php
                                $controllerLink = trim((string)($edital->controller ?? ''));
                                $actionLink = trim((string)($edital->link ?? ''));

                                if ($controllerLink !== '' && $actionLink !== '') {
                                    $inscricaoUrl = $this->Url->build([
                                        'controller' => $controllerLink,
                                        'action' => $actionLink,
                                        $edital->id,
                                    ]);
                                } elseif ($actionLink !== '') {
                                    $inscricaoUrl = rtrim($actionLink, '/') . '/' . $edital->id;
                                } else {
                                    $inscricaoUrl = $this->Url->build([
                                        'controller' => 'Projetos',
                                        'action' => 'testepag',
                                        $edital->id,
                                    ]);
                                }
                            ?>
                            <tr>
                                <td><?= h($edital->nome) ?></td>
                                <td>
                                    <?php
                                        $fim = $edital->fim_inscricao ?? null;
                                        if ($fim instanceof \Cake\I18n\FrozenTime) {
                                            echo h($fim->modify('-1 day')->i18nFormat('dd/MM/yyyy'));
                                        } else {
                                            echo h($fim ? date('d/m/Y', strtotime('-1 day', strtotime((string)$fim))) : '-');
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($edital->id)): ?>
                                        <a href="<?= h($inscricaoUrl) ?>"
                                           class="btn btn-sm btn-outline-success">
                                            Inscreva-se
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

    
</body>
</html>
