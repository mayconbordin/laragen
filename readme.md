# <img src="https://raw.githubusercontent.com/mayconbordin/laragen/master/artwork/logo_h.png" width="300">

Laragen is a tool for code generation for Laravel 5.

This tool can generate the following components:

 - Migration file (+ pivot migration)
 - Model
 - Repository
 - Controller
 - Database Seeder
 - Views (CRUD + Forms)
 - Form Request
 - Language Resource

**It can also scaffold all components at once, and you can also generate your components from a database schema.**

# Documentation

1. [Installation](#installation)
2. [Configuration](#configuration)
4. [Generator Commands](#generator-commands)
	1. [Migrations](#migrations)

## Installation

In order to install Laragen, just add 

```json
"mayconbordin/laragen": "dev-master"
```

to your composer.json. Then run `composer install` or `composer update`.

Then in your `config/app.php` add 

```php
'Mayconbordin\Laragen\GeneratorServiceProvider'
```

in the `providers` array.

## Configuration

To publish the configuration for this package execute `php artisan vendor:publish` and a `generator.php` 
file will be created in your `app/config` directory.

The configuration file contains the default path for each type of component, i.e. where the generated components will be save, as well as the namespace to be used for some of the components (e.g. models and controllers).

## Generator Commands

### Migrations

```bash
php artisan generate:migration [options] [<name>]
```

The migration generator can create migrations in two ways: by informing the `name` of the migration (and optionally the `fields` option) or by using a database connection to generate the migrations for all tables (or some of them, if desired).

#### Migration Name

Depending on the keywork used in the name of the migration, a different template will be used for generating the migration. From the migration name the generator will also deduce the name of the table.

- `create` or `make` (`create_users_table`): migration for creating a new table.
- `add`, `append`, `update` or `insert` (`add_user_id_to_posts_table`): add a new column to an existing table.
- `remove` or `delete` (`remove_user_id_from_posts_table`): remove an existing column from a table.
- `destroy` or `drop` (`drop_users_table`): drop an existing table.

#### Options
 - `--fields[=FIELDS]`: The fields of the migration, separated by comma(,).
 - `--action`: The name of the action: create, create_simple, add, delete or drop. Overwrites the action parsed from the migration name.
 - `-t, --tables[=TABLES]`: A list of tables you wish to generate migrations for, separated by a comma: `users,posts,comments`.
 - `-i, --ignore[=IGNORE]`: A list of tables you wish to ignore, separated by a comma: `users,posts,comments`.
 - `--default-index-names`: If provided, won't use the index names from the database for migrations.
 - `--default-fk-names`: If provided, won't use the foreign key names from the database for migrations.

#### Fields syntax

The fields of a migration are declared as a comma-separated list of key:value:option pairs, where the `key` is the name of the field, the `value` is the [column type](http://laravel.com/docs/5.1/migrations#creating-columns), and the `option` can describe column column modifiers, such as `nullable`, `unique` and `unsigned`. Examples:

- `--fields="username:string:unique, email:string:unique"`
- `--fields="age:integer, yob:date"`
- `--fields="name:string:default('John Doe'), bio:text:nullable"`
- `--fields="username:string(30):unique, age:integer:nullable:default(18)"`
- `--fields="username:string, user_type_id:integer:unsigned:nullable:foreign"`

#### Examples

Create a `posts` table:

```bash
php artisan generate:migration create_posts_table --fields="title:string, body:text"
```

Delete the `posts` table:

```bash
php artisan generate:migration drop_posts_table
```

Delete only the `body` column:

```bash
php artisan generate:migration remove_body_from_posts_table --fields="body:text"
```

Create migrations for all tables in the `mysql` database connection:

```bash
php artisan generate:migration --connection=mysql
```
