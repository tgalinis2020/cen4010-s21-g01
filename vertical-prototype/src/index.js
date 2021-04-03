import React from 'react'
import ReactDOM from 'react-dom'

import App from './App'

import 'bootstrap/dist/css/bootstrap.min.css'

function main() {
    return (
        <React.StrictMode>
            <App />
        </React.StrictMode>
    )
}

ReactDOM.render(main, document.getElementById('root'))
