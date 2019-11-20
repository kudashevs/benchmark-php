# Benchmark PHP  [![Build Status](https://travis-ci.org/kudashevs/benchmark-php.svg?branch=master)](https://travis-ci.org/kudashevs/benchmark-php)

This is an open-source benchmark application allows you to get the big picture on your server performance running PHP
and easily compare different hosting platforms and their services.  

## Description

Sometimes, we want to benchmark out hosting platform, it doesn’t matter what it is (a shared hosting, a VPS/VDS, a cloud,
a dedicated server or anything). The goal of testing could be a desire to get some real idea of hosting platform, compare
different hosting companies plans, choose something new or more powerful for the same money, and also check changes
in performance after making some improvements (hardware update, performance system settings, etc).

In such cases people usually use different system utilities and different techniques; moreover, each developer prefers
his own set of benchmarks. When I faced this problem once again, I decided to make the benchmark application in pure PHP.
In this way, we will benchmark the PHP itself and keep all benchmarks in one application.

## Features

The main features provided by this application are:
* a set of benchmarks for scalar types (integers, floats, strings)
* a set of benchmarks for compound types (arrays, objects)
* a benchmark of read/write filesystem operations
* two levels of verbosity are available

## Installation

You can install the package via composer:
```
composer require kudashevs/benchmark-php
```

## Usage

After the installation composer will create executable file `benchmark-php` in `./vendor/bin` folder.

To get the help message run:
```
./vendor/bin/benchmark-php -h
```

To list all available benchmarks use `-l` or `--list` command-line options:
```
./vendor/bin/benchmark-php -l
```

To run all available benchmarks use `-a` or `-all` command-line options:
```
./vendor/bin/benchmark-php -a
```

To run only specific benchmarks use `-b` or `-benchmarks` command-line options:
```
./vendor/bin/benchmark-php -b integers,strings
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
