import { useEffect, useState, useContext } from 'react'

import { Link } from 'react-router-dom'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Card from 'react-bootstrap/Card'

import SessionContext from '../../context/SessionContext'
import getPosts from '../../utils/getPosts'
import debounce from '../../utils/debouce'

// TODO:    Most of this is pretty much copy-paste from the Explore page.
//          Could put shared logic in its own self-contained component.
function SubscriptionsPage() {
    const [session] = useContext(SessionContext)
    const [posts, setPosts] = useState([])
    const [searchMode, setSearchMode] = useState(false)
    const subsFilter = `filter[pets][in]=${session.subscriptions.join(',')}`

    const handleSearch = debounce(
        1000, // Wait for one second before doing anything.

        ({ target }) => {

            if (target.value === '') {

                if (searchMode) {    

                    setSearchMode(false)

                    getPosts(null, [subsFilter]).then(setPosts)

                }

            } else {

                const tagFilter = `filter[tags.text][in]=${target.value.split(' ').join(',')}`

                setSearchMode(true)

                getPosts(null, [subsFilter, tagFilter]).then(setPosts)

            }

        },

        ({ key, target }) => key === 'Enter' || (key === 'Backspace' && target.value === '')
    )
    
    useEffect(() => {
        getPosts(null, [subsFilter])
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
                <p>You have no subscriptions!</p>
            )}
        </>
    )
}

export default SubscriptionsPage