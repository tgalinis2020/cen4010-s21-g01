import { useState } from 'react'

import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import User from './Models/User'

import apiRequest from './utils/apiRequest'
import convertToJsonOrThrowError from './utils/convertToJsonOrThrowError'

function LoginForm({ onSuccess, onError }) {
    const [username, setUsername] = useState('')
    const [password, setPassword] = useState('')

    const login = () => apiRequest('POST', '/session', { username, password })
        .then(convertToJsonOrThrowError(201))
        .then(res => res.data)
        .then(({ uid }) => apiRequest('GET', `/users/${uid}`))
        .then(res => res.json())
        .then(res => new User(res.data))
        .then(onSuccess)
        .catch(onError)

    return (
        <Form>
            <Form.Group>
                <Form.Label>Username</Form.Label>
                <Form.Control
                    type="text"
                    placeholder="Enter username"
                    onChange={({ target }) => setUsername(target.value)} />
            </Form.Group>

            <Form.Group>
                <Form.Label>Password</Form.Label>
                <Form.Control
                    type="password"
                    placeholder="Enter password"
                    onChange={({ target })=> setPassword(target.value)} />
            </Form.Group>

            <ButtonGroup>
                <Button variant="primary" onClick={login}>Log In</Button>
            </ButtonGroup>
        </Form>
    )
}

export default LoginForm