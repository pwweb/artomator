# Artomator

[![Latest Stable Version](https://poser.pugx.org/pwweb/artomator/v/stable?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![Total Downloads](https://poser.pugx.org/pwweb/artomator/downloads?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![License](https://poser.pugx.org/pwweb/artomator/license?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/pwweb/artomator?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/pwweb/artomator/)
[![StyleCI Status](https://github.styleci.io/repos/190910947/shield?branch=master)](https://github.styleci.io/repos/190910947)

![](robot.png)

**Artomator**: Custom commands making life easier. Extending the package [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package to include GraphQL and extend the document blocks.

## Installation

Via Composer run the following:

```bash
$ composer require pwweb/artomator --dev
```

Then publish the necessary files as follows:

```bash
$ php artisan vendor:publish --tag=artomator
```

This will publish the config files for the necesary packages and the `graphql.schema` file needed to run the GraphQL server.

Add the following aliases to the aliases array in `config/app/php`:

```php
'Form'  => Collective\Html\FormFacade::class,
'Html'  => Collective\Html\HtmlFacade::class,
'Flash' => Laracasts\Flash\Flash::class,
```

Publish the generator views etc.:

```bash
$ php artisan artomator:publish
```

This will publish the following files:

```bash
├── app
│   ├── Http
│   |   └── Controllers
│   │       └── AppBaseController.php
│   └── Repositories
|       └── BaseRepository.php
└── Tests
    ├── ApiTestTrait.php
    ├── Traits
    ├── APIs
    └── Repositories
```

**Recommended:** If you have a fresh new Laravel application and want a basic admin panel layout then you can use the following Publish Layout Commands.

`laravel/ui` is installed as a dependency but we need to run it if it's a new installation:

```bash
$ php artisan ui bootstrap --auth
```

This will generate Auth Controllers and layout files along with authentication blade view files. These will be overwritten later.

By default `infyomlabs\laravel-generator` uses `infyomlabs\adminlte-templates` templates. If you would prefer to use the `coreui` templates, then this is included as an additional package dependency and you can update the `Templates` section of the config file: `config/inyom/laravel-generator.php` as follows:

```php
<?php
return [
    ...

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    */

    'templates'         => 'coreui-templates',
    ...
];
```

It's recommended that you change this config setting now, before progressing, as the next commands will use whatever template is defined when generating the view files.

We now need to run the generator's command to publish and overwrite the default authentication files which was generated by `laravel\ui` package. It will also generate new files for the sidebar, menu etc.

```bash
$ php artisan artomator.publish:layout
```

This will:

1.  publish the following files:

```bash
├── controllers
|   └── HomeController.php
├── resources
    └── views
        ├── home.blade.php
        ├── layouts
        |   ├── app.blade.php
        |   ├── menu.blade.php
        |   └── sidebar.blade.php
        └── auth
            ├── login.blade.php
            ├── register.blade.php
            └── passwords
                ├── email.blade.php
                └── reset.blade.php
```

2.  add the following routes:

```php
<?php
Auth::routes();

Route::get('/home', 'HomeController@index');
```

### InfyoLabs documentation

As this is an extension to the [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package, it's strongly recommended that you make yourself familiar with their [**documentation**](https://labs.infyom.com/laravelgenerator/docs/7.0/introduction). When you are reviewing these, keep in mind that all commands that start: `$ php artisan infyom` should be replaced with `$php artisan artomator` if you want the additional benefits of this package to be utilised.

## Usage

### Commands

As this is an extension of the [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package the documentation for the base package can be found [here](https://labs.infyom.com/laravelgenerator/docs/6.0/introduction).

All commands in the base package have been "extended" so there is an `artomator` version of each. Refer to the original documentation for instructions on how to use these.

```bash
$ php artisan artomator.publish
$ php artisan artomator.api <MODEL_NAME>
$ php artisan artomator.scaffold <MODEL_NAME>
$ php artisan artomator.api_scaffold <MODEL_NAME>
$ php artisan artomator.publish.layout
$ php artisan artomator.publish.templates
$ php artisan artomator.migration <MODEL_NAME>
$ php artisan artomator.model <MODEL_NAME>
$ php artisan artomator.repository <MODEL_NAME>
$ php artisan artomator.api.controller <MODEL_NAME>
$ php artisan artomator.api.requests <MODEL_NAME>
$ php artisan artomator.api.tests <MODEL_NAME>
$ php artisan artomator.scaffold.controller <MODEL_NAME>
$ php artisan artomator.scaffold.requests <MODEL_NAME>
$ php artisan artomator.scaffold.views <MODEL_NAME>
$ php artisan artomator.rollback <MODEL_NAME>
$ php artisan artomator.publish.user
```

In addition to the base package commands there are the following:

```bash
$ php artisan artomator.graphql_scaffold <MODEL_NAME>
$ php artisan artomator:graphql <MODEL_NAME>
$ php artisan artomator.graphql:mutations <MODEL_NAME>
$ php artisan artomator.graphql.query <MODEL_NAME>
$ php artisan artomator.graphql:type <MODEL_NAME>
```

For the GraphQL commands you can also provide an additional switch `--gqlName=AlternativeGraphqlName` which will allow you to customise the name used by the GraphQL engine. If this is omitted the model name will be used instead.

### `artomator.publish:templates`

To alter the stub files provided with the package, you can publish them from the command line

```bash
$ php artisan artomator.publish:templates
```

This command will run the InfyOmLabs equivalent publish command and then overwrite with those within the Artomator package. Therefore you will be asked to confirm the overwrite of the files, type `yes` to confirm.

This will put the stub files into the `.\resources\infyom\infyom-generator-templates` folder. These can be edited and the when the commands are run, these templates will be used.

### `artomator.graphql_scaffold <MODEL_NAME>`

This function follows the same principal as the `php artisan infyom:api_scaffold <MODEL_NAME>` function but generates the GraphQL files instead of the API files along with the laravel scaffold files.

### `artomator:graphql <MODEL_NAME>`

This function follows the same principal as the `php artisan infyom:api <MODEL_NAME>` function but generates the GraphQL files instead of the API files.

#### `artomator.graphql:mutations <MODEL_NAME>`

This function generates the GraphQL Mutations files only.

#### `artomator.graphql:query <MODEL_NAME>`

This function generates the GraphQL Query file only.

#### `artomator.graphql:type <MODEL_NAME>`

This function generates the GraphQL Type file only.

### Custom Routes

If you want to define custom routes that are persisted and re-generated when new models are added there is now a `custom` property that you can add to the json file `web.json`:

```json
"custom": [
    {
        "method": "post",
        "endpoint": "/print/{id}",
        "controller": "Printing",
        "function": "printer",
        "name": "customprint"
    }
],
"group": {...}
```

Ensure this is inline with the `group` property.

The above will result in a route being added as follows:

```php
<?php
Route::post('/print/{id}', 'PrintingController@printer')->name('customprint');
```

If you leave the function blank it will remove the `@printer` part from the callback.

### Resource Routes Only

If you want to specify that only certain parts of a resource route are used, then you can update your `web.json` file and use a comma separated list of endpoints to specify. For example:

```json
"resources": {
    "ModelName": "index,create,store"
}
```

## Security

If you discover any security related issues, please email security@pw-websolutions.com instead of using the issue tracker.

## Credits

-   [Richard Browne](https://github.com/orgs/pwweb/people/rabrowne85)
-   [Frank Pillukeit](https://github.com/orgs/pwweb/people/frankpde)
-   [PWWEB][link-author]
-   [All Contributors][link-contributors]
-   [InfyOmLabs](https://github.com/InfyOmLabs)

## License

Copyright © pw-websolutions.com. Please see the [license file](license.md) for more information.

[link-author]: https://github.com/pwweb

[link-contributors]: https://github.com/pwweb/artomator/graphs/contributors
