# Artomator

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pwweb/artomator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pwweb/artomator/?branch=master)

<!-- [![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis] -->

![](robot.png)

**Artomator**: Custom commands making life easier. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer run the following:

``` bash
$ composer require pwweb/artomator --dev
```

### Config

To publish the `config` file from the command line

``` bash
$ php artisan vendor:publish --tag=artomator.config
```

## Usage

From the command line

To be completed...

### Extending

To alter the stub files provided with the package, you can publish them from the command line

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
