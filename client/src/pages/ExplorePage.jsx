import { useEffect, useState } from 'react'

import { Link, useHistory } from 'react-router-dom'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Card from 'react-bootstrap/Card'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus } from '@fortawesome/free-solid-svg-icons'

import formatDate from '../utils/formatDate'
import apiRequest from '../utils/apiRequest'
import getPosts from '../utils/getPosts'

function ExplorePage({ session }) {
    const [posts, setPosts] = useState([])
    const [moreAvailable, setMoreAvailable] = useState(true)
    const history = useHistory()

    const addPosts = (newPosts) => setPosts((oldPosts) => oldPosts.concat(newPosts))

    const goToCreatePost = () => history.push('/post')
    
    const seeMorePosts = () => {}
    
    useEffect(() => {
        /*getPosts()
            .then((posts) => {
                if (posts.length === 0) {
                    setMoreAvailable(false)
                }

                return posts
            })
            .then(addPosts)*/
        const item = {
            id: '1',
            image: 'https://i.imgur.com/uDCyg1E.jpeg',
            title: 'Doggo',
            text: 'Cute doggy :)',
            createdAt: formatDate('2021-04-11 12:30:00'),

            // Posts MUST have an author so it should be safe to assume
            // that the find method returns a resource object of
            // type "users".
            author: 'tgalinis2020',
            tags: ['cute', 'dog', 'aww'],
        }

        const items = []

        for (let i = 0; i < 12; ++i) {
            items.push(item)
        }

        setPosts(items)
    }, [setPosts])

    return (
        <>
            {session && (
                <ButtonGroup className="my-2">
                    <Button className="ml-auto" onClick={goToCreatePost}>
                        <FontAwesomeIcon className="mr-2" icon={faPlus} /> Create Post
                    </Button>
                </ButtonGroup>
            )}

            <Row>
                {posts.map(({ id, image, title, author, text, createdAt, tags }, i) => (
                    <Col key={i} xs={12} sm={6} md={4}>
                        <Card className="my-4">
                            <Link to={`/post/${id}`}>
                                <Card.Img src={image} />
                            </Link>

                            <Card.Body>
                                <Card.Title>{title}</Card.Title>

                                <Card.Text>
                                    <small className="text-muted">Posted by {author} on {createdAt}</small>
                                    <p>{text}</p>
                                    <p className="text-muted">Tags: {tags.map(t => `#${t}`).join(' ')}</p>
                                </Card.Text>
                            </Card.Body>
                        </Card>
                    </Col>
                ))}
            </Row>

            {moreAvailable && (
                <ButtonGroup className="d-block text-center my-4">
                    <Button onClick={seeMorePosts}>See More</Button>
                </ButtonGroup>
            )}
        </>
    )
}

export default ExplorePage