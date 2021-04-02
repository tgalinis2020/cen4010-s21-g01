import Base from './Base'
import api_request from '../api/api_request'

export default class User extends Base
{
    constructor() {
        super('users')
    }

    /**
     * Creating users is a two-step process: a resource must be created and
     * their password must be set immediately afterward.
     * 
     * @param {string}   password
     */
    create(password) {
        return api_request('POST', `/${this.type}`, {
            type:       this.type,
            attributes: this.attributes,
        }).then(obj => {
            this.hydrate(obj.data)
            return api_request('PUT', `/passwords/${obj.data.id}`, password)
        })
    }

    updatePassword(current, password) {
        return api_request('PATCH', `/passwords/${this.id}`, { current, password })
    }

    login(password) {
        return api_request('POST', '/session', {
            username: this.getAttribute('username'),
            password
        })
    }

    logout() {
        return api_request('DELETE', '/session')
    }

    subscribeTo(pet) {
        return this.updateToManyRelationship('POST', 'subscriptions', [pet])
    }

    unsubscribeFrom(pet) {
        return this.updateToManyRelationship('DELETE', 'subscriptions', [pet])
    }

    addFavorite(post) {
        return this.updateToManyRelationship('POST', 'favorites', [post])
    }

    removeFavorite(post) {
        return this.updateToManyRelationship('DELETE', 'favorites', [post])
    }

    like(post) {
        return this.updateToManyRelationship('POST', 'liked-posts', [post])
    }

    unlike(post) {
        return this.updateToManyRelationship('DELETE', 'liked-posts', [post])
    }
}