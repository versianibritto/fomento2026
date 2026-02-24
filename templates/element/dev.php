<?php
if(in_array($this->request->getenv('HTTP_HOST'), ['fomento2026.local', 'homolog.pibic.fiocruz.br'])) {
?>
<div class="bg-danger text-white text-center py-2">
    AMBIENTE DE TESTES E DESENVOLVIMENTO, OS DADOS <strong>NÃO</strong> SÃO REFLETIDOS EM BASE DE DADOS
</div>
<?php
}
?>