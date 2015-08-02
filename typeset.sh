#!/bin/bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
for ALBUM in data/albums/*; do
    cd $DIR/$ALBUM
    pdflatex album
    makeindex album
    pdflatex album
    pdflatex album
done
