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

## Usage

From the command line

``` bash
$ php artisan artomator
```

You will be prompted to provide the name to be used for all the classes. This is generally namespaced and singular. i.e. `Namespace\Name`.

This will create the following files:
1. Model: `Namespace\Name::class`
2. Controller: `Namespace\NameController::class`
3. Validation Request: `ValidateName::class`
4. GraphQL Query: `Namespace\NameQuery::class`
5. GraphQL Type: `Namespace\NameType::class`
6. Standard Database Migration
7. Database Seeder: `NamespaceNameTableSeeder::class`
8. Database Factory: `NamespaceNameFactory::class`

Optionally you can provide a comma separated list of generators to include or exclude:

``` bash
$ php artisan artomator -e "factory,seeder,migration"
```
This will exclude the Factory, Seeder and Migration from being generated.

### Including schema

You can optionally provide the `schama` option to the command `artomator` that will allow you to specify the fields for the `migration` during the initial generation. This utilises the package [laracasts/generators](https://github.com/laracasts/Laravel-5-Generators-Extended).

It will also use the schema to populate the `arguments()` and `resolvers()` methods in the `GraphQL::Query`. It will also use the schema to populate the `fields()` method in the `GraphQL::Type`. Finally it will also use the schema to populate the `store()` and `update()` methods in the `controller`.


```bash
php artisan artomator --schema="username:string, email:string:unique"
```

Notice the format that we use, when declaring any applicable schema: a comma-separate list...

```bash
COLUMN_NAME:COLUMN_TYPE
```

So any of these will do:

```bash
username:string
body:text
age:integer
published_at:date
excerpt:text:nullable
email:string:unique:default('foo@example.com')
```

Using the schema from earlier...

```bash
--schema="username:string, email:string:unique"
```

...this will give you a migration file like this:

```php
<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table) {
			$table->increments('id');
			$table->string('username');
			$table->string('email')->unique();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
```

This also means that you can use the [laracasts/generators](https://github.com/laracasts/Laravel-5-Generators-Extended) as per their instructions separately in order to add/delete/update migrations following the initial generation.

### Build From Table
In a similar fashion to the `schema` option, it's also possible to provide a table name of an existing table and then the generator will inspect the table and reverse engineer the schema from this.

It's important to keep in mind that this only determines the schema of the table, not the table name and the primary key etc. In fact this will ignore the primary key field and replace with a standard `id` field.

That being said, it's a shortcut for creating a number of files with ease by providing a table in the database to use.

From the command line:
``` bash
$ php artisan artomator -t "TableName"
```

This will populate the `schema` from the table and pass this to the generators called. Other options can be used as detailed elsewhere, but this will supersede any `schema` passed through the command line.


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
- Robot icon made by [Roundicons][link-robot] from [www.flaticon.com](https://www.flaticon.com)

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
[link-robot]: https://www.flaticon.com/authors/roundicons
