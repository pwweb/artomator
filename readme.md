# Artomator

![](https://banners.beyondco.de/Artomator.png?theme=dark&packageName=pwweb%2Fartomator&pattern=morphingDiamonds&style=style_1&description=Custom+commands+to+make+life+easy&md=1&showWatermark=1&fontSize=100px&images=fast-forward)

[![Latest Stable Version](https://poser.pugx.org/pwweb/artomator/v/stable?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![Total Downloads](https://poser.pugx.org/pwweb/artomator/downloads?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![License](https://poser.pugx.org/pwweb/artomator/license?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/pwweb/artomator?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/pwweb/artomator/)
[![StyleCI Status](https://github.styleci.io/repos/190910947/shield?branch=master)](https://github.styleci.io/repos/190910947)

**Artomator**: Custom commands making life easier. Extending the package [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package to include GraphQL and extend the document blocks.

## Installation

Via Composer run the following:

```bash
$ composer require pwweb/artomator --dev
```

To simplify the installation the following command can be run:

```bash
$ php artisan artomator:install
```

This will guide you through the various steps to set things up. The questions you'll be asked:

**1. Choose your templating package: [CoreUI / AdminLTE]?**
This will install the laravel-generator packages for the templates based on your choice. See [AdminLTE](https://www.infyom.com/open-source/laravelgenerator/docs/8.0/adminlte-templates) or [CoreUI](https://www.infyom.com/open-source/laravelgenerator/docs/8.0/coreui-templates)

**2. Do you want to install Laravel Jetstea (Inertia & Vue)?**
This will install the [Laravel Jetstream](https://jetstream.laravel.com/2.x/introduction.html) if you choose yes. It will use the [Inertia](https://inertiajs.com/) stack.

**2a. Do you want to support Laravel Jetstream Teams?**
If you want to support the [Teams](https://jetstream.laravel.com/2.x/features/teams.html) feature of Laravel Jetstream answer `YES` and the installation will include the support. Otherwise the Teams feature will not be installed.

**3. Do you want to publish the stub files?**
If you want to be able to override the template files to suit your requirements, then answer `YES` and these will be published to the resources folder. If you want to do this after the installation you can always run `php artisan artomator.publish:templates`.

### Alternate Installation (MANUAL)

Alternatively you can do the following, which is what the `install` command does:

```bash
$ php artisan vendor:publish --tag=artomator
$ php artisan vendor:publish --provider="InfyOm\Generator\InfyOmGeneratorServiceProvider"
$ php artisan vendor:publish --tag=lighthouse-schema
$ php artisan vendor:publish --tag=lighthouse-config
$ php artisan artomator:publish
```

This will publish the config files for the necesary packages and the `graphql.schema` file needed to run the GraphQL server and the following files:

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

Add the following aliases to the aliases array in `config/app.php`:

```php
'Form'  => Collective\Html\FormFacade::class,
'Html'  => Collective\Html\HtmlFacade::class,
'Flash' => Laracasts\Flash\Flash::class,
```

**Recommended:** If you have a fresh new Laravel application and want a basic admin panel layout then you should consider setting up [Laravel Jetstream](https://jetstream.laravel.com/2.x/introduction.html). You can find installation instructions [here](https://jetstream.laravel.com/2.x/installation.html).

By default `infyomlabs\laravel-generator` uses `infyomlabs\adminlte-templates` templates. If you would prefer to use the `coreui` templates, then update the `Templates` section of the config file: `config/inyom/laravel-generator.php` as follows:

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

You will need to add to the appropriate package to your composer:

```bash
$ composer require infyomlabs/coreui-templates
```

or

```bash
$ composer require infyomlabs/adminlte-templates
```

**It's recommended that you change this config setting now, before progressing, as the next commands will use whatever template is defined when generating the view files.**

Finally, you should run the following to publish the stub files used by Artomator so you can configure these to your setup:

```bash
$ php artisan artomator.publish:templates
```

### InfyoLabs documentation

As this is an extension to the [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package, it's strongly recommended that you make yourself familiar with their [documentation](https://labs.infyom.com/laravelgenerator/docs/7.0/introduction). When you are reviewing these, keep in mind that all commands that start: `$ php artisan infyom` should be replaced with `$php artisan artomator` if you want the additional benefits of this package to be utilised.

## Usage

### VueJS Support

Running the `*scaffold` based commands (see [commands](#commands)) you can use the following additional flag to generate the views using the VueJS templates instead.

```bash
$php artisan artomator.scaffold [...] --vue
```

This will follow the same additional switches as the `viewsGenerator` i.e. `--skip=views` will skip the generation of the VueJS views too.

### Commands

As this is an extension of the [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package the documentation for the base package can be found [here](https://labs.infyom.com/laravelgenerator/docs/6.0/introduction).

All commands in the base package have been "extended" so there is an `artomator` version of each. Refer to the original documentation for instructions on how to use these.

```bash
$ php artisan artomator:publish
$ php artisan artomator:api <MODEL_NAME>
$ php artisan artomator:scaffold <MODEL_NAME>
$ php artisan artomator:api_scaffold <MODEL_NAME>
$ php artisan artomator.publish:layout
$ php artisan artomator.publish:templates
$ php artisan artomator:migration <MODEL_NAME>
$ php artisan artomator:model <MODEL_NAME>
$ php artisan artomator:repository <MODEL_NAME>
$ php artisan artomator.api:controller <MODEL_NAME>
$ php artisan artomator.api:requests <MODEL_NAME>
$ php artisan artomator.api:tests <MODEL_NAME>
$ php artisan artomator.scaffold:controller <MODEL_NAME>
$ php artisan artomator.scaffold:requests <MODEL_NAME>
$ php artisan artomator.scaffold:views <MODEL_NAME>
$ php artisan artomator:rollback <MODEL_NAME>
$ php artisan artomator.publish:user
```

In addition to the base package commands there are the following:

```bash
$ php artisan artomator:graphql_scaffold <MODEL_NAME>
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
        "controller": "App\\Http\\Controllers\\Gran\\Parent\\PrintingController",
        "as": "PrintingController",
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
Route::post(
    '/print/{id}',
    [PrintingController::class, 'printer']
)->name('customprint');
```

If you leave the function blank it will remove the `@printer` part from the callback.

### Routes General

Below is an example output for the `web.json` file. You are free to add the `"fallback"` option and change the `"only"` `"controller"` and `"as"` options for a resource controller:

```json
{
  "Gran": {
    "prefix": "gran",
    "name": "gran",
    "fallback": "gran.parent.somethings.index",
    "group": {
      "Parent": {
        "prefix": "parent",
        "name": "parent",
        "resources": {
          "Something": {
            "only": "index,create,store",
            "controller": "App\\Http\\Controllers\\Gran\\Parent\\SomethingController",
            "as": "WeirdnameController"
          }
        }
      }
    }
  }
}
```

- `"fallback"` - this is the name of the route you want to use as the fallback for the group level. This is appended to the end of the group after all the other resources and groups have been generated.
- `"only"` - a comma separated list of the routes you want to limit to.
- `"controller"` - this is the full namespace path of the controller for the routes. If you update this, it will be set at the top of the `web.php` file in the `use` calls.
- `"as"` - this allows you to override the name used for the controller. It defaults to the name of the controller but this allows you to override it should there be a clash.

**!! WARNING !!** These additional fields are not compatible with prior versions. Please manually update the JSON file with the missing fields.

## Security

If you discover any security related issues, please email security@pw-websolutions.com instead of using the issue tracker.

## Credits

- [Richard Browne](https://github.com/orgs/pwweb/people/rabrowne85)
- [Frank Pillukeit](https://github.com/orgs/pwweb/people/frankpde)
- [PWWEB][link-author]
- [All Contributors][link-contributors]
- [InfyOmLabs](https://github.com/InfyOmLabs)

## License

Copyright © pw-websolutions.com. Please see the [license file](license.md) for more information.

[link-author]: https://github.com/pwweb
[link-contributors]: https://github.com/pwweb/artomator/graphs/contributors
