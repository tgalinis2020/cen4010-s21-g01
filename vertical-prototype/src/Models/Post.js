import upload_image from '../api/upload_image'
import Base from './Base'

class Post extends Base
{
    constructor() {
        super('posts')
    }

    // Posts should immediately be associated with an author upon creation.
    create(image, author = null, pets = [], tags = []) {
        let relationships = null

        if (author !== null) {
            relationships['author'] = { data: author.toResourceIdentifier() }
        }

        if (pets.length > 0) {
            relationships['pets'] = { data: pets.map(pet => pet.toResourceIdentifier()) }
        }

        if (tags.length > 0) {
            relationships['tags'] = { data: tags.map(tag => tag.toResourceIdentifier()) }
        }

        return upload_image(image)
            .then(path => this.setAttribute('image', path))
            .then(() => super.create(relationships))
            .then(obj => this.hydrate(obj))
    }
}