#!/usr/bin/env bash
time for i in {1..10}; do ./eisago.sh import --mode=concurrent --quiet; done