# Artomator

<!-- [![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis] -->

![](robot.png)

**Artomator**: Custom commands making life easier. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer add this to your root node of composer.json file:

``` json
{
    "repositories": [{
        "type": "vcs",
        "url": "https://bitbucket.koda.tools/scm/pwweb/artomator.git"
    }],    
}
```

Then add this to your require or require-dev object in composer.json:

``` json
"pwweb/artomator": "dev-master"
```

Finally run:

``` bash
$ composer update pwweb/artomator
```

## Usage

From the command line

``` bash
$ php artisan artomator:all Namespace/Name
```

This will create the following files:
1. Model: `Namespace\Name::class`
2. Controller: `Namespace\NameController::class`
3. Validation Request: `ValidateName::class`
4. GraphQL Query: `Namespace\NameQuery::class`
5. GraphQL Type: `Namespace\NameType::class`
6. Standard Database Migration
7. Database Seeder: `NamespaceNameTableSeeder::class`
8. Database Factory: `NamespaceNameFactory::class`

### Config

To publish the `config` file from the command line

``` bash
$ php artisan vendor:publish --tag=artomator.config
```

This will put the config file into the `app\config` folder.

| Option | Description |
| ------ | ----------- |
| `stubPath` | The custom location for the template stubs. If left blank this will default to the package stub files. |
| `authors` | The array of authors in the format `Forename Surname <email@domain.com>`. |
| `copyright` | The copyright text to be displayed in the file docBlock at the top of each created file. |
| `license` | The license text to be displayed in the file docBlock at the top of each created file. |

**Note:** You may need to clear your config cache file when you've edited the config file. To do this from the command line:

``` base
$ php artisan config:clear
```

### Extending

To alter the default stub files provided with the package, you can publish them from the command line

``` bash
$ php artisan vendor:publish --tag=artomator.stubs
```

This will put the stub files into the `public\vendor\PWWEB` folder. These can be edited and the when the commands are run, these templates will be used.

**Note:** Remember to update the config file to point to the location of the template files.

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

<!-- ## Testing

``` bash
$ composer test
``` -->

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email richard@pw-websolutions.com instead of using the issue tracker.

## Credits

- [Richard Browne][link-author]
- [All Contributors][link-contributors]

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
[link-contributors]: ../../contributors
