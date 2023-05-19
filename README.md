### Pre requirements
Install [typicode/husky](https://github.com/typicode/husky) package

<hr>

### Installation

#### Install the package via composer
``` composer require zimbop/git-hooks --dev ```

#### Publish package files
``` php artisan vendor:publish --tag=git-hooks ```

#### Fill "environment" variables
Create and fill .husky/env/.env.sh by copying stable example file:
* In case PHP runs in docker container <br />
  ``` cp .husky/env/env.sh.example.docker .husky/env/env.sh ```
* In case PHP runs on host machine <br />
  ``` cp .husky/env/env.sh.example.host .husky/env/env.sh ```

#### If there is no config/git_hooks.php in repo
Publish config ``` php artisan vendor:publish --tag=git-hooks-config```
and set values