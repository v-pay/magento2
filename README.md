# VirtualPay VP - Adobe Commerce 

**Composer**

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

