import { useState, useEffect } from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link,
    useHistory,
    useRouteMatch,
    Redirect
} from 'react-router-dom'

import Container from 'react-bootstrap/Container'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Card from 'react-bootstrap/Card'
import Table from 'react-bootstrap/Table'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import Nav from 'react-bootstrap/Nav'
import NavDropdown from 'react-bootstrap/NavDropdown'
import Navbar from 'react-bootstrap/Navbar'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faBone, faCog, faSignInAlt, faSignOutAlt, faUser, faUserCircle } from '@fortawesome/free-solid-svg-icons'

import apiRequest from './utils/apiRequest'
import formatDate from './utils/formatDate'
//import { session$ } from './state'

import ExplorePage from './pages/ExplorePage'
import PostPage from './pages/PostPage'
import SubscriptionsPage from './pages/SubscriptionsPage'
import FavoritesPage from './pages/FavoritesPage'
import SettingsPage from './pages/SettingsPage'
import SignInPage from './pages/SignInPage'
import SignUpPage from './pages/SignUpPage'
import CreatePostPage from './pages/CreatePost'

// This part of the navigation bar shows if the user is not logged in.
function Anonymous() {
    const history = useHistory()

    // Use history.push to programarically navigate to pages.
    return (
        <Nav className="ml-auto">
            <NavDropdown title={<FontAwesomeIcon icon={faUserCircle} size="2x" />}>
                <NavDropdown.Item onClick={() => history.push('/signin')}>
                    <FontAwesomeIcon className="mr-2" icon={faSignInAlt} /> Sign In
                </NavDropdown.Item>

                <NavDropdown.Item onClick={() => history.push('/signup')}>
                    <FontAwesomeIcon className="mr-2" icon={faUser} /> Sign Up
                </NavDropdown.Item>
            </NavDropdown>
        </Nav>
    )
}

function Authenticated({ user }) {
    const history = useHistory()
    const avatarStyle = {
        borderRadius: '50%',
        border: '1px solid #888',
        width: '48px',
        height: '48px'
    }

    const avatar = user.avatar
        ? <img style={avatarStyle} src={user.avatar} />
        : <FontAwesomeIcon icon={faUserCircle} />
    
    // Navigate to the explore page on logout
    const logout = () => apiRequest('DELETE', '/session')
        .then(() => history.push('/'))

    return (
        <Nav className="ml-auto">
            <NavDropdown title={avatar}>
                <NavDropdown.ItemText className="text-center">
                    {user.username}
                </NavDropdown.ItemText>

                <NavDropdown.Divider />

                <NavDropdown.Item as={Link} to="/settings">
                    <FontAwesomeIcon className="mr-2" icon={faCog} />Settings
                </NavDropdown.Item>

                <NavDropdown.Item onClick={logout}>
                    <FontAwesomeIcon className="mr-2" icon={faSignOutAlt} />Sign Out
                </NavDropdown.Item>
            </NavDropdown>
        </Nav>
    )
}

function DashboardNav() {
    const { url } = useRouteMatch()
    const history = useHistory()
    const prev = history.location.pathname.split('/').pop()
    const pages = ['explore', 'subscriptions', 'favorites']
    const [page, setPage] = useState(pages.includes(prev) ? prev : 'explore')

    const goToPage = (p) => () => {
        setPage(p)
        history.replace(`${url}/${p}`)
    }

    return (
        <ButtonGroup className="d-flex my-4">
            {pages.map((p, i) => (
                <Button
                    key={i}
                    variant={p === page ? 'primary' : 'secondary'}
                    onClick={goToPage(p)}>{`${p.charAt(0).toUpperCase()}${p.substr(1)}`}</Button>
            ))}
        </ButtonGroup>
    )
}

function Dashboard({ session }) {
    const { url } = useRouteMatch()

    return (
        <>
            {/*session ? <DashboardNav /> : null*/}

            <Switch>
                <Route path={`${url}/explore`}>
                    <ExplorePage session={session} />
                </Route>

                <Route path={`${url}/subscriptions`}>
                    <SubscriptionsPage />
                </Route>

                <Route path={`${url}/favorites`}>
                    <FavoritesPage />
                </Route>

                <Route path={`${url}/`}>
                    <Redirect to={`${url}/explore`} />
                </Route>
            </Switch>
        </>
    )
}

function Main({ title }) {
    const history = useHistory()

    // Component state is managed using the useState hook.
    // Use the setSession function to update the session; don't try to mutate
    // the session variable directly!
    /*
    const defaultUser = null;
    /*/
    const defaultUser = {
        id: '1',
        username: 'tgalinis2020',
        firstName: 'Thomas',
        lastName: 'Galinis',
        email: 'tgalinis2020@fau.edu',
        avatar: 'https://i.imgur.com/l3e1XuO.jpeg',
        pets: [],
    };
    //*/

    const [session, setSession] = useState(defaultUser)

    const goToPostPage = (post) => history.replace(`/post/${post.id}`)

    // useEffect hooks into this component's lifecycle. When it is loaded, it
    // runs the provided callback function. If another callback is provided
    // within the callback function, it will run it when the component is
    // unloaded. In other words, inner callback = setup, outer callback =
    // teardown. The second argument to useEffect is a list of objects and
    // functions in the scope of this component that are in use in the effect.
    //
    // In this case, we're listening to the session observable. If it's not
    // unsubscribed from when the component is not in use, a memory leak can
    // occur.
    /*
    useEffect(() => {
        const subs = [
            session$.subscribe(setSession)
        ]

        apiRequest('GET', '/session')
            .then(res => res.json())
            .then(res => res.data.uid)
            .then(uid => apiRequest('GET', `/users/${uid}`)
            .then(res => res.json())
            .then(res => new User(res.data)))
            .then(user => session$.next(user))
            .catch(err => console.log('Not logged in'))

        return () => subs.forEach(sub => sub.unsubscribe())
    }, [setSession])
    */

    // Since this app is served in a directory within the server, the basename
    // for all routes must be specified.
    return (
        <Router basename="/~cen4010_s21_g01">
            <Navbar className="mb-4" bg="dark" variant="dark" expand="lg">
                <Container>
                    <Navbar.Brand as={Link} to="/">
                        {title}<FontAwesomeIcon className="ml-2" icon={faBone} />
                    </Navbar.Brand>

                    <Navbar.Toggle aria-controls="main-nav" />
                    
                    <Navbar.Collapse id="main-nav">
                        {session ? <Authenticated user={session} /> : <Anonymous />}
                    </Navbar.Collapse>
                </Container>
            </Navbar>
            
            <Container>
                <Switch>
                    <Route path="/dashboard">
                        <Dashboard session={session} />
                    </Route>

                    <Route path="/post/:id">
                        <PostPage session={session} />
                    </Route>

                    <Route path="/post">
                        <CreatePostPage author={session} onPostCreated={goToPostPage} />
                    </Route>

                    <Route path="/signin">
                        <SignInPage onSignedIn={setSession} />
                    </Route>

                    <Route path="/signup">
                        <SignUpPage onSignedUp={setSession} />
                    </Route>

                    <Route path="/settings">
                        <SettingsPage />
                    </Route>

                    <Route exact path="/">
                        <Redirect to="/dashboard" />
                    </Route>
                </Switch>
            </Container>
        </Router>
    )
}

export default Main
