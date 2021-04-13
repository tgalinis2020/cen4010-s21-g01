import { useContext, useState } from 'react'

import Form from 'react-bootstrap/Form'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Button from 'react-bootstrap/Button'

import BackButton from '../components/BackButton'

import apiRequest from '../utils/apiRequest'
import debounce from '../utils/debouce'
import convertToJsonOrThrowError from '../utils/convertToJsonOrThrowError'
import User from '../Models/User'
import SessionContext from '../context/SessionContext'

function SignInPage() {
    const [session, setSession] = useContext(SessionContext)
    const [username, setUsername] = useState('')
    const [password, setPassword] = useState('')

    const login = () => apiRequest('POST', '/session', { username, password })
        .then(convertToJsonOrThrowError(201))
        .then((res) => res.data)
        .then(({ uid }) => apiRequest('GET', `/users/${uid}?include=subscriptions`))
        .then((res) => res.json())
        .then((res) => res.data)
        .then(({ data, included }) => ({
            user: new User(data),
            subscriptions: included.map(({ id }) => id),
        }))
        .then(setSession)
        .catch((error) => {
            // TODO: alerts don't look so nice, replace with something fancier
            window.alert('Invalid username/password combination!')
        })

    const debounced = setter => debounce(
        500,

        ({ target }) => setter(target.value),

        // Ignore debouncing when enter or backspace keys are pressed.
        ({ key, target }) => key === 'Enter' || (key === 'Backspace' && target.value === '')
    )

    return (
        <>
            <h1 className="mb-4"><BackButton />Sign In</h1>

            <Form>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Username</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            type="text"
                            placeholder="Enter username"
                            onChange={debounced(setUsername)} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Password</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            type="password"
                            placeholder="Enter password"
                            onChange={debounced(setPassword)} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Col sm={{ span: 10, offset: 2 }}>
                        <Button variant="primary" onClick={login}>Sign In</Button>
                    </Col>
                </Form.Group>
            </Form>
        </>
    )
}

export default SignInPage