import { useContext, useEffect, useState } from 'react'
import { useHistory } from 'react-router'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus, faSpinner } from '@fortawesome/free-solid-svg-icons'

import uploadImage from '../utils/uploadImage'
import Post from '../Models/Post'
import BackButton from '../components/BackButton'
import SessionContext from '../context/SessionContext'
import useValidators from '../hooks/useValidators'
import getPets from '../utils/getPets'


function CreatePostPage() {
    const [file, setFile] = useState(null)
    const [pets, setPets] = useState([])
    const [loading, setLoading] = useState(true)
    const [session] = useContext(SessionContext)
    const history = useHistory()

    const minChars = (n) => (value) => Promise
        .resolve(value.length < n ? `Post title must be ${n} or more characters long.` : null)

    const maxChars = (n) => (value) => Promise
        .resolve(value.length > n ? `Post title length cannot exceed ${n} characters.` : null)

    const lettersOnly = (value) => Promise
        .resolve(/^[A-Za-z ]*$/.test(value) ? null : 'Each tag must be a word separated by a space.')

    const fields = useValidators({
        title: [
            minChars(10),
            maxChars(35),
        ],

        text: [],

        tags: [
            lettersOnly,
        ]
    })

    useEffect(() => {

        getPets(session.user.id).then((pets) => {

            setLoading(false)

            setPets(pets.map((pet) => ({ ...pet, isChecked: false })))
        
        })

    }, [])
    
    const createPost = () => {
        if (file === null) {
            window.alert('Posts need an image!')
            return
        }

        const tagList = fields.get('tags').split(' ')
            .map(t => t.trim().toLowerCase())
            .filter(t => t.length > 0)

        const petList = pets
            .filter(({ isChecked }) => isChecked)
            .map(({ id }) => id)
        
        const makePostAndAddFields = (image) => (new Post())
            .setAttribute('image', image)
            .setAttribute('title', fields.get('title'))
            .setAttribute('text', fields.get('text'))
        
        return uploadImage(file)
            .then((res) => res.json())
            .then((res) => res.data)
            .then(makePostAndAddFields)
            .then((post) => post.create(session.user, tagList, petList))
            .then((post) => history.replace(`/post/${post.id}`))
    }

    const handlePetChange = (index) => () => setPets((items) => {
        items[index].isChecked = !items[index].isChecked

        return items
    })

    const handleSubmit = () => fields.getValidity()
        .then((valid) => valid && createPost())

    return (
        <>
            <h1 className="mb-4"><BackButton />Create Post</h1>

            <Form>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Title</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            type="text"
                            isInvalid={fields.isInvalid('title')}
                            placeholder="Enter post title"
                            onChange={fields.set('title')} />

                        {fields.isInvalid('title') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('title')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Image</Form.Label>
                    <Col sm={10}>
                        <Form.File
                            custom
                            label={file ? file.name : 'Upload in image'}
                            onChange={({ target }) => setFile(target.files.item(0))} />

                        <Form.Control.Feedback type="invalid">
                            A post image is required.
                        </Form.Control.Feedback>
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Caption</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            as="textarea"
                            isInvalid={fields.isInvalid('text')}
                            placeholder="Enter post caption"
                            onChange={fields.set('text')} />

                        {fields.isInvalid('text') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('text')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Tags</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            type="text"
                            isInvalid={fields.isInvalid('tags')}
                            placeholder="Enter tags separated by a space"
                            onChange={fields.set('tags')} />

                        {fields.isInvalid('tags') && (
                            <Form.Control.Feedback type="invalid">
                                {fields.getError('tags')}
                            </Form.Control.Feedback>
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Pets in this post</Form.Label>

                    <Col sm={10}>
                        {loading && (
                            <FontAwesomeIcon className="my-3" icon={faSpinner} pulse />
                        )}

                        {!loading && pets.map((pet, i) => (
                            <Form.Check
                                key={i}
                                type="checkbox"
                                label={pet.name}
                                defaultChecked={pet.isChecked}
                                onChange={handlePetChange(i)} />
                        ))}

                        {!loading && pets.length === 0 && (
                            <Form.Control plaintext readOnly defaultValue="You have no pets!" />
                        )}
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Col sm={{ span: 10, offset: 2 }}>
                        <Button variant="primary" onClick={handleSubmit}>
                            <FontAwesomeIcon icon={faPlus} className="mr-2" />
                            Create Post
                        </Button>
                    </Col>
                </Form.Group>
            </Form>
        </>
    )
}

export default CreatePostPage