<?php

namespace extension;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\io;
use function Castor\run;

#[AsTask(description: 'Install dependencies')]
function install(): void
{
    io()->title('Install dependencies');

    run('npm install', context: context()->withWorkingDirectory('./extension'));
}

function copy_libs(): void
{
    $context = context()->withWorkingDirectory('./extension');
    run('mkdir -p lib', context: $context);
    run('cp node_modules/tom-select/dist/js/tom-select.complete.min.js lib/tom-select.complete.min.js', context: $context);
    run('cp node_modules/tom-select/dist/css/tom-select.css lib/tom-select.css', context: $context);
}

#[AsTask(description: 'Build the production artifact')]
function build(): void
{
    io()->title('Build the production artifact');

    $context = context()->withWorkingDirectory('./extension');
    copy_libs();
    run('node esbuild.config.js content', context: $context);
    run('node esbuild.config.js background', context: $context);
    run('node esbuild.config.js popup', context: $context);
    run('node esbuild.config.js options', context: $context);
}

#[AsTask(description: 'Start the dev server in watch mode', aliases: ['extension:start'])]
function watch(): void
{
    io()->title('Start the dev server in watch mode');

    copy_libs();
    $concurrent = [
        '"node esbuild.config.js content --watch"',
        '"node esbuild.config.js background --watch"',
        '"node esbuild.config.js popup --watch"',
        '"node esbuild.config.js options --watch"',
    ];
    run(
        'npx concurrently ' . implode(' ', $concurrent),
        context: context()->withWorkingDirectory('./extension')
    );
}

#[AsTask(description: 'Type check TypeScript files')]
function typecheck(): void
{
    io()->title('Type check TypeScript files');

    run('tsc --noEmit', context: context()->withWorkingDirectory('./extension'));
}

#[AsTask(description: 'Clean build artifacts')]
function clean(): void
{
    io()->title('Clean build artifacts');

    run('rm -f *.js *.js.map lib/*.js lib/*.js.map', context: context()->withWorkingDirectory('./extension'));
}

#[AsTask(description: 'Lint code')]
function lint(): void
{
    io()->title('Lint code');

    run('yarn run eslint .', context: context()->withWorkingDirectory('./extension'));
}
