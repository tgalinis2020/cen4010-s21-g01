import User from '../Models/User'
import apiRequest from './apiRequest'

function getSession() {
    return apiRequest('GET', '/session')
        .then((res) => res.json())
        .then(({ data }) => data.uid)
        .then((uid) => apiRequest('GET', `/users/${uid}?include=subscriptions`))
        .then((res) => res.json())
        .then(({ data, included }) => ({
            user: new User(data),
            subscriptions: included.map((pet) => pet.id)
        }))
}

export default getSession
