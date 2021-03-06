import { useState, useEffect } from 'react'

import Container from 'react-bootstrap/Container'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Card from 'react-bootstrap/Card'
import Table from 'react-bootstrap/Table'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/esm/ButtonGroup'

import Session from './Session'
import RegistrationForm from './RegistrationForm'
import PostForm from './PostForm'
import LoginForm from './LoginForm'

import User from './Models/User'

import apiRequest from './utils/apiRequest'
import formatDate from './utils/formatDate'

function Main() {
    const [users, setUsers] = useState([])
    const [posts, setPosts] = useState([])
    const [session, setSessionUser] = useState(null)

    const handleLoginError = code => window.alert(`Can't log in! (error: ${code})`)

    const getUsers = () => apiRequest('GET', '/users')
        .then(res => res.json())
        .then(res => res.data)
        .then(users => users.map(user => new User(user)))
        .then(setUsers)

    const getPosts = () => apiRequest('GET', '/posts?include=author,tags&fields[users]=username')
        .then(res => res.json())
        .then(({ data, included }) => {
            const items = []

            for (const post of data) {
                const { attributes, relationships } = post
                
                // TODO:    Pets and likes should be here as well but they are
                //          not represented in the front-end yet.
                // Note that some relationships might not be available, such as
                // tags.
                const related = {
                    author: relationships.author.data.id,
                    tags: 'tags' in relationships ? relationships.tags.data.map(obj => obj.id) : [],
                }

                items.push({
                    image: attributes.image,
                    title: attributes.title,
                    text: attributes.text,
                    createdAt: formatDate(attributes.createdAt),

                    // Posts MUST have an author so it should be safe to assume
                    // that the find method returns a resource object of
                    // type "users".
                    author: included
                        .find(obj => obj.type === 'users' && obj.id === related.author)
                        .attributes
                        .username,

                    tags: included
                        .filter(obj => obj.type === 'tags' && related.tags.includes(obj.id))
                        .map(tag => `#${tag.attributes.text}`),
                })
            }

            return items
        })
        .then(setPosts)
    
    // Load users and posts, check session status when component loads.
    // The useEffect React hook follows the lifecyle of this component.
    // The arrow function passed inside runs when the component is completely
    // loaded. Optionally, it can return a cleanup function that runs
    // when the component is destroyed.
    //
    // The second argument of useEffect is the dependencies, i.e. anything
    // declared above in this component that is used in the hook.
    useEffect(() => {
        getUsers()
        getPosts()
        apiRequest('GET', '/session')
            .then(res => res.json())
            .then(res => res.data.uid)
            .then(uid => apiRequest('GET', `/users/${uid}`)
            .then(res => res.json())
            .then(res => new User(res.data)))
            .then(setSessionUser)
            .catch(err => console.log('Not logged in'))
    }, [setSessionUser])

    return (
        <Container>
            <h1>Users and Authentication</h1>

            <Button className="mb-4" onClick={() => getUsers()}>Refresh</Button>

            <Table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>E-mail Address</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Joined On</th>
                    </tr>
                </thead>

                <tbody>
                    {users.map((user, index) => (
                        <tr key={index}>
                            <td>{user.id}</td>
                            <td>{user.getAttribute('username')}</td>
                            <td>{user.getAttribute('email')}</td>
                            <td>{user.getAttribute('firstName')}</td>
                            <td>{user.getAttribute('lastName')}</td>
                            <td>{user.getAttribute('createdAt')}</td>
                        </tr>
                    ))}
                </tbody>
            </Table>

            {session ?
                <Session user={session} onLogout={() => setSessionUser(null)} /> :
                <>
                    <LoginForm
                        onSuccess={setSessionUser}
                        onError={handleLoginError} />
                    
                    <p>Don't have an account? Create one!</p>

                    <RegistrationForm
                        onRegistered={setSessionUser}
                        onError={console.error} />
                </>
            }

            <hr />

            <h1>Posts</h1>

            <ButtonGroup>
                <Button onClick={() => getPosts()}>Refresh</Button>
            </ButtonGroup>

            <Row>
                {posts.map(post => (
                    <Col xs={1} sm={2} md={3} lg={4}>
                        <Card>
                            <Card.Img src={post.image} />
                            <Card.Body>
                                <Card.Title>{post.title}</Card.Title>
                                <Card.Text>
                                    <small className="text-muted">Posted by {post.author} on {post.createdAt}</small>
                                    <p>{post.text}</p>
                                    <p className="text-muted">Tags: {post.tags.join(', ')}</p>
                                </Card.Text>
                            </Card.Body>
                        </Card>
                    </Col>
                ))}
            </Row>

            {session && <>
                <h3>Create Post</h3>

                <PostForm user={session} onPostCreated={() => getPosts()} />
            </>}
        </Container>
    )
}

export default Main