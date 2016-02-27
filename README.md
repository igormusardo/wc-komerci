# wc-komerci
NÃO ESTÁ PRONTO! Adiciona o método de pagamento da Komerci/RedeCard

### To do ###

- Desenvolver um metabox para o método de pré autorização. Isso é necessário para confirmar a compra dentro do shop_order quando se está utilizando o método pre_auth (GetAuthorized + ConfPreAuthorization). Pensei em algo em AJAX, fazendo uma requisição Soap no server da RedeCard.- 
- Realizar as funções de estornos (VoidTransaction).
- Tratar algumas funções.
- Trabalhar nas parcelas (divisão, mostrar os valores do jeito certo).
- Validar formulários.
- Quando for o método pre_auth, não dar pedido concluido, o pedido só será concluido quando for usado o ConfPreAuthorization.
- Trocar tudo o que é woocommerce-komerci para wc-komerci por ser uma versão beta xD
- Os comentários tão bichados hein tiozão
