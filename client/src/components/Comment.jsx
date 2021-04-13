import Media from 'react-bootstrap/Media'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faUserCircle } from '@fortawesome/free-solid-svg-icons'

import formatDate from '../utils/formatDate'

function Comment({ text, createdAt, author }) {
    const commentStyles = {
        width: '64px',
        height: '64px',
        borderRadius: '50%',
        border: '1px solid #ccc' 
    }
    
    const image = author.avatar
        ? <img style={commentStyles} className="mr-3" src={author.avatar} alt={author.username} />
        : <FontAwesomeIcon className="mr-3" size="3x" icon={faUserCircle} />

    return (
        <Media>
            {image}

            <Media.Body>
                <small className="text-muted">Posted by {author} on {formatDate(createdAt)}</small>
                <p>{text}</p>
            </Media.Body>
        </Media>
    )
}

export default Comment