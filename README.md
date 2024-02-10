# VirtualPay - Adobe Commerce 

# VirtualPay Integration

Bem-vindo ao repositório da ferramenta de integração Magento / Adobe Commerce com VirtualPay! Esta ferramenta permite uma integração fácil e eficiente com a plataforma VirtualPay, proporcionando uma experiência de pagamento suave em sua aplicação.

## Pré-requisitos

Antes de começar a usar a ferramenta, certifique-se de ter os seguintes requisitos atendidos:

1. **Conta no VirtualPay:** É necessário possuir uma conta ativa no [VirtualPay](https://www.virtualpay.com.br). Se ainda não tiver uma, por favor, registre-se no site.

2. **Chave de Integração:** Faça o cadastro de uma chave de integração em sua conta VirtualPay. Esta chave será essencial para autenticar as requisições feitas pela ferramenta de integração. Siga as instruções no painel administrativo do VirtualPay para gerar e obter sua chave.

3. **Configuração do Webhook:** Para receber notificações em tempo real sobre transações e eventos importantes, é crucial configurar um webhook. Cadastre a URL `https://{{URL-LOJA}}/virtualpay/webhook` como webhook em sua conta VirtualPay e salve as chaves geradas durante esse processo. Essas informações serão necessárias para a correta operação da ferramenta.

## Instalação    

**Instalação via Composer**

```
composer require v-pay/magento2

php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy pt_BR en_US
```

**Instalação Manual**

  

1 - Faça Download do módulo e coloque na pasta
```
app/code/VirtualPay/Payment
```

3 - Depois rodar os comandos de instalação

```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy pt_BR en_US
```

## Desinstalar

1 - Remova o módulo, isso dependerá da forma como foi instalado

**Composer**  

Rode o comando de remoção via composer:  
```
composer remove v-pay/magento2
```

**Manual**  

Remova a pasta:  
```
app/code/VirtualPay/Payment
```

2 - Rode os comandos de atualização

```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy pt_BR en_US
```


## Descrição
Módulo disponível em português e inglês, compatível com a versão 2.4+ do Adobe Commerce.
O módulo utiliza a API da VirtualPay para a geração de pagamentos com PIX

