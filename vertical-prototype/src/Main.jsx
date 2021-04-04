import { useState, useEffect } from 'react'

import Container from 'react-bootstrap/Container'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Card from 'react-bootstrap/Card'
import Table from 'react-bootstrap/Table'
import Button from 'react-bootstrap/Button'
//import Form from 'react-bootstrap/Form'
import ButtonGroup from 'react-bootstrap/esm/ButtonGroup'

import Session from './Session'
import RegistrationForm from './RegistrationForm'
import PostForm from './PostForm'
import LoginForm from './LoginForm'

import User from './Models/User'

import apiRequest from './utils/apiRequest'
//import uploadImage from './utils/uploadImage'
import formatDate from './utils/formatDate'

function Main() {
    //const [file, setFile] = useState(null)
    const [users, setUsers] = useState([])
    const [posts, setPosts] = useState([])
    //const [imageUrl, setImageUrl] = useState(null)
    const [session, setSessionUser] = useState(null)

    //const handleFileChanged = event => setFile(event.target.files.item(0))
    const handleLoginError = code => window.alert(`Can't log in! (error: ${code})`)

    /*
    const submitFile = () => uploadImage(file)
        .then(res => res.json())
        .then(res => res.data)
        .then(setImageUrl)
        .catch(err => window.alert(`Unable to upload the image. ${session === null ? 'You are not signed in!' : 'Go bug Tom about this.'}`))
    */

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
                
                const related = {
                    tags: relationships.tags.data.map(obj => obj.id),
                    author: relationships.author.data.id
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

            <Button className="mb-4" onClick={getUsers}>Refresh</Button>

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
                <Session user={session} /> :
                <LoginForm
                    onSuccess={setSessionUser}
                    onError={handleLoginError} />
            }

            <p>Don't have an account? Create one!</p>

            <RegistrationForm
                onRegistered={setSessionUser}
                onError={console.error} />

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
                                    <small className="text-muted">Posted By {posts.author} on {posts.createdAt}</small>
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