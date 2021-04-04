import { useState } from 'react'

import ButtonGroup from 'react-bootstrap/ButtonGroup'
import Button from 'react-bootstrap/Button'
import Form from 'react-bootstrap/Form'

import Post from './Models/Post'
import uploadImage from './utils/uploadImage'


function PostForm({ user, onPostCreated }) {
    const [title, setTitle] = useState('')
    const [file, setFile] = useState(null)
    const [text, setText] = useState('')
    const [tags, setTags] = useState('')
    
    const createPost = () => {
        if (file === null) {
            return
        }

        const tagList = tags
            .split(',')
            .map(t => t.trim().toLowerCase())
        
        uploadImage(file)
            .then(res => res.json())
            .then(res => res.data)
            .then(image => new Post({
                attributes: { title, text, image }
            }))
            .then(post => post.create(user, tagList, []))
            .then(onPostCreated)
    }

    return (
        <Form>
            <Form.Group>
                <Form.Label>Title</Form.Label>
                <Form.Control
                    type="text"
                    placeholder="Enter post title"
                    onChange={({ target }) => setTitle(target.value)} />
            </Form.Group>

            <Form.Group>
                <Form.Label>Image</Form.Label>
                <Form.File
                    custom
                    label="Upload an image"
                    onChange={({ target }) => setFile(target.files.item(0))} />
            </Form.Group>

            <Form.Group>
                <Form.Label>Text</Form.Label>
                <Form.Control
                    type="text"
                    placeholder="Enter post text"
                    onChange={({ target }) => setText(target.value)} />
            </Form.Group>

            <Form.Group>
                <Form.Label>Tags</Form.Label>
                <Form.Control
                    type="text"
                    placeholder="Enter commea-separated tags"
                    onChange={({ target })=> setTags(target.value)} />
            </Form.Group>

            <ButtonGroup>
                <Button onClick={createPost}>Create</Button>
            </ButtonGroup>
        </Form>
    )
}

export default PostForm