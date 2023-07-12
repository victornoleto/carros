#!/bin/bash

python3 generate-conf.py

mv conf/* /etc/supervisor/conf.d/

supervisorctl reread

supervisorctl update

supervisorctl restart all

supervisorctl status