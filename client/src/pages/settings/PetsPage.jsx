import { useContext, useEffect, useState } from 'react'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import Media from 'react-bootstrap/Media'
import ListGroup from 'react-bootstrap/ListGroup'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPaw } from '@fortawesome/free-solid-svg-icons'

import apiRequest from '../../utils/apiRequest'
import uploadImage from '../../utils/uploadImage'
import SessionContext from '../../context/SessionContext'
import useValidators from '../../hooks/useValidators'

function PetsPage() {
    const [session] = useContext(SessionContext)
    const [avatar, setAvatar] = useState(null)
    const [pets, setPets] = useState([])

    const checkEmpty = (field) => (value) => Promise
        .resolve(value === '' ? `${field} cannot be empty.` : null)

    const checkNotExists = (value) => Promise
        .resolve(pets.includes(value) ? `You already have a pet named "${value}."` : null)
        
    const fields = useValidators({
        petName: [
            checkEmpty('Pet name'),
            checkNotExists,
        ]
    })

    const createResource = (image) => ({
        type: 'pets',

        attributes: {
            name: fields.get('petName'),
            avatar: image,
        },
        
        relationships: {
            owner: {
                data: { type: 'users', id: session.user.id }
            }
        }
    })

    const createPet = () => {
        const promise = Promise.resolve(null)

        if (avatar !== null) {
            promise
                .then(() => uploadImage(avatar))
                .then((res) => res.json())
                .then((res) => res.data)
        }

        return promise
            .then((img) => apiRequest('POST', '/pets', createResource(img)))
            .then((res) => res.json())
            .then((res) => res.data)
            .then(({ id, attributes }) =>
                setPets((pets) => ([...pets, {
                    id,
                    name: attributes.name,
                    avatar: attributes.avatar,
                }]))
            )
    }

    const handleAddPet = () => fields.getValidity()
        .then((valid) => (valid && createPet()))

    const getPets = () => apiRequest('GET', `/users/${session.user.id}/pets`)
        .then((res) => res.json())
        .then((res) => res.data)
        .then((items) => items.map(({ id, attributes }) => ({
            id,
            name: attributes.name,
            avatar: attributes.avatar
        })))

    useEffect(() => {
       getPets().then(setPets)
    }, [setPets])

    return (
        <>
            <Form noValidate>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Pet Name</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            isInvalid={fields.isInvalid('petName')}
                            type="text"
                            placeholder="Enter you pet's name"
                            onChange={fields.set('petName')} />
                        
                        {fields.isInvalid('petName') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('petName')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Pet Avatar</Form.Label>

                    <Col sm={10}>
                        <Form.File
                            custom
                            placeholder="Upload an image"
                            onChange={({ target }) => setAvatar(target.files.item(0))} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Col sm={{ span: 10, offset: 2 }}>
                        <Button variant="primary" onClick={handleAddPet}>Add Pet</Button>
                    </Col>
                </Form.Group>
            </Form>

            <hr />

            <h3>Pets</h3>

            {pets.length > 0 ? (
                <ListGroup>
                    {pets.map((pet, i) => (
                        <ListGroup.Item key={i}>
                            <Media>
                                {pet.avatar === null ? (
                                    <FontAwesomeIcon icon={faPaw} size="2x" className="d-block mr-3" />
                                ) : (
                                    <img
                                        style={{ width: '64px', height: '64px', borderRadius: '50%' }}
                                        src={pet.avatar}
                                        className="mr-3"
                                    />
                                )}
                                <Media.Body>
                                    {pet.name}

                                    {/*<Button className="d-block float-right" variant="success">Subscribe</Button>*/}
                                </Media.Body>
                            </Media>
                        </ListGroup.Item>
                    ))}
                </ListGroup>
            ) : (
                <p>You have no pets!</p>
            )}
        </>
    )
}

export default PetsPage