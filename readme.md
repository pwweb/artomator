# Artomator

[![Latest Stable Version](https://poser.pugx.org/pwweb/artomator/v/stable?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![Total Downloads](https://poser.pugx.org/pwweb/artomator/downloads?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![License](https://poser.pugx.org/pwweb/artomator/license?format=flat-square)](https://packagist.org/packages/pwweb/artomator)
[![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/pwweb/artomator?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/pwweb/artomator/)
[![StyleCI Status](https://github.styleci.io/repos/190910947/shield?branch=feature/lg)](https://github.styleci.io/repos/190910947)


![](robot.png)

**Artomator**: Custom commands making life easier. Extending the package [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package to include GraphQL and extend the document blocks.

## Installation

Via Composer run the following:

``` bash
$ composer require pwweb/artomator --dev
```

Then publish the necessary files as follows:
``` bash
$ php artisan vendor:publish --tag=artomator
```

This will publish the config files for the necesary packages and the `graphql.schema` file needed to run the GraphQL server.

## Usage

### Commands

As this is an extension of the [`InfyOmLabs/laravel-generator`](https://github.com/InfyOmLabs/laravel-generator) package the documentation for the base package can be found [here](https://labs.infyom.com/laravelgenerator/docs/6.0/introduction).

In addition to the base package commands there are the following:
``` bash
$ php artisan artomator.publish:templates
$ php artisan artomator.graphql_scaffold $MODEL_NAME
$ php artisan artomator:graphql $MODEL_NAME
$ php artisan artomator.graphql:mutations $MODEL_NAME
$ php artisan artomator.graphql.query $MODEL_NAME
$ php artisan artomator.graphql:type $MODEL_NAME
```

### `artomator.publish:templates`

To alter the stub files provided with the package, you can publish them from the command line

``` bash
$ php artisan artomator.publish:templates
```
This command will run the InfyOmLabs equivalent publish command and then overwrite with those within the Artomator package. Therefore you will be asked to confirm the overwrite of the files, type `yes` to confirm.

This will put the stub files into the `.\resources\infyom\infyom-generator-templates` folder. These can be edited and the when the commands are run, these templates will be used.

### `artomator.graphql_scaffold $MODEL_NAME`

This function follows the same principal as the `php artisan infyom:api_scaffold $MODEL_NAME` function but generates the GraphQL files instead of the API files along with the laravel scaffold files.

### `artomator:graphql $MODEL_NAME`

This function follows the same principal as the `php artisan infyom:api $MODEL_NAME` function but generates the GraphQL files instead of the API files.

#### `artomator.graphql:mutations $MODEL_NAME`

This function generates the GraphQL Mutations files only.

#### `artomator.graphql:query $MODEL_NAME`

This function generates the GraphQL Query file only.

#### `artomator.graphql:type $MODEL_NAME`

This function generates the GraphQL Type file only.

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email securtity@pw-websolutions.com instead of using the issue tracker.

## Credits

- [Richard Browne](https://github.com/orgs/pwweb/people/rabrowne85)
- [Frank Pillukeit](https://github.com/orgs/pwweb/people/frankpde)
- [PWWEB][link-author]
- [All Contributors][link-contributors]
- [InfyOmLabs](https://github.com/InfyOmLabs)

## License

Copyright &copy; pw-websolutions.com. Please see the [license file](license.md) for more information.

<!-- [ico-version]: https://img.shields.io/packagist/v/pwweb/artomator.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/pwweb/artomator.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/pwweb/artomator/master.svg?style=flat-square -->

<!-- [link-packagist]: https://packagist.org/packages/pwweb/artomator
[link-downloads]: https://packagist.org/packages/pwweb/artomator
[link-travis]: https://travis-ci.org/pwweb/artomator
[link-styleci]: https://styleci.io/repos/12345678 -->
[link-author]: https://github.com/pwweb
[link-contributors]: https://github.com/pwweb/artomator/graphs/contributors
