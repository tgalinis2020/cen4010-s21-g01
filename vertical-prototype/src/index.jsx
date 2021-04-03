import React from 'react'
import ReactDOM from 'react-dom'

import Main from './Main'

import 'bootstrap/dist/css/bootstrap.min.css'

console.log(Main)

ReactDOM.render(
    <React.StrictMode>
        <Main />
    </React.StrictMode>,
    
    document.getElementById('root')
)
