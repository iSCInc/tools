#! /data/project/simplecommonstransfer/bin/python
# -*- coding: utf-8 -*-

import flup.server.fcgi
import simplecommonstransfer.web
import toolsweb

toolsweb.log_to_file(simplecommonstransfer.web.app,
                     '/data/project/simplecommonstransfer/error.log')

if __name__ == '__main__':
    flup.server.fcgi.WSGIServer(simplecommonstransfer.web.app).run()

