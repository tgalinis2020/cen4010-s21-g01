import { useState, useEffect } from 'react'
import { useHistory, useParams, useRouteMatch } from 'react-router-dom'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Card from 'react-bootstrap/Card'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'
import FormGroup from 'react-bootstrap/esm/FormGroup'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHeart, faPlus } from '@fortawesome/free-solid-svg-icons'

import formatDate from '../utils/formatDate'
import apiRequest from '../utils/apiRequest'

import CommentForm from '../components/CommentForm'
import BackButton from '../components/BackButton'
import Comment from '../components/Comment'

function PostPage({ session }) {
    const { id } = useParams()
    
    /*
    const defaultPost = null
    const defaultComments = null
    /*/
    const defaultPost = {
        id: '1',
        image: 'https://i.imgur.com/uDCyg1E.jpeg',
        title: 'Doggo',
        text: 'Cute doggy :)',
        createdAt: formatDate('2021-04-11 12:30:00'),
        author: 'tgalinis2020',
        tags: ['cute', 'dog', 'aww'],
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
    const getPost = () => apiRequest('GET', `/posts/${id}?include=author,tags&fields[users]=username`)
        .then((res) => res.json())
        .then((res) => res.data)
        .then(({ data, included }) => {
            const { id, attributes, relationships } = data
            const { image, title, text, createdAt } = attributes
                
            // TODO:    Pets and likes should be here as well but they are
            //          not represented in the front-end yet.
            // Note that some relationships might not be available, such as
            // tags.
            const related = {
                author: relationships.author.data.id,
                tags: 'tags' in relationships ? relationships.tags.data.map(({ id }) => id) : [],
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
                    .map(tag => `#${tag.attributes.text}`),
            }
        })
        .then(setPost)

    //useEffect(() => getPost().then(getComments), [setPost, setComments])

    // TODO:    Add comment pagination, currently limited to 9999 comments.
    //          For our use case, this might suffice but it's a quick-and-dirty
    //          solution.
    const getComments = () => apiRequest('GET', `/posts/${id}/comments?include=author&fields[users]=username&page[size]=9999&sort=-createdAt`)
        .then((res) => res.json())
        .then((res) => res.data)
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

    return (
        <>
            <h1><BackButton />{post.title}</h1>

            <Card className="my-4">
                <Card.Img src={post.image} />

                <Card.Body>
                    <Card.Text>
                        <small className="text-muted">Posted by {post.author} on {post.createdAt}</small>
                        <p>{post.text}</p>
                        <p className="text-muted">Tags: {post.tags.map(t => `#${t}`).join(' ')}</p>
                    </Card.Text>
                </Card.Body>
            </Card>

            {session && <CommentForm author={session} post={post} onSubmitted={getComments} />}

            <hr />

            <h3 className="mb-4">Comments ({comments.length})</h3>

            {comments.map(({ text, createdAt, author }, i) => (
                <Comment key={i} text={text} createdAt={createdAt} author={author} />
            ))}
        </>
    )
}

export default PostPage