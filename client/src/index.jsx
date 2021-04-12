import React from 'react'
import ReactDOM from 'react-dom'

import Main from './Main'

import 'bootstrap/dist/css/bootstrap.min.css'

ReactDOM.render(
    <React.StrictMode>
        <Main title="The Pet Park" />
    </React.StrictMode>,
    
    document.getElementById('root')
)
