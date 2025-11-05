<?php

namespace App\Console;

class NanoCLI
{
    public static function handle(array $argv): void
    {
        $command  = $argv[1] ?? null;
        $argument = $argv[2] ?? null;

        if (!$command || in_array($command, ['help', '-h', '--help'])) {
            self::showHelp();
            return;
        }

        match ($command) {
            'make:model'      => self::makeModel($argument),
            'make:controller' => self::makeController($argument),
            'make:resource'   => self::makeResource($argument),
            'serve'           => self::serve($argument),
            'list'            => self::showHelp(),
            default           => self::unknownCommand($command),
        };
    }

    /**
     * Inicia o servidor com auto reload
     */
    protected static function serve(?string $argument): void
    {
        $port = $argument ?? 8000;
        $host = "127.0.0.1";
        $publicPath = "public";

        if (!is_dir($publicPath)) {
            self::error("Diretório 'public' não encontrado.");
            return;
        }

        self::line("");
        self::line("\033[1;35m───────────────────────────────────────────────\033[0m");
        self::line(" NanoPHP Development Server");
        self::line("───────────────────────────────────────────────");
        self::line(" Server running at: \033[32mhttp://{$host}:{$port}\033[0m");
        self::line(" Serving directory: {$publicPath}/");
        self::line(" Watching for file changes...");
        self::line("───────────────────────────────────────────────\n");

        $cmd = sprintf("php -S %s:%d -t %s", $host, $port, $publicPath);
        $server = self::startProcess($cmd);
        $lastChange = self::latestFileModificationTime(__DIR__ . '/../../');

        while (true) {
            sleep(1);
            $currentChange = self::latestFileModificationTime(__DIR__ . '/../../');
            if ($currentChange > $lastChange) {
                $lastChange = $currentChange;
                self::line("\033[33mAlterações detectadas. Reiniciando servidor...\033[0m");
                self::stopProcess($server);
                $server = self::startProcess($cmd);
                self::line("\033[32mServidor reiniciado com sucesso.\033[0m\n");
            }
        }
    }

    /**
     * Mostra ajuda/descrição dos comandos disponíveis
     */
    protected static function showHelp(): void
    {
        self::line("\033[1;35mNanoPHP CLI\033[0m — Ferramenta de linha de comando");
        self::line("Versão: 1.0.0\n");
        self::line("\033[1mComandos disponíveis:\033[0m\n");

        self::line("  \033[32mphp nano make:model <Nome>\033[0m       Cria um novo Model");
        self::line("  \033[32mphp nano make:controller <Nome>\033[0m  Cria um novo Controller");
        self::line("  \033[32mphp nano make:resource <Nome>\033[0m    Cria Model, Controller e View automaticamente");
        self::line("  \033[32mphp nano serve [porta]\033[0m           Inicia o servidor local (default: 8000)");
        self::line("  \033[32mphp nano list\033[0m                    Mostra esta lista de comandos");
        self::line("  \033[32mphp nano help\033[0m                    Mostra ajuda detalhada\n");

        self::line("Exemplos:");
        self::line("  php nano make:model User");
        self::line("  php nano make:controller UserController");
        self::line("  php nano make:resource Product");
        self::line("  php nano serve 8080\n");
    }

    /**
     * Cria um Model isolado
     */
    protected static function makeModel(?string $name): void
    {
        if (!$name) {
            self::error("Falta o nome do model. Exemplo: php nano make:model User");
            return;
        }

        [$Name, $table] = self::resolveNaming($name);

        $dir = "App/Models";
        $path = "$dir/{$Name}.php";

        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (file_exists($path)) {
            self::warn("Model '{$Name}' já existe.");
            return;
        }

        $content = <<<PHP
<?php

namespace App\Models;

use Core\Model;

class {$Name} extends Model
{
    protected static string \$table = '{$table}';
}

PHP;

        file_put_contents($path, $content);
        self::success("Model criado com sucesso: App/Models/{$Name}.php");
    }

    /**
     * Cria um Controller isolado
     */
    protected static function makeController(?string $name): void
    {
        if (!$name) {
            self::error("Falta o nome do controller. Exemplo: php nano make:controller UserController");
            return;
        }

        $Name = ucfirst($name);
        if (!str_ends_with($Name, 'Controller')) {
            $Name .= 'Controller';
        }

        $dir = "App/Controllers";
        $path = "$dir/{$Name}.php";

        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (file_exists($path)) {
            self::warn("Controller '{$Name}' já existe.");
            return;
        }

        $content = <<<PHP
<?php

namespace App\Controllers;

use Core\ControllerBase;

class {$Name} extends ControllerBase
{
    public function index()
    {
        echo "Controller {$Name} carregado com sucesso!";
    }
}

PHP;

        file_put_contents($path, $content);
        self::success("Controller criado com sucesso: App/Controllers/{$Name}.php");
    }

    /**
     * Cria Model + Controller + View
     */
    protected static function makeResource(?string $name): void
    {
        if (!$name) {
            self::error("Falta o nome do recurso. Exemplo: php nano make:resource User");
            return;
        }

        [$Name, $table] = self::resolveNaming($name);

        // Cria Model
        self::makeModel($Name);

        // Cria Controller
        self::makeController($Name);

        // Cria View
        $viewDir = "App/Views";
        if (!is_dir($viewDir)) mkdir($viewDir, 0777, true);

        $viewFile = "{$viewDir}/" . strtolower($name) . ".php";
        if (!file_exists($viewFile)) {
            file_put_contents($viewFile, "<h1>{$Name} view</h1>\n");
        }

        self::success("Resource '{$Name}' criado com sucesso (Model + Controller + View).");
    }

    /**
     * Resolve nomes e pluraliza tabela em lowercase
     */
    protected static function resolveNaming(string $name): array
    {
        $Name = ucfirst($name);
        $lower = strtolower($name);

        if (str_ends_with($lower, 'y')) {
            $table = substr($lower, 0, -1) . 'ies';
        } elseif (str_ends_with($lower, 's')) {
            $table = $lower;
        } else {
            $table = $lower . 's';
        }

        return [$Name, $table];
    }

    /**
     * Trata comandos desconhecidos
     */
    protected static function unknownCommand(string $command): void
    {
        self::error("Comando desconhecido: '{$command}'");
        self::line("Use \033[32mphp nano help\033[0m para ver os comandos disponíveis.\n");
    }

    // Métodos auxiliares do servidor
    protected static function startProcess(string $cmd)
    {
        return proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
    }

    protected static function stopProcess($process): void
    {
        if (is_resource($process)) {
            proc_terminate($process);
        }
    }

    protected static function latestFileModificationTime(string $dir): int
    {
        $latest = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                $mtime = $file->getMTime();
                if ($mtime > $latest) {
                    $latest = $mtime;
                }
            }
        }
        return $latest;
    }

    // Helpers de estilo
    protected static function line(string $text): void
    {
        echo $text . PHP_EOL;
    }

    protected static function success(string $message): void
    {
        echo "\033[32m{$message}\033[0m\n";
    }

    protected static function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m\n";
    }

    protected static function warn(string $message): void
    {
        echo "\033[33m{$message}\033[0m\n";
    }
}
