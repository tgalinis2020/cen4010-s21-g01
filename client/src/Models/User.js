import Base from './Base'
import apiRequest from '../utils/apiRequest'

export default class User extends Base
{
    get type() {
        return 'users'
    }

    /**
     * Creating users is a two-step process: a resource must be created and
     * their password must be set immediately afterward.
     * 
     * @param {string}   password
     */
    create(password) {
        const type = this.type
        const attributes = {}

        for (const attr of this.dirtyAttributes) {
            attributes[attr] = this.attributes[attr]
        }

        return apiRequest('POST', `/${type}`, { type, attributes })
            .then(res => res.json())
            .then(({ data }) => {
                this.hydrate(data)
                return apiRequest('PUT', `/passwords/${data.id}`, password)
            }).then(res => this)
    }

    updatePassword(current, password) {
        return apiRequest('PATCH', `/passwords/${this.id}`, { current, password })
    }

    login(password) {
        return apiRequest('POST', '/session', {
            username: this.getAttribute('username'),
            password
        })
    }

    logout() {
        return apiRequest('DELETE', '/session')
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