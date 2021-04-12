import { useState } from 'react'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus } from '@fortawesome/free-solid-svg-icons'

import uploadImage from '../utils/uploadImage'
import Post from '../Models/Post'
import BackButton from '../components/BackButton'

// TODO:    Add form validation
//          Implement adding pets to posts, maybe with checkboxes
function CreatePostPage({ author, onPostCreated }) {
    const [title, setTitle] = useState('')
    const [file, setFile] = useState(null)
    const [text, setText] = useState('')
    const [tags, setTags] = useState('')
    
    const createPost = () => {
        if (file === null) {
            return
        }

        const tagList = tags
            .split(' ')
            .map(t => t.trim().toLowerCase())
        
        const makePostAndAddFields = (image) => (new Post())
            .setAttribute('image', image)
            .setAttribute('title', title)
            .setAttribute('text', text)
        
        uploadImage(file)
            .then(res => res.json())
            .then(res => res.data)
            .then(makePostAndAddFields)
            .then(post => post.create(author, tagList, []))
            .then(onPostCreated)
    }

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
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Image</Form.Label>
                    <Col sm={10}>
                        <Form.File
                            custom
                            label="Upload an image"
                            onChange={({ target }) => setFile(target.files.item(0))} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Caption</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            type="text"
                            placeholder="Enter post caption"
                            onChange={({ target }) => setText(target.value)} />
                    </Col>
                </Form.Group>

                <Form.Group as={Row}>
                    <Form.Label column sm={2}>Tags</Form.Label>

                    <Col sm={10}>
                        <Form.Control
                            type="text"
                            placeholder="Enter comma-separated tags"
                            onChange={({ target }) => setTags(target.value)} />
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