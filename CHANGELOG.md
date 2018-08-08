# Changelog

All notable changes to `laravel-binary-uuid` will be documented in this file

## 1.2.0 - 2018-08-08

- Configurable UUID attribute names (#49)

## 1.1.7 - 2018-07-25

- Extra isset check on present key when serializing a model (#47)

## 1.1.6 - 2018-03-01

- Prevent decoding the uuid when the model does not exist (#40).
- Bump minimum PHP version to 7.1 to support nullable type hints. 

## 1.1.5 - 2018-02-12

- Better support for route binding #36.
- Deprecate the `HasUuidPrimaryKey` trait, as its functionality is moved to `HasBinaryUuid`.

## 1.1.4 - 2018-02-08

- Support Laravel 5.6
- Temporary remove `doctrine/dbal` support, so the benchmarks can't be run anymore.

## 1.1.3 - 2018-01-18

- remove dependency om `laravel/framework` and add dependency on `laravel/queue`

## 1.1.2 - 2018-01-16

- add table prefix support

## 1.1.0 - 2017-11-30

- add `getRouteKey` method

## 1.0.2 - 2017-11-30

- fix constraints

## 1.0.1 - 2017-11-29

- refactor to make use of `OrderedTimeCodec`

## 1.0.0 - 2017-11-29

- initial release
