import { useState } from 'react'

import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import User from './Models/User'

import apiRequest from './utils/apiRequest'
import uploadImage from './utils/uploadImage'

function RegistrationForm({ onRegistered, onError }) {
    const [username, setUsername] = useState('')
    const [password, setPassword] = useState('')
    const [firstName, setFirstName] = useState('')
    const [lastName, setLastName] = useState('')
    const [email, setEmail] = useState('')
    const [avatar, setAvatar] = useState(null)

    const user = new User()

    const handleFileChanged = event => setAvatar(event.target.files.item(0))

    const createAccount = () => user
        .setAttribute('firstName', firstName)
        .setAttribute('lastName', lastName)
        .setAttribute('email', email)
        .setAttribute('username', username)
        .create(password)
        .then(() => apiRequest('POST', '/session', { username, password }))
        .then(() => uploadImage(avatar))
        .then(res => res.json())
        .then(res => res.data())
        .then(url => user.setAttribute('avatar', url))
        .then(user => user.update())
        .then(onRegistered)
        .catch(onError)

    return (
        <Form>
            <Form.Group>
                <Form.Label>First Name</Form.Label>
                <Form.Control type="text"
                              placeholder="Enter first name"
                              onChange={e => setFirstName(e.target.value)} />
            </Form.Group>

            <Form.Group>
                <Form.Label>Last Name</Form.Label>
                <Form.Control type="text"
                              placeholder="Enter last name"
                              onChange={e => setLastName(e.target.value)} />
            </Form.Group>

            <Form.Group>
                <Form.Label>E-mail Address</Form.Label>
                <Form.Control type="text"
                              placeholder="Enter e-mail address"
                              onChange={e => setEmail(e.target.value)} />
            </Form.Group>

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

            <Form.Group>
                <Form.Label>Avatar</Form.Label>
                <Form.File custom label="Upload an image" onChange={handleFileChanged} />
            </Form.Group>

            <ButtonGroup>
                <Button variant="primary" onClick={createAccount}>Register</Button>
            </ButtonGroup>
        </Form>
    )
}

export default RegistrationForm