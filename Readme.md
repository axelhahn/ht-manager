# Ht-manager

Ht-manager is an interactive cli tool to simplify management of htpasswd and htgroup files.

This tool is written in PHP 8 and uses the classes of

* <https://github.com/axelhahn/php-htpasswd>

---

👤 Author: Axel Hahn \
🧾 Source: https://github.com/axelhahn/ht-manager/ \
📜 License: GNU GPL 3.0

---

Using spc the file "src/htman.php" can be compiled to a standalone binary. This was tested on Linux only so far.

* see <https://static-php.dev/>

## Screenshot

![Screenshot](screenshot-01.png)

## Compile

### Requirements

Linux packages

* git
* wget
* elfpatch

### Compile

### Start once

Start `installer.php` to download the spc binary and let it generate needed files and Micro sfx.
This compilation takes some minutes.

As long you dont't want to set another php version or include an additional php module you don't need to repeat this step.

### Compile binary

Run `build.php` to merge changes in `src/htman.php` into a newly generated binary. This is done within a second.

Output files on Linux:

built_packages/htman
built_packages/htman_linux_x86_64
built_packages/htman_linux_x86_64__README.md

### Usage

Copy built_packages/htman into a bin directory eg /usr/bin/ or ~/bin/ to start it without path.

Start `htman -h` to see supported parameters.

Start `htman` for interactive mode.
