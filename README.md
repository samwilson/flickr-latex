[![Stories in Ready](https://badge.waffle.io/samwilson/flickr-latex.png?label=ready&title=Ready)](https://waffle.io/samwilson/flickr-latex)
Print Flickr Groups as LaTeX photo albums
=========================================

https://github.com/samwilson/flickr-latex

## Requirements

* PHP
* Composer
* LaTeX

## Installation

1. Clone from Github: `git clone https://github.com/samwilson/flickr-latex`
2. Run `composer install`
3. `cp config.dist.php` to `config.php` and add your API details
4. Navigate to `authenticate.php`

## Usage

1. Run `php download.php` to get the photos and build the LaTeX source.
2. Run `./typeset.sh` to compile the LaTeX source and produce the PDFs.

The PDFs are in `/data/albums/<album-id>/`.

## Feedback

If you have any feedback about issues, feature requests, etc. please report them
via https://github.com/samwilson/flickr-latex/issues
