import { useState, useEffect } from 'react'

import Table from 'react-bootstrap/Table'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import Container from 'react-bootstrap/Container'
import Form from 'react-bootstrap/Form'
import Figure from 'react-bootstrap/Figure'

import User from './Models/User'

import upload_image from './api/upload_image'
import api_request from './api/api_request'
import convert_datetime from './api/convert_datetime'

function getSession(props) {
    return {
        type: 'users',
        id: props.id,
        attributes: {
            username: props.username,
            firstName: props.firstName,
            lastName: props.lastName,
            email: props.email,
            createdAt: convert_datetime(props.createdAt)
        }
    }
}

function LoginForm({ onLoginSuccess, onLoginError, onLogoutSuccess }) {
    const [username, setUsername] = useState('')
    const [password, setPassword] = useState('')

    const onLogin = () => api_request('POST', '/session', { username, password })
        .then(res => {
            if (res.status !== 201) {
                throw res.status
            }

            return res.json()
        })
        .then(res => res.data)
        .then(getSession)
        .then(onLoginSuccess)
        .catch(onLoginError)

    const onLogout = () => {
        api_request('DELETE', '/session').then(onLogoutSuccess)
    }

    return (
        <Form>
            <Form.Group>
                <Form.Label>Username</Form.Label>
                <Form.Control type="text"
                              placeholder="Enter username"
                              onChange={e => setUsername(e.target.value)} />
            </Form.Group>

            <Form.Group>
                <Form.Label>Password</Form.Label>
                <Form.Control type="password"
                              placeholder="Enter password"
                              onChange={e => setPassword(e.target.value)} />
            </Form.Group>

            <ButtonGroup>
                <Button variant="primary" onClick={onLogin}>Login</Button>
                <Button variant="primary" onClick={onLogout}>Logout</Button>
            </ButtonGroup>
        </Form>
    )
}


function ImageFigure({ image }) {
    return (
        <Figure>
            <Figure.Image src={image} width={400} />

            <Figure.Caption>Uploaded Image</Figure.Caption>
        </Figure>
    )
}


// Normally an app would be made up of many smaller, self-contained components.
// For the sake of demonstrating that things work, it's okay to have a mess for
// now :)
export default function Main() {
    const [file, setFile] = useState(null)
    const [users, setUsers] = useState([])
    const [imageUrl, setImageUrl] = useState(null)
    const [session, setSession] = useState(null)

    const onFileChanged = event => setFile(event.target)
    const onLoginError = code => window.alert(`Can't log in! (error code ${code})`)
    const onLogoutSuccess = () => setSession(null)

    const onSubmitFile = () => upload_image(file)
        .then(res => res.json())
        .then(res => res.data)
        .then(path => {
            setImageUrl(path)
            window.alert(`File uploaded! Path: ${path}`)
        })
        .catch(err => window.alert(`Unable to upload the image. ${session === null ? 'You are not signed in!' : 'Go bug Tom about this.'}`))

    const onGetUsers = () => api_request('GET', '/users')
        .then(res => res.json())
        .then(res => res.data)
        .then(users => users.map(user => {
            const u = new User()
            
            u.hydrate(user)

            return u
        }))
        .then(setUsers)

    // Check to see if the user is already logged in.
    
    // Load users when component loads.
    useEffect(() => {
        onGetUsers()
        api_request('GET', '/session')
            .then(res => res.json())
            .then(res => res.data)
            .then(getSession)
            .then(setSession)
            .catch(err => console.log('Not logged in'))
    }, [setSession])

    return (
        <Container>
            <h1>Users and Authentication</h1>

            <Button className="mb-4" onClick={onGetUsers}>Refresh</Button>

            <Table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>E-mail Address</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Joined On</th>
                    </tr>
                </thead>

                <tbody>
                    {users.map((user, index) =>
                        <tr key={index}>
                            <td>{user.id}</td>
                            <td>{user.getAttribute('username')}</td>
                            <td>{user.getAttribute('email')}</td>
                            <td>{user.getAttribute('firstName')}</td>
                            <td>{user.getAttribute('lastName')}</td>
                            <td>{user.getAttribute('createdAt')}</td>
                        </tr>
                    )}
                </tbody>
            </Table>

            <p>Logged in as: {session ? `${session.attributes.firstName} ${session.attributes.lastName}` : '(unauthenticated)'}</p>

            <LoginForm onLoginSuccess={setSession}
                       onLoginError={onLoginError}
                       onLogoutSuccess={onLogoutSuccess} />

            <hr />

            <h1>File Upload</h1>

            <p>Note: you must be authenticated to upload images!</p>

            {imageUrl && <ImageFigure image={imageUrl} />}

            <Form>
                <Form.File label="Upload an image" onChange={onFileChanged} />
                <Button className="my-4" variant="primary" onClick={onSubmitFile}>Upload Image</Button>
            </Form>
        </Container>
    )
}