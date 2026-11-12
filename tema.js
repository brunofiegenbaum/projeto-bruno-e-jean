(function () {
    var tema = localStorage.getItem('tema-marketplace') || 'escuro';
    document.documentElement.setAttribute('data-tema', tema);

    document.addEventListener('DOMContentLoaded', function () {
        var botao = document.getElementById('botao-tema');
        if (!botao) return;

        function atualizar() {
            var escuro = document.documentElement.getAttribute('data-tema') === 'escuro';
            botao.textContent = escuro ? 'Modo claro' : 'Modo escuro';
            botao.setAttribute('aria-label', escuro ? 'Ativar modo claro' : 'Ativar modo escuro');
        }

        botao.addEventListener('click', function () {
            var atual = document.documentElement.getAttribute('data-tema');
            var novo = atual === 'escuro' ? 'claro' : 'escuro';
            document.documentElement.setAttribute('data-tema', novo);
            localStorage.setItem('tema-marketplace', novo);
            atualizar();
        });
        atualizar();
    });
})();
