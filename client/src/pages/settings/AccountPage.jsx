import { useContext, useState } from 'react'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'

import debounce from '../../utils/debouce'
import apiRequest from '../../utils/apiRequest'
import uploadImage from '../../utils/uploadImage'
import useValidators from '../../hooks/useValidators'
import SessionContext from '../../context/SessionContext'

function AccountPage() {
    const [session] = useContext(SessionContext)
    const [avatar, setAvatar] = useState(null)

    const checkEmpty = (field) => (value) => Promise
        .resolve(value === '' ? `${field} cannot be empty.` : null)

    //const checkMatchesPassword = (value, getValue) => Promise
    //    .resolve(value === getValue('password') ? 'Passwords should not match' : null)

    const fields = useValidators({
        password: [
            checkEmpty('Password'),
        ],

        newPassword: [
            checkEmpty('New password'),
            //checkMatchesPassword,
        ],
    })

    const updatePassword = () => session.user
        .updatePassword(fields.get('password'), fields.get('newPassword'))
        .then((res) => {
            window.alert(res.code === 204
                ? 'Password updated!'
                : 'An error occured while attempting to update your password.'
            )
        })

    const updateAvatar = () => uploadImage(avatar)
        .then((res) => res.json())
        .then((res) => res.data)
        .then((url) => session.user.setAttribute('avatar', url))
        .then((user) => user.update())
        .then(() => window.alert('Avatar updated!'))

    return (
        <>
            <Form noValidate>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Current Password</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('password')}
                            type="password"
                            placeholder="Current password"
                            onChange={fields.set('password')} />
                        
                        {fields.isInvalid('password') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('password')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>New Password</Form.Label>
                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('newPassword')}
                            type="password"
                            placeholder="New password"
                            onChange={fields.set('newPassword')} />
                        
                        {fields.isInvalid('newPassword') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('newPassword')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Col sm={{ span: 10, offset: 2 }}>
                        <Button variant="primary" onClick={updatePassword}>Update Password</Button>
                    </Col>
                </Form.Group>
            </Form>

            <hr />

            <Form noValidate>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Avatar</Form.Label>
                    <Col sm={10}>
                        <Form.File
                            custom
                            label={avatar ? avatar.name : 'Upload an image'}
                            onChange={({ target }) => setAvatar(target.files.item(0))} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Col sm={{ span: 10, offset: 2 }}>
                        <Button variant="primary" onClick={updateAvatar}>Update Avatar</Button>
                    </Col>
                </Form.Group>
            </Form>
        </>
    )
}

export default AccountPage