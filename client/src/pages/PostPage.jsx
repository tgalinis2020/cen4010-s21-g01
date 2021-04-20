import { useState, useEffect, useContext } from 'react'
import { useParams } from 'react-router-dom'

import Card from 'react-bootstrap/Card'
import ListGroup from 'react-bootstrap/ListGroup'
import Media from 'react-bootstrap/Media'
import Button from 'react-bootstrap/Button'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPaw, faUserCircle, faSpinner } from '@fortawesome/free-solid-svg-icons'

import CommentForm from '../components/CommentForm'
import BackButton from '../components/BackButton'
import Comment from '../components/Comment'
import SessionContext from '../context/SessionContext'
import getComments from '../utils/getComments'
import getPost from '../utils/getPost'
import apiRequest from '../utils/apiRequest'

function PostPage() {
    const [session, setSession] = useContext(SessionContext)
    const [post, setPost] = useState(null)
    const [comments, setComments] = useState([])
    const { id } = useParams()

    const avatarStyle = {
        width: '64px',
        height: '64px',
        borderRadius: '50%',
        border: '1px solid #ccc',
    }

    const petResource = (id) => [{ type: 'pets', id }]

    const subToPet = (petId) => () => {
        apiRequest('POST', `/users/${session.user.id}/relationships/subscriptions`, petResource(petId))
            .then((res) => {
                if (res.status !== 204) {
                    console.error('Subscription was not created! Bug Tom about this! >:(')
                }

                setSession((sess) => ({
                    ...sess,
                    subscriptions: sess.subscriptions.concat([petId])
                }))
            })
    }

    const unsubFromPet = (petId) => () => {
        apiRequest('DELETE', `/users/${session.user.id}/relationships/subscriptions`, petResource(petId))
            .then((res) => {
                if (res.status !== 204) {
                    console.error('Subscription was not removed! Bug Tom about this! >:(')
                }

                setSession((sess) => ({
                    ...sess,
                    subscriptions: sess.subscriptions.filter((p) => p !== petId)
                }))
            })
    }

    useEffect(() => {
        getPost(id)
            .then(setPost)
            .then(() => getComments(id))
            .then(setComments)
    }, [])

    return (
        <>
            {post ? (
                <>
                    <h1><BackButton />{post.title}</h1>

                    <Card className="my-4">
                        <Card.Img src={post.image} />

                        <Card.Body>
                            <Card.Text>
                                <Media className="mb-4">
                                    {post.author.avatar ? (
                                        <img style={avatarStyle} className="mr-3" src={post.author.avatar} alt={`${post.author.username}'s profile picture`} />
                                    ) : (
                                        <FontAwesomeIcon className="mr-3" size="4x" icon={faUserCircle} />
                                    )}

                                    <Media.Body>
                                        <small className="text-muted">Posted by {post.author.username} on {post.createdAt}</small>
                                        
                                        <p>{post.text}</p>
                                    </Media.Body>
                                </Media>
                                
                                {post.pets.length > 0 && (
                                    <div className="my-3">
                                        <p className="text-muted">{post.author.username}'s pets in this post:</p>

                                        <ListGroup>
                                            {post.pets.map((pet, i) => (
                                                <ListGroup.Item key={i}>
                                                    <Media>
                                                        {pet.avatar === null ? (
                                                            <FontAwesomeIcon icon={faPaw} size="4x" className="d-block mr-3" />
                                                        ) : (
                                                            <img
                                                                style={{ width: '64px', height: '64px', borderRadius: '50%' }}
                                                                src={pet.avatar}
                                                                className="mr-3"
                                                            />
                                                        )}

                                                        <Media.Body className="d-flex align-self-center">
                                                            {pet.name}

                                                            {session.subscriptions.includes(pet.id) ? (
                                                                <Button
                                                                    className="ml-auto"
                                                                    variant="danger"
                                                                    onClick={unsubFromPet(pet.id)}
                                                                >
                                                                    Unsubscribe
                                                                </Button>
                                                            ) : (
                                                                <Button
                                                                    className="ml-auto"
                                                                    variant="success"
                                                                    onClick={subToPet(pet.id)}
                                                                >
                                                                    Subscribe
                                                                </Button>
                                                            )}
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