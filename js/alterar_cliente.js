        $(document).ready(function() {
            // Máscara para telefone
            $('#telefone').mask('(00) 00000-0000');
            
            // Auto-foco no campo de busca
            $('#busca_cliente').focus();
            
            // Validação do formulário
            $('#editarForm').on('submit', function(e) {
                let isValid = true;
                $(this).find('[required]').each(function() {
                    if (!$(this).val().trim()) {
                        $(this).css('border-color', 'var(--danger-color)');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor, preencha todos os campos obrigatórios!');
                }
            });

                // Bloqueia números no campo nome
          $('#nome').on('input', function () {
        this.value = this.value.replace(/[0-9]/g, '');
         });

    // Após submissão bem-sucedida, limpa os campos e esconde formulário
    const sucesso = $('.alert-success').length > 0;
    if (sucesso) {
        $('#editarForm').hide();
        $('#buscaForm').hide();
    }
            
            // Busca de sugestões (AJAX)
            $('#busca_cliente').on('input', function() {
                const termo = $(this).val().trim();
                if (termo.length >= 2) {
                    $.ajax({
                        url: 'buscar_clientes.php',
                        method: 'GET',
                        data: { termo: termo },
                        success: function(data) {
                            const sugestoes = $('#sugestoes');
                            if (data.length > 0) {
                                sugestoes.empty().show();
                                data.forEach(cliente => {
                                    sugestoes.append(
                                        `<div class="sugestao-item" data-id="${cliente.id_cliente}">
                                            ${cliente.id_cliente} - ${cliente.nome_cliente}
                                        </div>`
                                    );
                                });
                                
                                $('.sugestao-item').on('click', function() {
                                    $('#busca_cliente').val($(this).text().trim());
                                    sugestoes.hide();
                                    $('#buscaForm').submit();
                                });
                            } else {
                                sugestoes.hide();
                            }
                        }
                    });
                } else {
                    $('#sugestoes').hide();
                }
            });
            
            // Esconde sugestões ao clicar fora
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#busca_cliente, #sugestoes').length) {
                    $('#sugestoes').hide();
                }
            });
        });
        

        
