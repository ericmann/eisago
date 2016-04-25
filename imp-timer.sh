#!/usr/bin/env sh
cmd="./eisago.sh import --mode=imperative --porcelain"; time for i in {1..10}; do $cmd; done