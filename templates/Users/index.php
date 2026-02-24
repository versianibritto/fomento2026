<h3>Busca de Usuários</h3>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


<div class="col-12">
    <div class="alert bg-info bg-opacity-25 text-dark text-center mb-3" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Para realizar a busca, utilize ao menos um dos filtros abaixo:
        <br>
        <strong>Nome</strong> (ou parte),
        <strong>CPF</strong> (ou parte, apenas números)
        ou <strong>Tipo de usuário</strong>.
        <br>
        <small>
            <strong>
                Os filtros podem ser combinados para refinar o resultado.
            </strong>
        </small>
    </div>
</div>

<?= $this->Form->create(null, [
    'url' => ['action' => 'buscar'],
    'class' => 'row g-3 align-items-end'
]) ?>

<div class="col-md-4">
    <?= $this->Form->control('nome', [
        'label' => 'Nome',
        'class' => 'form-control',
        'placeholder' => 'Digite o nome ou parte',
        'maxlength' => 150,
    ]) ?>
</div>

<div class="col-md-4">
    <?= $this->Form->control('cpf', [
        'label' => 'CPF',
        'class' => 'form-control',
        'placeholder' => 'Digite o CPF ou parte (somente números)',
        'maxlength' => 25,
    ]) ?>
</div>

<div class="col-md-4">
    <?= $this->Form->control('tipo_usuario', [
        'label' => 'Tipo de usuário',
        'options' => [
            '' => 'Todos',
            'yoda' => 'Gestão Fomento',
            'jedi' => 'Coordenação de Unidade',
            'padauan' => 'Coordenação de Programa'
        ],
        'class' => 'form-select'
    ]) ?>
</div>


<div class="col-12">
    <button class="btn btn-primary">
        <i class="fa fa-search"></i> Buscar
    </button>
</div>

<?= $this->Form->end() ?>
