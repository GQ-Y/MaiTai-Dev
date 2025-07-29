#!/bin/bash
ps -ef | grep -v grep | grep MineAdmin | awk '{print $2}'|xargs -t -r kill -9