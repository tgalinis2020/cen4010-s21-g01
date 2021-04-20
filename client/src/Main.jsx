import { useState, useEffect, useContext } from 'react'
import {
    HashRouter as Router,
    Switch,
    Route,
    Link,
    useHistory,
    useRouteMatch,
    Redirect
} from 'react-router-dom'

import Container from 'react-bootstrap/Container'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import Nav from 'react-bootstrap/Nav'
import NavDropdown from 'react-bootstrap/NavDropdown'
import Navbar from 'react-bootstrap/Navbar'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faBone, faCog, faSignInAlt, faSignOutAlt, faUser, faUserCircle } from '@fortawesome/free-solid-svg-icons'

import apiRequest from './utils/apiRequest'
import SessionContext from './context/SessionContext'

import ExplorePage from './pages/dashboard/ExplorePage'
import Page from './pages/Page'
import SubscriptionsPage from './pages/dashboard/SubscriptionsPage'
import FavoritesPage from './pages/dashboard/FavoritesPage'
import SettingsPage from './pages/SettingsPage'
import SignInPage from './pages/SignInPage'
import SignUpPage from './pages/SignUpPage'
import CreatePage from './pages/CreatePage'
import getSession from './utils/getSession'

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

function Authenticated() {
    const [session, setSession] = useContext(SessionContext)
    const history = useHistory()
    const avatarStyle = {
        borderRadius: '50%',
        border: '1px solid #888',
        width: '48px',
        height: '48px'
    }

    const avatar = session.user.getAttribute('avatar') === null
        ? <FontAwesomeIcon icon={faUserCircle} size="2x" />
        : <img style={avatarStyle} src={session.user.getAttribute('avatar')} />
    
    // Navigate to the explore page on logout
    const logout = () => apiRequest('DELETE', '/session')
        .then(() => setSession(null))
        .then(() => history.push('/'))

    return (
        <Nav className="ml-auto">
            <NavDropdown className="text-center" title={avatar}>
                <NavDropdown.ItemText className="text-center">
                    {session.user.getAttribute('username')}
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
    const pages = ['explore', 'subscriptions'/*, 'favorites'*/]
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

function Dashboard() {
    const [session] = useContext(SessionContext)
    const { url } = useRouteMatch()

    return (
        <>
            {session ? <DashboardNav /> : null}

            <Switch>
                <Route path={`${url}/explore`}>
                    <ExplorePage />
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

    // Component state is managed using the useState hook.
    // Use the setSession function to update the session; don't try to mutate
    // the session variable directly!
    const sessionState = useState(null)
    const [session, setSession] = sessionState

    // useEffect hooks into this component's lifecycle. When it is loaded, it
    // runs the provided callback function. If another callback is provided
    // within the callback function, it will run it when the component is
    // unloaded. In other words, inner callback = setup, outer callback =
    // teardown.
    //
    // In this case, we're listening to the session observable. If it's not
    // unsubscribed from when the component is not in use, a memory leak can
    // occur.
    useEffect(() => {
        getSession()
            .then(setSession)
            .catch(() => console.log('Not logged in'))
    }, [])

    // Since this app is served in a directory within the server, the basename
    // for all routes must be specified.
    return (
        <SessionContext.Provider value={sessionState}>
            <Router>
                <Navbar className="mb-4" bg="dark" variant="dark" expand="lg">
                    <Container>
                        <Navbar.Brand as={Link} to="/">
                            {title}<FontAwesomeIcon className="ml-2" icon={faBone} />
                        </Navbar.Brand>

                        <Navbar.Toggle aria-controls="main-nav" />
                        
                        <Navbar.Collapse id="main-nav">
                            {session ? <Authenticated /> : <Anonymous />}
                        </Navbar.Collapse>
                    </Container>
                </Navbar>
                
                <Container>
                    <Switch>
                        <Route path="/dashboard">
                            <Dashboard />
                        </Route>

                        <Route path="//:id">
                            <Page />
                        </Route>

                        <Route path="/">
                            <CreatePage />
                        </Route>

                        <Route path="/signin">
                            <SignInPage />
                        </Route>

                        <Route path="/signup">
                            <SignUpPage />
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
        </SessionContext.Provider>
    )
}

export default Main
