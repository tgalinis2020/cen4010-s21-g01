import { useState } from 'react'
import upload_image from './api/upload_image'

function App() {
    const [file, setFile] = useState(null)
    const onChange = event => setFile(event.target)
    const onSubmit = upload_image(file)
        .then(path => window.alert(`File uploaded! Path: ${path}`))

    return (
        <div className="App">
            <h1>File Upload</h1>
            <p><input type="file" onChange={onChange} /></p>
            <p><button onClick={onSubmit}>Upload</button></p>
        </div>
    )
}

export default App;
