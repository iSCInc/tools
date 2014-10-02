# -*- coding: utf-8 -*-

import flask
import toolsweb

app = toolsweb.create_app('SimpleCommonsTransfer',
                          template_package='simplecommonstransfer')

@app.route('/')
def index():
    return flask.render_template('index.html')

