# NanoPHP Starter

O **NanoPHP Starter** é o ponto de partida oficial para criar aplicações com o micro-framework [NanoPHP](https://github.com/marinoricardo/nanophp).  
Ele fornece uma estrutura limpa, organizada e pronta para desenvolvimento **fullstack** (API + Views PHP).


## Requisitos

- **PHP 8.1+**
- **Composer**
- **MySQL**
- Extensões PHP:
    - `pdo`
    - `pdo_mysql`
    - `mbstring`

---

## Instalação

Crie um novo projeto com o Composer:

```bash
composer create-project marinoricardo/nanophp-starter minha-app
cd minha-app
composer install
```

Inicie o servidor de desenvolvimento:

```bash
php nano serve
```

Abra no navegador:  
[http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Estrutura do Projeto

```
App/
 ├── Controllers/     # Lógica de rotas e respostas HTTP
 ├── Models/          # Modelos e interação com o banco de dados
 ├── Views/           # Templates PHP (HTML + lógica mínima)
Core/
 ├── Model.php
 ├── ControllerBase.php
 ├── Database.php
 ├── Router.php
 └── (outros utilitários do framework)
public/
 └── index.php        # Ponto de entrada da aplicação
nano                  # CLI personalizada do NanoPHP
```

---

## CLI — NanoPHP Command Line Tool

O **NanoPHP Starter** inclui o comando `php nano`, uma CLI leve e prática.

### Ver comandos disponíveis

```bash
php nano help
```

### Criar um Model

```bash
php nano make:model User
```

### Criar um Controller

```bash
php nano make:controller UserController
```

### Criar Model + Controller + View automaticamente

```bash
php nano make:resource Product
```

### Iniciar o servidor local (com auto reload)

```bash
php nano serve
```

> O servidor reinicia automaticamente ao detectar alterações em arquivos `.php`.

---

## Exemplo de Uso

### Model — `App/Models/User.php`

```php
<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected static string $table = 'users';
}
```

### Controller — `App/Controllers/UserController.php`

```php
<?php

namespace App\Controllers;

use Core\ControllerBase;
use App\Models\User;

class UserController extends ControllerBase
{
    public function index()
    {
        $data = User::getAll();
        return $this->success($data);
    }
}
```

---

## Funcionalidades Atuais

- Estrutura MVC simples e clara
- CLI personalizada (`php nano`)
- Servidor com auto reload
- Geração automática de Models, Controllers e Views
- Logging de aplicação (em desenvolvimento)
- Migrations (planeado)
- Autenticação JWT (planeado)

---

## Roadmap

- [ ] Adicionar módulo de **logs de aplicação**
- [ ] Suporte a **migrations** e **seeders**
- [ ] Middlewares de autenticação e caching
- [ ] CLI para gerenciamento de banco (`php nano migrate`)
- [ ] Templates base em HTML e Tailwind CSS

---

## Licença

MIT License © [Marino Ricardo](https://github.com/marinoricardo)
