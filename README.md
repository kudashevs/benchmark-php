# Benchmark PHP  [![Build Status](https://travis-ci.org/kudashevs/benchmark-php.svg?branch=master)](https://travis-ci.org/kudashevs/benchmark-php)

This is an open-source benchmark application which allows you to get the big picture of your server performance
running PHP and easily compare different hosting platforms and their services.  

## Description

Sometimes, we want to benchmark and compare our hosting platforms. It doesn’t matter what type of hosting it is
(a shared hosting, a VPS/VDS, a dedicated server, a cloud or anything else). The goal of testing could be a desire
to see the big picture, compare different hosting companies and their plans, choose something new or more powerful
for the same money, and also check changes in performance after making some improvements (hardware update, performance
system settings, etc).

In such cases, people usually use different system utilities and different techniques; moreover, each developer prefers
his own set of benchmarks. When I ran into this problem once again, I decided to make the benchmark application
in pure PHP. In this way, we will benchmark the PHP itself and keep all benchmarks in one application.

## Features

The main features provided by this application are:
* a set of benchmarks for scalar types (integers, floats, strings)
* a set of benchmarks for compound types (arrays, objects)
* a benchmark of read/write filesystem operations
* two levels of verbosity are available

## Installation

You should install the package via composer:
```
composer require kudashevs/benchmark-php
```

## Usage

During the installation process composer will create executable file `benchmark-php` in `./vendor/bin` folder.

Before start using the application read the help message:
```
./vendor/bin/benchmark-php -h
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
