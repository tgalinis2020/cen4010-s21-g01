import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import Media from 'react-bootstrap/Media'

import apiRequest from './utils/apiRequest'
import formatDate from './utils/formatDate'

function Session({ user, onLogout }) {
    const logout = () => apiRequest('DELETE', '/session')
        .then(onLogout)

    const joinedOn = formatDate(user.getAttribute('createdAt'))

    return (
        <Media>
            <img
                width={64}
                height={64}
                className="mr-3"
                src={user.getAttribute('avatar')}
                alt={user.getAttribute('username') + "'s avatar"}
            />
            <Media.Body>
                <h5>{user.getAttribute('firstName')} {user.getAttribute('lastName')} ({user.getAttribute('username')})</h5>

                <p>Joined on {joinedOn}</p>
                <ButtonGroup>
                    <Button onClick={logout}>Log out</Button>
                </ButtonGroup>
            </Media.Body>
        </Media>
    )
}

export default Session