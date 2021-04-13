import { useState, useEffect, useContext } from 'react'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'

import debounce from '../../utils/debouce'
import apiRequest from '../../utils/apiRequest'
import uploadImage from '../../utils/uploadImage'
import SessionContext from '../../context/SessionContext'

function SubscriptionsPage() {
    const [session, setSession] = useContext(SessionContext)
    const [subscriptions, setSubscriptions] = useState([])

    const getSubscriptions = () =>  {}

    useEffect(() => {
        apiRequest('GET', `/users/${session.user.id}/subscriptions`)
            .then((res) => res.json())
            .then((res) => res.data).then(setSubscriptions)
    }, [setSubscriptions])

    const removeSubscription = (id) => () => {

    }

    return (
        <>
            
        </>
    )
}

export default SubscriptionsPage