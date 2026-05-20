<style>
.campo-vazio {
    border-color: #dc3545 !important;
    background-color: #fff5f5 !important;
}
.campo-vazio:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .2) !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function campoVisivel(campo) {
        if (campo.type === 'hidden' || campo.type === 'button' || campo.type === 'submit') {
            return false;
        }
        if (campo.disabled || campo.classList.contains('d-none') || campo.dataset.skipEmptyHighlight === '1') {
            return false;
        }
        return !!(campo.offsetWidth || campo.offsetHeight || campo.getClientRects().length);
    }

    function campoPreenchido(campo) {
        if (campo.type === 'file') {
            return campo.files && campo.files.length > 0;
        }
        if (campo.type === 'checkbox' || campo.type === 'radio') {
            const grupo = campo.form ? Array.from(campo.form.elements).filter(function (item) {
                return item.name === campo.name;
            }) : [campo];
            return grupo.some(function (item) {
                return item.checked;
            });
        }
        return String(campo.value || '').trim() !== '';
    }

    function atualizarCampo(campo) {
        if (!campoVisivel(campo)) {
            campo.classList.remove('campo-vazio');
            return;
        }
        campo.classList.toggle('campo-vazio', !campoPreenchido(campo));
    }

    function atualizarFormulario(form) {
        form.querySelectorAll('input, select, textarea').forEach(atualizarCampo);
    }

    document.querySelectorAll('form').forEach(function (form) {
        atualizarFormulario(form);
        form.addEventListener('input', function (event) {
            if (event.target.matches('input, textarea')) {
                atualizarCampo(event.target);
            }
        });
        form.addEventListener('change', function (event) {
            if (event.target.matches('input, select, textarea')) {
                atualizarFormulario(form);
            }
        });
    });
});
</script>
