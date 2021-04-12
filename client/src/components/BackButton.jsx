import { useHistory } from 'react-router-dom'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowCircleLeft } from '@fortawesome/free-solid-svg-icons'

function BackButton() {
    const history = useHistory()

    return (
        <FontAwesomeIcon
            style={{ cursor: "pointer" }}
            className="mr-3"
            icon={faArrowCircleLeft}
            size="1x"
            onClick={() => history.replace('/dashboard')} />
    )
}

export default BackButton