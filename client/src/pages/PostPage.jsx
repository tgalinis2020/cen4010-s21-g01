import { useState, useEffect, useContext } from 'react'
import { useHistory, useParams, useRouteMatch } from 'react-router-dom'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Card from 'react-bootstrap/Card'
import ListGroup from 'react-bootstrap/ListGroup'
import Media from 'react-bootstrap/Media'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import FormGroup from 'react-bootstrap/esm/FormGroup'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHeart, faPaw, faPlus, faSpinner } from '@fortawesome/free-solid-svg-icons'

import formatDate from '../utils/formatDate'
import apiRequest from '../utils/apiRequest'

import CommentForm from '../components/CommentForm'
import BackButton from '../components/BackButton'
import Comment from '../components/Comment'
import SessionContext from '../context/SessionContext'

function PostPage() {
    const [session] = useContext(SessionContext)
    const { id } = useParams()
    
    //*
    const defaultPost = null
    const defaultComments = []
    /*/
    const defaultPost = {
        id: '1',
        image: 'https://i.imgur.com/uDCyg1E.jpeg',
        title: 'Doggo',
        text: 'Cute doggy :)',
        createdAt: formatDate('2021-04-11 12:30:00'),
        author: 'tgalinis2020',
        tags: ['cute', 'dog', 'aww'],
        pets: [{ id: '1', name: 'Mr. Meow', avatar: null }, { id: '2', name: 'Bean', avatar: null }],
    }

    const defaultComments = [
        {
            text: 'Aww, such a good boi!',
            createdAt: formatDate('2021-04-12 06:00:23'),
            author: 'blahblah123',
        },

        {
            text: 'Precious.',
            createdAt: formatDate('2021-04-12 08:21:42'),
            author: 'ilovepets99',
        }
    ]
    //*/
    const [post, setPost] = useState(defaultPost)
    const [comments, setComments] = useState(defaultComments)
    //const [commentsAvailable, setCommentsAvailable] = useState(true)

    // Since included data cannot be paginated and sorted, two separate requests
    // are made for getting posts and comments.
    const getPost = () => apiRequest('GET', `/posts/${id}?include=author,tags,pets&fields[users]=username`)
        .then((res) => res.json())
        .then(({ data, included }) => {
            const { id, attributes, relationships } = data
            const { image, title, text, createdAt } = attributes
            
            const related = {
                author: relationships.author.data.id,
            }

            // Pluck the IDs from to-many relationships, if applicable.
            for (const key of ['tags', 'pets', 'likes']) {
                related[key] = key in relationships ? relationships[key].data.map(({ id }) => id) : []
            }

            return {
                id,
                image,
                title,
                text,
                createdAt: formatDate(createdAt),

                author: included
                    .find(obj => obj.type === 'users' && obj.id === related.author)
                    .attributes
                    .username,

                tags: included
                    .filter(obj => obj.type === 'tags' && related.tags.includes(obj.id))
                    .map(({ attributes }) => attributes.text),

                pets: included
                    .filter((obj) => obj.type === 'pets' && related.pets.includes(obj.id)),
            }
        })
        .then(setPost)

    const getComments = () => apiRequest('GET', `/posts/${id}/comments?include=author&fields[users]=username,avatar&sort=-createdAt`)
        .then((res) => res.json())
        .then(({ data, included }) => data.map(({ id, attributes, relationships }) => {
            const author = included
                .find(user => user.id === relationships.author.data.id)

            return {
                id,
                text: attributes.text,
                createdAt: attributes.createdAt,
                author: author.attributes.username
            }
        }))
        .then(setComments)

    useEffect(() => getPost().then(getComments), [setPost, setComments])

    return (
        <>
            {post ? (
                <>
                    <h1><BackButton />{post.title}</h1>

                    <Card className="my-4">
                        <Card.Img src={post.image} />

                        <Card.Body>
                            <Card.Text>
                                <small className="text-muted">Posted by {post.author} on {post.createdAt}</small>
                                
                                <p>{post.text}</p>
                                
                                {post.pets.length > 0 && (
                                    <div className="my-3">
                                        <p className="text-muted">{post.author}'s pets in this post:</p>

                                        <ListGroup>
                                            {post.pets.map((pet, i) => (
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
                                    </div>
                                )}

                                {post.tags.length > 0 && (
                                    <p className="text-muted">Tags: {post.tags.join(', ')}</p>
                                )}
                            </Card.Text>
                        </Card.Body>
                    </Card>

                    
                    {session && <CommentForm session={session} post={post} onSubmitted={getComments} />}

                    <hr />

                    <h3 className="mb-4">Comments ({comments.length})</h3>

                    {comments.length > 0 ? (
                        comments.map((comment, i) => <Comment key={i} {...comment} />)
                    ) : (
                        <p>No comments available.</p>
                    )}
                </>
            ) : (
                <p className="text-center my-4"><FontAwesomeIcon icon={faSpinner} size="3x" pulse /></p>
            )}
        </>
    )
}

export default PostPage