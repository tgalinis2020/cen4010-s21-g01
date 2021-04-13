import { useState, useRef } from 'react'

import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'

import apiRequest from '../utils/apiRequest'
import { faPlus } from '@fortawesome/free-solid-svg-icons'

function CommentForm({ post, session, onSubmitted }) {
    const [text, setText] = useState('')
    const inputRef = useRef(null)
    
    const submit = () => apiRequest('POST', '/comments', {
            type: 'comments',
            attributes: { text },
            relationships: {
                author: {
                    data: { type: 'users', id: session.user.id }
                },

                post: {
                    data: { type: 'posts', id: post.id }
                }
            }
        })
        .then((res) => res.json())
        .then((res) => res.data)
        .then(onSubmitted)
        .then(() => setText(''))
        .then(() => {
            inputRef.current.value = ''
        })
        .catch(console.log)

    return (
        <Form>
            <Form.Group>
                <Form.Control as="textarea" ref={inputRef} onChange={({ target }) => setText(target.value)}></Form.Control>
            </Form.Group>

            <Form.Group>
                <Button variant="primary" onClick={submit}>
                    <FontAwesomeIcon className="mr-2" icon={faPlus} />
                    Add Comment
                </Button>
            </Form.Group>
        </Form>
    )
}

export default CommentForm