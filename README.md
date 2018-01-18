# Using optimised binary UUIDs in Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-binary-uuid.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-binary-uuid)
[![Build Status](https://img.shields.io/travis/spatie/laravel-binary-uuid/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-binary-uuid)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/39e435d7-88b4-49ea-9822-ba4c68233a30.svg?style=flat-square)](https://insight.sensiolabs.com/projects/39e435d7-88b4-49ea-9822-ba4c68233a30)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-binary-uuid.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-binary-uuid)
[![StyleCI](https://styleci.io/repos/110949385/shield?branch=master)](https://styleci.io/repos/110949385)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-binary-uuid.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-binary-uuid)

Using a regular uuid as a primary key is guaranteed to be slow.

This package solves the performance problem by storing slightly tweaked binary versions of the uuid. You can read more about the storing mechanism here: [http://mysqlserverteam.com/storing-uuid-values-in-mysql-tables/](http://mysqlserverteam.com/storing-uuid-values-in-mysql-tables/).

The package can generate optimized versions of the uuid. It also provides handy model scopes to easily retrieve models that use binary uuids.

Want to test the perfomance improvements on your system? No problem, we've included [benchmarks](#running-the-benchmarks).

The package currently only supports MySQL and SQLite.

## Installation

You can install the package via Composer:

```bash
composer require spatie/laravel-binary-uuid
```

## Usage
 
To let a model make use of optimised UUIDs, you must add a `uuid` field as the primary field in the table.

```php
Schema::create('table_name', function (Blueprint $table) {
    $table->uuid('uuid');
    $table->primary('uuid');
});
```

If you want to use uuid as a primary key you must let your model use the `HasBinaryUuid` and the `HasUuidPrimaryKey` traits.

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\BinaryUuid\HasBinaryUuid;
use Spatie\BinaryUuid\HasUuidPrimaryKey;

class TestModel extends Model
{
    use HasBinaryUuid,
        HasUuidPrimaryKey;
}
```

If don't like the primary key named `uuid` you can leave off the `HasUuidPrimaryKey` trait and manually specify `$primaryKey`. Don't forget set `$incrementing` to false.

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\BinaryUuid\HasBinaryUuid;

class TestModel extends Model
{
    use HasBinaryUuid;

    public $incrementing = false;
    
    public $primaryKey = 'uuid';
}
```

If you try converting your model to JSON with binary attributes, you will see errors. By declaring your binary attributes in `$uuidAttributes` on your model, you will tell the package to cast those UUID's to text whenever they are converted to array. Also, this adds a dynamic accessor for each of the uuid attributes.

```php
```php
use Illuminate\Database\Eloquent\Model;
use Spatie\BinaryUuid\HasBinaryUuid;

class TestModel extends Model
{
    use HasBinaryUuid;
    
    /**
     * The binary UUID attributes that should be converted to text.
     *
     * @var array
     */
    protected $uuidAttributes = [
        'uuid',
        'country_uuid',
    ];
}
```

In your JSON you will see `uuid` and `country_uuid` in their textual representation.

You can also access the textual representations directly with `$testModel->uuid_text` and `$testModel->country_uuid_text`. 

#### A note on the `uuid` blueprint method

Laravel currently doesn't allow adding new blueprint methods which can be used out of the box.
Because of this, we decided to override the `uuid` behaviour which will create a `BINARY` column instead of a `CHAR(36)` column.

There are some cases in which Laravel's generated code will also use `uuid`, but doesn't support our binary implementation.
An example are database notifications. 
To make those work, you'll have to change the migration of those notifications to use `CHAR(36)`.

```php
// $table->uuid('id')->primary();

$table->char('id', 36)->primary();
```


### Creating a model

The UUID of a model will automatically be generated upon save.

```php
$model = MyModel::create();

dump($model->uuid); // b"\x11þ╩ÓB#(ªë\x1FîàÉ\x1EÝ." 
```

### Getting a human-readable UUID

UUIDs are only stored as binary in the database. You can however use a textual version for eg. URL generation.

```php
$model = MyModel::create();

dump($model->uuid_text); // "6dae40fa-cae0-11e7-80b6-8c85901eed2e" 
```

If you want to set a specific UUID before creating a model, that's also possible.

It's unlikely though that you'd ever want to do this.

```php
$model = new MyModel();

$model->uuid_text = $uuid;

$model->save();
```

### Querying the model

The most optimal way to query the database:

```php
$uuid = 'ff8683dc-cadd-11e7-9547-8c85901eed2e'; // UUID from eg. the URL.

$model = MyModel::withUuid($uuid)->first();
``` 

The `withUuid` scope will automatically encode the UUID string to query the database.
The manual approach would be something like this.

```php
$model = MyModel::where('uuid', MyModel::encodeUuid($uuid))->first();
```

You can also query for multiple UUIDs using the `withUuid` scope.

```php
$models = MyModel::withUuid([
    'ff8683dc-cadd-11e7-9547-8c85901eed2e',
    'ff8683ab-cadd-11e7-9547-8c85900eed2t',
])->get();
```

#### Querying relations

You can also use the `withUuid` scope to query relation fields by specifying a field to query.

```php
$models = MyModel::withUuid('ff8683dc-cadd-11e7-9547-8c85901eed2e', 'relation_field')->get();

$models = MyModel::withUuid([
    'ff8683dc-cadd-11e7-9547-8c85901eed2e',
    'ff8683ab-cadd-11e7-9547-8c85900eed2t',
], 'relation_field')->get();
```

## Running the benchmarks

The package contains benchmarks that prove storing uuids in a tweaked binary form is really more performant. 

Before running the tests you should set up a MySQL database and specify the connection configuration in `phpunit.xml.dist`.

To run the tests issue this command.
```
phpunit -d memory_limit=-1 --testsuite=benchmarks
```

Running the benchmarks can take several minutes. You'll have time for several cups of coffee!


While the test are running average results are outputted in the terminal. After the tests are complete you'll find individual query stats as CSV files in the test folder.

You may use this data to further investigate the performance of UUIDs in your local machine.

Here are some results for the benchmarks running on our machine.

*A comparison of the normal ID, binary UUID and optimised UUID approach. Optimised UUIDs outperform all other on larger datasets.*

![Comparing different methods](./docs/comparison.png "Comparing different methods")

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Brent Roose](https://github.com/brendt)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
