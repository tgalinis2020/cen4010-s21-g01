import { useContext, useState } from 'react'
import { useHistory } from 'react-router-dom'

import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'

import User from '../Models/User'

import apiRequest from '../utils/apiRequest'
import uploadImage from '../utils/uploadImage'
import useValidators from '../hooks/useValidators'

import BackButton from '../components/BackButton'
import SessionContext from '../context/SessionContext'

function SignUpPage() {
    const [avatar, setAvatar] = useState(null)
    const [session, setSession] = useContext(SessionContext)
    const history = useHistory()
    
    const checkEmpty = (field = 'Value') => (value) => Promise
        .resolve(value === '' ? `${field} cannot be empty.` : null)

    const checkExists = (field, label = null) => (value) =>
        apiRequest('GET', `/users?filter[${field}]=${value}`)
            .then(res => res.json())
            .then(
                ({ data }) => typeof data.pop() === 'undefined'
                    ? null 
                    : `Provided ${label || field} is already in use.`
            )

    const fields = useValidators({
        username: [
            checkEmpty('Username'),
            checkExists('username'),
        ],

        password: [
            checkEmpty('Password'),
        ],

        firstName: [
            checkEmpty('First name'),
        ],

        lastName: [
            checkEmpty('Last name'),
        ],

        email: [
            checkEmpty('E-mail address'),
            checkExists('email', 'e-mail address')
        ],
    })

    const user = new User()

    const handleSubmit = () => fields.getValidity()
        .then((valid) => (valid && user
            .setAttribute('firstName', fields.get('firstName'))
            .setAttribute('lastName', fields.get('lastName'))
            .setAttribute('email', fields.get('email'))
            .setAttribute('username', fields.get('username'))
            .create(fields.get('password'))
            .then(() => {
                const promise = apiRequest('POST', '/session', {
                    username: fields.get('username'),
                    password: fields.get('password'),
                })

                if (avatar !== null) {
                    promise
                        .then(() => uploadImage(avatar))
                        .then((res) => res.json())
                        .then(({ data }) => user.setAttribute('avatar', data))
                        .then((user) => user.update())
                }

                return promise
            })
            .then(() => setSession({ user, subscriptions: [] }))
            .then(() => history.replace('/dashboard'))
            .catch(console.log)
        ))

    return (
        <>
            <h1 className="mb-4"><BackButton />Sign Up</h1>

            <Form noValidate>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>First Name</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('firstName')}
                            type="text"
                            placeholder="Enter first name"
                            onChange={fields.set('firstName')} />

                        {fields.isInvalid('firstName') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('firstName')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Last Name</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('lastName')}
                            type="text"
                            placeholder="Enter last name"
                            onChange={fields.set('lastName')} />

                        {fields.isInvalid('lastName') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('lastName')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>E-mail Address</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('email')}
                            type="text"
                            placeholder="Enter e-mail address"
                            onChange={fields.set('email')} />

                        {fields.isInvalid('email') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('email')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Username</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('username')}
                            type="text"
                            placeholder="Enter username"
                            onChange={fields.set('username')} />

                        {fields.isInvalid('username') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('username')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Password</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('password')}
                            type="password"
                            placeholder="Enter password"
                            onChange={fields.set('password')} />
                        
                        {fields.isInvalid('password') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('password')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Avatar</Form.Label>
                    <Col sm={10}>
                        <Form.File
                            custom
                            label="Upload an image"
                            onChange={({ target }) => setAvatar(target.files.item(0))} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Col sm={{ span: 10, offset: 2 }}>
                        <Button variant="primary" onClick={handleSubmit}>Sign Up</Button>
                    </Col>
                </Form.Group>
            </Form>
        </>
    )
}

export default SignUpPage