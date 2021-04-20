import { useEffect, useState, useContext } from 'react'

import { Link, useHistory } from 'react-router-dom'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Card from 'react-bootstrap/Card'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlus } from '@fortawesome/free-solid-svg-icons'

import SessionContext from '../../context/SessionContext'
import getPosts from '../../utils/getPosts'
import debounce from '../../utils/debouce'

function ExplorePage() {
    const [session, setSession] = useContext(SessionContext)
    const [posts, setPosts] = useState([])
    const [searchMode, setSearchMode] = useState(false)
    const history = useHistory()

    const goToCreatePost = () => history.push('/post')

    const handleSearch = debounce(
        1000, // Wait for one second before doing anything.

        ({ target }) => {

            if (target.value === '') {

                if (searchMode) {    

                    setSearchMode(false)

                    getPosts().then(setPosts)

                }

            } else {

                const filterParam = `filter[tags.text][in]=${target.value.split(' ').join(',')}`

                setSearchMode(true)

                getPosts(null, [filterParam]).then(setPosts)

            }

        },

        ({ key, target }) => key === 'Enter' || (key === 'Backspace' && target.value === '')
    )
    
    useEffect(() => {
        getPosts(session === null ? 10 : null)
            .then(setPosts)
    }, [])

    return (
        <>
            {session && (
                <Form>
                    <Form.Group>
                        <Form.Control
                            type="text"
                            placeholder="Search for posts by tag..."
                            onChange={handleSearch} />
                    </Form.Group>

                    <ButtonGroup className="my-2">
                        <Button className="ml-auto" onClick={goToCreatePost}>
                            <FontAwesomeIcon className="mr-2" icon={faPlus} />
                            Create Post
                        </Button>
                    </ButtonGroup>
                </Form>
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
                                    
                                    {tags.length > 0 && (
                                        <p className="text-muted">Tags: {tags.join(', ')}</p>
                                    )}
                                </Card.Text>
                            </Card.Body>
                        </Card>
                    </Col>
                ))}
            </Row>

            {posts.length === 0 && (
                <p>There are currently no posts.</p>
            )}

            {session === null && (
                <div className="my-4 text-center">
                    <p>You must be logged in to see more posts. <Button variant="primary" onClick={() => history.push('/signin')}>Sign in</Button></p>
                    <p>Don't have an account? <Button variant="primary" onClick={() => history.push('/signup')}>Sign up</Button></p>
                </div>
            )}
        </>
    )
}

export default ExplorePage