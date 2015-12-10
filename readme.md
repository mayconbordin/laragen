# <img alt="laragen" src="https://raw.githubusercontent.com/mayconbordin/laragen/master/artwork/logo_h.png" width="300">

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
	2. [Models](#models)
	3. [Repositories](#repositories)
	4. [Controllers](#controllers)
	5. [Database Seeders](#database-seeders)
	6. [Views](#views)
	7. [Form Requests](#form-requests)
	8. [Language Resources](#language-resources)
	9. [Scaffolding](#scaffolding)

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
 - `-c, --connection`: The database connection to use. Default: `database.default` from the configuration.
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

#### Pivot Table Migration

```bash
php artisan generate:pivot [options] <table_one> <table_two>
```

#### Options
 - `-t, --timestamp`: Add timestamp to migration schema.

### Models

```bash
php artisan generate:model [options] <name>
```

#### Options
 - `--fields[=FIELDS]`: The fields of the model, separated by comma(,). The syntax is the same as that of migrations and can be used to generate validation rules.
 - `--fillable`: Comma-separated list of field names that are fillable.
 - `--table-name`: In case the name of the table differs from that of the model.
 
### Repositories

```bash
php artisan generate:repository [options] <name>
```

Create a new repository based on [reloquent](https://github.com/mayconbordin/reloquent), the model that it represents will be infered from the name (e.g. `UserRepository` will refer to the `User` model).

### Controllers

```bash
php artisan generate:controller [options] <name>
```

Create a new controller with the given `<name>` (e.g. `UserController`). By default, the controller created has no methods, for more options see below.

#### Options
 - `-r, --resource`: Will generate a resource controller, i.e. with CRUD methods (the methods have no logic).
 - `-s, --scaffold`: Will generate a scaffolding controller, i.e. with CRUD methods and logic.
 - `--repository`: Same as an scaffolding controller, but using a repository for data manipuation instead of Eloquent models.

### Database Seeders

```bash
php artisan generate:seed [options] <name>
```

#### Options
 - `-m, --master`: Will generate the master database seeder.

### Views

```bash
php artisan generate:view [options] <name>
```

Create a new view file with the name `<name>.blade.php`.

#### Options
 - `-e, --extends`: The name of view layout being used. Default: `layouts.master`.
 - `-s, --section`: The name of section being used. Default: `content`.
 - `-c, --content`: The view content.
 - `-t, --template`: The path of view template. Inform it to use a custom template.
 - `-m, --master`: Create a master view.
 - `-p, --plain`: Create a blank view.

### Form Request

```bash
php artisan generate:request [options] <name>
```

#### Options
 - `-r, --rules`: List of rules for validation. Example: `name:string|max(255)|required, age:integer|required, email:unique(users;email_address)|required`.
 - `--fields[=FIELDS]`: The fields for creating the rules. Separated with comma (,).
 - `-s, --scaffold`: Determine whether the request class generated with scaffold.
 - `-a, --auth`: Determine whether the request class needs authorized.

### Language Resources

```bash
php artisan generate:lang [options] <name>
```

Create a new language resource with the given `<name>`.

#### Options
 - `-l, --languages`: The list of languages (comma-separated) in which the resource will be created. Default: en.
 - `-t, --translations`: List of translations to be included in the resource file. Example: `"test1=\'test one\', test2=\'teste two\'"`.

### Scaffolding

```bash
php artisan generate:scaffold [options] <name>
```

Create all the above components for the entity with the given `<name>`.

#### Options
 - `--fields[=FIELDS]`: The fields of the entity, separated by comma(,).
 - `--prefix`: The prefix for the view path, routes and controller class(es).
 - `-c, --connection`: The database connection to use. Default: `database.default` from the configuration.
 - `-t, --tables[=TABLES]`: A list of tables you wish to generate migrations for, separated by a comma: `users,posts,comments`.
 - `-i, --ignore[=IGNORE]`: A list of tables you wish to ignore, separated by a comma: `users,posts,comments`.
 - `--default-index-names`: If provided, won't use the index names from the database for migrations.
 - `--default-fk-names`: If provided, won't use the foreign key names from the database for migrations.
 - `-l, --languages`: The list of languages (comma-separated) in which the resource will be created. Default: en.
 - `--no-question`: Don't ask any question.
 - `--repository`: Generate the repository classes and controllers that use the repositories.
