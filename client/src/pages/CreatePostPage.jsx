import { useEffect, useState } from 'react'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus, faSpinner } from '@fortawesome/free-solid-svg-icons'

import uploadImage from '../utils/uploadImage'
import Post from '../Models/Post'
import BackButton from '../components/BackButton'
import apiRequest from '../utils/apiRequest'

// TODO:    Add form validation
//          Implement adding pets to posts, maybe with checkboxes
function CreatePostPage({ author, onPostCreated }) {
    const [title, setTitle] = useState('')
    const [file, setFile] = useState(null)
    const [text, setText] = useState('')
    const [tags, setTags] = useState('')
    const [pets, setPets] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        apiRequest('GET', `/users/${author.id}/pets`)
            .then((res) => res.json())
            .then((res) => res.data)
            .then(({ data }) => {
                setLoading(false)

                setPets(data.map(({ id, attributes }) => ({
                    id: id,
                    image: attributes.image,
                    name: attributes.name,
                    isChecked: false
                })))
            })
        
        /*
        setPets([
            { id: '1', name: 'Bean', isChecked: true },
            { id: '2', name: 'Charlie', isChecked: false },
            { id: '3', name: 'Mr. Meow', isChecked: false },
        ])

        setLoading(false)
        */
    }, [setPets])
    
    const createPost = () => {
        if (file === null) {
            return
        }

        const tagList = tags.split(' ').map(t => t.trim().toLowerCase())
        const petList = pets.map(({ id }) => ({
            type: 'pets',
            id
        }))
        
        const makePostAndAddFields = (image) => (new Post())
            .setAttribute('image', image)
            .setAttribute('title', title)
            .setAttribute('text', text)
        
        uploadImage(file)
            .then(res => res.json())
            .then(res => res.data)
            .then(makePostAndAddFields)
            .then(post => post.create(author, tagList, petList))
            .then(onPostCreated)
    }

    const handlePetChange = (index) => () => setPets(items => {
        items[index].isChecked = !items[index].isChecked

        return items
    })

    return (
        <>
            <h1 className="mb-4"><BackButton />Create Post</h1>

            <Form>
                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Title</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            type="text"
                            placeholder="Enter post title"
                            onChange={({ target }) => setTitle(target.value)} />

                        <Form.Control.Feedback type="invalid">
                            Title must be 6 characters or longer.
                        </Form.Control.Feedback>
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Image</Form.Label>
                    <Col sm={10}>
                        <Form.File
                            custom
                            label="Upload an image"
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
                            placeholder="Enter post caption"
                            onChange={({ target }) => setText(target.value)} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Tags</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            type="text"
                            placeholder="Enter tags separated by a space"
                            onChange={({ target }) => setTags(target.value)} />
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
                        <Button variant="primary" onClick={createPost}>
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