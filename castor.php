<?php

/** @noinspection PhpUnused */

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\guard_min_version;
use function Castor\import;
use function Castor\io;
use function Castor\notify;
use function Castor\variable;
use function docker\build;
use function docker\docker_compose_run;
use function docker\up;

// use function docker\workers_start;
// use function docker\workers_stop;

guard_min_version('0.26.0');

import(__DIR__ . '/.castor');

/**
 * @return array{project_name: string, root_domain: string, extra_domains: string[], php_version: string}
 */
function create_default_variables(): array
{
    $projectName = 'bookmarkhive';
    $tld = 'test';

    return [
        'project_name' => $projectName,
        'root_domain' => "{$projectName}.{$tld}",
        'extra_domains' => [],
        // In order to test docker stater, we need a way to pass different values.
        // You should remove the `$_SERVER` and hardcode your configuration.
        'php_version' => '8.4',
        'registry' => $_SERVER['DS_REGISTRY'] ?? null,
    ];
}

#[AsTask(description: 'Builds and starts the infrastructure, then install the api (composer, yarn, ...)')]
function start(): void
{
    io()->title('Starting the stack');

    // workers_stop();
    build();
    install('api');
    install('client');
    up(profiles: ['default']); // We can't start worker now, they are not installed
    migrate();
    // workers_start();

    notify('The stack is now up and running.');
    io()->success('The stack is now up and running.');
}

#[AsTask(name: 'install', description: 'Installs the api (composer, yarn, ...)', namespace: 'api')]
function install_api(): void
{
    install('api');
}

#[AsTask(name: 'install', description: 'Installs the client (composer, yarn, ...)', namespace: 'client')]
function install_client(): void
{
    install('client');
}

function install(string $frontend): void
{
    io()->title("Installing the {$frontend}");

    $localPath = sprintf("%s/{$frontend}", variable('root_dir'));
    $distPath = "/var/www/{$frontend}";

    if (is_file("{$localPath}/composer.json")) {
        io()->section('Installing PHP dependencies');
        docker_compose_run('composer install -n --prefer-dist --optimize-autoloader', workDir: $distPath);
    }
    if (is_file("{$localPath}/yarn.lock")) {
        io()->section('Installing Node.js dependencies');
        docker_compose_run('yarn install --frozen-lockfile', workDir: $distPath);
    } elseif (is_file("{$localPath}/package.json")) {
        io()->section('Installing Node.js dependencies');

        if (is_file("{$localPath}/package-lock.json")) {
            docker_compose_run('npm ci', workDir: $distPath);
        } else {
            docker_compose_run('npm install', workDir: $distPath);
        }
    }
    if (is_file("{$localPath}/importmap.php")) {
        io()->section('Installing importmap');
        docker_compose_run('bin/console importmap:install', workDir: $distPath);
    }

    docker_compose_run('bin/console lexik:jwt:generate-keypair --skip-if-exists', workDir: $distPath);

    qa\install();
}

#[AsTask(description: 'Migrates database schema', namespace: 'api:db', aliases: ['migrate'])]
function migrate(): void
{
    io()->title('Migrating the database schema');

    $distPath = '/var/www/api';
    docker_compose_run('bin/console doctrine:database:create --if-not-exists', workDir: $distPath);
    docker_compose_run('bin/console doctrine:migration:migrate -n --allow-no-migration --all-or-nothing', workDir: $distPath);
}

#[AsTask(description: 'Loads fixtures', namespace: 'api:db', aliases: ['fixtures'])]
function fixtures(): void
{
    io()->title('Loads fixtures');

    $distPath = '/var/www/api';
    docker_compose_run('bin/console foundry:load-stories -n', workDir: $distPath);
}

/**
 * @param array<mixed> $params
 */
#[AsTask(description: 'Opens a shell (bash) into a builder container', aliases: ['builder'])]
function builder(#[AsRawTokens] array $params = ['bash']): void
{
    if (0 === count($params)) {
        $params = ['bash'];
    }

    $c = context()
        ->toInteractive()
        ->withEnvironment($_ENV + $_SERVER)
    ;

    docker_compose_run(implode(' ', $params), c: $c);
}

/**
 * @param array<mixed> $params
 */
#[AsTask(namespace: 'api:proxy', description: 'Console command called in the builder', aliases: ['bin/console', 'console'])]
function console(#[AsRawTokens] array $params = []): void
{
    $basePath = sprintf('%s/api', variable('root_dir'));
    docker_compose_run('bin/console ' . implode(' ', $params), workDir: $basePath);
}
