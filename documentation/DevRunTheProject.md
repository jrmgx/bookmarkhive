# Run The Project Locally For Development 

This project use [docker-starter](https://github.com/jolicode/docker-starter)
and [castor](https://github.com/jolicode/castor) for ease of developer experience.

## Running the application locally

### Requirements

A Docker environment is provided and requires you to have these tools available:

* Docker
* Bash
* Castor

### Docker environment

The Docker infrastructure provides a web stack with:
- NGINX
- PostgreSQL
- PHP
- Traefik
- A container with some tooling:
    - Composer
    - Node
    - Yarn / NPM

### Domain configuration (first time only)

Before running the application for the first time, ensure your domain names
point the IP of your Docker daemon by editing your `/etc/hosts` file.

This IP is probably `127.0.0.1` unless you run Docker in a special way.

```
echo '127.0.0.1 bookmarkhive.test api.bookmarkhive.test admin.bookmarkhive.test' | sudo tee -a /etc/hosts
```

### Starting the stack

Launch the stack by running this command:

```bash
castor start
```

> [!NOTE]
> the first start of the stack should take a few minutes.

The site is now accessible at the hostnames you have configured over HTTPS
(you may need to accept self-signed SSL certificate if you do not have `mkcert`
installed on your computer - see below).

### SSL certificates

HTTPS is supported out of the box. SSL certificates are not versioned and will
be generated the first time you start the infrastructure (`castor start`) or if
you run `castor docker:generate-certificates`.

If you have `mkcert` installed on your computer, it will be used to generate
locally trusted certificates. See [`mkcert` documentation](https://github.com/FiloSottile/mkcert#installation)
to understand how to install it. Do not forget to install CA root from `mkcert`
by running `mkcert -install`.

If you don't have `mkcert`, then self-signed certificates will instead be
generated with `openssl`. You can configure [infrastructure/docker/services/router/openssl.cnf](infrastructure/docker/services/router/openssl.cnf)
to tweak certificates.

You can run `castor docker:generate-certificates --force` to recreate new certificates
if some were already generated. Remember to restart the infrastructure to make
use of the new certificates with `castor build && castor up` or `castor start`.

### Builder

Having some composer, yarn or other modifications to make on the project?
Start the builder which will give you access to a container with all these
tools available:

```bash
castor builder
```

### Other tasks

Checkout `castor` to have the list of available tasks.
