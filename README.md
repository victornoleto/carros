## Sobre o projeto

A ídeia do projeto surgiu da minha necessidade de comprar um novo carro. Eu gostaria não só de comparar vários modelos mas também vários anúncios de um mesmo modelo.

Tudo se baseia em encontrar "o melhor anúncio" com base em três parâmetros: custo, quilometragem e ano do carro. Claro que somente essas informações não bastam, mas já é  um começo, principalmente para filtrar as centenas de anúncios que existem.

Através de uma técnica conhecia como _web scraping_ eu extraio as informações dos anúncios dos seguintes sites:

- OLX ✅
- Webmotors ✅
- iCarros ❎
- UsadosBR ❎

> A extração dos anúncios do iCarros e UsadosBR está desativada no momento.

## Como utilizar o projeto

Instalação das depedências: `composer install`

Crie uma database no seu banco de dados, configure o arquivo `.env` com as credenciais de acesso e após isso execute `php artisan migrate`

Para extrair os anúncios, execute o comando `php artisan cars:sync {brand} {model} {provider?}`
