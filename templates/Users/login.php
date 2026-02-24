<?=$this->Html->css('login', ['block' => true]);?>
<div class="form-signin">
    <?=$this->Form->create(null, ['class' => "my-5 py-4"])?>
        <h1 class="h3 mb-3 fw-normal">Login em homolog</h1>
        <div class="form-floating mb-3">
            <input type="text" name="cpf" class="form-control" id="cpf" placeholder="Seu CPF" required>
            <label for="cpf">Seu CPF</label>
        </div>
        <div class="form-floating mb-4">
            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>
        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
    <?=$this->Form->end()?>
</div>