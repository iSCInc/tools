#!/bin/bash

LOG_DIR="/data/project/mp/log"
find -path "$LOG_DIR/*.log" -mtime +1 -exec rm {} \;
