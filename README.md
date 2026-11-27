# 🚗 AutoGestor — Plataforma Comercial & Vitrine Digital para Revendas de Veículos

Sistema completo de gestão de estoque, vitrine pública de alta conversão, captação de leads e operação comercial desenvolvida sob medida para revendas de veículos seminovos e usados.

---

## 🎯 Visão Geral

Projetado para resolver os gargalos reais do dia a dia de uma revenda — da chegada do veículo no pátio até o fechamento da venda — o sistema une **velocidade no atendimento pelo celular** com **governança financeira e proteção de margem**.

### Principais Pilares
* **Vitrine Pública Otimizada (Web & Smart TV):** Catálogo responsivo com filtros instantâneos por marca/modelo, ano, preço e combustível, exibição segura (oculta placa completa, preço de custo e anotações internas) e links diretos para WhatsApp.
* **Captação e Distribuição de Leads:** Formulário integrado em cada veículo com proteção anti-spam (honeypot), alertas internos em tempo real no painel (sino de notificações) e disparo formatado para o WhatsApp do consultor responsável em 1 clique.
* **Gestão de Estoque & Mídia:** Upload múltiplo de fotos com geração automática de miniaturas e conversões otimizadas, trava de publicação (exige mínimo de 3 fotos) e histórico completo de alterações de preço e status.
* **Ficha de Pátio A4 com QR Code:** Geração instantânea de ficha técnica pronta para impressão e fixação no para-brisa, levando o cliente direto para a página do veículo no celular.
* **Governança de Equipe & KPIs:** Separação clara entre **Administrador/Gerente** (visão de capital imobilizado, margem potencial, custo e gestão de equipe) e **Consultor de Vendas** (gestão dos próprios leads, fila de atendimento e acesso controlado).

---

## 🛠️ Stack Tecnológica

* **Backend:** PHP 8.5 • Laravel 13
* **Painel Administrativo:** FilamentPHP v3 + Livewire
* **Banco de Dados:** PostgreSQL (com suporte a `jsonb` para notificações e auditoria)
* **Mídia & Imagens:** Spatie Laravel Media Library
* **Frontend Público:** Blade • Tailwind CSS • Vite
* **Qualidade & Testes:** PestPHP • Larastan (PHPStan Nível 5) • Laravel Pint

---

## 🚀 Instalação e Ambiente Local (Laravel Sail)

### Pré-requisitos
* Docker e Docker Compose instalados
* Git

### Passo a passo

```bash
# 1. Clonar o repositório
git clone git@github.com:seu-usuario/revenda-veiculos.git
cd revenda-veiculos

# 2. Copiar arquivo de ambiente e configurar dados da loja
cp .env.example .env

# 3. Subir os containers (App, PostgreSQL, Redis/Mailpit)
./vendor/bin/sail up -d

# 4. Instalar dependências PHP e Frontend
./vendor/bin/sail composer install
./vendor/bin/sail npm install && ./vendor/bin/sail npm run build

# 5. Gerar chave da aplicação, rodar migrations e link de storage
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link