<?php

namespace client;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\io;
use function Castor\run;

#[AsTask(description: 'Install dependencies')]
function install(): void
{
    io()->title('Install dependencies');

    run('yarn install', context: context()->withWorkingDirectory('./client'));
}

#[AsTask(description: 'Start the dev server in watch mode', aliases: ['client:start'])]
function watch(): void
{
    io()->title('Start the dev server in watch mode');

    run('yarn run vite', context: context()->withWorkingDirectory('./client'));
}

#[AsTask(description: 'Build the production artifact')]
function build(#[AsArgument] $defaultInstance, #[AsArgument] $toDirectory): void
{
    io()->title('Build the production artifact');

    $envFile = './client/.env.production';
    $styleGuideFile = './client/src/pages/Styleguide.tsx';

    rename($styleGuideFile, $styleGuideFile.'_skip');
    file_put_contents($styleGuideFile, 'export const Styleguide = () => null;');
    file_put_contents($envFile, "VITE_API_BASE_URL=$defaultInstance\n");
    run('yarn run tsc -b && yarn run vite build', context: context()->withWorkingDirectory('./client'));
    unlink($envFile);
    rename($styleGuideFile.'_skip', $styleGuideFile);

    $toDirectory = rtrim($toDirectory, '/');
    run("rm -rfv $toDirectory/*");
    run("cp -rfv ./client/dist/* $toDirectory/");
}

#[AsTask(description: 'Lint code')]
function lint(): void
{
    io()->title('Lint code');

    run('yarn run eslint .', context: context()->withWorkingDirectory('./client'));
}
