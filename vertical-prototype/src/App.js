import { useState } from 'react'

import Table from 'react-bootstrap/Table'
import Button from 'react-bootstrap/Button'
import Container from 'react-bootstrap/Container'
import Form from 'react-bootstrap/Form'
import Figure from 'react-bootstrap/Figure'
import FigureImage from 'react-bootstrap/FigureImage'
import FigureCaption from 'react-bootstrap/esm/FigureCaption'

import User from './Models/User'

import upload_image from './api/upload_image'
import api_request from './api/api_request'
import convert_datetime from './api/convert_datetime'

// Normally an app would be made up of many smaller, self-contained components.
// For the sake of demonstrating that things work, it's okay to have a mess for
// now :)
export default function App() {
    const [file, setFile] = useState(null)
    const [users, setUsers] = useState([])
    const [imageUrl, setImageUrl] = useState(null)
    const [session, setSession] = useState(null)
    const [username, setUsername] = useState('')
    const [password, setPassword] = useState('')

    const onFileChanged = event => setFile(event.target)

    const onSubmitFile = upload_image(file)
        .then(path => {
            setImageUrl(path)
            window.alert(`File uploaded! Path: ${path}`)
        })
        .catch(err => window.alert(`Unable to upload the image. ${session === null ? 'You are not signed in!' : 'Go bug Tom about this.'}`))

    const onGetUsers = () => api_request('GET', '/users')
        .then(res => res.data)
        .then(users => users.map(user => {
            const u = new User()
            
            u.hydrate(user)

            return u
        }))
        .then(setUsers)

    const onUsernameChange = event => setUsername(event.target.value)
    const onPasswordChange = event => setPassword(event.target.value)

    const onLogin = () => api_request('POST', '/session', { username, password })
        .then(res => res.data)
        .then(({ id, username, firstName, lastName, email, createdAt }) => ({
            type: 'users',
            id,
            attributes: {
                username,
                firstName,
                lastName,
                email,
                createdAt: convert_datetime(createdAt)
            }
        }))
        .then(setSession)

    // Properties are passed in as a plain JavaScript object.
    const ImageFigure = ({ image }) =>
        <Figure>
            <FigureImage src={image} width={400} />

            <FigureCaption>Uploaded Image</FigureCaption>
        </Figure>

    return (
        <Container>
            <h1>Users and Authentication</h1>

            <Button onClick={onGetUsers}>Get Users</Button>

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

            <Form>
                <Form.Group>
                    <Form.Label>Username</Form.Label>
                    <Form.Control type="text"
                                  placeholder="Enter username"
                                  onChange={onUsernameChange} />
                </Form.Group>

                <Form.Group>
                    <Form.Label>Password</Form.Label>
                    <Form.Control type="password"
                                  placeholder="Enter password"
                                  onChange={onPasswordChange} />
                </Form.Group>

                <Button variant="primary" onClick={onLogin}>Login</Button>
            </Form>

            <hr />

            <h1>File Upload</h1>

            <p>Note: you must be authenticated to upload images!</p>

            {imageUrl && <ImageFigure image={imageUrl} />}

            <Form>
                <Form.File label="Upload an image" onChange={onFileChanged} />
                <Button variant="primary" onClick={onSubmitFile}>Upload Image</Button>
            </Form>
        </Container>
    )
}
