import { useState } from 'react'
import { useHistory } from 'react-router-dom'

import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'

import User from '../Models/User'

import apiRequest from '../utils/apiRequest'
import uploadImage from '../utils/uploadImage'
import debounce from '../utils/debouce'

import BackButton from '../components/BackButton'

function SignUpPage({ onSignedUp }) {
    const [fields, setFields] = useState({
        username:  { value: '', dirty: false, error: null },
        firstName: { value: '', dirty: false, error: null },
        lastName:  { value: '', dirty: false, error: null },
        password:  { value: '', dirty: false, error: null },
        email:     { value: '', dirty: false, error: null },
    })

    const [avatar, setAvatar] = useState(null)
    const history = useHistory()

    const user = new User()
    
    const setField = (field, value) => setFields(state => ({ ...state, [field]: value }))

    const checkEmpty = (field) => (value) => Promise
        .resolve(value === '' ? `${field} cannot be empty.` : null)

    const checkExists = (field, label = false) => (value) => apiRequest('GET', `/users?filter[${field}]=${value}`)
        .then(res => res.json())
        .then(({ data }) => typeof data.pop() === 'undefined' ? null : `Provided ${field || label} is already in use.`)

    const validators = {
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
            checkExists('email')
        ],
    }

    const setAndValidate = (field) => debounce(
        500,

        // Go through each validation function and stop the promise chain
        // when an error is not null.
        //
        // Note: a === accumilated promise
        //       c === current promise
        //       e === error message
        ({ target }) => validators[field]
            .reduce((a, c) => a.then(e => e ?? c(target.value)), Promise.resolve(null))
            .then((error) => {
                setField(field, { value: target.value, dirty: true, error })

                return error === null
            })
            .catch((error) => {
                setField(field, { value: target.value, dirty: true, error: 'Invalid field.' })
                
                return false
            }),

        ({ key, target }) => key === 'Enter' || (key === 'Backspace' && target.value === '')
    )

    const handleSubmit = () => {

        // Validate the fields before submitting the form!
        // TODO:    This is a quick-n-dirty copy paste from above.
        //          Refactor when possible.
        const promises = Object.keys(fields).map(field => (
            validators[field]
                .reduce((a, c) => a.then(e => e ?? c(fields[field].value)), Promise.resolve(null))
                .then((error) => {
                    setField(field, { value: fields[field].value, dirty: true, error })

                    return error === null
                })
                .catch((error) => {
                    setField(field, { value: fields[field].value, dirty: true, error: 'Invalid field.' })

                    return false
                })
        ))

        // For all validation functions...
        Promise.all(promises)
            // ...reduce them to a single boolean representing the form's state.
            .then((results) => results.reduce((acc, curr) => acc && curr, true))
            .then((valid) => (valid && user
                .setAttribute('firstName', fields.firstName.value)
                .setAttribute('lastName', fields.lastName.value)
                .setAttribute('email', fields.email.value)
                .setAttribute('username', fields.username.value)
                .create(fields.password.value)
                .then(() => {
                    const promise = apiRequest('POST', '/session', {
                        username: fields.username.value,
                        password: fields.password.value
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
                .then(onSignedUp)
                .then(() => history.replace('/dashboard'))
                .catch(console.log)
            ))
    }

    return (
        <>
            <h1 className="mb-4"><BackButton />Sign Up</h1>

            <Form noValidate>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>First Name</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.firstName.dirty && fields.firstName.error !== null}
                            type="text"
                            placeholder="Enter first name"
                            onChange={setAndValidate('firstName')} />

                        {fields.firstName.error && (
                            <Form.Control.Feedback type="invalid">
                                {fields.firstName.error}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Last Name</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.lastName.dirty && fields.lastName.error !== null}
                            type="text"
                            placeholder="Enter last name"
                            onChange={setAndValidate('lastName')} />

                        {fields.lastName.error && (
                            <Form.Control.Feedback type="invalid">
                                {fields.lastName.error}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>E-mail Address</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.email.dirty && fields.email.error !== null}
                            type="text"
                            placeholder="Enter e-mail address"
                            onChange={setAndValidate('email')} />

                        {fields.email.error && (
                            <Form.Control.Feedback type="invalid">
                                {fields.email.error}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Username</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.username.dirty && fields.username.error !== null}
                            type="text"
                            placeholder="Enter username"
                            onChange={setAndValidate('username')} />

                        {fields.username.error && (
                            <Form.Control.Feedback type="invalid">
                                {fields.username.error}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Password</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.password.dirty && fields.password.error !== null}
                            type="password"
                            placeholder="Enter password"
                            onChange={setAndValidate('password')} />
                        
                        {fields.password.error && (
                            <Form.Control.Feedback type="invalid">
                                {fields.password.error}
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