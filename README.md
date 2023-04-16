# messaging_queue
This is a small testing application for a prof of concept for utilizing message queues for transfering information 
to remote servers.

[RabbitMQ tutorials](https://www.rabbitmq.com/getstarted.html)

## Initial Setup

### Requirements
* Docker

### Configure the Override File for your local environment
Once the solution has been cloned from the source repo you will need to set up some of the overrides to allow the 
docker containers to access the solution code.

```shell
cd <solution directory>/devops/message_dev/

cp docker-compose.override.yml.example docker-compose.override.yml
```

edit the `docker-compose.override.yml` file, replace the `<path to solution>` with the path to your solution.  If 
you are running this from inside windows, then docker runs through the 
[WSL](https://learn.microsoft.com/en-us/windows/wsl/about) the path used to access the `C:\` drive is `/mnt/c/` you 
should not check out the solution into you Documents folder, this is a special folder for windows that contains a lot 
of virtual links and is often an issue for Docker to access it.

Once you have edited the file, bring up the solution (run from the solutions `message_dev` directory).  The first time it is ran Docker will build the `php` 
container and pull the base images that the solution needs, this could take several minutes to start the first time.

```shell
docker-compose up -d
```

### Install the Solution Assets

Enter the `php` container's shell (run from the solutions `message_dev` directory)

```shell
docker-compose exec php bash
```

From inside the `php` container.  Run the composer install to pull in the assets

```shell
composer install
```

### Accessing the RabbitMQ Management area

The RabbitMQ management console takes a few minutes to come up and be ready to access from the time the solution is 
brought up.  It is accessible from http://localhost:15672 you can access the console using username: `guest` and 
password `guest`

## Running Commands
There are two commands that are part of the solution, both are only demonstration scripts on loading data into the 
RabbitMQ queues.  These commands can be access either by entering the `php` container's shell or directly by 
executing them through `docker-compose`.

### Shell access
(run from the solutions `message_dev` directory)
```shell
docker-compose exec php bash

./console <command>
```

### Docker direct access
(run from the solutions `message_dev` directory)
```shell
docker-compose exec php ./console <command>
```

---
<dl>
    <dt>
        <em>Based of the <a href="https://github.com/ryanwhowe/symfony-template">symfony-template</a> GitHub Template project</em>
    </dt>
    <dd>
        <strong>by <a href="https://github.com/ryanwhowe" target="_blank">Ryan Howe</a></strong>
    </dd>
</dl>