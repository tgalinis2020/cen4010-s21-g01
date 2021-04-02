import api_request from '../api/api_request'
import upload_image from '../api/upload_image'
import Base from './Base'
import Tag from './tag'

export default class Post extends Base
{
    constructor() {
        super('posts')
    }

    // Posts should immediately be associated with an author upon creation.
    // Note that provided tags may not exist in the backend so they might have
    // to be created on-the-fly.
    create(image, author = null, pets = [], tags = []) {
        return upload_image(image)
            .then(path => this.setAttribute('image', path))
            .then(() => api_request('GET', `/tags?filter[text][in]=${tags.map(tag => tag.getAttribute('text')).join(',')}`))
            .then(res => res.data)
            .then(data => {
                const tagmap = {}
                const hydratedTags = []
    
                for (const resource of data) {
                    const tag = new Tag()
                    tag.hydrate(resource)
                    tagmap[tag.getAttribute('text')] = tag
                    hydratedTags.append(tag)
                }
    
                const newTags = tags.filter(tag => !(tag.getAttribute('text') in tagmap))
    
                // The API does not support creating entities in bulk.
                // Need to make one request for each new tag.
                if (newTags.length > 0) {
                    return Promise
                        .all(newTags.map(tag => api_request('POST', '/tags', tag.toResourceIdentifier())))
                        .then(results => results.forEach(resource => {
                            const tag = new Tag()
                            tag.hydrate(resource)
                            hydratedTags.append(tag)
                        }))
                        .then(() => hydratedTags)
    
                } else {
    
                    return new Promise((resolve, reject) => resolve(hydratedTags))
                        .then(r => r.json())
                }
            })
            .then(tags => {
                const r = {}
    
                if (author !== null) {
                    r['author'] = { data: author.toResourceIdentifier() }
                }
    
                if (pets.length > 0) {
                    r['pets'] = { data: pets.map(pet => pet.toResourceIdentifier()) }
                }
    
                if (tags.length > 0) {
                    r['tags'] = { data: tags.map(tag => tag.toResourceIdentifier()) }
                }
    
                return Object.keys(relationships).length > 0 ? r : null
            })
            .then(relationships => super.create(relationships))
            .then(resource => this.hydrate(resource))
    }
}