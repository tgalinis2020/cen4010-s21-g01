import apiRequest from '../utils/apiRequest'
import Base from './Base'
import Tag from './Tag'

export default class Post extends Base
{
    get type() {
        return 'posts'
    }

    // Posts should immediately be associated with an author upon creation.
    // Note that provided tags may not exist in the backend so they might have
    // to be created on-the-fly.
    create(author = null, tags = [], pets = []) {
        const tagmap = {}

        // The value in the tag map determines whether or not a tag exists
        // in the backend.
        for (const tag of tags) {
            tagmap[tag] = false
        }

        return apiRequest('GET', `/tags?filter[text][in]=${tags.map(tag => tag.getAttribute('text')).join(',')}`)
            .then(res => res.json())
            .then(res => res.data)
            .then(data => {
                const hydratedTags = []
    
                for (const resource of data) {
                    const tag = new Tag(resource)
                    tagmap[tag.getAttribute('text')] = true
                    hydratedTags.append(tag)
                }
    
                const newTags = tags.filter(tag => !(tag.getAttribute('text') in tagmap))
    
                // The API does not support creating entities in bulk.
                // Need to make one request for each new tag.
                if (newTags.length > 0) {
                    return Promise
                        .all(newTags.map(tag => apiRequest('POST', '/tags', tag.toResourceIdentifier())))
                        .then(results => results.map(res => new Tag(res.json().data)))
                        .then(createdTags => hydratedTags.concat(createdTags))
    
                } else {
                    return new Promise((resolve, reject) => resolve(hydratedTags))
                }
            })
            .then(tags => {
                const r = {}
                let c = 0
    
                if (author !== null) {
                    r['author'] = { data: author.toResourceIdentifier() }
                    c++
                }
    
                if (pets.length > 0) {
                    r['pets'] = { data: pets.map(pet => pet.toResourceIdentifier()) }
                    c++
                }
    
                if (tags.length > 0) {
                    r['tags'] = { data: tags.map(tag => tag.toResourceIdentifier()) }
                    c++
                }
    
                return c > 0 ? r : null
            })
            .then(relationships => super.create(relationships))
            .then(resource => this.hydrate(resource))
    }
}