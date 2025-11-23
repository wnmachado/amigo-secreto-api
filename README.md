# API Amigo Secreto

API backend desenvolvida em Laravel para gerenciamento de sorteios de amigo secreto.

## 🚀 Funcionalidades

- **Autenticação via e-mail + código de 6 dígitos** (OTP/Magic Code)
- **CRUD completo de Eventos** de amigo secreto
- **CRUD completo de Participantes** vinculados a eventos
- **Sistema de sorteio** que garante que ninguém tire a si mesmo
- **Envio de e-mails** para códigos de login
- **API RESTful** protegida com Laravel Sanctum

## 📋 Requisitos

- PHP >= 8.2
- Composer
- MySQL ou PostgreSQL
- Extensões PHP: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML

## 🔧 Instalação

1. Clone o repositório:
```bash
git clone <repository-url>
cd amigo-secreto-api
```

2. Instale as dependências:
```bash
composer install
```

3. Configure o arquivo `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure as variáveis de ambiente no `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amigo_secreto
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

5. Execute as migrations:
```bash
php artisan migrate
```

6. (Opcional) Execute os testes:
```bash
php artisan test
```

## 📡 Endpoints da API

### Autenticação

#### Solicitar código de login
```
POST /api/auth/request-code
Body: { "email": "usuario@example.com" }
```

#### Verificar código e autenticar
```
POST /api/auth/verify-code
Body: { "email": "usuario@example.com", "code": "123456" }
Response: { "user": {...}, "token": "..." }
```

### Eventos (Requer autenticação)

- `GET /api/events` - Listar eventos do usuário
- `POST /api/events` - Criar evento
- `GET /api/events/{id}` - Detalhar evento
- `PUT /api/events/{id}` - Atualizar evento
- `DELETE /api/events/{id}` - Deletar evento

### Participantes (Requer autenticação)

- `GET /api/events/{event}/participants` - Listar participantes
- `POST /api/events/{event}/participants` - Adicionar participante
- `PUT /api/participants/{id}` - Atualizar participante
- `DELETE /api/participants/{id}` - Remover participante

### Sorteio (Requer autenticação)

- `POST /api/events/{event}/draw` - Realizar sorteio
- `GET /api/events/{event}/draw-results` - Consultar resultado do sorteio

## 🔐 Autenticação

A API utiliza **Laravel Sanctum** para autenticação via token. Após verificar o código, você receberá um token que deve ser enviado no header:

```
Authorization: Bearer {token}
```

## 🧪 Testes

Execute os testes com:
```bash
php artisan test
```

Os testes cobrem:
- Autenticação (solicitar código, verificar código, códigos inválidos/expirados)
- CRUD de Eventos
- CRUD de Participantes
- Lógica de sorteio

## 📧 Configuração de E-mail

Para desenvolvimento, você pode usar o **Mailpit** (já configurado no Laravel Sail) ou configurar um servidor SMTP real no `.env`.

## 🏗️ Estrutura do Projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── EventController.php
│   │       ├── ParticipantController.php
│   │       └── DrawController.php
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── Event/
│   │   └── Participant/
│   └── Resources/
│       ├── EventResource.php
│       └── ParticipantResource.php
├── Mail/
│   └── LoginCodeMail.php
├── Models/
│   ├── User.php
│   ├── LoginCode.php
│   ├── Event.php
│   ├── Participant.php
│   └── DrawResult.php
├── Policies/
│   └── EventPolicy.php
└── Services/
    ├── AuthCodeService.php
    └── DrawService.php
```

## 📝 Modelagem de Dados

- **User**: Usuários do sistema (autenticação via e-mail)
- **LoginCode**: Códigos de autenticação temporários
- **Event**: Eventos de amigo secreto
- **Participant**: Participantes de cada evento
- **DrawResult**: Resultados do sorteio (pares giver/receiver)

## 🚧 Próximos Passos

- [ ] Integração com WhatsApp para envio de mensagens
- [ ] Sistema de notificações por e-mail
- [ ] Dashboard administrativo
- [ ] API de estatísticas

## 📄 Licença

Este projeto está sob a licença MIT.
