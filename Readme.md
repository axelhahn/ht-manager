# Ht-manager

Ht-manager is an interactive cli tool to simplify management of htpasswd and htgroup files.

This tool is written in PHP 8 and uses the classes of

* <https://github.com/axelhahn/php-htpasswd>

---

👤 Author: Axel Hahn \
🧾 Source: https://github.com/axelhahn/ht-manager/ \
📜 License: GNU GPL 3.0

## Screenshot

![Screenshot](screenshot-01.png)

## Use php script

You can use the php script directly if you have a php interpreter installed.
Just run `php src/htman.php`.

You cannot move the script around - it includes a few files from vendor directory.

## Compile

`src/htman.php`can be compiled into a standalone binary with the delivered scripts by using SPC (check out <https://static-php.dev/>). This was tested on Linux.

A compiled binary is a single binary without dependencies. You can put onto a machine of the same architecture. On your targets no PHP is needed.

### Requirements

Linux packages

* git
* wget
* elfpatch

### Compile

### Start once

Start `./compile/installer.php` to download the SPC binary and let it generate needed files and Micro sfx.
This compilation takes some minutes.

As long you dont't want to set another php version or include an additional php module you don't need to repeat this step.

### Compile binary

Run `./compile/build.php` to merge changes in `src/htman.php` into a newly generated binary. This is done within a second.

Output files on Linux:

```txt
Merged php file:
- ./built_packages/htman.php

Compiled binay:
- ./built_packages/htman
- ./built_packages/htman_linux_x86_64
- ./built_packages/htman_linux_x86_64__README.md
```

### Usage

Copy `built_packages/htman` into a bin directory eg `/usr/bin/` or `~/bin/` to start it without path on any system without PHP installed.
On Systems with PHP you can deploy `built_packages/htman.php`. This is a merged php script that includes all needed files.

Start `htman -h` to see supported parameters.

Start `htman` for interactive mode.
