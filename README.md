Print Flickr Groups as LaTeX photo albums
=========================================

https://github.com/samwilson/flickr-latex

## Requirements

* [PHP](https://php.net/)
* [Composer](https://getcomposer.org/)
* [LaTeX](https://en.wikibooks.org/wiki/LaTeX)

On Ubuntu, you can install these with:

    sudo apt install php composer texlive-latex-extra

## Installation

1. Clone from Github: `git clone https://github.com/samwilson/flickr-latex`
   and then `cd flickr-latex`
2. Run `composer install` (this will create `config.php`)
3. Add your API key and secret to `config.php` (you can get these by registering
   a new app in the [App Garden](https://www.flickr.com/services/))

## Usage

1. Run `./download.php` to get the photos and build the LaTeX source
2. Then run `./typeset.sh` to compile the LaTeX source and produce the PDFs

The PDFs will be stored in `/data/albums/<album-id>/`.

The first time you run `download.php` it will prompt you to open a URL
and authorize the application. Your authorization credentials will be
stored in `data/credentials.txt` so that next time the script will be
able to run without any interaction (e.g. as a cronjob).

## Feedback

If you have any feedback about issues, feature requests, etc. please report them
via https://github.com/samwilson/flickr-latex/issues

We use Waffle.io to track issues:
[![Stories in Ready](https://badge.waffle.io/samwilson/flickr-latex.png?label=ready&title=Ready)](https://waffle.io/samwilson/flickr-latex)

## Licence (GPL-3.0+)

Copyright © 2016 [Sam Wilson](https://samwilson.id.au/)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.