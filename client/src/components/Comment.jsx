import Media from 'react-bootstrap/Media'

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faUserCircle } from '@fortawesome/free-solid-svg-icons'

function Comment({ text, createdAt, author }) {
    const commentStyles = {
        width: '64px',
        height: '64px',
        borderRadius: '50%',
        border: '1px solid #ccc' 
    }
    
    const image = author.avatar
        ? <img style={commentStyles} className="mr-3" src={author.avatar} alt={`${author.username}'s profile picture`} />
        : <FontAwesomeIcon className="mr-3" size="4x" icon={faUserCircle} />

    return (
        <Media className="mb-4">
            {image}

            <Media.Body>
                <small className="text-muted">Posted by {author.username} on {createdAt}</small>
                <p>{text}</p>
            </Media.Body>
        </Media>
    )
}

export default Comment